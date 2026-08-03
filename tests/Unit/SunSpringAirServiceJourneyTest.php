<?php

namespace Tests\Unit;

use App\Services\SunSpring\SunSpringAirService;
use App\Services\SunSpring\SunSpringClient;
use App\Services\SunSpring\SunSpringFlightParser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SunSpringAirServiceJourneyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('sunspring.authorization_token');
    }

    public function test_search_price_book_ticket_journey(): void
    {
        $flight = [
            'ref_number' => 'REF-100',
            'currency_code' => 'USD',
            'price' => 120,
            'capacity' => 9,
            'duration' => '01:30',
            'departure' => [
                'location_code' => 'THR',
                'date' => '2026-08-20',
                'time' => '10:00:00',
            ],
            'arrival' => [
                'location_code' => 'MHD',
                'date' => '2026-08-20',
                'time' => '11:30:00',
            ],
            'flight_details' => [
                'airline' => 'HH',
                'flight_number' => '123',
                'class' => 'Y',
                'cabin' => 'Economy',
                'farebasis_code' => 'YOW',
                'airline_name_en' => 'Sepehran',
            ],
            'passenger_fare' => [
                'adult' => ['fare' => 100, 'tax' => 20],
            ],
        ];

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
                'err' => [['code' => 0, 'message' => '']],
                'outbound' => [$flight],
                'return' => [],
            ], 200),
            'sandbox.sunspring.ae/api/v2/flight/AirPrice' => Http::response([
                'status' => 'success',
                'err' => 0,
                'total' => 120,
                'adult' => ['fare' => 100, 'tax' => 20, 'total' => 120],
            ], 200),
            'sandbox.sunspring.ae/api/v2/flight/Book' => Http::response([
                'status' => 'success',
                'err' => 0,
                'refrence_id' => 98765,
                'payment' => ['status' => 'pending'],
                'flights' => [$flight],
            ], 200),
            'sandbox.sunspring.ae/api/v2/flight/Confirm' => Http::response([
                'status' => 'success',
                'err' => 0,
                'refrence_id' => 98765,
            ], 200),
            'sandbox.sunspring.ae/api/v2/flight/AirDemandTicket' => Http::response([
                'status' => 'success',
                'err' => 0,
                'refrence_id' => 98765,
                'tickets' => [
                    ['ticket_number' => '999-1234567890'],
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
                    'message' => 'test token',
                    'http_status' => 200,
                    'token' => 'jwt.fake.token',
                    'expired_at' => time() + 3600,
                    'response_excerpt' => '',
                ];
            }
        };

        $air = new class($client, new SunSpringFlightParser) extends SunSpringAirService
        {
            public function isReady(): bool
            {
                return true;
            }
        };

        $search = $air->lowFareSearch([
            'origin' => 'THR',
            'destination' => 'MHD',
            'departure_date' => '2026-08-20',
            'adults' => 1,
            'trip_type' => 'oneway',
        ]);

        $this->assertTrue($search['ok'], $search['message'] ?? 'search failed');
        $this->assertNotEmpty($search['solutions']);
        $key = (string) $search['solutions'][0]['key'];
        $this->assertSame('REF-100', $key);

        $price = $air->airPrice([
            'solution_key' => $key,
            'adults' => 1,
        ]);
        $this->assertTrue($price['ok'], $price['message'] ?? 'price failed');

        $book = $air->book([
            'passengers' => [[
                'prefix' => 'Mr',
                'first' => 'Test',
                'last' => 'Traveler',
                'email' => 'test@example.com',
                'phone' => '9151231231',
                'dob' => '1990-01-01',
                'gender' => 'M',
                'national_id' => '0000000000',
                'nationality' => 'USA',
            ]],
            'country_code' => '+98',
        ]);
        $this->assertTrue($book['ok'], $book['message'] ?? 'book failed');
        $this->assertSame('98765', (string) $book['universal_locator']);

        $ticket = $air->issueTicket(['reference' => 98765]);
        $this->assertTrue($ticket['ok'], $ticket['message'] ?? 'ticket failed');
        $this->assertSame(['999-1234567890'], $ticket['ticket_numbers']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/api/v2/flight/FlightSearch')
                && ($request->header('Authorization')[0] ?? '') === 'Bearer jwt.fake.token';
        });
    }
}
