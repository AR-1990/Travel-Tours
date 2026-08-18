<?php

namespace Tests\Unit;

use App\Support\FlightDisplay;
use Tests\TestCase;

class FlightDisplayTest extends TestCase
{
    public function test_airport_and_airline_friendly_labels(): void
    {
        $this->assertStringContainsString('Chicago', FlightDisplay::airportCity('ORD'));
        $this->assertSame('American Airlines', FlightDisplay::airlineName('AA'));
        $this->assertStringContainsString('American Airlines', FlightDisplay::flightLabel('AA', '100'));
        $this->assertStringContainsString('New York', FlightDisplay::tripSummary('JFK', 'ORD', '2026-08-01', null, 1));
        $this->assertStringContainsString('Chicago', FlightDisplay::tripSummary(
            'JFK',
            'LAX',
            '2026-08-01',
            null,
            2,
            [
                ['origin' => 'JFK', 'destination' => 'ORD', 'departure_date' => '2026-08-01'],
                ['origin' => 'ORD', 'destination' => 'LAX', 'departure_date' => '2026-08-05'],
            ]
        ));
    }

    public function test_price_sort_key_reads_currency_strings(): void
    {
        $this->assertSame(1095.23, FlightDisplay::priceSortKey('USD1095.23'));
        $this->assertSame(1095.23, FlightDisplay::priceSortKey('USD 1,095.23'));
        $this->assertSame(90.0, FlightDisplay::priceSortKey(90));
        $this->assertSame(PHP_FLOAT_MAX, FlightDisplay::priceSortKey(null));
    }
}
