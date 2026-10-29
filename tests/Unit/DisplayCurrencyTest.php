<?php

namespace Tests\Unit;

use App\Models\System\Tenant;
use App\Models\Users\User;
use App\Support\DisplayCurrency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisplayCurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_agency_uses_assigned_tenant_currency(): void
    {
        $this->seed(\Database\Seeders\TenantRbacSeeder::class);

        $tenant = Tenant::query()->create([
            'name' => 'PK Agency',
            'slug' => 'pk-agency-'.uniqid(),
            'email' => 'pk-'.uniqid().'@example.com',
            'currency' => 'PKR',
            'is_active' => true,
            'status' => 'approved',
        ]);

        $user = new User([
            'name' => 'Agent',
            'email' => 'agent-'.uniqid().'@example.com',
            'user_type' => 'tenant_admin',
        ]);
        $user->tenant_id = $tenant->id;

        $this->assertSame('PKR', DisplayCurrency::resolve($user));
    }

    public function test_b2c_uses_region_country_currency(): void
    {
        $this->assertSame('PKR', DisplayCurrency::forRegion('PK'));
        $this->assertSame('AED', DisplayCurrency::forRegion('AE'));
        $this->assertSame('GBP', DisplayCurrency::forRegion('GB'));
        $this->assertSame('USD', DisplayCurrency::forRegion('US'));
    }
}
