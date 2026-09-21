<?php

namespace Tests\Unit;

use App\Services\DowntownTravel\DowntownTravelAirService;
use App\Services\DowntownTravel\DowntownTravelClient;
use App\Services\DowntownTravel\DowntownTravelFlightParser;
use Tests\TestCase;

class DowntownTravelPostBookApisTest extends TestCase
{
    public function test_get_order_issue_cancel_void_and_refund_paths(): void
    {
        $client = new class extends DowntownTravelClient
        {
            /** @var list<array{method: string, path: string, body: array<string, mixed>}> */
            public array $calls = [];

            public function getAir(string $path, array $query = [], ?string $token = null): array
            {
                $this->calls[] = ['method' => 'GET', 'path' => $path, 'body' => $query];

                return [
                    'ok' => true,
                    'http_status' => 200,
                    'data' => [
                        'id' => 'order-1',
                        'readable_id' => 9001,
                        'booking_records' => [[
                            'id' => 'br-1',
                            'can_ticket' => true,
                            'can_cancel' => true,
                            'can_void' => false,
                            'can_refund' => false,
                            'airline_record_locator' => 'ABC123',
                            'passengers' => [[
                                'adult' => [
                                    'air_ticket' => ['ticket_number' => '0161234567890'],
                                ],
                            ]],
                        ]],
                    ],
                ];
            }

            public function postAir(string $path, array $body = [], ?string $token = null): array
            {
                $this->calls[] = ['method' => 'POST', 'path' => $path, 'body' => $body];

                if (str_contains($path, '/issue')) {
                    return [
                        'ok' => true,
                        'http_status' => 200,
                        'data' => [
                            'id' => 'br-1',
                            'passengers' => [[
                                'adult' => ['air_ticket' => ['ticket_number' => '0169999999999']],
                            ]],
                        ],
                    ];
                }

                if (str_contains($path, '/create_refund_offer')) {
                    return [
                        'ok' => true,
                        'http_status' => 200,
                        'data' => ['offer' => ['id' => 'offer-1']],
                    ];
                }

                return [
                    'ok' => true,
                    'http_status' => 200,
                    'data' => ['id' => 'br-1'],
                ];
            }
        };

        $service = new DowntownTravelAirService($client, new DowntownTravelFlightParser);

        $order = $service->getOrder('order-1');
        $this->assertTrue($order['ok']);
        $this->assertSame('br-1', $order['booking_record_id']);
        $this->assertSame(['0161234567890'], $order['ticket_numbers']);
        $this->assertSame('GET', $client->calls[0]['method']);
        $this->assertSame('/api/public/v2/orders/order-1', $client->calls[0]['path']);

        $issue = $service->issueTickets([
            'booking_record_id' => 'br-1',
            'phone' => '+14057787503',
            'passengers' => [[
                'type' => 'ADT',
                'first' => 'Ada',
                'last' => 'Lovelace',
                'dob' => '1990-01-01',
                'gender' => 'F',
                'nationality' => 'US',
            ]],
        ]);
        $this->assertTrue($issue['ok']);
        $this->assertSame(['0169999999999'], $issue['ticket_numbers']);
        $this->assertSame('agent_cash', $client->calls[1]['body']['payment_option']);

        $cancel = $service->cancelBookingRecord('br-1');
        $this->assertTrue($cancel['ok']);
        $this->assertTrue($cancel['cancelled']);
        $this->assertStringContainsString('/cancel', $client->calls[2]['path']);

        $void = $service->voidBookingRecord('br-1');
        $this->assertTrue($void['ok']);
        $this->assertTrue($void['voided']);

        $offer = $service->createRefundOffer('br-1');
        $this->assertTrue($offer['ok']);
        $this->assertSame('offer-1', $offer['offer_id']);

        $refund = $service->refundBookingRecord('br-1', 'offer-1');
        $this->assertTrue($refund['ok']);
        $this->assertSame(['id' => 'offer-1'], $client->calls[array_key_last($client->calls)]['body']);

        $instant = $service->instantIssue([
            'booking_record_id' => 'br-1',
            'payment_option' => 'agent_cash',
        ]);
        $this->assertTrue($instant['ok']);
        $this->assertStringContainsString('/instant_issue', $client->calls[array_key_last($client->calls)]['path']);

        $list = $service->listOrders(['limit' => 10]);
        $this->assertTrue($list['ok']);
        $this->assertSame('GET', $client->calls[array_key_last($client->calls)]['method']);
        $this->assertSame('/api/public/v2/orders', $client->calls[array_key_last($client->calls)]['path']);

        $comments = $service->listOrderComments('order-1');
        $this->assertTrue($comments['ok']);
        $this->assertSame(['order' => 'order-1'], $client->calls[array_key_last($client->calls)]['body']);

        $added = $service->addOrderComment('order-1', 'Follow up');
        $this->assertTrue($added['ok']);
        $this->assertSame('Follow up', $client->calls[array_key_last($client->calls)]['body']['comment']);
    }
}
