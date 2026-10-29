<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\View;
use Tests\TestCase;

class FlightResultCardClassDisplayTest extends TestCase
{
    public function test_panel_and_public_cards_show_cabin_and_booking_class(): void
    {
        $sol = [
            'key' => 'sol-1',
            'provider' => 'travelport',
            'total_price' => 'USD1244.89',
            'base_price' => 'USD659.00',
            'plating_carrier' => 'QR',
            'cabin' => 'Economy',
            'segments' => [[
                'carrier' => 'QR',
                'flight_number' => '451',
                'origin' => 'EBL',
                'destination' => 'DOH',
                'departure' => '2026-11-10T04:50:00+03:00',
                'arrival' => '2026-11-10T07:15:00+03:00',
                'cabin' => 'Economy',
                'booking_class' => 'N',
                'class_of_service' => 'N',
            ]],
            'journeys' => [[
                'travel_time' => 'P0DT15H35M0S',
                'cabin' => 'Economy',
                'segments' => [[
                    'carrier' => 'QR',
                    'flight_number' => '451',
                    'origin' => 'EBL',
                    'destination' => 'DOH',
                    'departure' => '2026-11-10T04:50:00+03:00',
                    'arrival' => '2026-11-10T07:15:00+03:00',
                    'cabin' => 'Economy',
                    'booking_class' => 'N',
                    'class_of_service' => 'N',
                ]],
            ]],
        ];

        $panel = View::make('flights.partials.result-cards', [
            'searchResult' => ['ok' => true, 'solutions' => [$sol], 'provider' => 'travelport'],
            'searchInput' => ['origin' => 'EBL', 'destination' => 'JFK', 'departure_date' => '2026-11-10', 'adults' => 1],
            'flightsRoutePrefix' => 'agent',
            'providerReady' => true,
            'travelportReady' => true,
        ])->render();

        $this->assertStringContainsString('Cabin Economy', $panel);
        $this->assertStringContainsString('Class N', $panel);

        $public = View::make('frontend.partials.flight-result-card', [
            'sol' => $sol,
            'flightSearchResult' => ['provider' => 'travelport'],
            'travelportReady' => true,
            'providerReady' => true,
        ])->render();

        $this->assertStringContainsString('Cabin Economy', $public);
        $this->assertStringContainsString('Class N', $public);
        $this->assertStringContainsString('04:50', $public);
        $this->assertStringContainsString('07:15', $public);
    }
}
