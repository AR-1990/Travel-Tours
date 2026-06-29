<?php

namespace App\Services\Travelport;

use App\Models\Integration;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

abstract class TravelportSoapClient
{
    /**
     * @return array<string, mixed>
     */
    protected function cfg(): array
    {
        return TravelportIntegrationConfig::merged();
    }

    public function baseUrl(): string
    {
        $c = $this->cfg();
        $override = TravelportSystemService::normalizeHostOnly((string) ($c['base_url_override'] ?? ''));
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

    protected function serviceUrl(string $suffix): string
    {
        $c = $this->cfg();
        $override = trim((string) ($c['base_url_override'] ?? ''));

        if ($override !== '') {
            $override = rtrim($override, '/');
            if (Str::contains($override, '/B2BGateway/')) {
                return preg_replace('#/uAPI/[^/]+$#', '/uAPI/'.basename($suffix), $override)
                    ?: $override;
            }

            return TravelportSystemService::normalizeHostOnly($override).$suffix;
        }

        return $this->baseUrl().$suffix;
    }

    /**
     * @return array{ok: bool, http_status: ?int, message: string, body: string, response_excerpt: ?string}
     */
    protected function postSoap(string $endpoint, string $body): array
    {
        $c = $this->cfg();
        $user = $this->normalizeUsername((string) ($c['username'] ?? ''));
        $pass = (string) ($c['password'] ?? '');

        if ($user === '' || $pass === '') {
            return [
                'ok' => false,
                'http_status' => null,
                'message' => 'Travelport credentials are not configured.',
                'body' => '',
                'response_excerpt' => null,
            ];
        }

        $auth = base64_encode('Universal API/'.$user.':'.$pass);
        $host = parse_url($endpoint, PHP_URL_HOST) ?: 'emea.universal-api.pp.travelport.com';

        try {
            $response = Http::timeout((int) ($c['timeout'] ?? 60))
                ->withHeaders([
                    'Content-Type' => 'text/xml; charset=utf-8',
                    'Accept-Encoding' => 'gzip,deflate',
                    'SOAPAction' => '""',
                    'Authorization' => 'Basic '.$auth,
                    'Host' => $host,
                ])
                ->withBody($body, 'text/xml; charset=utf-8')
                ->post($endpoint);

            return $this->wrapHttpResponse($response);
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'http_status' => null,
                'message' => 'Connection error: '.$e->getMessage(),
                'body' => '',
                'response_excerpt' => null,
            ];
        }
    }

    /**
     * @return array{ok: bool, http_status: ?int, message: string, body: string, response_excerpt: ?string}
     */
    protected function wrapHttpResponse(Response $response): array
    {
        $status = $response->status();
        $text = $response->body();
        $excerpt = Str::limit(preg_replace('/\s+/', ' ', $text), 8000);

        if (! $response->successful()) {
            $fault = $this->extractSoapFaultMessage($text);
            $message = $fault !== null ? $fault : 'HTTP '.$status;
            if ($fault === null && $text !== '') {
                if (preg_match('/<title>([^<]+)<\/title>/i', $text, $m)) {
                    $message .= ' — '.trim(html_entity_decode($m[1], ENT_XML1 | ENT_QUOTES, 'UTF-8'));
                } elseif (preg_match('/"message"\s*:\s*"([^"]+)"/', $text, $m)) {
                    $message .= ' — '.$m[1];
                }
            }

            return [
                'ok' => false,
                'http_status' => $status,
                'message' => $message,
                'body' => $text,
                'response_excerpt' => $excerpt,
            ];
        }

        $fault = $this->extractSoapFaultMessage($text);
        if ($fault !== null) {
            return [
                'ok' => false,
                'http_status' => $status,
                'message' => $fault,
                'body' => $text,
                'response_excerpt' => $excerpt,
            ];
        }

        return [
            'ok' => true,
            'http_status' => $status,
            'message' => 'OK',
            'body' => $text,
            'response_excerpt' => $excerpt,
        ];
    }

    protected function integrationBlockedMessage(): ?string
    {
        $row = Integration::query()
            ->where('slug', Integration::SLUG_TRAVELPORT)
            ->first();

        if ($row && ! $row->is_enabled) {
            return 'Travelport integration is disabled. Enable it under Admin → Integrations.';
        }

        return null;
    }

    protected function normalizeUsername(string $user): string
    {
        $user = trim($user);

        return preg_replace('#^Universal API/#i', '', $user) ?? $user;
    }

    protected function xmlEscape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    protected function schemaVersion(): int
    {
        return (int) ($this->cfg()['schema_major_version'] ?? 32);
    }

    protected function extractSoapFaultMessage(string $xml): ?string
    {
        if (! preg_match('/<(?:[\w]+:)?Fault\b/i', $xml)) {
            return null;
        }

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

        return 'Unknown SOAP fault (see response)';
    }
}
