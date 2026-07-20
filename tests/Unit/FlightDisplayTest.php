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
    }
}
