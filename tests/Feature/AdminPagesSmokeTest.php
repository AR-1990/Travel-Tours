<?php

namespace Tests\Feature;

use App\Models\Users\User;
use Tests\TestCase;

class AdminPagesSmokeTest extends TestCase
{
    public function test_super_admin_key_admin_pages_render(): void
    {
        $user = User::where('email', 'superadmin@traveltours.com')->first();
        if (! $user) {
            $this->markTestSkipped('Run TenantRbacSeeder for demo users.');
        }

        $routes = [
            'admin.dashboard',
            'admin.integrations.index',
            'admin.integrations.edit',
            'admin.tenants.index',
            'admin.blogs.index',
            'admin.debtor-types.index',
        ];

        foreach ($routes as $name) {
            $params = str_contains($name, 'integrations.edit') ? ['slug' => 'travelport'] : [];
            $this->actingAs($user)->get(route($name, $params))->assertOk();
        }
    }
}
