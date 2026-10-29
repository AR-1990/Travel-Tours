<?php

namespace Tests\Unit;

use App\Support\FlightProvider;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class FlightProviderAllOptionTest extends TestCase
{
    public function test_options_with_all_lists_all_first(): void
    {
        $options = FlightProvider::optionsWithAll();
        $this->assertSame(FlightProvider::ALL, $options[0]['id'] ?? null);
        $this->assertSame('All', $options[0]['label'] ?? null);
        $this->assertContains(FlightProvider::TRAVELPORT, array_column($options, 'id'));
        $this->assertTrue(FlightProvider::isAggregated(FlightProvider::ALL));
        $this->assertTrue(FlightProvider::isAggregated('mixed'));
    }

    public function test_search_form_hides_selector_for_agency_and_omits_popular_routes(): void
    {
        $agencyHtml = View::make('flights.partials.search-form', [
            'flightsRoutePrefix' => 'agent',
            'searchInput' => [],
            'canChooseFlightProvider' => false,
            'anyProviderReady' => true,
            'travelportReady' => true,
            'sunspringReady' => false,
            'downtownTravelReady' => true,
            'flightProvider' => FlightProvider::ALL,
            'flightProviders' => FlightProvider::optionsWithAll(),
        ])->render();

        $this->assertStringNotContainsString('Search via', $agencyHtml);
        $this->assertStringNotContainsString('Popular routes:', $agencyHtml);
        $this->assertStringContainsString('name="provider"', $agencyHtml);
        $this->assertStringContainsString('value="all"', $agencyHtml);

        $adminHtml = View::make('flights.partials.search-form', [
            'flightsRoutePrefix' => 'admin',
            'searchInput' => ['provider' => 'all'],
            'canChooseFlightProvider' => true,
            'anyProviderReady' => true,
            'travelportReady' => true,
            'sunspringReady' => true,
            'downtownTravelReady' => true,
            'flightProvider' => FlightProvider::ALL,
            'flightProviders' => FlightProvider::optionsWithAll(),
        ])->render();

        $this->assertStringContainsString('Search via', $adminHtml);
        $this->assertStringContainsString('value="all"', $adminHtml);
        $this->assertMatchesRegularExpression('/<span>\s*All(\s*\(off\))?\s*<\/span>/', $adminHtml);
        $this->assertStringNotContainsString('Popular routes:', $adminHtml);
    }

    public function test_status_pills_only_for_super_admin_diagnostics(): void
    {
        $hidden = View::make('flights.partials.status', [
            'anyProviderReady' => true,
            'travelportReady' => true,
            'sunspringReady' => false,
            'downtownTravelReady' => true,
            'showFlightProviderDiagnostics' => false,
            'flightsRoutePrefix' => 'agent',
        ])->render();
        $this->assertStringNotContainsString('Travelport ready', $hidden);

        $shown = View::make('flights.partials.status', [
            'anyProviderReady' => true,
            'travelportReady' => true,
            'sunspringReady' => false,
            'downtownTravelReady' => true,
            'showFlightProviderDiagnostics' => true,
            'flightsRoutePrefix' => 'admin',
        ])->render();
        $this->assertStringContainsString('Travelport ready', $shown);
        $this->assertStringContainsString('Downtown Travel ready', $shown);
    }
}
