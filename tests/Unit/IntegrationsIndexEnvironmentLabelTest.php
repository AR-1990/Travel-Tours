<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\IntegrationsController;
use App\Models\Integration;
use ReflectionMethod;
use Tests\TestCase;

class IntegrationsIndexEnvironmentLabelTest extends TestCase
{
    public function test_disabled_production_payload_resolves_to_live(): void
    {
        $slug = Integration::SLUG_DOWNTOWN_TRAVEL;
        $existing = Integration::query()->where('slug', $slug)->first();

        Integration::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'name' => 'Downtown Travel Air API',
                'is_enabled' => false,
                'payload' => [
                    'environment' => 'production',
                    'client_id' => 'label-test-client',
                    'client_secret' => 'label-test-secret',
                    'username' => 'label-test-user',
                    'password' => 'label-test-pass',
                ],
            ]
        );

        try {
            $method = new ReflectionMethod(IntegrationsController::class, 'resolvedEnvironmentLabel');
            $method->setAccessible(true);
            $label = $method->invoke(new IntegrationsController, $slug);

            $this->assertSame('live', $label['key']);
            $this->assertSame('Live', $label['label']);
        } finally {
            if ($existing) {
                $existing->save();
            } else {
                Integration::query()->where('slug', $slug)->delete();
            }
        }
    }

    public function test_disabled_sandbox_payload_resolves_to_sandbox(): void
    {
        $slug = Integration::SLUG_SUNSPRING;
        $existing = Integration::query()->where('slug', $slug)->first();

        Integration::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'name' => 'SunSpring Airline API',
                'is_enabled' => false,
                'payload' => [
                    'environment' => 'sandbox',
                    'username' => 'label-test-user',
                    'password' => 'label-test-pass',
                ],
            ]
        );

        try {
            $method = new ReflectionMethod(IntegrationsController::class, 'resolvedEnvironmentLabel');
            $method->setAccessible(true);
            $label = $method->invoke(new IntegrationsController, $slug);

            $this->assertSame('sandbox', $label['key']);
            $this->assertSame('Sandbox', $label['label']);
        } finally {
            if ($existing) {
                $existing->save();
            } else {
                Integration::query()->where('slug', $slug)->delete();
            }
        }
    }
}
