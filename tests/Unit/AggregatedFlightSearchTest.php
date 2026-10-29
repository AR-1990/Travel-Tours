<?php

namespace Tests\Unit;

use App\Support\AggregatedFlightSearch;
use PHPUnit\Framework\TestCase;

class AggregatedFlightSearchTest extends TestCase
{
    public function test_merge_tags_each_fare_with_its_api(): void
    {
        $merged = AggregatedFlightSearch::merge([
            'travelport' => [
                'ok' => true,
                'message' => 'tp',
                'solutions' => [['key' => 'tp-1', 'total_price' => '100 USD']],
            ],
            'sunspring' => [
                'ok' => true,
                'message' => 'ss',
                'solutions' => [['key' => 'ss-1', 'total_price' => '90 USD']],
            ],
        ]);

        $this->assertTrue($merged['ok']);
        $this->assertSame('mixed', $merged['provider']);
        $this->assertCount(2, $merged['solutions']);
        $this->assertSame('sunspring', $merged['solutions'][0]['provider']);
        $this->assertSame('travelport', $merged['solutions'][1]['provider']);
        $this->assertSame(1, $merged['sources']['travelport']['count']);
        $this->assertSame(1, $merged['sources']['sunspring']['count']);
        $this->assertStringContainsString('Travelport: 1', $merged['message']);
        $this->assertStringContainsString('SunSpring: 1', $merged['message']);
        $this->assertArrayHasKey('environment', $merged['solutions'][0]);
        $this->assertContains($merged['solutions'][0]['environment'], ['live', 'sandbox']);
    }

    public function test_merge_sorts_fares_low_to_high(): void
    {
        $merged = AggregatedFlightSearch::merge([
            'travelport' => [
                'ok' => true,
                'message' => 'tp',
                'solutions' => [
                    ['key' => 'tp-high', 'total_price' => 'USD200.00'],
                    ['key' => 'tp-low', 'total_price' => 'USD80.00'],
                ],
            ],
            'sunspring' => [
                'ok' => true,
                'message' => 'ss',
                'solutions' => [['key' => 'ss-mid', 'total_price' => 'USD90.00']],
            ],
        ]);

        $this->assertSame(['tp-low', 'ss-mid', 'tp-high'], array_column($merged['solutions'], 'key'));
    }

    public function test_sort_by_price_high_to_low(): void
    {
        $sorted = AggregatedFlightSearch::sortByPrice([
            ['key' => 'a', 'total_price' => 'USD80.00'],
            ['key' => 'b', 'total_price' => 'USD200.00'],
        ], 'desc');

        $this->assertSame(['b', 'a'], array_column($sorted, 'key'));
    }

    public function test_merge_keeps_one_api_when_the_other_is_missing(): void
    {
        $merged = AggregatedFlightSearch::merge([
            'travelport' => null,
            'sunspring' => [
                'ok' => true,
                'message' => 'ss',
                'solutions' => [['key' => 'ss-1']],
            ],
        ]);

        $this->assertTrue($merged['ok']);
        $this->assertSame('sunspring', $merged['provider']);
        $this->assertCount(1, $merged['solutions']);
        $this->assertSame('sunspring', $merged['solutions'][0]['provider']);
    }

    public function test_dedupe_drops_identical_downtown_cards_keeps_real_variants(): void
    {
        $segmentA = [
            'carrier' => 'ET',
            'flight_number' => '377',
            'origin' => 'MGQ',
            'destination' => 'ADD',
            'departure' => '2026-11-03T11:50:00+03:00',
            'arrival' => '2026-11-03T14:00:00+03:00',
        ];
        $segmentB = [
            'carrier' => 'ET',
            'flight_number' => '700',
            'origin' => 'ADD',
            'destination' => 'IST',
            'departure' => '2026-11-03T16:00:00+03:00',
            'arrival' => '2026-11-04T08:55:00+03:00',
        ];
        $segmentBLater = [
            'carrier' => 'ET',
            'flight_number' => '700',
            'origin' => 'ADD',
            'destination' => 'IST',
            'departure' => '2026-11-03T16:00:00+03:00',
            'arrival' => '2026-11-04T13:50:00+03:00',
        ];

        $merged = AggregatedFlightSearch::merge([
            'downtown_travel' => [
                'ok' => true,
                'message' => 'dt',
                'solutions' => [
                    [
                        'key' => 'dup-1',
                        'total_price' => 'USD 1888.00',
                        'plating_carrier' => 'ET',
                        'segments' => [$segmentA, $segmentB],
                        'digest' => 'aaa',
                    ],
                    [
                        'key' => 'dup-2',
                        'total_price' => 'USD 1888.00',
                        'plating_carrier' => 'ET',
                        'segments' => [$segmentA, $segmentB],
                        'digest' => 'bbb',
                    ],
                    [
                        'key' => 'variant-arrival',
                        'total_price' => 'USD 1985.10',
                        'plating_carrier' => 'ET',
                        'segments' => [$segmentA, $segmentBLater],
                        'digest' => 'ccc',
                    ],
                ],
            ],
            'travelport' => [
                'ok' => true,
                'message' => 'tp',
                'solutions' => [[
                    'key' => 'tp-same-itin',
                    'total_price' => 'USD 1888.00',
                    'plating_carrier' => 'ET',
                    'segments' => [$segmentA, $segmentB],
                ]],
            ],
        ]);

        $keys = array_column($merged['solutions'], 'key');
        $this->assertContains('dup-1', $keys);
        $this->assertNotContains('dup-2', $keys);
        $this->assertContains('variant-arrival', $keys);
        // Same schedule from another API must remain selectable.
        $this->assertContains('tp-same-itin', $keys);
        $this->assertSame(2, $merged['sources']['downtown_travel']['count']);
        $this->assertSame(1, $merged['sources']['travelport']['count']);
    }

    public function test_dedupe_drops_same_provider_key_and_digest(): void
    {
        $merged = AggregatedFlightSearch::merge([
            'downtown_travel' => [
                'ok' => true,
                'message' => 'dt',
                'solutions' => [
                    ['key' => 'same-key', 'digest' => 'd1', 'total_price' => 'USD 10.00', 'plating_carrier' => 'FZ'],
                    ['key' => 'same-key', 'digest' => 'd2', 'total_price' => 'USD 99.00', 'plating_carrier' => 'FZ'],
                    ['key' => 'other-key', 'digest' => 'd1', 'total_price' => 'USD 11.00', 'plating_carrier' => 'F9'],
                    ['key' => 'kept', 'digest' => 'd3', 'total_price' => 'USD 12.00', 'plating_carrier' => 'EK'],
                ],
            ],
        ]);

        $keys = array_column($merged['solutions'], 'key');
        $this->assertSame(['same-key', 'kept'], $keys);
    }
}
