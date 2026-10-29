<?php

namespace Tests\Feature;

use App\Services\DowntownTravel\DowntownTravelAirService;
use App\Support\FlightProvider;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class DowntownTravelBookFormFeatureTest extends TestCase
{
    protected function seedDowntownPriceSession(): void
    {
        FlightProvider::set(FlightProvider::TRAVELPORT);

        $solution = [
            'key' => 'dt-1',
            'provider' => FlightProvider::DOWNTOWN_TRAVEL,
            'digest' => 'digest-test',
            'airline' => 'QR',
            'airline_name' => 'Qatar Airways',
            'total_amount' => 459.2,
            'currency' => 'USD',
            'base_amount' => 74,
        ];

        session([
            'flight.provider' => FlightProvider::TRAVELPORT,
            'downtown_travel.last_price' => [
                'solution' => $solution,
            ],
            'public.flight_search' => [
                'input' => [
                    'adults' => 1,
                    'children' => 0,
                    'infants' => 0,
                    'origin' => 'JFK',
                    'destination' => 'DOH',
                    'departure_date' => now()->addDays(20)->format('Y-m-d'),
                    'provider' => FlightProvider::DOWNTOWN_TRAVEL,
                ],
                'result' => [
                    'ok' => true,
                    'provider' => FlightProvider::DOWNTOWN_TRAVEL,
                    'solutions' => [$solution],
                ],
            ],
            'public.flight_price' => [
                'solution_key' => 'dt-1',
                'provider' => FlightProvider::DOWNTOWN_TRAVEL,
                'input' => ['adults' => 1, 'children' => 0, 'infants' => 0],
                'result' => [
                    'ok' => true,
                    'message' => 'Price confirmed',
                    'provider' => FlightProvider::DOWNTOWN_TRAVEL,
                    'solutions' => [$solution],
                ],
            ],
        ]);
    }

    public function test_book_form_shows_passengers_array_for_downtown_even_if_session_toggle_is_travelport(): void
    {
        $this->seedDowntownPriceSession();

        $this->app->bind(DowntownTravelAirService::class, function () {
            return new class
            {
                public function isReady(): bool
                {
                    return true;
                }

                public function hasStoredPricingContext(): bool
                {
                    return true;
                }
            };
        });

        $response = $this->get(route('frontend.flights.book'));

        $response->assertOk();
        $response->assertSee('name="provider"', false);
        $response->assertSee('value="downtown_travel"', false);
        $response->assertSee('passengers[0][first]', false);
        $response->assertSee('passengers[0][nationality]', false);
        $response->assertDontSee('name="passenger_first"', false);
        $response->assertDontSee('passengers[0][national_id]', false);
        $response->assertSee('passengers[0][passport_number]', false);
        $response->assertSee('Payment option', false);
    }

    public function test_book_store_rejects_travelport_flat_fields_for_downtown(): void
    {
        $this->seedDowntownPriceSession();

        $this->app->bind(DowntownTravelAirService::class, function () {
            return new class
            {
                public function isReady(): bool
                {
                    return true;
                }

                public function book(array $params): array
                {
                    return ['ok' => true, 'message' => 'should not reach', 'provider' => 'downtown_travel'];
                }
            };
        });

        $this->from(route('frontend.flights.book'))
            ->post(route('frontend.flights.book.store'), [
                'provider' => FlightProvider::DOWNTOWN_TRAVEL,
                'passenger_first' => 'Ubaid',
                'passenger_last' => 'Syed',
                'passenger_email' => 'qamuken@mailinator.com',
                'passenger_phone' => '+12236826712',
                'passenger_dob' => '1990-11-24',
                'passenger_gender' => 'M',
                'passenger_prefix' => 'Mr',
            ])
            ->assertRedirect(route('frontend.flights.book'))
            ->assertSessionHasErrors('passengers');
    }

    public function test_book_store_accepts_passengers_array_for_downtown(): void
    {
        $this->seedDowntownPriceSession();

        $captured = [];
        $this->app->bind(DowntownTravelAirService::class, function () use (&$captured) {
            return new class($captured)
            {
                /** @var array<string, mixed> */
                private array $captured;

                public function __construct(array &$captured)
                {
                    $this->captured = &$captured;
                }

                public function isReady(): bool
                {
                    return true;
                }

                public function book(array $params): array
                {
                    $this->captured = $params;

                    return [
                        'ok' => true,
                        'message' => 'Downtown Travel booking created (order DT-99).',
                        'provider' => FlightProvider::DOWNTOWN_TRAVEL,
                        'universal_locator' => 'DT-99',
                        'provider_locator' => 'DT-99',
                        'air_locator' => 'DT-99',
                        'raw' => ['order_id' => 'DT-99'],
                    ];
                }
            };
        });

        $response = $this->post(route('frontend.flights.book.store'), [
            'provider' => FlightProvider::DOWNTOWN_TRAVEL,
            'country_code' => '+1',
            'form_of_payment' => 'Credit',
            'passengers' => [[
                'type' => 'ADT',
                'prefix' => 'Mr',
                'first' => 'Ubaid',
                'last' => 'Syed',
                'email' => 'qamuken@mailinator.com',
                'phone' => '2236826712',
                'dob' => '1990-11-24',
                'gender' => 'M',
                'nationality' => 'USA',
                'passport_number' => 'P1234567',
                'passport_expire' => '2030-12-31',
            ]],
        ]);

        $response->assertRedirect();
        $this->assertNotEmpty(session('public.last_reservation_id') ?? session('travelport.last_reservation_id'));
        $this->assertSame('+12236826712', $captured['phone'] ?? null);
        $this->assertSame('+12236826712', data_get($captured, 'passengers.0.phone'));
        $this->assertSame('Credit', $captured['form_of_payment'] ?? null);
        $this->assertSame('P1234567', data_get($captured, 'passengers.0.passport_number'));
        $this->assertSame('USA', data_get($captured, 'passengers.0.nationality'));
    }

    public function test_passenger_partial_renders_travelport_passenger_array(): void
    {
        $html = View::make('flights.partials.passenger-book-fields', [
            'flightProvider' => FlightProvider::TRAVELPORT,
            'flightPriceResult' => ['ok' => true, 'provider' => FlightProvider::TRAVELPORT],
            'passengerSlots' => [
                ['type' => 'ADT', 'label' => 'Adult 1', 'prefix' => 'Mr', 'gender' => 'M'],
                ['type' => 'ADT', 'label' => 'Adult 2', 'prefix' => 'Mr', 'gender' => 'M'],
            ],
            'bookInput' => [],
        ])->render();

        $this->assertStringContainsString('name="passengers[0][first]"', $html);
        $this->assertStringContainsString('name="passengers[1][first]"', $html);
        $this->assertStringNotContainsString('name="passenger_first"', $html);
    }
}
