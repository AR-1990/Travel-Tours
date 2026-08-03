<?php

namespace Tests\Feature;

use App\Models\Users\User;
use App\Services\SunSpring\SunSpringAirService;
use App\Services\SunSpring\SunSpringClient;
use App\Services\SunSpring\SunSpringFlightParser;
use App\Support\FlightProvider;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SunSpringWorkflowFeatureTest extends TestCase
{
    protected function bindReadySunSpring(): void
    {
        Http::fake([
            'sandbox.sunspring.ae/*' => function ($request) {
                $url = $request->url();

                if (str_contains($url, 'getAuthorizeToken')) {
                    return Http::response([
                        'status' => 'success',
                        'err' => 0,
                        'data' => ['authorization' => 'jwt.fake.token', 'expired_at' => time() + 3600],
                    ], 200);
                }

                if (str_contains($url, 'FlightSearch')) {
                    return Http::response([
                        'status' => 'success',
                        'err' => 0,
                        'outbound' => [[
                            'ref_number' => 'WF-100',
                            'currency_code' => 'USD',
                            'price' => 200,
                            'capacity' => 4,
                            'duration' => '01:15',
                            'departure' => ['location_code' => 'THR', 'date' => now()->addDays(12)->format('Y-m-d'), 'time' => '07:00:00'],
                            'arrival' => ['location_code' => 'MHD', 'date' => now()->addDays(12)->format('Y-m-d'), 'time' => '08:15:00'],
                            'flight_details' => [
                                'airline' => 'HH',
                                'flight_number' => '55',
                                'class' => 'Y',
                                'cabin' => 'Economy',
                                'farebasis_code' => 'YOW',
                                'airline_name_en' => 'Sepehran',
                            ],
                            'passenger_fare' => ['adult' => ['fare' => 160, 'tax' => 40]],
                        ]],
                        'return' => [],
                    ], 200);
                }

                if (str_contains($url, 'AirPrice')) {
                    return Http::response([
                        'status' => 'success',
                        'err' => 0,
                        'total' => 200,
                        'adult' => ['fare' => 160, 'tax' => 40, 'total' => 200],
                    ], 200);
                }

                if (str_contains($url, '/Book')) {
                    return Http::response([
                        'status' => 'success',
                        'err' => 0,
                        'refrence_id' => 55501,
                    ], 200);
                }

                if (str_contains($url, 'Confirm')) {
                    return Http::response(['status' => 'success', 'err' => 0, 'refrence_id' => 55501], 200);
                }

                if (str_contains($url, 'AirDemandTicket')) {
                    return Http::response([
                        'status' => 'success',
                        'err' => 0,
                        'refrence_id' => 55501,
                        'tickets' => [['ticket_number' => '888-55501001']],
                    ], 200);
                }

                return Http::response(['status' => 'success', 'err' => 0], 200);
            },
        ]);

        $this->app->bind(SunSpringAirService::class, function () {
            $client = new class extends SunSpringClient
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

            return new class($client, new SunSpringFlightParser) extends SunSpringAirService
            {
                public function isReady(): bool
                {
                    return true;
                }
            };
        });
    }

    public function test_public_search_price_book_ticket_flow(): void
    {
        $this->bindReadySunSpring();

        $this->post(route('frontend.flights.search'), [
            'provider' => 'sunspring',
            'trip_type' => 'oneway',
            'origin' => 'THR',
            'destination' => 'MHD',
            'departure_date' => now()->addDays(12)->format('Y-m-d'),
            'adults' => 1,
        ])->assertRedirect(route('frontend.flights.results'));

        $this->assertSame(FlightProvider::SUNSPRING, FlightProvider::current());

        $this->post(route('frontend.flights.price'), [
            'solution_key' => 'WF-100',
        ])->assertRedirect(route('frontend.flights.book'));

        $this->get(route('frontend.flights.book'))
            ->assertOk()
            ->assertSee('API: SunSpring', false)
            ->assertSee('national_id', false);

        $book = $this->post(route('frontend.flights.book.store'), [
            'passenger_prefix' => 'Mr',
            'passenger_first' => 'Ada',
            'passenger_last' => 'Lovelace',
            'passenger_email' => 'ada@example.com',
            'passenger_phone' => '9151112233',
            'passenger_dob' => '1991-05-05',
            'passenger_gender' => 'F',
            'national_id' => '1234567890',
            'nationality' => 'USA',
            'country_code' => '+98',
        ]);

        $book->assertRedirect();
        $reservationId = session('public.last_reservation_id') ?? session('travelport.last_reservation_id');
        $this->assertNotEmpty($reservationId);

        $this->get(route('frontend.flights.reservations.show', ['id' => $reservationId]))
            ->assertOk()
            ->assertSee('API: SunSpring', false)
            ->assertSee('55501', false);

        $this->post(route('frontend.flights.reservations.ticket', ['id' => $reservationId]))
            ->assertRedirect(route('frontend.flights.reservations.show', ['id' => $reservationId]));

        $this->get(route('frontend.flights.reservations.show', ['id' => $reservationId]))
            ->assertOk()
            ->assertSee('888-55501001', false);
    }

    public function test_admin_search_price_book_flow(): void
    {
        $user = User::where('email', 'superadmin@traveltours.com')->first();
        if (! $user) {
            $this->markTestSkipped('Run TenantRbacSeeder for demo users.');
        }

        $this->bindReadySunSpring();

        $this->actingAs($user)
            ->post(route('admin.flights.search'), [
                'provider' => 'sunspring',
                'trip_type' => 'oneway',
                'origin' => 'THR',
                'destination' => 'MHD',
                'departure_date' => now()->addDays(12)->format('Y-m-d'),
                'adults' => 1,
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('admin.flights.price'), ['solution_key' => 'WF-100'])
            ->assertRedirect(route('admin.flights.book'));

        $this->actingAs($user)
            ->get(route('admin.flights.book'))
            ->assertOk()
            ->assertSee('API: SunSpring', false);

        $book = $this->actingAs($user)->post(route('admin.flights.book.store'), [
            'passenger_prefix' => 'Mr',
            'passenger_first' => 'Admin',
            'passenger_last' => 'Tester',
            'passenger_email' => 'admin.book@example.com',
            'passenger_phone' => '9159998877',
            'passenger_dob' => '1988-02-02',
            'passenger_gender' => 'M',
            'national_id' => '0000000000',
            'nationality' => 'USA',
            'country_code' => '+98',
        ]);

        $book->assertRedirect();
        $reservationId = session('travelport.last_reservation_id');
        $this->assertNotEmpty($reservationId);

        $this->actingAs($user)
            ->get(route('admin.flights.reservations.show', ['id' => $reservationId]))
            ->assertOk()
            ->assertSee('API: SunSpring', false);

        $this->actingAs($user)
            ->get(route('admin.flights.reservations.index'))
            ->assertOk()
            ->assertSee('API: SunSpring', false);
    }
}
