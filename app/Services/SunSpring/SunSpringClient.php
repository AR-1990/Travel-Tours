<?php

namespace App\Services\SunSpring;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SunSpringClient
{
    private const TOKEN_CACHE_KEY = 'sunspring.authorization_token';

    /**
     * @return array<string, mixed>
     */
    protected function config(): array
    {
        return SunSpringIntegrationConfig::merged();
    }

    public function baseUrl(): string
    {
        return SunSpringIntegrationConfig::baseUrl();
    }

    /**
     * POST /api/v2/accounting/getAuthorizeToken
     * Auth is via headers: api-username, api-password (empty body).
     * Token is returned at data.authorization and must be sent as Authorization on later calls.
     *
     * @return array{ok: bool, message: string, http_status?: int|null, token?: string|null, expired_at?: int|null, response_excerpt?: string, raw?: mixed}
     */
    public function authorizeToken(bool $forceRefresh = false): array
    {
        if ($blocked = $this->integrationBlockedMessage()) {
            return [
                'ok' => false,
                'message' => $blocked,
                'http_status' => null,
                'token' => null,
                'response_excerpt' => '',
            ];
        }

        if (! SunSpringIntegrationConfig::isReadyForAir()) {
            return [
                'ok' => false,
                'message' => 'SunSpring is not ready. Set username and password under Admin → Integrations → SunSpring.',
                'http_status' => null,
                'token' => null,
                'response_excerpt' => '',
            ];
        }

        if (! $forceRefresh) {
            $cached = Cache::get(self::TOKEN_CACHE_KEY);
            if (is_array($cached) && ! empty($cached['token'])) {
                return [
                    'ok' => true,
                    'message' => 'Using cached SunSpring authorize token.',
                    'http_status' => 200,
                    'token' => (string) $cached['token'],
                    'expired_at' => isset($cached['expired_at']) ? (int) $cached['expired_at'] : null,
                    'response_excerpt' => '',
                    'raw' => $cached,
                ];
            }
        }

        $c = $this->config();
        $username = (string) ($c['username'] ?? '');
        $password = (string) ($c['password'] ?? '');

        try {
            // Swagger: headers api-username + api-password, empty body.
            $response = $this->http()
                ->withHeaders([
                    'api-username' => $username,
                    'api-password' => $password,
                ])
                ->withBody('', 'application/json')
                ->post($this->url('/api/v2/accounting/getAuthorizeToken'));
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'message' => 'SunSpring authorize request failed: '.$e->getMessage(),
                'http_status' => null,
                'token' => null,
                'response_excerpt' => '',
            ];
        }

        $json = $this->jsonOrNull($response);
        $token = $this->extractToken($json);
        $expiredAt = $this->extractExpiredAt($json);
        $excerpt = $this->excerpt($response->body());
        $err = $this->extractError($json);
        $statusOk = $response->successful()
            && strtolower((string) data_get($json, 'status', '')) === 'success'
            && (int) data_get($json, 'err', 1) === 0;

        if ($statusOk && $token !== null && $token !== '') {
            $ttl = $this->tokenCacheTtl($expiredAt);
            Cache::put(self::TOKEN_CACHE_KEY, [
                'token' => $token,
                'expired_at' => $expiredAt,
            ], $ttl);

            return [
                'ok' => true,
                'message' => 'Authorize token received from SunSpring.',
                'http_status' => $response->status(),
                'token' => $token,
                'expired_at' => $expiredAt,
                'response_excerpt' => $excerpt,
                'raw' => $json,
            ];
        }

        Cache::forget(self::TOKEN_CACHE_KEY);

