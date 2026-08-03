<?php

namespace Tests\Feature;

use App\Models\Users\User;
use App\Support\FlightProvider;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FlightProviderUiTest extends TestCase
{
    public function test_admin_search_shows_provider_selector(): void
    {
        $user = User::where('email', 'superadmin@traveltours.com')->first();
        if (! $user) {
            $this->markTestSkipped('Run TenantRbacSeeder for demo users.');
        }

        $this->actingAs($user)
            ->get(route('admin.flights.search'))
            ->assertOk()
            ->assertSee('Search via API', false)
            ->assertSee('name="provider"', false)
            ->assertSee('Travelport', false)
            ->assertSee('SunSpring', false);
    }

    public function test_agent_and_subagent_search_show_provider_selector(): void
    {
        $tenantAdmin = User::where('email', 'tenantadmin@traveltours.com')->first();
        $salesAgent = User::where('email', 'sales.agent@traveltours.com')->first();
        if (! $tenantAdmin || ! $salesAgent) {
            $this->markTestSkipped('Run TenantRbacSeeder for demo users.');
        }

        $this->actingAs($tenantAdmin)
            ->get(route('agent.flights.search'))
            ->assertOk()
            ->assertSee('Search via API', false)
            ->assertSee('SunSpring', false);

        $this->actingAs($salesAgent)
            ->get(route('subagent.flights.search'))
            ->assertOk()
            ->assertSee('Search via API', false)
            ->assertSee('SunSpring', false);
    }

    public function test_public_home_and_flights_page_show_provider_selector(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Search via API', false)
            ->assertSee('name="provider"', false)
            ->assertSee('Travelport', false)
            ->assertSee('SunSpring', false);

        $this->get(route('pages.flights'))
            ->assertOk()
            ->assertSee('Search via API', false)
            ->assertSee('SunSpring', false);
    }

    public function test_sunspring_integrations_page_renders(): void
    {
        $user = User::where('email', 'superadmin@traveltours.com')->first();
        if (! $user) {
            $this->markTestSkipped('Run TenantRbacSeeder for demo users.');
        }

        $this->actingAs($user)
            ->get(route('admin.integrations.edit', ['slug' => 'sunspring']))
            ->assertOk()
            ->assertSee('SunSpring', false)
            ->assertSee('Authorize', false)
            ->assertSee('Bearer', false);
    }

    public function test_public_sunspring_search_stores_results_with_provider_badge(): void
    {
        config([
            'sunspring.username' => 'test.user',
            'sunspring.password' => 'test.pass',
            'sunspring.environment' => 'sandbox',
            'sunspring.base_url_override' => 'https://sandbox.sunspring.ae',
        ]);

        Http::fake([
            'sandbox.sunspring.ae/api/v2/accounting/getAuthorizeToken' => Http::response([
                'status' => 'success',
                'err' => 0,
                'data' => [
                    'authorization' => 'jwt.fake.token',
                    'expired_at' => time() + 3600,
                ],
            ], 200),
            'sandbox.sunspring.ae/api/v2/flight/FlightSearch' => Http::response([
                'status' => 'success',
                'err' => [['code' => 0]],
                'outbound' => [[
                    'ref_number' => 'REF-UI-1',
                    'currency_code' => 'USD',
                    'price' => 99,
                    'capacity' => 5,
                    'duration' => '01:10',
                    'departure' => ['location_code' => 'THR', 'date' => now()->addDays(14)->format('Y-m-d'), 'time' => '09:00:00'],
                    'arrival' => ['location_code' => 'MHD', 'date' => now()->addDays(14)->format('Y-m-d'), 'time' => '10:10:00'],
                    'flight_details' => [
                        'airline' => 'HH',
                        'flight_number' => '101',
                        'class' => 'Y',
                        'cabin' => 'Economy',
                        'farebasis_code' => 'YOW',
                        'airline_name_en' => 'Sepehran',
                    ],
                    'passenger_fare' => ['adult' => ['fare' => 80, 'tax' => 19]],
                ]],
                'return' => [],
            ], 200),
        ]);

        // Force readiness even if DB integration row is disabled.
        $this->app->bind(\App\Services\SunSpring\SunSpringAirService::class, function () {
            $client = new class extends \App\Services\SunSpring\SunSpringClient
            {
                protected function integrationBlockedMessage(): ?string
                {
                    return null;
                }

                protected function config(): array
                {
                    return [
                        'username' => 'test.user',
                        'password' => 'test.pass',
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
                    return [
                        'ok' => true,
                        'message' => 'ok',
                        'http_status' => 200,
                        'token' => 'jwt.fake.token',
                        'expired_at' => time() + 3600,
                        'response_excerpt' => '',
                    ];
                }
            };

            return new class($client, new \App\Services\SunSpring\SunSpringFlightParser) extends \App\Services\SunSpring\SunSpringAirService
            {
                public function isReady(): bool
                {
                    return true;
                }
            };
        });

        $response = $this->post(route('frontend.flights.search'), [
            'provider' => 'sunspring',
            'trip_type' => 'oneway',
            'origin' => 'THR',
            'destination' => 'MHD',
            'departure_date' => now()->addDays(14)->format('Y-m-d'),
            'adults' => 1,
        ]);

        $response->assertRedirect(route('frontend.flights.results'));
        $this->assertSame('sunspring', session('flight.provider'));
        $this->assertTrue((bool) data_get(session('public.flight_search.result'), 'ok'));
        $this->assertSame('sunspring', data_get(session('public.flight_search.result'), 'provider'));

        $this->get(route('frontend.flights.results'))
            ->assertOk()
            ->assertSee('API: SunSpring', false)
            ->assertSee('REF-UI-1', false);
    }

    public function test_admin_sunspring_search_shows_api_badge(): void
    {
        $user = User::where('email', 'superadmin@traveltours.com')->first();
        if (! $user) {
            $this->markTestSkipped('Run TenantRbacSeeder for demo users.');
        }

        $this->app->bind(\App\Services\SunSpring\SunSpringAirService::class, function () {
            $client = new class extends \App\Services\SunSpring\SunSpringClient
            {
                protected function integrationBlockedMessage(): ?string
                {
                    return null;
                }

                public function authorizeToken(bool $forceRefresh = false): array
                {
                    return [
                        'ok' => true,
                        'message' => 'ok',
                        'http_status' => 200,
                        'token' => 'jwt.fake.token',
                        'expired_at' => time() + 3600,
                        'response_excerpt' => '',
                    ];
                }

                public function post(string $path, array $body = [], ?string $token = null): array
                {
                    if (str_contains($path, 'FlightSearch')) {
                        return [
                            'ok' => true,
                            'message' => 'OK',
                            'http_status' => 200,
                            'data' => [
                                'status' => 'success',
                                'err' => 0,
                                'outbound' => [[
                                    'ref_number' => 'ADM-SS-1',
                                    'currency_code' => 'USD',
                                    'price' => 150,
                                    'capacity' => 3,
                                    'duration' => '01:20',
                                    'departure' => ['location_code' => 'THR', 'date' => now()->addDays(10)->format('Y-m-d'), 'time' => '08:00:00'],
                                    'arrival' => ['location_code' => 'MHD', 'date' => now()->addDays(10)->format('Y-m-d'), 'time' => '09:20:00'],
                                    'flight_details' => [
                                        'airline' => 'HH',
                                        'flight_number' => '200',
                                        'class' => 'Y',
                                        'cabin' => 'Economy',
                                        'farebasis_code' => 'YOW',
                                        'airline_name_en' => 'Sepehran',
                                    ],
                                    'passenger_fare' => ['adult' => ['fare' => 120, 'tax' => 30]],
                                ]],
                                'return' => [],
                            ],
                            'response_excerpt' => '',
                        ];
                    }

                    return parent::post($path, $body, $token);
                }
            };

            return new class($client, new \App\Services\SunSpring\SunSpringFlightParser) extends \App\Services\SunSpring\SunSpringAirService
            {
                public function isReady(): bool
                {
                    return true;
                }
            };
        });

        $this->actingAs($user)
            ->post(route('admin.flights.search'), [
                'provider' => 'sunspring',
                'trip_type' => 'oneway',
                'origin' => 'THR',
                'destination' => 'MHD',
                'departure_date' => now()->addDays(10)->format('Y-m-d'),
                'adults' => 1,
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->get(route('admin.flights.search'))
            ->assertOk()
            ->assertSee('API: SunSpring', false)
            ->assertSee('ADM-SS-1', false);
    }

    public function test_provider_badge_helpers(): void
    {
        $this->assertSame('Travelport', FlightProvider::label('travelport'));
        $this->assertSame('SunSpring', FlightProvider::label('sunspring'));
        $this->assertSame('sunspring', FlightProvider::fromResult(['provider' => 'sunspring']));
        $this->assertSame('API: Travelport', FlightProvider::badge('travelport')['short']);
        $this->assertSame('API: SunSpring', FlightProvider::badge('sunspring')['short']);
    }

    public function test_public_workflow_guards_still_redirect_cleanly(): void
    {
        $this->get(route('frontend.flights.results'))->assertRedirect(route('home'));
        $this->get(route('frontend.flights.price.show'))->assertRedirect();
        $this->get(route('frontend.flights.book'))->assertRedirect();
        $this->get(route('frontend.flights.confirmation'))->assertRedirect();
    }
}
