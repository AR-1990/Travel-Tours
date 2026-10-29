<?php

namespace Tests\Feature;

use App\Models\Finance\TenantMarkup;
use App\Models\Finance\WalletTransaction;
use App\Models\FlightReservation;
use App\Models\System\Tenant;
use App\Models\Users\User;
use App\Services\Finance\TicketPdfService;
use App\Services\Finance\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceModuleFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\TenantRbacSeeder::class);
    }

    public function test_admin_finance_pages_render(): void
    {
        $user = User::where('email', 'superadmin@traveltours.com')->firstOrFail();

        foreach ([
            'admin.finance.wallets.index',
            'admin.finance.markups.index',
            'admin.finance.statement',
            'admin.finance.sales',
            'admin.finance.currency-rates.index',
            'admin.finance.airline-fees.index',
        ] as $route) {
            $this->actingAs($user)->get(route($route))->assertOk();
        }
    }

    public function test_currency_rate_and_airline_fee_crud(): void
    {
        $user = User::where('email', 'superadmin@traveltours.com')->firstOrFail();

        $this->actingAs($user)
            ->post(route('admin.finance.currency-rates.store'), [
                'base_currency' => 'USD',
                'quote_currency' => 'PKR',
                'rate' => 278.5,
                'is_active' => 1,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('currency_rates', [
            'base_currency' => 'USD',
            'quote_currency' => 'PKR',
        ]);

        $this->actingAs($user)
            ->post(route('admin.finance.airline-fees.store'), [
                'airline_code' => 'PK',
                'fee_type' => 'service',
                'label' => 'PK service fee',
                'mode' => 'fixed',
                'amount' => 5,
                'currency' => 'USD',
                'is_active' => 1,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('airline_fees', [
            'airline_code' => 'PK',
            'label' => 'PK service fee',
        ]);

        $breakdown = app(\App\Services\Finance\MarkupService::class)->breakdown([
            'base_price' => 'USD100',
            'taxes' => 'USD20',
            'total_price' => 'USD120',
            'plating_carrier' => 'PK',
        ], null, 'travelport', 0, 'Credit', 'PKR');

        $this->assertSame(278.5, $breakdown['currency_rate']);
        $this->assertSame('PKR', $breakdown['currency']);
        $this->assertSame(27850.0, $breakdown['fare']);
        $this->assertSame(1392.5, $breakdown['airline_fees_total']); // 5 USD × 278.5
    }

    public function test_wallet_topup_refund_reverse_via_http(): void
    {
        $user = User::where('email', 'superadmin@traveltours.com')->firstOrFail();
        $tenant = Tenant::query()->firstOrFail();

        $this->actingAs($user)
            ->post(route('admin.finance.wallets.topup', $tenant->id), [
                'amount' => 250.50,
                'description' => 'Test top-up',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(250.50, app(WalletService::class)->balance($tenant));

        $this->actingAs($user)
            ->get(route('admin.finance.wallets.show', $tenant->id))
            ->assertOk()
            ->assertSee('250.50')
            ->assertSee('Test top-up');

        $sale = app(WalletService::class)->debitSale($tenant, 50, $user, [
            'description' => 'Manual sale',
            'pnr' => 'TESTPNR',
        ]);

        $this->actingAs($user)
            ->post(route('admin.finance.wallets.refund', $tenant->id), [
                'amount' => 20,
                'description' => 'Test refund',
                'pnr' => 'TESTPNR',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->actingAs($user)
            ->post(route('admin.finance.wallets.reverse', $sale->id))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(270.50, app(WalletService::class)->balance($tenant));
        $this->assertTrue(
            WalletTransaction::query()->where('type', WalletTransaction::TYPE_REVERSE)->exists()
        );
    }

    public function test_markup_store_and_statement_csv(): void
    {
        $user = User::where('email', 'superadmin@traveltours.com')->firstOrFail();

        $this->actingAs($user)
            ->post(route('admin.finance.markups.store'), [
                'scope' => TenantMarkup::SCOPE_AIRLINE,
                'scope_key' => 'PK',
                'mode' => TenantMarkup::MODE_PERCENT,
                'value' => 5,
                'priority' => 10,
                'is_active' => 1,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tenant_markups', [
            'scope' => 'airline',
            'scope_key' => 'PK',
            'mode' => 'percent',
        ]);

        $statementCsv = $this->actingAs($user)
            ->get(route('admin.finance.statement', ['export' => 'csv']))
            ->assertOk()
            ->assertHeader('content-disposition');
        $this->assertStringContainsString('maturity', $statementCsv->streamedContent());
        $this->assertStringContainsString('carrier', $statementCsv->streamedContent());

        $this->actingAs($user)
            ->get(route('admin.finance.statement'))
            ->assertOk()
            ->assertSee('Tr. date', false)
            ->assertSee('Maturity', false)
            ->assertSee('Seller', false)
            ->assertSee('Transaction type', false)
            ->assertSee('Route', false)
            ->assertSee('Details', false);

        $csv = $this->actingAs($user)
            ->get(route('admin.finance.sales', ['export' => 'csv']))
            ->assertOk()
            ->assertHeader('content-disposition');
        $this->assertStringContainsString('e_ticket', $csv->streamedContent());
        $this->assertStringContainsString('agency_comm', $csv->streamedContent());

        $this->actingAs($user)
            ->get(route('admin.finance.sales'))
            ->assertOk()
            ->assertSee('E-Ticket', false)
            ->assertSee('Fuel/Other', false)
            ->assertSee('Agency user', false);
    }

    public function test_ticket_pdf_and_print_routes(): void
    {
        $user = User::where('email', 'superadmin@traveltours.com')->firstOrFail();
        $reservation = FlightReservation::query()->create([
            'user_id' => $user->id,
            'channel' => 'admin',
            'status' => FlightReservation::STATUS_TICKETED,
            'universal_locator' => 'UR123',
            'air_reservation_locator' => 'PNR999',
            'origin' => 'KHI',
            'destination' => 'DXB',
            'departure_date' => now()->addWeek()->toDateString(),
            'adults' => 1,
            'carrier' => 'PK',
            'passenger_first' => 'Test',
            'passenger_last' => 'Traveler',
            'passenger_email' => 'test@example.com',
            'passenger_phone' => '+923001234567',
            'total_price' => 'USD 200.00',
            'form_of_payment' => 'Credit',
            'sell_total' => 210,
            'currency' => 'USD',
            'ticket_numbers' => ['1234567890123'],
            'itinerary' => [
                'segments' => [[
                    'carrier' => 'PK',
                    'flight_number' => '300',
                    'origin' => 'KHI',
                    'destination' => 'DXB',
                    'departure' => now()->addWeek()->toIso8601String(),
                    'arrival' => now()->addWeek()->addHours(3)->toIso8601String(),
                ]],
            ],
            'booked_at' => now(),
            'ticketed_at' => now(),
        ]);

        $pdf = $this->actingAs($user)
            ->get(route('admin.flights.reservations.ticket-pdf', $reservation->id));
        $pdf->assertOk();
        $this->assertStringStartsWith('%PDF', $pdf->getContent());

        $this->actingAs($user)
            ->get(route('admin.flights.reservations.ticket-print', $reservation->id))
            ->assertOk()
            ->assertSee('Electronic ticket', false)
            ->assertSee('PNR999', false)
            ->assertSee('Test Traveler', false);

        $binary = app(TicketPdfService::class)->pdfBinary($reservation);
        $this->assertStringContainsString('PNR999', $binary);
    }
}
