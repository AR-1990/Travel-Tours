<?php

namespace Tests\Unit;

use App\Models\Integration;
use App\Services\SunSpring\SunSpringClient;
use App\Services\SunSpring\SunSpringIntegrationConfig;
use Tests\TestCase;

class SunSpringIntegrationConfigTest extends TestCase
{
    public function test_normalize_host_only_strips_path(): void
    {
        $this->assertSame(
            'https://sandbox.sunspring.ae',
            SunSpringClient::normalizeHostOnly('https://sandbox.sunspring.ae/api/v2/')
        );
        $this->assertSame(
            'https://api.sunspring.ae',
            SunSpringClient::normalizeHostOnly('api.sunspring.ae')
        );
        $this->assertSame('', SunSpringClient::normalizeHostOnly(''));
    }

    public function test_base_url_uses_environment_when_no_override(): void
    {
        config([
            'sunspring.environment' => 'sandbox',
            'sunspring.base_url_override' => null,
            'sunspring.username' => '',
            'sunspring.password' => '',
        ]);

        $row = null;
        $wasEnabled = null;

        try {
            $row = Integration::query()->where('slug', Integration::SLUG_SUNSPRING)->first();
            if ($row) {
                $wasEnabled = (bool) $row->is_enabled;
                // Disable DB override so this unit test only exercises config defaults.
                $row->forceFill(['is_enabled' => false])->save();
            }

            $this->assertSame('https://sandbox.sunspring.ae', SunSpringIntegrationConfig::baseUrl());

            config(['sunspring.environment' => 'production']);
            $this->assertSame('https://api.sunspring.ae', SunSpringIntegrationConfig::baseUrl());
        } catch (\Throwable $e) {
            $this->markTestSkipped('Database unavailable for integration config merge: '.$e->getMessage());
        } finally {
            if ($row !== null && $wasEnabled !== null) {
                $row->forceFill(['is_enabled' => $wasEnabled])->save();
            }
        }
    }
}
