<?php

namespace App\Services\DowntownTravel;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DowntownTravelHotelsClient
{
    private const TOKEN_CACHE_KEY = 'downtown_travel_hotels.oauth_tokens';

    /**
     * @return array<string, mixed>
     */
    protected function config(): array
    {
        return DowntownTravelHotelsIntegrationConfig::merged();
    }

    public function ssoBaseUrl(): string
    {
        return DowntownTravelHotelsIntegrationConfig::ssoBaseUrl();
    }

    public function hotelsBaseUrl(): string
    {
        return DowntownTravelHotelsIntegrationConfig::hotelsBaseUrl();
    }

    public static function normalizeHostOnly(string $value): string
    {
        return DowntownTravelClient::normalizeHostOnly($value);
    }

    public static function clearTokenCache(): void
    {
        Cache::forget(self::TOKEN_CACHE_KEY);
    }

    /**
     * @return array{ok: bool, message: string, http_status?: int|null, token?: string|null, refresh_token?: string|null, expires_in?: int|null, token_preview?: string|null, response_excerpt?: string, raw?: mixed}
     */
    public function getToken(bool $forceRefresh = false): array
    {
        if (! DowntownTravelHotelsIntegrationConfig::isReadyForHotels()) {
            return [
                'ok' => false,
                'message' => 'Downtown Travel Hotels is not ready. Set client_id, client_secret, username, and password under Admin → Integrations → Downtown Travel Hotels.',
                'http_status' => null,
                'token' => null,
                'response_excerpt' => '',
            ];
        }

        if (! $forceRefresh) {
            $cached = Cache::get(self::TOKEN_CACHE_KEY);
            if (is_array($cached) && ! empty($cached['access_token'])) {
                return [
                    'ok' => true,
                    'message' => 'Using cached Downtown Travel Hotels access token.',
                    'http_status' => 200,
                    'token' => (string) $cached['access_token'],
                    'refresh_token' => isset($cached['refresh_token']) ? (string) $cached['refresh_token'] : null,
                    'expires_in' => isset($cached['expires_in']) ? (int) $cached['expires_in'] : null,
                    'token_preview' => $this->tokenPreview((string) $cached['access_token']),
                    'response_excerpt' => '',
                    'raw' => $cached,
                ];
            }
        }

        $c = $this->config();
        $url = $this->ssoBaseUrl().'/oauth/token';

        try {
            $response = $this->http()
                ->withBasicAuth((string) ($c['client_id'] ?? ''), (string) ($c['client_secret'] ?? ''))
                ->asForm()
                ->post($url, [
                    'grant_type' => 'password',
                    'username' => (string) ($c['username'] ?? ''),
                    'password' => (string) ($c['password'] ?? ''),
                ]);
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'message' => 'Downtown Travel Hotels token request failed: '.$e->getMessage(),
                'http_status' => null,
                'token' => null,
                'response_excerpt' => '',
            ];
        }

        $json = $this->jsonOrNull($response);
        $token = is_array($json) ? trim((string) ($json['access_token'] ?? '')) : '';
        $refresh = is_array($json) ? trim((string) ($json['refresh_token'] ?? '')) : '';
        $expiresIn = is_array($json) && isset($json['expires_in']) ? (int) $json['expires_in'] : null;
        $excerpt = $this->excerpt($response->body());

        if ($response->successful() && $token !== '') {
            $ttl = $expiresIn !== null && $expiresIn > 60
                ? max(60, $expiresIn - 60)
                : 3500;
            Cache::put(self::TOKEN_CACHE_KEY, [
                'access_token' => $token,
                'refresh_token' => $refresh !== '' ? $refresh : null,
                'expires_in' => $expiresIn,
                'obtained_at' => time(),
            ], $ttl);

            return [
                'ok' => true,
                'message' => 'Downtown Travel Hotels access token received.',
                'http_status' => $response->status(),
                'token' => $token,
                'refresh_token' => $refresh !== '' ? $refresh : null,
                'expires_in' => $expiresIn,
                'token_preview' => $this->tokenPreview($token),
                'response_excerpt' => $excerpt,
                'raw' => $json,
            ];
        }

        self::clearTokenCache();

        $err = is_array($json)
            ? (string) ($json['error_description'] ?? $json['error'] ?? $json['message'] ?? '')
            : '';