        return [
            'ok' => false,
            'message' => $err ?: ('SunSpring authorize failed (HTTP '.$response->status().').'),
            'http_status' => $response->status(),
            'token' => $token,
            'expired_at' => $expiredAt,
            'response_excerpt' => $excerpt,
            'raw' => $json,
        ];
    }

    /**
     * Authenticated POST. Uses Authorization header with the JWT from getAuthorizeToken.
     *
     * @param  array<string, mixed>  $body
     * @return array{ok: bool, message: string, http_status?: int|null, data?: mixed, response_excerpt?: string}
     */
    public function post(string $path, array $body = [], ?string $token = null): array
    {
        if ($blocked = $this->integrationBlockedMessage()) {
            return ['ok' => false, 'message' => $blocked, 'http_status' => null];
        }

        if ($token === null || $token === '') {
            $auth = $this->authorizeToken();
            if (! ($auth['ok'] ?? false)) {
                return [
                    'ok' => false,
                    'message' => $auth['message'] ?? 'Could not authorize with SunSpring.',
                    'http_status' => $auth['http_status'] ?? null,
                    'response_excerpt' => $auth['response_excerpt'] ?? '',
                ];
            }
            $token = (string) ($auth['token'] ?? '');
        }

        try {
            $response = $this->http()
                ->withHeaders([
                    // Swagger Authorize: JWT from data.authorization as Bearer token.
                    'Authorization' => 'Bearer '.$token,
                ])
                ->post($this->url($path), $body);
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'message' => 'SunSpring request failed: '.$e->getMessage(),
                'http_status' => null,
            ];
        }

        // If token expired mid-flight, refresh once and retry.
        if ($response->status() === 401 || $this->isAuthError($this->jsonOrNull($response))) {
            Cache::forget(self::TOKEN_CACHE_KEY);
            $auth = $this->authorizeToken(true);
            if ($auth['ok'] ?? false) {
                $token = (string) ($auth['token'] ?? '');
                try {
                    $response = $this->http()
                        ->withHeaders(['Authorization' => 'Bearer '.$token])
                        ->post($this->url($path), $body);
                } catch (\Throwable $e) {
                    return [
                        'ok' => false,
                        'message' => 'SunSpring request failed after token refresh: '.$e->getMessage(),
                        'http_status' => null,
                    ];
                }
            }
        }

        $json = $this->jsonOrNull($response);
        $excerpt = $this->excerpt($response->body());
        $err = $this->extractError($json);
        $apiFailed = $this->isApiFailure($json);

        if ($response->successful() && $err === null && ! $apiFailed) {
            return [
                'ok' => true,
                'message' => 'OK',
                'http_status' => $response->status(),
                'data' => $json,
                'response_excerpt' => $excerpt,
            ];
        }

        return [
            'ok' => false,
            'message' => $err ?: ('SunSpring request failed (HTTP '.$response->status().').'),
            'http_status' => $response->status(),
            'data' => $json,
            'response_excerpt' => $excerpt,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $json
     */
    protected function isApiFailure(?array $json): bool
    {
        if ($json === null) {
            return false;
        }

        $status = strtolower((string) ($json['status'] ?? ''));
        if ($status === 'error') {
            return true;
        }
        if ($status === 'success') {
            return false;
        }

        $err = $json['err'] ?? null;
        if (is_array($err) && array_is_list($err)) {
            foreach ($err as $item) {
                if (is_array($item) && (int) ($item['code'] ?? 0) !== 0) {
                    return true;
                }
            }

            return false;
        }

        if (is_numeric($err) && (int) $err !== 0) {
            return true;
        }

        return false;
    }

    /**
     * Connection test used by Admin → Integrations (same role as Travelport Ping).
     *
     * @return array{ok: bool, message: string, http_status?: int|null, response_excerpt?: string, token_preview?: string|null, expired_at?: int|null}
     */
    public function ping(): array
    {
        $result = $this->authorizeToken(true);
        $token = (string) ($result['token'] ?? '');

        return [
            'ok' => (bool) ($result['ok'] ?? false),
            'message' => (string) ($result['message'] ?? ''),
            'http_status' => $result['http_status'] ?? null,
            'response_excerpt' => (string) ($result['response_excerpt'] ?? ''),
            'token_preview' => $token !== '' ? substr($token, 0, 12).'…'.substr($token, -6) : null,
            'expired_at' => $result['expired_at'] ?? null,
        ];
    }

    /**
     * Lightweight credit check (optional secondary connectivity test).
     *
     * @return array{ok: bool, message: string, http_status?: int|null, data?: mixed, response_excerpt?: string}
     */
    public function myCredit(): array
    {
        return $this->post('/api/v2/accounting/myCredit', []);
    }

    public static function normalizeHostOnly(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $value = rtrim($value, '/');
        if (! str_starts_with($value, 'http://') && ! str_starts_with($value, 'https://')) {
            $value = 'https://'.$value;
        }

        $parts = parse_url($value);
        if (! is_array($parts) || empty($parts['host'])) {
            return $value;
        }

        $scheme = $parts['scheme'] ?? 'https';
        $host = $parts['host'];
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';

        return $scheme.'://'.$host.$port;
    }

    public static function clearTokenCache(): void
    {
        Cache::forget(self::TOKEN_CACHE_KEY);
    }

    protected function url(string $path): string
    {
        return $this->baseUrl().'/'.ltrim($path, '/');
    }

    protected function http(): \Illuminate\Http\Client\PendingRequest
    {
        $timeout = max(5, min(120, (int) ($this->config()['timeout'] ?? 60)));

        return Http::timeout($timeout)
            ->acceptJson()
            ->asJson()
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]);
    }

    protected function integrationBlockedMessage(): ?string
    {
        $row = \App\Models\Integration::query()
            ->where('slug', \App\Models\Integration::SLUG_SUNSPRING)
            ->first();

        if ($row && ! $row->is_enabled) {
            return 'SunSpring integration is disabled in Admin → Integrations.';
        }

        return null;
    }

    /**
     * @param  array<string, mixed>|null  $json
     */
    protected function extractToken(?array $json): ?string
    {
        if ($json === null) {
            return null;
        }

        $candidates = [
            data_get($json, 'data.authorization'),
            data_get($json, 'Data.authorization'),
            data_get($json, 'data.Authorization'),
            $json['authorization'] ?? null,
            $json['Authorization'] ?? null,
            $json['Token'] ?? null,
            $json['token'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return trim($candidate);
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>|null  $json
     */
    protected function extractExpiredAt(?array $json): ?int
    {
        if ($json === null) {
            return null;
        }

        $value = data_get($json, 'data.expired_at', data_get($json, 'Data.expired_at'));
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    protected function tokenCacheTtl(?int $expiredAt): int
    {
        if ($expiredAt !== null && $expiredAt > time()) {
            // Refresh a few minutes before expiry.
            return max(60, $expiredAt - time() - 300);
        }

        // Default ~7 hours if API omits expired_at (sandbox tokens looked ~8h).
        return 7 * 60 * 60;
    }

    protected function isAuthError(?array $json): bool
    {
        if ($json === null) {
            return false;
        }

        $message = strtolower((string) ($json['message'] ?? ''));
        if (str_contains($message, 'authentication')) {
            return true;
        }

        $err = $json['err'] ?? null;
        if (is_array($err)) {
            foreach ($err as $item) {
                if ((int) data_get($item, 'code') === 60105) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>|null  $json
     */
    protected function extractError(?array $json): ?string
    {
        if ($json === null) {
            return null;
        }

        if (strtolower((string) data_get($json, 'status', '')) === 'success' && (int) data_get($json, 'err', 0) === 0) {
            return null;
        }

        $errList = $json['err'] ?? null;
        if (is_array($errList) && $errList !== [] && array_is_list($errList)) {
            $parts = [];
            foreach ($errList as $item) {
                if (is_array($item)) {
                    $msg = (string) ($item['msg'] ?? $item['message'] ?? '');
                    $code = $item['code'] ?? null;
                    if ($msg !== '') {
                        $parts[] = $code ? "[{$code}] {$msg}" : $msg;
                    }
                } elseif (is_string($item) && $item !== '') {
                    $parts[] = $item;
                }
            }
            if ($parts !== []) {
                return implode('; ', $parts);
            }
        }

        $candidates = [
            data_get($json, 'data.message'),
            data_get($json, 'Data.message'),
            $json['Message'] ?? null,
            $json['message'] ?? null,
            $json['Error'] ?? null,
            $json['error'] ?? null,
            is_scalar($json['err'] ?? null) && (int) $json['err'] !== 0 ? 'Error code: '.(string) $json['err'] : null,
            data_get($json, 'Err.Message'),
            data_get($json, 'Errors.0.Message'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== '') {
                return trim($candidate);
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function jsonOrNull(Response $response): ?array
    {
        $json = $response->json();

        return is_array($json) ? $json : null;
    }

    protected function excerpt(string $body, int $max = 4000): string
    {
        $body = trim($body);
        if (mb_strlen($body) <= $max) {
            return $body;
        }

        return mb_substr($body, 0, $max).'…';
    }
}
