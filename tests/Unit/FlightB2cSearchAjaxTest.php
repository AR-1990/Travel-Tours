<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\View;
use Tests\TestCase;

class FlightB2cSearchAjaxTest extends TestCase
{
    public function test_results_page_swaps_shell_without_full_refresh(): void
    {
        $html = View::make('frontend.flight-results', [
            'flightSearchInput' => [
                'origin' => 'KHI',
                'destination' => 'DXB',
                'departure_date' => '2026-11-10',
                'adults' => 1,
                'children' => 0,
                'infants' => 0,
                'trip_type' => 'oneway',
            ],
            'flightSearchResult' => [
                'ok' => true,
                'solutions' => [],
                'provider' => 'travelport',
            ],
            'travelportReady' => true,
            'sunspringReady' => false,
            'downtownTravelReady' => false,
            'providerReady' => true,
        ])->render();

        $this->assertStringContainsString('id="b2c-flights-shell"', $html);
        $this->assertStringContainsString('__b2cFlightSearchAjax', $html);
        $this->assertStringContainsString('homeFlightSearchForm', $html);
        $this->assertStringContainsString('initFlightResultFilters', $html);
    }
}
