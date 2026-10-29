<?php

namespace Tests\Unit;

use App\Models\FlightReservation;
use App\Support\FlightSalesReport;
use Tests\TestCase;

class FlightSalesReportTest extends TestCase
{
    public function test_sales_row_exposes_reference_columns(): void
    {
        $r = new FlightReservation([
            'channel' => 'public',
            'status' => FlightReservation::STATUS_TICKETED,
            'air_reservation_locator' => 'ABC123',
            'origin' => 'KHI',
            'destination' => 'DXB',
            'departure_date' => '2026-11-10',
            'return_date' => '2026-11-30',
            'carrier' => 'FZ',
            'passenger_first' => 'Ali',
            'passenger_last' => 'Khan',
            'total_price' => 'USD 500.00',
            'base_price' => 'USD 400.00',
            'taxes' => 'USD 100.00',
            'service_fee' => 10,
            'airline_fees_total' => 5,
            'markup_amount' => 20,
            'sell_total' => 525,
            'agent_profit' => 20,
            'commission_amount' => 15,
            'currency' => 'USD',
            'form_of_payment' => 'Cash',
            'ticket_numbers' => ['6032953299076'],
            'itinerary' => [
                'segments' => [
                    ['origin' => 'KHI', 'destination' => 'DXB', 'arrival' => '2026-11-10T14:00:00'],
                    ['origin' => 'DXB', 'destination' => 'KHI', 'arrival' => '2026-11-30T22:00:00'],
                ],
            ],
            'booked_at' => now(),
        ]);
        $r->id = 42;

        $this->assertSame('6032953299076', $r->ticketNumbersLabel());
        $this->assertSame('ABC123', $r->pnrLabel());
        $this->assertSame('KHI-DXB-KHI', $r->salesRouteLabel());
        $this->assertSame(525.0, $r->salesTotalAmount());
        $this->assertSame(400.0, $r->salesBaseAmount());
        $this->assertSame(100.0, $r->salesTaxAmount());
        $this->assertSame('B2C / Public', $r->salesAgencyLabel());
        $this->assertSame('2026-11-30', $r->salesArrivalLabel());

        $headers = FlightSalesReport::csvHeaders(true);
        $this->assertContains('e_ticket', $headers);
        $this->assertContains('agency', $headers);
        $this->assertContains('departure', $headers);

        $row = FlightSalesReport::csvRow($r, true);
        $this->assertSame('6032953299076', $row[0]);
        $this->assertSame('ABC123', $row[1]);
        $this->assertSame(42, $row[2]);
        $this->assertContains('agency', $headers);
    }

    public function test_sell_falls_back_to_total_price_when_sell_total_missing(): void
    {
        $r = new FlightReservation([
            'total_price' => 'USD3820.77',
            'sell_total' => null,
        ]);

        $this->assertSame(3820.77, $r->salesTotalAmount());
    }
}
