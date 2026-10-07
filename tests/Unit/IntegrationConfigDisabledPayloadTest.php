<?php

namespace Tests\Unit;

use App\Models\Integration;
use App\Services\DowntownTravel\DowntownTravelIntegrationConfig;
use Tests\TestCase;

class IntegrationConfigDisabledPayloadTest extends TestCase
{
    public function test_disabled_integration_keeps_keys_visible_for_admin_form(): void
    {
        $slug = Integration::SLUG_DOWNTOWN_TRAVEL;
        $existing = Integration::query()->where('slug', $slug)->first();

        Integration::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'name' => 'Downtown Travel Air API',
                'is_enabled' => false,
                'payload' => [
                    'environment' => 'sandbox',
                    'client_id' => 'admin-form-keep-client',
                    'client_secret' => 'admin-form-keep-secret',
                    'username' => 'admin-form-keep-user',
                    'password' => 'admin-form-keep-pass',
                    'timeout' => 60,
                ],
            ]
        );

        try {
            $runtime = DowntownTravelIntegrationConfig::merged(false);
            $this->assertNotSame('admin-form-keep-client', $runtime['client_id'] ?? null);

            $form = DowntownTravelIntegrationConfig::merged(true);
            $this->assertSame('admin-form-keep-client', $form['client_id'] ?? null);
            $this->assertSame('admin-form-keep-secret', $form['client_secret'] ?? null);
            $this->assertSame('admin-form-keep-user', $form['username'] ?? null);
            $this->assertSame('admin-form-keep-pass', $form['password'] ?? null);
        } finally {
            if ($existing) {
                $existing->save();
            } else {
                Integration::query()->where('slug', $slug)->delete();
            }
        }
    }
}
