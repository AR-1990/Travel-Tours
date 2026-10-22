<?php

namespace Tests\Unit;

use App\Models\Finance\TenantMarkup;
use App\Models\Finance\WalletTransaction;
use App\Models\System\Tenant;
use App\Services\Finance\MarkupService;
use App\Services\Finance\TicketPdfService;
use App\Services\Finance\WalletService;
use App\Models\FlightReservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceWalletMarkupTest extends TestCase
{
    use RefreshDatabase;

    public function test_wallet_topup_debit_refund_and_reverse(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Test Agency',
            'slug' => 'test-agency-'.uniqid(),
            'email' => 'agency-'.uniqid().'@example.com',
            'currency' => 'USD',
            'is_active' => true,
            'status' => 'approved',
        ]);

        $wallets = app(WalletService::class);
        $wallets->topUp($tenant, 100.00, null, ['description' => 'Initial']);
        $this->assertSame(100.0, $wallets->balance($tenant));

        $sale = $wallets->debitSale($tenant, 40.50, null, ['description' => 'Sale', 'pnr' => 'ABC123']);
        $this->assertSame(59.5, $wallets->balance($tenant));
        $this->assertSame(WalletTransaction::TYPE_SALE, $sale->type);
        $this->assertSame(WalletTransaction::DIR_DEBIT, $sale->direction);

        $wallets->refund($tenant, 10.00, null, ['description' => 'Partial refund', 'pnr' => 'ABC123']);
        $this->assertSame(69.5, $wallets->balance($tenant));

        $wallets->reverse($sale);
        $this->assertSame(110.0, $wallets->balance($tenant));
    }

    public function test_markup_percent_and_fixed_resolution(): void
    {
        TenantMarkup::query()->create([
            'tenant_id' => null,
            'scope' => TenantMarkup::SCOPE_GLOBAL,
            'scope_key' => null,
            'mode' => TenantMarkup::MODE_PERCENT,
            'value' => 10,
            'is_active' => true,
            'priority' => 100,
        ]);
        TenantMarkup::query()->create([
            'tenant_id' => null,
            'scope' => TenantMarkup::SCOPE_AIRLINE,
            'scope_key' => 'PK',
            'mode' => TenantMarkup::MODE_FIXED,
            'value' => 5,
            'is_active' => true,
            'priority' => 10,
        ]);

        $svc = app(MarkupService::class);
        $airline = $svc->resolve(null, 'travelport', 'PK', 200);
        $this->assertSame(5.0, $airline['amount']);

        $global = $svc->resolve(null, 'travelport', 'EK', 200);
        $this->assertSame(20.0, $global['amount']);

        // Markup must use base fare only (never fall back to total for %).
        $breakdown = $svc->breakdown([
            'base_price' => 'USD100.00',
            'taxes' => 'USD50.00',
            'total_price' => 'USD150.00',
            'plating_carrier' => 'EK',
        ], null, 'travelport', 0);
        $this->assertSame(10.0, $breakdown['markup_amount']); // 10% of 100 base
        $this->assertSame(160.0, $breakdown['sell_total']);
    }

    public function test_ticket_pdf_binary_is_valid_pdf(): void
    {
        $reservation = new FlightReservation([
            'origin' => 'KHI',
            'destination' => 'DXB',
            'carrier' => 'PK',
            'passenger_first' => 'Ali',
            'passenger_last' => 'Khan',
            'status' => FlightReservation::STATUS_RESERVED,
            'air_reservation_locator' => 'ABC123',
            'total_price' => 'USD200.00',
            'itinerary' => [
                'segments' => [[
                    'carrier' => 'PK',
                    'flight_number' => '300',
                    'origin' => 'KHI',
                    'destination' => 'DXB',
                    'departure' => '2026-11-01T10:00:00',
                    'arrival' => '2026-11-01T12:00:00',
                ]],
            ],
        ]);

        $pdf = app(TicketPdfService::class)->pdfBinary($reservation);
        $this->assertStringStartsWith('%PDF', $pdf);
        $this->assertStringContainsString('ABC123', $pdf);
        $this->assertStringContainsString('Ali Khan', $pdf);
    }
}
