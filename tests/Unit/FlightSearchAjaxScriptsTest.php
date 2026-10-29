<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\View;
use Tests\TestCase;

class FlightSearchAjaxScriptsTest extends TestCase
{
    public function test_ajax_swap_reinitializes_airport_pickers_and_uses_redirect_url(): void
    {
        $html = View::make('flights.partials.scripts-ajax')->render();

        $this->assertStringContainsString('initAirportPickers', $html);
        $this->assertStringContainsString('initFlightProviderAirportScopes', $html);
        $this->assertStringContainsString('initFlightResultFilters', $html);
        $this->assertStringContainsString('res.url', $html);
        $this->assertStringContainsString('redirect: \'follow\'', $html);
        $this->assertStringContainsString('input[name="origin"]', $html);
    }

    public function test_init_flight_ui_rebinds_airport_pickers_once_per_form(): void
    {
        $html = View::make('flights.partials.scripts-airports', [
            'airportSearchUrl' => '/api/airports/search',
        ])->render();

        $this->assertStringContainsString('initAirportPickers', $html);
        $this->assertStringContainsString('flightUiBound', $html);
    }
}
