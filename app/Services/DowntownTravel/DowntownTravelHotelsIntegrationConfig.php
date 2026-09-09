<?php

namespace App\Services\DowntownTravel;

use App\Models\Integration;

class DowntownTravelHotelsIntegrationConfig
{
    /**
     * @return array<string, mixed>
     */
    public static function merged(): array
    {
        $base = config('downtown_travel_hotels');
        if (! is_array($base)) {
            $base = [];
        }

        $row = Integration::query()
            ->where('slug', Integration::SLUG_DOWNTOWN_TRAVEL_HOTELS)
            ->first();

        if (! $row || ! is_array($row->payload) || ! $row->is_enabled) {
            return $base;
        }

        foreach ($row->payload as $key => $value) {
            if ($value === null) {
                continue;
            }
            if ($value === '' && in_array($key, ['sso_base_url_override', 'hotels_base_url_override'], true)) {
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
            ->where('slug', Integration::SLUG_DOWNTOWN_TRAVEL_HOTELS)
            ->first();

        if ($row) {
            return (bool) $row->is_enabled;
        }

        return (string) config('downtown_travel_hotels.client_id', '') !== ''
            && (string) config('downtown_travel_hotels.client_secret', '') !== ''
            && (string) config('downtown_travel_hotels.username', '') !== ''
            && (string) config('downtown_travel_hotels.password', '') !== '';
    }

    public static function isReadyForHotels(): bool
    {
        if (! self::isEnabled()) {
            return false;
        }

        $c = self::merged();

        return (string) ($c['client_id'] ?? '') !== ''
            && (string) ($c['client_secret'] ?? '') !== ''
            && (string) ($c['username'] ?? '') !== ''
            && (string) ($c['password'] ?? '') !== '';
    }

    public static function ssoBaseUrl(): string
    {
        $c = self::merged();
        $override = trim((string) ($c['sso_base_url_override'] ?? ''));
        if ($override !== '') {
            return rtrim($override, '/');
        }

        $env = strtolower((string) ($c['environment'] ?? 'sandbox'));

        return $env === 'production'
            ? 'https://sso.thebestagent.pro'
            : 'https://sso.sandbox.thebestagent.pro';
    }

    public static function hotelsBaseUrl(): string
    {
        $c = self::merged();
        $override = trim((string) ($c['hotels_base_url_override'] ?? ''));
        if ($override !== '') {
            return rtrim($override, '/');
        }

        $env = strtolower((string) ($c['environment'] ?? 'sandbox'));

        return $env === 'production'
            ? 'https://hotels.thebestagent.pro'
            : 'https://hotels.sandbox.thebestagent.pro';
    }
}
