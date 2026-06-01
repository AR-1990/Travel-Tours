<?php

namespace App\Services\Travelport;

use App\Models\Integration;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TravelportSystemService
{
    private const SYSTEM_SERVICE_SUFFIX = '/B2BGateway/connect/uAPI/SystemService';

    /**
     * @return array<string, mixed>
     */
    private function cfg(): array
    {
        return TravelportIntegrationConfig::merged();
    }

    public function baseUrl(): string
    {
        $c = $this->cfg();
        $override = self::normalizeHostOnly((string) ($c['base_url_override'] ?? ''));
        if ($override !== '') {
            return $override;
        }

        $region = strtolower((string) ($c['region'] ?? 'emea'));
        $env = strtolower((string) ($c['environment'] ?? 'pp'));

        if (! in_array($region, ['emea', 'americas', 'apac'], true)) {
            $region = 'emea';
        }

        $subdomain = $region.'.universal-api';
        if ($env === 'production') {
            return 'https://'.$subdomain.'.travelport.com';
        }

        return 'https://'.$subdomain.'.pp.travelport.com';
    }

    public function systemServiceUrl(): string
    {
        $c = $this->cfg();
        $override = trim((string) ($c['base_url_override'] ?? ''));

        if ($override !== '') {
            $override = rtrim($override, '/');
            if (Str::contains($override, '/B2BGateway/') || Str::endsWith($override, 'SystemService')) {
                return $override;
            }

            return self::normalizeHostOnly($override).self::SYSTEM_SERVICE_SUFFIX;
        }

        return $this->baseUrl().self::SYSTEM_SERVICE_SUFFIX;
    }

    public static function normalizeHostOnly(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        if (preg_match('#^(https?://[^/]+)#i', $url, $m)) {
            return rtrim($m[1], '/');
        }

        return rtrim($url, '/');
    }

    /**
     * Sample Ping XML for Travelport SOAP test tool (placeholders only).
     */
    public function samplePingXml(): string
    {
        $c = $this->cfg();
        $targetBranch = trim((string) ($c['target_branch'] ?? ''));
        $pcc = trim((string) ($c['branch'] ?? ''));
        $gds = trim((string) ($c['gds'] ?? '1G'));
        $originApp = trim((string) ($c['origin_application'] ?? 'UAPI')) ?: 'UAPI';
        $schema = (int) ($c['schema_major_version'] ?? 52);

        return $this->buildPingXml($schema, $schema, $originApp, $targetBranch, $pcc, $gds, 'doc', false);
    }

    /**
     * @return array{ok: bool, http_status: ?int, message: string, response_excerpt: ?string}
     */
    public function ping(): array
    {
        $row = Integration::query()
            ->where('slug', Integration::SLUG_TRAVELPORT)
            ->first();

        if ($row && ! $row->is_enabled) {
            return [
                'ok' => false,
                'http_status' => null,
                'message' => 'Travelport is disabled under Admin → Integrations.',
                'response_excerpt' => null,
            ];
        }

        $c = $this->cfg();
        $user = $this->normalizeUsername((string) ($c['username'] ?? ''));
        $pass = (string) ($c['password'] ?? '');

        if ($user === '' || $pass === '') {
            return [
                'ok' => false,
                'http_status' => null,
                'message' => 'Add Travelport username and password under Admin → Integrations.',
                'response_excerpt' => null,
            ];
        }

        $targetBranch = trim((string) ($c['target_branch'] ?? ''));
        $pcc = trim((string) ($c['branch'] ?? ''));
        $gds = trim((string) ($c['gds'] ?? ''));
        $originApp = trim((string) ($c['origin_application'] ?? 'UAPI')) ?: 'UAPI';
        $endpoint = $this->systemServiceUrl();
        $preferred = (int) ($c['schema_major_version'] ?? 52);

        $pairs = $this->schemaPairs($preferred);
        $variants = ['doc', 'no-target', 'standard', 'with-target'];

        $lastResult = null;

        foreach ($pairs as $pair) {
            $sys = $pair['system'];
            $com = $pair['common'];

            foreach ($variants as $variant) {
                $useTarget = $variant === 'with-target' || $variant === 'standard';
                if ($variant === 'no-target' || $variant === 'doc') {
                    $useTarget = false;
                }
                if ($useTarget && $targetBranch === '') {
                    continue;
                }

                $body = $this->buildPingXml($sys, $com, $originApp, $targetBranch, $pcc, $gds, $variant, $useTarget);
                $label = $variant.' (system v'.$sys.', common v'.$com.')';
                $result = $this->sendPing($c, $user, $pass, $body, $endpoint, $label);

                if ($result['ok']) {
                    return $result;
                }

                $lastResult = $result;

                if (! $this->isMarshallingOrSchemaFault($result['message'] ?? '')) {
                    return $result;
                }
            }
        }

        $hint = 'If every attempt shows "marshalling", test the same credentials in Travelport\'s SOAP/XML test tool. '
            .'Try Ping without TargetBranch first (see sample XML below). '
            .'Verify PCC and Target branch are not swapped. Endpoint: '.$endpoint;

        if ($lastResult !== null) {
            $lastResult['message'] .= ' '.$hint;

            return $lastResult;
        }

        return [
            'ok' => false,
            'http_status' => null,
            'message' => 'Ping failed. '.$hint,
            'response_excerpt' => null,
        ];
    }

    /**
     * @return list<array{system: int, common: int}>
     */
    private function schemaPairs(int $preferred): array
    {
        $raw = [
            ['system' => $preferred, 'common' => $preferred],
            ['system' => 52, 'common' => 52],
            ['system' => 37, 'common' => 37],
            ['system' => 32, 'common' => 32],
            ['system' => 9, 'common' => 28],
        ];

        $seen = [];
        $pairs = [];

        foreach ($raw as $pair) {
            $key = $pair['system'].'-'.$pair['common'];
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $pairs[] = $pair;
        }

        return $pairs;
    }

    private function buildPingXml(
        int $systemVer,
        int $commonVer,
        string $originApp,
        string $targetBranch,
        string $pcc,
        string $gds,
        string $variant,
        bool $useTargetBranch
    ): string {
        $systemNs = 'http://www.travelport.com/schema/system_v'.$systemVer.'_0';
        $commonNs = 'http://www.travelport.com/schema/common_v'.$commonVer.'_0';
        $traceId = $this->xmlEscape('laravel-ping-'.Str::lower(Str::random(8)));
        $originApp = $this->xmlEscape($originApp);
        $payload = $variant === 'doc'
            ? 'this is a test for testing'
            : 'Travelport connectivity test';

        $targetAttr = '';
        if ($useTargetBranch && $targetBranch !== '') {
            $targetAttr = ' TargetBranch="'.$this->xmlEscape($targetBranch).'"';
        }

        $overridePcc = '';
        if ($variant === 'with-target' && $pcc !== '' && $gds !== '') {
            $overridePcc = "\n      <com:OverridePCC ProviderCode=\"".$this->xmlEscape($gds)
                .'" PseudoCityCode="'.$this->xmlEscape($pcc).'"/>';
        }

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
  <soapenv:Header/>
  <soapenv:Body>
    <sys:PingReq TraceId="{$traceId}"{$targetAttr} xmlns:sys="{$systemNs}" xmlns:com="{$commonNs}">
      <com:BillingPointOfSaleInfo OriginApplication="{$originApp}"/>{$overridePcc}
      <sys:Payload>{$this->xmlEscape($payload)}</sys:Payload>
    </sys:PingReq>
  </soapenv:Body>
</soapenv:Envelope>
XML;
    }

    /**
     * @param  array<string, mixed>  $c
     * @return array{ok: bool, http_status: ?int, message: string, response_excerpt: ?string}
     */
    private function sendPing(array $c, string $user, string $pass, string $body, string $endpoint, string $label): array
    {
        $auth = base64_encode('Universal API/'.$user.':'.$pass);
        $host = parse_url($endpoint, PHP_URL_HOST) ?: 'emea.universal-api.pp.travelport.com';

        try {
            $response = Http::timeout((int) ($c['timeout'] ?? 30))
                ->withHeaders([
                    'Content-Type' => 'text/xml; charset=utf-8',
                    'Accept-Encoding' => 'gzip,deflate',
                    'SOAPAction' => '""',
                    'Authorization' => 'Basic '.$auth,
                    'Host' => $host,
                ])
                ->withBody($body, 'text/xml; charset=utf-8')
                ->post($endpoint);

            return $this->interpretPingResponse($response, $label);
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'http_status' => null,
                'message' => 'Connection error ('.$label.'): '.$e->getMessage(),
                'response_excerpt' => null,
            ];
        }
    }

    /**
     * @return array{ok: bool, http_status: ?int, message: string, response_excerpt: ?string}
     */
    private function interpretPingResponse(Response $response, string $label): array
    {
        $status = $response->status();
        $text = $response->body();
        $excerpt = Str::limit(preg_replace('/\s+/', ' ', $text), 3000);

        if (! $response->successful()) {
            return [
                'ok' => false,
                'http_status' => $status,
                'message' => 'HTTP '.$status.' ('.$label.')',
                'response_excerpt' => $excerpt,
            ];
        }

        $fault = $this->extractSoapFaultMessage($text);
        if ($fault !== null) {
            return [
                'ok' => false,
                'http_status' => $status,
                'message' => 'SOAP fault ('.$label.'): '.$fault,
                'response_excerpt' => $excerpt,
            ];
        }

        if (Str::contains($text, 'PingRsp')) {
            return [
                'ok' => true,
                'http_status' => $status,
                'message' => 'Ping succeeded ('.$label.').',
                'response_excerpt' => $excerpt,
            ];
        }

        return [
            'ok' => false,
            'http_status' => $status,
            'message' => 'No PingRsp ('.$label.')',
            'response_excerpt' => $excerpt,
        ];
    }

    private function normalizeUsername(string $user): string
    {
        $user = trim($user);

        return preg_replace('#^Universal API/#i', '', $user) ?? $user;
    }

    private function isMarshallingOrSchemaFault(string $message): bool
    {
        return Str::contains(Str::lower($message), [
            'marshal',
            'schema',
            'namespace',
            'invalid content',
            'cvc-',
            'invalid api',
        ]);
    }

    private function xmlEscape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function extractSoapFaultMessage(string $xml): ?string
    {
        $parts = [];

        if (preg_match('/<(?:[\w]+:)?faultstring[^>]*>([^<]+)</i', $xml, $m)) {
            $parts[] = trim(html_entity_decode($m[1], ENT_XML1 | ENT_QUOTES, 'UTF-8'));
        }

        if (preg_match('/<(?:[\w]+:)?Description[^>]*>([^<]+)</i', $xml, $m)) {
            $parts[] = trim(html_entity_decode($m[1], ENT_XML1 | ENT_QUOTES, 'UTF-8'));
        }

        if (preg_match_all('/<(?:[\w]+:)?Text[^>]*>([^<]+)</i', $xml, $m)) {
            foreach ($m[1] as $t) {
                $t = trim(html_entity_decode($t, ENT_XML1 | ENT_QUOTES, 'UTF-8'));
                if ($t !== '') {
                    $parts[] = $t;
                }
            }
        }

        if ($parts !== []) {
            return implode(' | ', array_unique($parts));
        }

        if (Str::contains($xml, 'Fault') || Str::contains($xml, ':Fault')) {
            return 'Unknown fault (see response excerpt)';
        }

        return null;
    }
}
