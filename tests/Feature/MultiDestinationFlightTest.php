<?php

namespace Tests\Feature;

use App\Models\Users\User;
use App\Services\SunSpring\SunSpringAirService;
use App\Services\SunSpring\SunSpringClient;
use App\Services\SunSpring\SunSpringFlightParser;
use App\Services\Travelport\TravelportAirXmlBuilder;
use App\Support\FlightProvider;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MultiDestinationFlightTest extends TestCase
{
    public function test_travelport_xml_builds_one_search_air_leg_per_multi_city_leg(): void
    {
        $builder = new TravelportAirXmlBuilder;
        $xml = $builder->build('low_fare_search', [
            'trip_type' => 'multicity',
            'adults' => 1,
            'legs' => [
                ['origin' => 'LHR', 'destination' => 'CDG', 'departure_date' => '2026-09-01'],
                ['origin' => 'CDG', 'destination' => 'JFK', 'departure_date' => '2026-09-05'],
                ['origin' => 'JFK', 'destination' => 'LHR', 'departure_date' => '2026-09-12'],
            ],
        ], 52);

        $this->assertSame(3, preg_match_all('/<air:SearchAirLeg\b/', $xml));
        $this->assertStringContainsString('Code="LHR"', $xml);
        $this->assertStringContainsString('Code="CDG"', $xml);
        $this->assertStringContainsString('Code="JFK"', $xml);
    }

    public function test_sunspring_multicity_uses_first_and_last_leg_airports(): void
    {
        Http::fake([
            'sandbox.sunspring.ae/api/v2/flight/FlightSearch' => Http::response([
                'status' => 'success',
                'err' => 0,
                'outbound' => [],
                'return' => [],
            ], 200),
        ]);

        $this->app->bind(SunSpringAirService::class, function () {
            $client = new class extends SunSpringClient
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

                public function baseUrl(): string
                {
                    return 'https://sandbox.sunspring.ae';
                }
            };

            return new class($client, new SunSpringFlightParser) extends SunSpringAirService
            {
                public function isReady(): bool
                {
                    return true;
                }
            };
        });

        $result = app(SunSpringAirService::class)->lowFareSearch([
            'trip_type' => 'multicity',
            'adults' => 1,
            'legs' => [
                ['origin' => 'THR', 'destination' => 'SYZ', 'departure_date' => '2026-09-01'],
                ['origin' => 'SYZ', 'destination' => 'MHD', 'departure_date' => '2026-09-05'],
            ],
        ]);

        $this->assertTrue($result['ok']);
        $this->assertSame('sunspring', $result['provider']);

        Http::assertSent(function ($request) {
            if (! str_contains($request->url(), 'FlightSearch')) {
                return false;
            }
            $body = $request->data();

            return ($body['outbound']['departure'] ?? null) === 'THR'
                && ($body['outbound']['arrival'] ?? null) === 'MHD'
                && ($body['outbound']['date'] ?? null) === '2026-09-01'
                && ! isset($body['return']);
        });
    }

    public function test_admin_and_public_search_forms_include_multi_destination(): void
    {
        $user = User::where('email', 'superadmin@traveltours.com')->first();
        if (! $user) {
            $this->markTestSkipped('Run TenantRbacSeeder for demo users.');
        }

        $this->actingAs($user)
            ->get(route('admin.flights.search'))
            ->assertOk()
            ->assertSee('Multi Destination', false)
            ->assertSee('name="trip_type" value="multicity"', false)
            ->assertSee('legs[0][origin]', false)
            ->assertSee('legs[1][origin]', false);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Multi Destination', false)
            ->assertSee('multi-city', false);
    }

    public function test_public_multicity_sunspring_search_accepts_legs(): void
    {
        Http::fake([
            'sandbox.sunspring.ae/api/v2/flight/FlightSearch' => Http::response([
                'status' => 'success',
                'err' => 0,
                'outbound' => [[
                    'ref_number' => 'MC-1',
                    'currency_code' => 'USD',
                    'price' => 180,
                    'capacity' => 4,
                    'duration' => '02:00',
                    'departure' => ['location_code' => 'THR', 'date' => '2026-09-01', 'time' => '08:00:00'],
                    'arrival' => ['location_code' => 'MHD', 'date' => '2026-09-01', 'time' => '10:00:00'],
                    'flight_details' => [
                        'airline' => 'HH',
                        'flight_number' => '77',
                        'class' => 'Y',
                        'cabin' => 'Economy',
                        'farebasis_code' => 'YOW',
                        'airline_name_en' => 'Sepehran',
                    ],
                    'passenger_fare' => ['adult' => ['fare' => 150, 'tax' => 30]],
                ]],
                'return' => [],
            ], 200),
        ]);

        $this->app->bind(SunSpringAirService::class, function () {
            $client = new class extends SunSpringClient
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

                public function baseUrl(): string
                {
                    return 'https://sandbox.sunspring.ae';
                }
            };

            return new class($client, new SunSpringFlightParser) extends SunSpringAirService
            {
                public function isReady(): bool
                {
                    return true;
                }
            };
        });

        $this->post(route('frontend.flights.search'), [
            'provider' => 'sunspring',
            'trip_type' => 'multi-city',
            'adults' => 1,
            'legs' => [
                ['origin' => 'THR', 'destination' => 'SYZ', 'departure_date' => '2026-09-01'],
                ['origin' => 'SYZ', 'destination' => 'MHD', 'departure_date' => '2026-09-05'],
            ],
        ])->assertRedirect(route('frontend.flights.results'));

        $this->assertSame(FlightProvider::SUNSPRING, FlightProvider::current());
        $stored = session('public.flight_search');
        $this->assertSame('multicity', data_get($stored, 'input.trip_type'));
        $this->assertCount(2, data_get($stored, 'input.legs', []));
        $this->assertTrue((bool) data_get($stored, 'result.ok'));
        $this->assertSame('sunspring', data_get($stored, 'result.provider'));

        $this->get(route('frontend.flights.results'))
            ->assertOk()
            ->assertSee('API: SunSpring', false)
            ->assertSee('MC-1', false);
    }

    public function test_admin_multicity_validation_rejects_single_leg(): void
    {
        $user = User::where('email', 'superadmin@traveltours.com')->first();
        if (! $user) {
            $this->markTestSkipped('Run TenantRbacSeeder for demo users.');
        }

        $this->actingAs($user)
            ->from(route('admin.flights.search'))
            ->post(route('admin.flights.search'), [
                'provider' => 'travelport',
                'trip_type' => 'multicity',
                'adults' => 1,
                'legs' => [
                    ['origin' => 'LHR', 'destination' => 'CDG', 'departure_date' => now()->addDays(20)->format('Y-m-d')],
                ],
            ])
            ->assertRedirect(route('admin.flights.search'))
            ->assertSessionHasErrors('legs');
    }
}
