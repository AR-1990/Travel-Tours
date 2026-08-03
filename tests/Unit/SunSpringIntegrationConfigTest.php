<?php

namespace Tests\Unit;

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

        // No integrations DB row required: merged() falls back to config when query returns null.
        // If MySQL is down this test is skipped.
        try {
            $url = SunSpringIntegrationConfig::baseUrl();
        } catch (\Throwable $e) {
            $this->markTestSkipped('Database unavailable for integration config merge: '.$e->getMessage());
        }

        $this->assertSame('https://sandbox.sunspring.ae', $url);

        config(['sunspring.environment' => 'production']);
        $this->assertSame('https://api.sunspring.ae', SunSpringIntegrationConfig::baseUrl());
    }
}
