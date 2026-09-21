<?php

namespace Tests\Unit;

use App\Services\DowntownTravel\DowntownTravelAirService;
use App\Services\DowntownTravel\DowntownTravelClient;
use App\Services\DowntownTravel\DowntownTravelFlightParser;
use Tests\TestCase;

class DowntownTravelAirBookPayloadTest extends TestCase
{
    public function test_parser_stores_agent_net_separately_from_passenger_total(): void
    {
        $parser = new DowntownTravelFlightParser;
        $parsed = $parser->parseOffer([
            'digest' => 'abc123digest',
            'booking_supported' => true,
            'travel_document_required' => false,
            'date_of_birth_required' => true,
            'validating_carrier' => ['iata' => 'QR', 'name' => 'Qatar Airways'],
            'price' => [
                'currency_code' => 'USD',
                'airline_base_fare' => 74,
                'pricing_options' => [
                    'agent_cash' => [
                        'passenger_total' => 459.20,
                        'agent_net_total' => 410.55,
                        'agent_net_per_pax' => ['adult' => 410.55],
                        'passenger_per_pax' => ['adult' => 459.20],
                        'agency_cash_payment' => 410.55,
                        'agent_service_fee' => 0,
                    ],
                ],
            ],
            'flights' => [
                ['segments' => [0], 'price_class' => 0],
            ],
        ], 0, [[
            'departure_point' => ['iata' => 'JFK', 'city' => 'New York', 'timestamp' => '2026-10-01T10:00:00Z'],
            'arrival_point' => ['iata' => 'DOH', 'city' => 'Doha', 'timestamp' => '2026-10-01T20:00:00Z'],
            'marketing_carrier' => ['iata' => 'QR', 'name' => 'Qatar Airways'],
            'flight_code' => 'QR701',
        ]], [['name' => 'Economy']]);

        $this->assertNotNull($parsed);
        $this->assertSame(459.20, $parsed['passenger_total']);
        $this->assertSame(410.55, $parsed['agent_net_total']);
        $this->assertSame(459.20, $parsed['total_amount']);
    }

    public function test_book_uses_preliminary_digest_and_agent_net_total(): void
    {
        $client = new class extends DowntownTravelClient
        {
            /** @var list<array{path: string, body: array<string, mixed>}> */
            public array $calls = [];

            public function postAir(string $path, array $body = [], ?string $token = null): array
            {
                $this->calls[] = ['path' => $path, 'body' => $body];

                if (str_contains($path, 'preliminary')) {
                    return [
                        'ok' => true,
                        'message' => 'OK',
                        'http_status' => 200,
                        'data' => [
                            'offers' => [[
                                'digest' => 'prelim-digest-999',
                                'travel_document_required' => false,
                                'price' => [
                                    'pricing_options' => [
                                        'agent_cash' => [
                                            'agent_net_total' => 410.55,
                                            'passenger_total' => 459.20,
                                        ],
                                    ],
                                ],
                            ]],
                        ],
                    ];
                }

                return [
                    'ok' => true,
                    'message' => 'OK',
                    'http_status' => 200,
                    'data' => ['order_id' => 'ORD-1'],
                ];
            }
        };

        $service = new DowntownTravelAirService($client, new DowntownTravelFlightParser);

        session([
            'downtown_travel.last_price' => [
                'solution' => [
                    'digest' => 'search-digest-111',
                    'total_amount' => 459.20,
                    'agent_net_total' => 410.55,
                    'travel_document_required' => false,
                ],
            ],
        ]);

        $result = $service->book([
            'email' => 'ada@example.com',
            'phone' => '+1 (223) 682-6712',
            'passengers' => [[
                'type' => 'ADT',
                'first' => 'Ada',
                'last' => 'Lovelace',
                'dob' => '1990-11-24',
                'gender' => 'F',
                'nationality' => 'USA',
            ]],
        ]);

        $this->assertTrue($result['ok'] ?? false);
        $this->assertCount(2, $client->calls);
        $this->assertSame('search-digest-111', $client->calls[0]['body']['digest']);
        $this->assertSame('prelim-digest-999', $client->calls[1]['body']['digest']);
        $this->assertSame(410.55, $client->calls[1]['body']['expected_agent_net_price']);
        $this->assertNotSame(459.20, $client->calls[1]['body']['expected_agent_net_price']);
        $this->assertSame('+12236826712', $client->calls[1]['body']['phone']);
        $this->assertSame('US', $client->calls[1]['body']['passengers'][0]['adult']['nationality']);
        $this->assertArrayNotHasKey('middle_name', $client->calls[1]['body']['passengers'][0]['adult']);
    }

    public function test_book_confirms_price_changed_with_new_agent_net(): void
    {
        $client = new class extends DowntownTravelClient
        {
            /** @var list<array{path: string, body: array<string, mixed>}> */
            public array $calls = [];

            public function postAir(string $path, array $body = [], ?string $token = null): array
            {
                $this->calls[] = ['path' => $path, 'body' => $body];

                if (str_contains($path, 'preliminary')) {
                    return [
                        'ok' => true,
                        'http_status' => 200,
                        'data' => [
                            'offers' => [[
                                'digest' => 'prelim-digest',
                                'travel_document_required' => false,
                                'price' => [
                                    'pricing_options' => [
                                        'agent_cash' => ['agent_net_total' => 400.00, 'passenger_total' => 450.00],
                                    ],
                                ],
                            ]],
                        ],
                    ];
                }

                if (str_contains($path, 'orders/booking')) {
                    return [
                        'ok' => false,
                        'message' => 'Price changed (price_changed)',
                        'http_status' => 400,
                        'data' => [
                            'reason' => 'price_changed',
                            'message' => 'Price changed',
                            'order_id' => 'tmp-order-uuid',
                            'pricing' => [
                                'pricing_options' => [
                                    'agent_cash' => ['agent_net_total' => 412.75, 'passenger_total' => 470.00],
                                ],
                            ],
                        ],
                    ];
                }

                if (str_contains($path, 'confirm_price_change')) {
                    return [
                        'ok' => true,
                        'http_status' => 200,
                        'data' => ['id' => 'final-order-1', 'readable_id' => 'DT-100'],
                    ];
                }

                return ['ok' => false, 'message' => 'unexpected', 'http_status' => 500];
            }
        };

        $service = new DowntownTravelAirService($client, new DowntownTravelFlightParser);
        session([
            'downtown_travel.last_price' => [
                'solution' => [
                    'digest' => 'search-digest',
                    'total_amount' => 450,
                    'agent_net_total' => 400,
                    'travel_document_required' => false,
                ],
            ],
        ]);

        $result = $service->book([
            'email' => 'ada@example.com',
            'phone' => '+14057787503',
            'passengers' => [[
                'type' => 'ADT',
                'first' => 'Ada',
                'last' => 'Lovelace',
                'dob' => '1990-08-11',
                'gender' => 'F',
                'nationality' => 'US',
            ]],
        ]);

        $this->assertTrue($result['ok'] ?? false);
        $this->assertSame('final-order-1', $result['universal_locator']);
        $this->assertSame(412.75, $client->calls[2]['body']['expected_agent_net_price']);
        $this->assertStringContainsString('confirm_price_change/tmp-order-uuid', $client->calls[2]['path']);
    }
}
