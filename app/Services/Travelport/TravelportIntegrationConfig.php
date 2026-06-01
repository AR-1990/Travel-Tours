<?php

namespace App\Services\Travelport;

use App\Models\Integration;

class TravelportIntegrationConfig
{
    /**
     * Effective Travelport options: `.env` defaults, overridden by Admin → Integrations (DB).
     * When the Travelport integration row exists and is disabled, only `.env` applies.
     *
     * @return array<string, mixed>
     */
    public static function merged(): array
    {
        $base = config('travelport');
        if (! is_array($base)) {
            $base = [];
        }

        $row = Integration::query()
            ->where('slug', Integration::SLUG_TRAVELPORT)
            ->first();

        if (! $row || ! is_array($row->payload) || ! $row->is_enabled) {
            return $base;
        }

        foreach ($row->payload as $key => $value) {
            if ($value === null) {
                continue;
            }
            if ($value === '' && in_array($key, ['branch', 'gds', 'target_branch', 'base_url_override', 'origin_application'], true)) {
                $base[$key] = '';

                continue;
            }
            if ($value === '') {
                continue;
            }
            $base[$key] = $value;
        }

        return $base;
    }

    public static function isEnabled(): bool
    {
        $row = Integration::query()
            ->where('slug', Integration::SLUG_TRAVELPORT)
            ->first();

        if ($row) {
            return (bool) $row->is_enabled;
        }

        return (string) config('travelport.username', '') !== ''
            && (string) config('travelport.password', '') !== '';
    }

    /**
     * Ready for AirService (Low Fare Search, etc.).
     */
    public static function isReadyForAir(): bool
    {
        if (! self::isEnabled()) {
            return false;
        }

        $c = self::merged();

        return (string) ($c['username'] ?? '') !== ''
            && (string) ($c['password'] ?? '') !== ''
            && trim((string) ($c['target_branch'] ?? '')) !== '';
    }
}
