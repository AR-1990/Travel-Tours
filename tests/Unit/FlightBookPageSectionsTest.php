<?php

namespace Tests\Unit;

use App\Support\FlightProvider;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class FlightBookPageSectionsTest extends TestCase
{
    public function test_book_page_renders_sections_in_reference_order(): void
    {
        $flightPriceResult = [
            'ok' => true,
            'provider' => FlightProvider::TRAVELPORT,
            'solutions' => [[
                'key' => 'sol1',
                'total_price' => 'USD900.00',
                'base_price' => 'USD700.00',
                'taxes' => 'USD200.00',
                'plating_carrier' => 'EK',
                'fare_basis' => 'TLXEPK1',
                'cabin' => 'Economy',
                'segments' => [[
                    'carrier' => 'EK',
                    'flight_number' => '319',
                    'origin' => 'NRT',
                    'destination' => 'DXB',
                    'departure' => '2026-12-14T22:20:00+09:00',
                    'arrival' => '2026-12-15T05:30:00+04:00',
                    'cabin' => 'Economy',
                    'booking_class' => 'T',
                    'class_of_service' => 'T',
                ]],
                'journeys' => [[
                    'travel_time' => 'P0DT12H10M0S',
                    'cabin' => 'Economy',
                    'segments' => [[
                        'carrier' => 'EK',
                        'flight_number' => '319',
                        'origin' => 'NRT',
                        'destination' => 'DXB',
                        'departure' => '2026-12-14T22:20:00+09:00',
                        'arrival' => '2026-12-15T05:30:00+04:00',
                        'cabin' => 'Economy',
                        'booking_class' => 'T',
                        'class_of_service' => 'T',
                    ]],
                ]],
            ]],
            'fare_rules' => [[
                'DepartureAirport' => 'NRT',
                'ArrivalAirport' => 'DXB',
                'FareRules' => [
                    ['title' => 'Changes', 'value' => 'Permitted with fee'],
                ],
            ]],
        ];

        $details = View::make('flights.partials.book-itinerary-details', [
            'searchResult' => $flightPriceResult,
        ])->render();
        $rules = View::make('flights.partials.book-flight-rules', [
            'flightPriceResult' => $flightPriceResult,
        ])->render();
        $passengers = '3. Passenger details'.View::make('flights.partials.passenger-book-fields', [
            'flightPriceResult' => $flightPriceResult,
            'flightProvider' => FlightProvider::TRAVELPORT,
            'passengerSlots' => [['type' => 'ADT', 'label' => 'Adult 1', 'prefix' => 'Mr', 'gender' => 'M']],
            'bookInput' => [],
            'compact' => false,
        ])->render();
        $payment = View::make('flights.partials.book-payment-fields', [
            'flightPriceResult' => $flightPriceResult,
            'flightProvider' => FlightProvider::TRAVELPORT,
            'compact' => false,
        ])->render();

        $html = $details.$rules.$passengers.$payment;

        $detailsPos = strpos($html, '1. Flight details');
        $rulesPos = strpos($html, '2. Flight rules');
        $passengersPos = strpos($html, '3. Passenger details');
        $paymentPos = strpos($html, '4. Payment information');

        $this->assertNotFalse($detailsPos);
        $this->assertNotFalse($rulesPos);
        $this->assertNotFalse($passengersPos);
        $this->assertNotFalse($paymentPos);
        $this->assertTrue($detailsPos < $rulesPos);
        $this->assertTrue($rulesPos < $passengersPos);
        $this->assertTrue($passengersPos < $paymentPos);
        $this->assertStringContainsString('Booking class:', $html);
        $this->assertStringContainsString('name="form_of_payment"', $html);
        $this->assertStringContainsString('name="passengers[0][first]"', $html);
        $this->assertStringNotContainsString('name="form_of_payment"', $passengers);
        // Ticket print with/without price belongs on reservation page after booking.
        $this->assertStringNotContainsString('Ticket print', $payment);
        $this->assertStringNotContainsString('name="print_with_price"', $payment);
    }

    public function test_reservation_detail_shows_ticket_print_with_and_without_price(): void
    {
        $reservation = new \App\Models\FlightReservation([
            'origin' => 'LHR',
            'destination' => 'JFK',
            'status' => \App\Models\FlightReservation::STATUS_RESERVED,
            'currency' => 'USD',
            'total_price' => 'USD100.00',
            'base_price' => 'USD80.00',
            'taxes' => 'USD20.00',
            'sell_total' => 100,
            'markup_amount' => 0,
            'service_fee' => 0,
            'agent_profit' => 0,
        ]);
        $reservation->id = 99;

        $html = View::make('flights.partials.reservation-detail', [
            'reservation' => $reservation,
            'flightsRoutePrefix' => 'admin',
            'searchInput' => [],
            'ticketPdfRoute' => '/admin/flights/reservations/99/ticket.pdf',
            'ticketPrintRoute' => '/admin/flights/reservations/99/ticket-print',
        ])->render();

        $this->assertStringContainsString('Ticket print', $html);
        $this->assertStringContainsString('with_price=1', $html);
        $this->assertStringContainsString('with_price=0', $html);
        $this->assertStringContainsString('Print with price', $html);
        $this->assertStringContainsString('Print without price', $html);
    }
}
