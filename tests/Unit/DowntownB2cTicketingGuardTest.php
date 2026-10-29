<?php

namespace Tests\Unit;

use App\Models\FlightReservation;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class DowntownB2cTicketingGuardTest extends TestCase
{
    public function test_public_downtown_reservation_disallows_customer_ticketing(): void
    {
        $public = new FlightReservation([
            'channel' => 'public',
            'form_of_payment' => 'Credit',
            'raw_result' => ['provider' => 'downtown_travel'],
        ]);
        $agency = new FlightReservation([
            'channel' => 'agent',
            'form_of_payment' => 'Wallet',
            'raw_result' => ['provider' => 'downtown_travel'],
        ]);

        $this->assertTrue($public->isPublicChannel());
        $this->assertFalse($public->allowsCustomerDowntownTicketing());
        $this->assertTrue($public->requiresCollectedPaymentBeforeIssue());
        $this->assertTrue($agency->allowsCustomerDowntownTicketing());
        $this->assertFalse($agency->requiresCollectedPaymentBeforeIssue());
    }

    public function test_public_reservation_ui_hides_downtown_agent_issue_controls(): void
    {
        $reservation = new FlightReservation([
            'id' => 99,
            'channel' => 'public',
            'status' => FlightReservation::STATUS_RESERVED,
            'universal_locator' => 'ord-1',
            'air_reservation_locator' => 'PNR1',
            'origin' => 'EBL',
            'destination' => 'STN',
            'carrier' => 'TK',
            'passenger_first' => 'Ada',
            'passenger_last' => 'Lovelace',
            'form_of_payment' => 'Credit',
            'raw_result' => ['provider' => 'downtown_travel'],
            'price_snapshot' => [
                'provider' => 'downtown_travel',
                'solutions' => [[
                    'total_price' => 'USD 770.10',
                    'base_price' => 'USD 199.00',
                    'plating_carrier' => 'TK',
                    'segments' => [],
                    'journeys' => [],
                ]],
            ],
        ]);

        $html = View::make('flights.partials.reservation-detail', [
            'reservation' => $reservation,
            'flightBooking' => $reservation->toWorkflowBookingArray(),
            'flightPriceResult' => $reservation->price_snapshot,
            'flightTicket' => null,
            'canBookFlights' => true,
            'isPublicBookingUi' => true,
            'providerReady' => true,
            'ticketActionRoute' => 'http://example.test/ticket',
            'retrieveActionRoute' => 'http://example.test/retrieve',
            'cancelActionRoute' => 'http://example.test/cancel',
            'voidActionRoute' => 'http://example.test/void',
            'refundActionRoute' => 'http://example.test/refund',
        ])->render();

        $this->assertStringNotContainsString('Downtown Travel flow', $html);
        $this->assertStringNotContainsString('Downtown ticketing', $html);
        $this->assertStringNotContainsString('Issue Downtown tickets', $html);
        $this->assertStringContainsString('after payment is confirmed', $html);
    }

    public function test_agency_reservation_ui_keeps_downtown_issue_controls(): void
    {
        $reservation = new FlightReservation([
            'id' => 100,
            'channel' => 'agent',
            'status' => FlightReservation::STATUS_RESERVED,
            'universal_locator' => 'ord-2',
            'air_reservation_locator' => 'PNR2',
            'origin' => 'EBL',
            'destination' => 'STN',
            'carrier' => 'TK',
            'passenger_first' => 'Ada',
            'passenger_last' => 'Lovelace',
            'form_of_payment' => 'Wallet',
            'raw_result' => ['provider' => 'downtown_travel'],
            'gds_snapshot' => ['can_ticket' => true],
            'price_snapshot' => [
                'provider' => 'downtown_travel',
                'solutions' => [[
                    'total_price' => 'USD 770.10',
                    'base_price' => 'USD 199.00',
                    'plating_carrier' => 'TK',
                    'segments' => [],
                    'journeys' => [],
                ]],
            ],
        ]);

        $html = View::make('flights.partials.reservation-detail', [
            'reservation' => $reservation,
            'flightBooking' => $reservation->toWorkflowBookingArray(),
            'flightPriceResult' => $reservation->price_snapshot,
            'flightTicket' => null,
            'canBookFlights' => true,
            'isPublicBookingUi' => false,
            'providerReady' => true,
            'ticketActionRoute' => 'http://example.test/ticket',
            'retrieveActionRoute' => 'http://example.test/retrieve',
            'cancelActionRoute' => 'http://example.test/cancel',
        ])->render();

        $this->assertStringContainsString('Downtown Travel flow', $html);
        $this->assertStringContainsString('Issue Downtown tickets', $html);
    }
}
