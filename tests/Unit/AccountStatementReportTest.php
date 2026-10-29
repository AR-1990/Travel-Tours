<?php

namespace Tests\Unit;

use App\Models\Finance\WalletTransaction;
use App\Models\FlightReservation;
use App\Support\AccountStatementReport;
use Tests\TestCase;

class AccountStatementReportTest extends TestCase
{
    public function test_statement_row_matches_reference_columns(): void
    {
        $reservation = new FlightReservation([
            'carrier' => 'FZ',
            'air_reservation_locator' => 'PTMORI',
            'departure_date' => '2026-11-10',
            'origin' => 'KHI',
            'destination' => 'DXB',
        ]);
        $reservation->id = 30482740;

        $tx = new WalletTransaction([
            'type' => WalletTransaction::TYPE_SALE,
            'direction' => WalletTransaction::DIR_DEBIT,
            'amount' => 128638.40,
            'balance_after' => 5000,
            'currency' => 'PKR',
            'description' => 'TICKET PRICE KHI-DXB',
            'pnr' => 'PTMORI',
            'order_ref' => '30482740',
            'flight_reservation_id' => 30482740,
            'vendor' => 'travelport',
            'meta' => [
                'carrier' => 'FZ',
                'provider' => 'travelport',
                'maturity' => '2026-11-10',
            ],
        ]);
        $tx->setRelation('reservation', $reservation);
        $tx->created_at = now();

        $this->assertSame('T. Sale', $tx->typeLabel());
        $this->assertSame(128638.40, $tx->debitAmount());
        $this->assertSame(0.0, $tx->creditAmount());
        $this->assertSame('PTMORI', $tx->pnrLabel());
        $this->assertSame('30482740', $tx->orderIdLabel());
        $this->assertSame('FZ', $tx->carrierLabel());
        $this->assertSame('PKR', $tx->unitLabel());
        $this->assertSame('2026-11-10', $tx->maturityLabel());

        $headers = AccountStatementReport::csvHeaders(true);
        $this->assertContains('maturity', $headers);
        $this->assertContains('seller', $headers);
        $this->assertContains('carrier', $headers);
        $this->assertContains('customer', $headers);

        $row = AccountStatementReport::csvRow($tx, true);
        $this->assertSame(128638.40, $row[0]);
        $this->assertSame('T. Sale', $row[5]);
    }

    public function test_topup_maps_to_payment_label(): void
    {
        $tx = new WalletTransaction([
            'type' => WalletTransaction::TYPE_TOPUP,
            'direction' => WalletTransaction::DIR_CREDIT,
            'amount' => 1000,
            'balance_after' => 1000,
            'currency' => 'USD',
        ]);

        $this->assertSame('Payment', $tx->typeLabel());
        $this->assertSame(0.0, $tx->debitAmount());
        $this->assertSame(1000.0, $tx->creditAmount());
    }
}
