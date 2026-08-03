<?php

namespace Tests\Unit;

use App\Services\SunSpring\SunSpringClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SunSpringClientAuthTest extends TestCase
{
    public function test_authorize_token_uses_api_username_password_headers(): void
    {
        Http::fake([
            'sandbox.sunspring.ae/api/v2/accounting/getAuthorizeToken' => Http::response([
                'status' => 'success',
                'err' => 0,
                'data' => [
                    'authorization' => 'eyJtest.token.value',
                    'expired_at' => time() + 3600,
                ],
            ], 200),
        ]);

        $client = new class extends SunSpringClient
        {
            protected function integrationBlockedMessage(): ?string
            {
                return null;
            }

            protected function config(): array
            {
                return [
                    'username' => 'wisetrust.test',
                    'password' => 'secret',
                    'timeout' => 30,
                    'environment' => 'sandbox',
                    'base_url_override' => 'https://sandbox.sunspring.ae',
                ];
            }

            public function baseUrl(): string
            {
                return 'https://sandbox.sunspring.ae';
            }

            public function authorizeToken(bool $forceRefresh = false): array
            {
                $c = $this->config();
                $response = $this->http()
                    ->withHeaders([
                        'api-username' => (string) ($c['username'] ?? ''),
                        'api-password' => (string) ($c['password'] ?? ''),
                    ])
                    ->withBody('', 'application/json')
                    ->post($this->url('/api/v2/accounting/getAuthorizeToken'));

                $json = is_array($response->json()) ? $response->json() : [];
                $token = (string) data_get($json, 'data.authorization', '');

                return [
                    'ok' => $response->successful() && $token !== '',
                    'message' => 'ok',
                    'http_status' => $response->status(),
                    'token' => $token,
                    'response_excerpt' => $response->body(),
                ];
            }
        };

        $result = $client->authorizeToken(true);

        $this->assertTrue($result['ok']);
        $this->assertSame('eyJtest.token.value', $result['token']);
        Http::assertSent(function ($request) {
            return $request->url() === 'https://sandbox.sunspring.ae/api/v2/accounting/getAuthorizeToken'
                && ($request->header('api-username')[0] ?? null) === 'wisetrust.test'
                && ($request->header('api-password')[0] ?? null) === 'secret';
        });
    }

    public function test_authenticated_post_sends_authorization_header(): void
    {
        Http::fake([
            'sandbox.sunspring.ae/api/v2/accounting/myCredit' => Http::response([
                'status' => 'success',
                'err' => 0,
                'data' => ['credit' => 100],
            ], 200),
        ]);

        $client = new class extends SunSpringClient
        {
            protected function integrationBlockedMessage(): ?string
            {
                return null;
            }

            protected function config(): array
            {
                return [
                    'timeout' => 30,
                    'environment' => 'sandbox',
                    'base_url_override' => 'https://sandbox.sunspring.ae',
                ];
            }

            public function baseUrl(): string
            {
                return 'https://sandbox.sunspring.ae';
            }
        };

        $result = $client->post('/api/v2/accounting/myCredit', [], 'eyJtest.token.value');

        $this->assertTrue($result['ok']);
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/api/v2/accounting/myCredit')
                && ($request->header('Authorization')[0] ?? null) === 'Bearer eyJtest.token.value';
        });
    }

    public function test_normalize_host_only_strips_path(): void
    {
        $this->assertSame(
            'https://sandbox.sunspring.ae',
            SunSpringClient::normalizeHostOnly('https://sandbox.sunspring.ae/api/v2/')
        );
    }
}
