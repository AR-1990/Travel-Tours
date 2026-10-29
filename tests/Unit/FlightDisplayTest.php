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
        $withKids = FlightDisplay::tripSummary('KHI', 'JFK', '2026-11-10', '2026-11-30', 1, null, 1, 1);
        $this->assertStringContainsString('1 adult', $withKids);
        $this->assertStringContainsString('1 child', $withKids);
        $this->assertStringContainsString('1 infant', $withKids);
        $this->assertSame(
            ['Adult USD 100.00', 'Child USD 80.00', 'Infant USD 20.00'],
            FlightDisplay::perPaxPriceLines(['adult' => 100, 'child' => 80, 'infant' => 20], 'USD')
        );
    }

    public function test_price_sort_key_reads_currency_strings(): void
    {
        $this->assertSame(1095.23, FlightDisplay::priceSortKey('USD1095.23'));
        $this->assertSame(1095.23, FlightDisplay::priceSortKey('USD 1,095.23'));
        $this->assertSame(90.0, FlightDisplay::priceSortKey(90));
        $this->assertSame(PHP_FLOAT_MAX, FlightDisplay::priceSortKey(null));
    }

    public function test_parse_price_accepts_spaced_downtown_amounts(): void
    {
        $compact = FlightDisplay::parsePrice('USD770.10');
        $spaced = FlightDisplay::parsePrice('USD 770.10');
        $this->assertSame('USD', $compact['currency'] ?? null);
        $this->assertSame('770.10', $compact['amount'] ?? null);
        $this->assertSame('USD', $spaced['currency'] ?? null);
        $this->assertSame('770.10', $spaced['amount'] ?? null);
    }

    public function test_stop_bucket_uses_outbound_journey_only(): void
    {
        $solution = [
            'plating_carrier' => 'GF',
            'segments' => [
                ['carrier' => 'GF'], ['carrier' => 'GF'],
                ['carrier' => 'GF'], ['carrier' => 'GF'],
            ],
            'journeys' => [
                ['segments' => [['carrier' => 'GF'], ['carrier' => 'FZ']]],
                ['segments' => [['carrier' => 'GF'], ['carrier' => 'GF']]],
            ],
        ];

        $this->assertSame('1', FlightDisplay::stopBucket($solution));
        $this->assertSame(['GF', 'FZ'], FlightDisplay::airlineCodes($solution));
        $this->assertSame('GF', FlightDisplay::primaryAirlineCode($solution));
    }

    public function test_airline_codes_include_marketing_carriers_when_plating_missing(): void
    {
        $solution = [
            'segments' => [
                ['carrier' => 'F9'],
                ['carrier' => 'F9'],
                ['carrier' => 'F9'],
            ],
        ];

        $this->assertSame('2', FlightDisplay::stopBucket($solution));
        $this->assertSame(['F9'], FlightDisplay::airlineCodes($solution));
        $this->assertSame('Frontier Airlines', FlightDisplay::airlineName('F9'));
        $this->assertSame('flydubai', FlightDisplay::airlineName('FZ'));
    }

    public function test_airline_code_resolves_flydubai_and_frontier_names(): void
    {
        $this->assertSame('FZ', FlightDisplay::airlineCode('flydubai'));
        $this->assertSame('F9', FlightDisplay::airlineCode('Frontier Airlines'));
        $this->assertSame(['FZ'], FlightDisplay::airlineCodes([
            'airline_name' => 'flydubai',
            'segments' => [['airline_name' => 'flydubai']],
        ]));
    }

    public function test_for_display_currency_keeps_host_when_no_rate(): void
    {
        $parsed = FlightDisplay::forDisplayCurrency('USD100.00', 'PKR');
        $this->assertSame('USD', $parsed['currency'] ?? null);
        $this->assertSame('100.00', $parsed['amount'] ?? null);
    }

    public function test_cabin_booking_class_and_duration_labels(): void
    {
        $this->assertSame('Economy', FlightDisplay::cabinLabel('economy'));
        $this->assertSame('Premium Economy', FlightDisplay::cabinLabel('premium_economy'));
        $this->assertSame('Business', FlightDisplay::cabinLabel('C'));
        $this->assertSame('T', FlightDisplay::bookingClass(['booking_class' => 't']));
        $this->assertSame('L', FlightDisplay::bookingClass(['class_of_service' => 'L']));
        $this->assertSame('20h 40m', FlightDisplay::formatDuration('P0DT20H40M0S'));
        $this->assertSame('13h 10m', FlightDisplay::formatDuration('790'));
        $this->assertSame('2h 30m', FlightDisplay::formatDuration('PT2H30M'));
    }
}
