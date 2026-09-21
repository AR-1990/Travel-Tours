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
}