        return [
            'ok' => false,
            'message' => $err !== ''
                ? $err
                : ('Downtown Travel Hotels token request failed (HTTP '.$response->status().').'),
            'http_status' => $response->status(),
            'token' => null,
            'response_excerpt' => $excerpt,
            'raw' => $json,
        ];
    }

    /**
     * @return array{ok: bool, message: string, http_status?: int|null, token_preview?: string|null, expires_in?: int|null, response_excerpt?: string}
     */
    public function ping(): array
    {
        $result = $this->getToken(true);

        return [
            'ok' => (bool) ($result['ok'] ?? false),
            'message' => (string) ($result['message'] ?? ''),
            'http_status' => $result['http_status'] ?? null,
            'token_preview' => $result['token_preview'] ?? null,
            'expires_in' => $result['expires_in'] ?? null,
            'response_excerpt' => (string) ($result['response_excerpt'] ?? ''),
        ];
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array{ok: bool, message: string, http_status?: int|null, data?: mixed, response_excerpt?: string}
     */
    public function postHotels(string $path, array $body = [], ?string $token = null): array
    {
        if ($token === null || $token === '') {
            $auth = $this->getToken();
            if (! ($auth['ok'] ?? false)) {
                return [
                    'ok' => false,
                    'message' => $auth['message'] ?? 'Could not authorize with Downtown Travel Hotels.',
                    'http_status' => $auth['http_status'] ?? null,
                    'response_excerpt' => $auth['response_excerpt'] ?? '',
                ];
            }
            $token = (string) ($auth['token'] ?? '');
        }

        $url = $this->hotelsBaseUrl().'/'.ltrim($path, '/');

        try {
            $response = $this->http()
                ->withToken($token)
                ->acceptJson()
                ->withHeaders(['Content-Type' => 'application/json;charset=utf-8'])
                ->post($url, $body);
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'message' => 'Downtown Travel Hotels request failed: '.$e->getMessage(),
                'http_status' => null,
            ];
        }

        if ($response->status() === 401) {
            self::clearTokenCache();
            $auth = $this->getToken(true);
            if ($auth['ok'] ?? false) {
                try {
                    $response = $this->http()
                        ->withToken((string) ($auth['token'] ?? ''))
                        ->acceptJson()
                        ->withHeaders(['Content-Type' => 'application/json;charset=utf-8'])
                        ->post($url, $body);
                } catch (\Throwable $e) {
                    return [
                        'ok' => false,
                        'message' => 'Downtown Travel Hotels request failed after token refresh: '.$e->getMessage(),
                        'http_status' => null,
                    ];
                }
            }
        }

        $json = $this->jsonOrNull($response);
        $excerpt = $this->excerpt($response->body());

        if ($response->successful()) {
            return [
                'ok' => true,
                'message' => 'OK',
                'http_status' => $response->status(),
                'data' => $json,
                'response_excerpt' => $excerpt,
            ];
        }

        $err = is_array($json)
            ? (string) ($json['message'] ?? $json['error_description'] ?? $json['error'] ?? '')
            : '';

        return [
            'ok' => false,
            'message' => $err !== '' ? $err : ('Downtown Travel Hotels request failed (HTTP '.$response->status().').'),
            'http_status' => $response->status(),
            'data' => $json,
            'response_excerpt' => $excerpt,
        ];
    }

    /**
     * Admin test: Check Availability by radius (London sample from docs).
     *
     * @param  array{check_in?: string, check_out?: string, latitude?: float, longitude?: float, radius?: int, adults?: int}  $params
     * @return array{ok: bool, message: string, total_found?: int, session_id?: string, http_status?: int|null, response_excerpt?: string, raw?: mixed}
     */
    public function testAvailability(array $params = []): array
    {
        $checkIn = (string) ($params['check_in'] ?? now()->addMonths(2)->format('Y-m-d'));
        $checkOut = (string) ($params['check_out'] ?? now()->addMonths(2)->addDays(2)->format('Y-m-d'));
        $lat = (float) ($params['latitude'] ?? 51.50735);
        $lng = (float) ($params['longitude'] ?? -0.12776);
        $radius = max(1000, (int) ($params['radius'] ?? 30000));
        $adults = max(1, (int) ($params['adults'] ?? 1));
        $sessionId = (string) Str::uuid();

        $result = $this->postHotels('/partner/v2/check_availability', [
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'rooms' => [
                [
                    // API rejects empty kids_ages list — omit when no children.
                    'adults_count' => $adults,
                ],
            ],
            'search_by' => [
                'center' => [
                    'latitude' => $lat,
                    'longitude' => $lng,
                ],
                'radius' => $radius,
            ],
            'session_id' => $sessionId,
        ]);

        if (! ($result['ok'] ?? false)) {
            return [
                'ok' => false,
                'message' => $result['message'] ?? 'Check Availability failed.',
                'session_id' => $sessionId,
                'http_status' => $result['http_status'] ?? null,
                'response_excerpt' => $result['response_excerpt'] ?? '',
                'raw' => $result['data'] ?? null,
            ];
        }

        $data = $result['data'] ?? null;
        $count = 0;
        if (is_array($data)) {
            if (array_is_list($data)) {
                $count = count($data);
            } else {
                foreach (['hotels', 'properties', 'results', 'data'] as $key) {
                    if (isset($data[$key]) && is_array($data[$key])) {
                        $count = count($data[$key]);
                        break;
                    }
                }
            }
        }

        return [
            'ok' => true,
            'message' => $count > 0
                ? "Check Availability OK — {$count} property/properties."
                : 'Check Availability OK — API responded (inventory may be empty).',
            'total_found' => $count,
            'session_id' => $sessionId,
            'http_status' => $result['http_status'] ?? 200,
            'response_excerpt' => $result['response_excerpt'] ?? '',
            'raw' => $data,
        ];
    }

    protected function http()
    {
        $timeout = max(5, (int) ($this->config()['timeout'] ?? 90));

        return Http::timeout($timeout)->connectTimeout(min(15, $timeout));
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
        if (strlen($body) <= $max) {
            return $body;
        }

        return substr($body, 0, $max).'…';
    }

    protected function tokenPreview(string $token): string
    {
        if ($token === '') {
            return '';
        }

        if (strlen($token) <= 24) {
            return $token;
        }

        return substr($token, 0, 12).'…'.substr($token, -6);
    }
}
