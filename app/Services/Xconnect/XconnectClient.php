<?php

namespace App\Services\Xconnect;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class XconnectClient
{
    /**
     * @return array<string, mixed>
     */
    protected function config(): array
    {
        return XconnectIntegrationConfig::merged();
    }

    public function baseUrl(): string
    {
        return XconnectIntegrationConfig::baseUrl();
    }

    public function token(): string
    {
        return (string) ($this->config()['token'] ?? '');
    }

    protected function isConfigured(): bool
    {
        return XconnectIntegrationConfig::isReadyForHotels();
    }

    public static function normalizeHostOnly(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        // Strip trailing path segments if someone pastes a full endpoint.
        if (preg_match('#^(https?://[^/]+)#i', $value, $m)) {
            return rtrim($m[1], '/');
        }

        return rtrim($value, '/');
    }

    /**
     * Connectivity check: Countries (lightweight Token-only call).
     *
     * @return array{ok: bool, message: string, http_status?: int|null, count?: int, response_excerpt?: string, raw?: mixed}
     */
    public function ping(): array
    {
        $result = $this->post('Countries', []);
        if (! $result['ok']) {
            return [
                'ok' => false,
                'message' => $result['message'],
                'http_status' => $result['http_status'] ?? null,
                'response_excerpt' => $result['response_excerpt'] ?? '',
                'raw' => $result['raw'] ?? null,
            ];
        }

        $raw = $result['raw'] ?? null;
        $count = is_array($raw) ? count($raw) : 0;

        return [
            'ok' => true,
            'message' => $count > 0
                ? "Xconnect OK — Countries returned {$count} rows."
                : 'Xconnect OK — Countries responded without API errors.',
            'http_status' => $result['http_status'] ?? 200,
            'count' => $count,
            'response_excerpt' => $result['response_excerpt'] ?? '',
            'raw' => $raw,
        ];
    }

    /**
     * POST /api/xconnect/{endpoint}
     *
     * @param  array<string, mixed>  $requestBody  Payload under "Request" (omit for Token-only endpoints)
     * @param  array<string, mixed>  $advanced
     * @return array{ok: bool, message: string, http_status?: int|null, raw?: mixed, response_excerpt?: string, errors?: array<int, mixed>}
     */
    public function post(string $endpoint, array $requestBody = [], array $advanced = []): array
    {
        if ($blocked = $this->integrationBlockedMessage()) {
            return [
                'ok' => false,
                'message' => $blocked,
                'http_status' => null,
                'response_excerpt' => '',
            ];
        }

        if (! $this->isConfigured()) {
            return [
                'ok' => false,
                'message' => 'Xconnect is not ready. Set Token and Base URL under Admin → Integrations → Xconnect.',
                'http_status' => null,
                'response_excerpt' => '',
            ];
        }

        $endpoint = trim($endpoint, '/');
        $c = $this->config();
        $currency = (string) ($advanced['Currency'] ?? $c['default_currency'] ?? 'USD');
        $ip = (string) ($advanced['CustomerIpAddress'] ?? request()?->ip() ?? '127.0.0.1');

        $payload = [
            'Token' => $this->token(),
        ];
        if ($requestBody !== []) {
            $payload['Request'] = $requestBody;
        }

        // Countries is Token-only in the published collection; other ops include AdvancedOptions.
        $includeAdvanced = $requestBody !== [] || strcasecmp($endpoint, 'Countries') !== 0;
        if ($includeAdvanced) {
            $payload['AdvancedOptions'] = array_merge([
                'Currency' => $currency,
                'CustomerIpAddress' => $ip,
            ], $advanced);
        }

        $url = $this->url('/api/xconnect/'.$endpoint);

        try {
            $response = $this->http()->post($url, $payload);
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'message' => 'Xconnect request failed: '.$e->getMessage(),
                'http_status' => null,
                'response_excerpt' => '',
            ];
        }

        return $this->normalizeResponse($response, $endpoint);
    }

    protected function url(string $path): string
    {
        return $this->baseUrl().'/'.ltrim($path, '/');
    }

    protected function http()
    {
        $timeout = (int) ($this->config()['timeout'] ?? 90);

        return Http::acceptJson()
            ->asJson()
            ->timeout($timeout)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ]);
    }

    /**
     * @return array{ok: bool, message: string, http_status?: int|null, raw?: mixed, response_excerpt?: string, errors?: array<int, mixed>}
     */
    protected function normalizeResponse(Response $response, string $endpoint): array
    {
        $body = $response->body();
        $excerpt = $this->excerpt($body);
        $json = $this->jsonOrNull($response);
        $errors = $this->extractErrors($json);
        $httpOk = $response->successful();

        if (! $httpOk) {
            return [
                'ok' => false,
                'message' => $this->errorMessage($errors, "Xconnect {$endpoint} HTTP ".$response->status()),
                'http_status' => $response->status(),
                'raw' => $json ?? $body,
                'response_excerpt' => $excerpt,
                'errors' => $errors,
            ];
        }

        if ($errors !== []) {
            return [
                'ok' => false,
                'message' => $this->errorMessage($errors, "Xconnect {$endpoint} returned an API error."),
                'http_status' => $response->status(),
                'raw' => $json,
                'response_excerpt' => $excerpt,
                'errors' => $errors,
            ];
        }

        return [
            'ok' => true,
            'message' => "Xconnect {$endpoint} OK.",
            'http_status' => $response->status(),
            'raw' => $json,
            'response_excerpt' => $excerpt,
            'errors' => [],
        ];
    }

    /**
     * @return list<mixed>
     */
    protected function extractErrors(mixed $json): array
    {
        if (! is_array($json)) {
            return [];
        }

        $error = $json['Error'] ?? null;
        if ($error === null || $error === [] || $error === '') {
            return [];
        }

        return is_array($error) ? array_values($error) : [$error];
    }

    /**
     * @param  list<mixed>  $errors
     */
    protected function errorMessage(array $errors, string $fallback): string
    {
        if ($errors === []) {
            return $fallback;
        }

        $parts = [];
        foreach ($errors as $err) {
            if (is_string($err)) {
                $parts[] = $err;

                continue;
            }
            if (! is_array($err)) {
                continue;
            }
            $code = (string) ($err['ErrorCode'] ?? $err['Code'] ?? '');
            $desc = (string) ($err['ErrorDescription'] ?? $err['Description'] ?? $err['Message'] ?? '');
            $parts[] = trim($code.($code && $desc ? ': ' : '').$desc) ?: json_encode($err);
        }

        $joined = implode('; ', array_filter($parts));

        return $joined !== '' ? $joined : $fallback;
    }

    protected function jsonOrNull(Response $response): mixed
    {
        try {
            return $response->json();
        } catch (\Throwable) {
            return null;
        }
    }

    protected function excerpt(string $body, int $max = 800): string
    {
        $body = trim($body);
        if (strlen($body) <= $max) {
            return $body;
        }

        return substr($body, 0, $max).'…';
    }

    protected function integrationBlockedMessage(): ?string
    {
        $row = \App\Models\Integration::query()
            ->where('slug', \App\Models\Integration::SLUG_XCONNECT)
            ->first();

        if ($row && ! $row->is_enabled) {
            return 'Xconnect integration is disabled in Admin → Integrations.';
        }

        return null;
    }
}
