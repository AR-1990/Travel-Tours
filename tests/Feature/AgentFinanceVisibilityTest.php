<?php

namespace Tests\Feature;

use App\Models\System\Tenant;
use App\Models\Users\User;
use App\Services\Finance\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentFinanceVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\TenantRbacSeeder::class);
    }

    public function test_agency_admin_can_see_wallet_statement_and_sales(): void
    {
        $agent = User::where('email', 'tenantadmin@traveltours.com')->firstOrFail();
        $tenant = Tenant::query()->findOrFail((int) $agent->tenant_id);

        app(WalletService::class)->topUp($tenant, 125.50, null, ['description' => 'Agency top-up']);

        $this->actingAs($agent)
            ->get(route('agent.finance.wallet'))
            ->assertOk()
            ->assertSee('Wallet balance', false)
            ->assertSee('125.50', false);

        $this->actingAs($agent)
            ->get(route('agent.finance.statement'))
            ->assertOk()
            ->assertSee('Account statement', false)
            ->assertSee('Agency top-up', false)
            ->assertSee('Tr. date', false)
            ->assertSee('Maturity', false)
            ->assertSee('Seller', false)
            ->assertSee('Carrier', false)
            ->assertSee('Route', false)
            ->assertSee('Details', false);

        $this->actingAs($agent)
            ->get(route('agent.finance.sales'))
            ->assertOk()
            ->assertSee('Sales report', false)
            ->assertSee('E-Ticket', false)
            ->assertSee('Agency comm.', false)
            ->assertSee('Departure', false);
    }

    public function test_agency_sidebar_includes_finance_links_and_balance(): void
    {
        $agent = User::where('email', 'tenantadmin@traveltours.com')->firstOrFail();
        $tenant = Tenant::query()->findOrFail((int) $agent->tenant_id);
        app(WalletService::class)->topUp($tenant, 88.00, null, ['description' => 'Visible balance']);

        $html = $this->actingAs($agent)
            ->get(route('agent.flights.search'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Wallet', $html);
        $this->assertStringContainsString('Sales report', $html);
        $this->assertStringContainsString('Account statement', $html);
        $this->assertStringContainsString('88.00', $html);
    }
}
