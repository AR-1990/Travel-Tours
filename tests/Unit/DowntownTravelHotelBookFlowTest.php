<?php

namespace Tests\Unit;

use App\Services\DowntownTravel\DowntownTravelHotelService;
use App\Services\DowntownTravel\DowntownTravelHotelsClient;
use Tests\TestCase;

class DowntownTravelHotelBookFlowTest extends TestCase
{
    public function test_recheck_validate_create_book_and_cancel_paths(): void
    {
        $client = new class extends DowntownTravelHotelsClient
        {
            /** @var list<array{method: string, path: string, body: array<string, mixed>}> */
            public array $calls = [];

            public function getToken(bool $forceRefresh = false): array
            {
                return ['ok' => true, 'token' => 'test-token', 'http_status' => 200];
            }

            public function postHotels(string $path, array $body = [], ?string $token = null): array
            {
                $this->calls[] = ['method' => 'POST', 'path' => $path, 'body' => $body];

                if (str_contains($path, '/offers') && ! str_contains($path, '/price')) {
                    return [
                        'ok' => true,
                        'http_status' => 200,
                        'data' => [
                            'session_id' => 'sess-1',
                            'offers' => [[
                                'offer_id' => 'offer-1',
                                'rooms' => [['id' => 'room-from-offer', 'room_name' => 'Deluxe']],
                                'payment_options' => [
                                    'check' => ['agency_net_price' => 100.5, 'agency_client_price' => 100.5],
                                ],
                                'pricing_details' => ['currency_code' => 'USD', 'total_rate' => 100.5],
                            ]],
                        ],
                    ];
                }

                if ($path === '/partner/v2/orders') {
                    return ['ok' => true, 'http_status' => 200, 'data' => '11111111-2222-3333-4444-555555555555'];
                }

                if (str_contains($path, '/book')) {
                    return ['ok' => true, 'http_status' => 200, 'data' => ['status' => 'processing']];
                }

                if (str_contains($path, '/cancel')) {
                    return [
                        'ok' => true,
                        'http_status' => 200,
                        'data' => [
                            'id' => '11111111-2222-3333-4444-555555555555',
                            'rooms' => [['booking_status' => 'canceled']],
                        ],
                    ];
                }

                return ['ok' => true, 'http_status' => 200, 'data' => []];
            }

            public function getHotels(string $path, array $query = [], ?string $token = null): array
            {
                $this->calls[] = ['method' => 'GET', 'path' => $path, 'body' => $query];

                if (str_contains($path, '/price')) {
                    return [
                        'ok' => true,
                        'http_status' => 200,
                        'data' => [
                            'offer_id' => 'offer-1',
                            'rooms' => [['id' => 'room-1', 'room_name' => 'Deluxe', 'meal_plan' => 'breakfast_included']],
                            'payment_options' => [
                                'check' => ['agency_net_price' => 100.5, 'agency_client_price' => 100.5],
                            ],
                            'pricing_details' => ['currency_code' => 'USD'],
                        ],
                    ];
                }

                return [
                    'ok' => true,
                    'http_status' => 200,
                    'data' => [
                        'id' => '11111111-2222-3333-4444-555555555555',
                        'readable_id' => 42,
                        'rooms' => [['booking_status' => 'booked']],
                        'property' => ['name' => 'Test Hotel'],
                    ],
                ];
            }
        };

        $service = new DowntownTravelHotelService($client);

        session([
            'downtown_travel_hotels.last_search' => [
                'meta' => [
                    'check_in' => '2026-11-01',
                    'check_out' => '2026-11-03',
                    'session_id' => 'sess-1',
                    'adults' => 2,
                    'rooms' => [['adults_count' => 2]],
                ],
                'solutions' => [[
                    'key' => 'dth:prop-1:0',
                    'property_id' => 'prop-1',
                    'hotel_id' => 'prop-1',
                    'hotel_name' => 'Test Hotel',
                    'city' => 'London',
                    'session_id' => 'sess-1',
                    'check_in' => '2026-11-01',
                    'check_out' => '2026-11-03',
                    'nights' => 2,
                    'total_price' => 100.5,
                    'currency' => 'USD',
                ]],
            ],
        ]);

        $prebook = $service->recheckAndPreBook('dth:prop-1:0');
        $this->assertTrue($prebook['ok']);
        $this->assertSame('offer-1', $prebook['prebook']['offer_id']);
        $this->assertSame('room-1', $prebook['prebook']['room_id']);
        $this->assertSame(100.5, $prebook['total_price']);

        $book = $service->book([
            'email' => 'guest@example.com',
            'phone' => '+15551234567',
            'prefix' => 'Mr.',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'nationality' => 'US',
            'guests' => [[
                'prefix' => 'Mr.',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'pax_type' => 'Adult',
            ]],
        ]);
        $this->assertTrue($book['ok']);
        $this->assertSame('11111111-2222-3333-4444-555555555555', $book['booking']['booking_id']);
        $this->assertSame('check', $client->calls[array_key_last($client->calls) - 1]['body']['payment_method'] ?? $client->calls[2]['body']['payment_method']);

        $createCall = collect($client->calls)->first(fn ($c) => ($c['path'] ?? '') === '/partner/v2/orders');
        $this->assertNotNull($createCall);
        $this->assertSame('check', $createCall['body']['payment_method']);
        $this->assertSame(100.5, $createCall['body']['total_price']);
        $this->assertSame('room-1', $createCall['body']['booking']['guests']['room_guests'][0]['room_id']);

        $cancel = $service->cancelOrder('11111111-2222-3333-4444-555555555555');
        $this->assertTrue($cancel['ok']);
        $this->assertTrue($cancel['cancelled']);
        $this->assertStringContainsString('/cancel', $client->calls[array_key_last($client->calls)]['path']);
    }
}
