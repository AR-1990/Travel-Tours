<?php

namespace Tests\Unit;

use App\Services\SunSpring\SunSpringAirService;
use App\Services\SunSpring\SunSpringClient;
use App\Services\SunSpring\SunSpringFlightParser;
use Tests\TestCase;

class SunSpringAirRuleAndCancelTrackingTest extends TestCase
{
    public function test_air_rule_and_cancel_tracking_paths(): void
    {
        $client = new class extends SunSpringClient
        {
            /** @var list<array{path: string, body: array<string, mixed>}> */
            public array $calls = [];

            public function post(string $path, array $body = [], ?string $token = null): array
            {
                $this->calls[] = ['path' => $path, 'body' => $body];

                if (str_contains($path, 'AirRule')) {
                    return [
                        'ok' => true,
                        'http_status' => 200,
                        'data' => [
                            'status' => 'Success',
                            'rules' => [[
                                'DepartureAirport' => 'THR',
                                'ArrivalAirport' => 'MHD',
                                'refundRuleArray' => [
                                    ['title' => '72 hour before', 'penalty' => '10.00'],
                                ],
                            ]],
                        ],
                    ];
                }

                if (str_contains($path, 'CancelTracking')) {
                    return [
                        'ok' => true,
                        'http_status' => 200,
                        'data' => [
                            'status' => 'success',
                            'message' => 'refunded successfuly.',
                            'penalty' => '37',
                            'tickets' => [[
                                'ticket_number' => '2452258276',
                                'status' => 'success',
                                'penalty' => '37',
                            ]],
                        ],
                    ];
                }

                if (str_contains($path, 'Cancel')) {
                    return [
                        'ok' => true,
                        'http_status' => 200,
                        'data' => [
                            'status' => 'process',
                            'request_id' => '1459',
                            'msg' => 'success.',
                        ],
                    ];
                }

                return ['ok' => true, 'http_status' => 200, 'data' => []];
            }
        };

        $service = new SunSpringAirService($client, new SunSpringFlightParser);

        $rules = $service->airRule(['ref-a', 'ref-b']);
        $this->assertTrue($rules['ok']);
        $this->assertCount(1, $rules['rules']);
        $this->assertSame('/api/v2/flight/AirRule', $client->calls[0]['path']);
        $this->assertSame(['ref-a', 'ref-b'], $client->calls[0]['body']['ref_number']);

        $track = $service->cancelTracking('1459');
        $this->assertTrue($track['ok']);
        $this->assertSame('37', $track['penalty']);
        $this->assertSame('2452258276', $track['tickets'][0]['ticket_number']);
        $this->assertSame('/api/v2/flight/CancelTracking', $client->calls[1]['path']);

        $cancel = $service->cancel([
            'reference' => '274451',
            'tickets' => ['2452258276'],
            'voucher' => ['ABC123'],
        ]);
        $this->assertTrue($cancel['ok']);
        $this->assertSame('1459', $cancel['request_id']);
    }
}
