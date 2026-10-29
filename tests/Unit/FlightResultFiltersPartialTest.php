<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\View;
use Tests\TestCase;

class FlightResultFiltersPartialTest extends TestCase
{
    public function test_agency_result_cards_include_stop_and_airline_filters(): void
    {
        $html = View::make('flights.partials.result-cards', [
            'flightsRoutePrefix' => 'agent',
            'searchInput' => [
                'origin' => 'EBL',
                'destination' => 'DXB',
                'departure_date' => '2026-11-10',
                'return_date' => '2026-11-30',
                'adults' => 1,
                'children' => 0,
                'infants' => 0,
            ],
            'searchResult' => [
                'ok' => true,
                'provider' => 'mixed',
                'solutions' => [
                    [
                        'key' => 'a',
                        'provider' => 'travelport',
                        'total_price' => 'USD 636.20',
                        'plating_carrier' => 'FZ',
                        'airline_name' => 'flydubai',
                        'segments' => [[
                            'carrier' => 'FZ',
                            'flight_number' => '1',
                            'origin' => 'EBL',
                            'destination' => 'DXB',
                            'departure' => '2026-11-10T10:00:00',
                            'arrival' => '2026-11-10T12:00:00',
                        ]],
                        'journeys' => [[
                            'segments' => [[
                                'carrier' => 'FZ',
                                'flight_number' => '1',
                                'origin' => 'EBL',
                                'destination' => 'DXB',
                                'departure' => '2026-11-10T10:00:00',
                                'arrival' => '2026-11-10T12:00:00',
                            ]],
                        ]],
                    ],
                    [
                        'key' => 'b',
                        'provider' => 'downtown_travel',
                        'total_price' => 'USD 656.80',
                        'currency' => 'USD',
                        'plating_carrier' => 'QR',
                        'airline_name' => 'Qatar Airways',
                        'passenger_per_pax' => ['adult' => 500.00, 'child' => 350.00, 'infant' => 50.00],
                        'segments' => [
                            [
                                'carrier' => 'QR',
                                'flight_number' => '1',
                                'origin' => 'EBL',
                                'destination' => 'DOH',
                                'departure' => '2026-11-10T08:00:00',
                                'arrival' => '2026-11-10T10:00:00',
                            ],
                            [
                                'carrier' => 'QR',
                                'flight_number' => '2',
                                'origin' => 'DOH',
                                'destination' => 'DXB',
                                'departure' => '2026-11-10T12:00:00',
                                'arrival' => '2026-11-10T14:00:00',
                            ],
                        ],
                        'journeys' => [[
                            'segments' => [
                                [
                                    'carrier' => 'QR',
                                    'flight_number' => '1',
                                    'origin' => 'EBL',
                                    'destination' => 'DOH',
                                    'departure' => '2026-11-10T08:00:00',
                                    'arrival' => '2026-11-10T10:00:00',
                                ],
                                [
                                    'carrier' => 'QR',
                                    'flight_number' => '2',
                                    'origin' => 'DOH',
                                    'destination' => 'DXB',
                                    'departure' => '2026-11-10T12:00:00',
                                    'arrival' => '2026-11-10T14:00:00',
                                ],
                            ],
                        ]],
                    ],
                ],
            ],
            'providerReady' => true,
            'travelportReady' => true,
            'downtownTravelReady' => true,
            'sunspringReady' => false,
        ])->render();

        $this->assertStringContainsString('Stops:', $html);
        $this->assertStringContainsString('Airlines:', $html);
        $this->assertStringContainsString('js-stop-filter', $html);
        $this->assertStringContainsString('js-airline-filter', $html);
        $this->assertStringContainsString('data-airline="FZ"', $html);
        $this->assertStringContainsString('Adult USD 500.00', $html);
        $this->assertStringContainsString('Child USD 350.00', $html);
        $this->assertStringContainsString('Infant USD 50.00', $html);

        $filtersHtml = View::make('flights.partials.result-filters', [
            'filterSolutions' => [
                [
                    'total_price' => 'USD 100.00',
                    'plating_carrier' => 'FZ',
                    'segments' => [['carrier' => 'FZ']],
                    'journeys' => [['segments' => [['carrier' => 'FZ']]]],
                ],
                [
                    'total_price' => 'USD 120.00',
                    'plating_carrier' => 'F9',
                    'segments' => [['carrier' => 'F9']],
                    'journeys' => [['segments' => [['carrier' => 'F9']]]],
                ],
            ],
            'resultsListId' => 'agency-flight-results-list',
        ])->render();
        $this->assertStringContainsString('data-results-list="agency-flight-results-list"', $filtersHtml);
        $this->assertStringContainsString('data-airline="FZ"', $filtersHtml);
        $this->assertStringContainsString('data-airline="F9"', $filtersHtml);
    }
}
