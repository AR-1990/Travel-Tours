<?php

namespace App\Services\DowntownTravel;

use App\Models\Integration;

class DowntownTravelIntegrationConfig
{
    /**
     * Effective Downtown Travel options: `.env` defaults, overridden by Admin → Integrations (DB).
     * When the integration row exists and is disabled, only `.env` applies.
     *
     * @return array<string, mixed>
     */
    public static function merged(): array
    {
        $base = config('downtown_travel');
        if (! is_array($base)) {
            $base = [];
        }

        $row = Integration::query()
            ->where('slug', Integration::SLUG_DOWNTOWN_TRAVEL)
            ->first();

        if (! $row || ! is_array($row->payload) || ! $row->is_enabled) {
            return $base;
        }

        foreach ($row->payload as $key => $value) {
            if ($value === null) {
                continue;
            }
            if ($value === '' && in_array($key, ['sso_base_url_override', 'air_base_url_override'], true)) {
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
            ->where('slug', Integration::SLUG_DOWNTOWN_TRAVEL)
            ->first();

        if ($row) {
            return (bool) $row->is_enabled;
        }

        return (string) config('downtown_travel.client_id', '') !== ''
            && (string) config('downtown_travel.client_secret', '') !== ''
            && (string) config('downtown_travel.username', '') !== ''
            && (string) config('downtown_travel.password', '') !== '';
    }

    /**
     * Ready for OAuth + Air API calls.
     */
    public static function isReadyForAir(): bool
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

    public static function airBaseUrl(): string
    {
        $c = self::merged();
        $override = trim((string) ($c['air_base_url_override'] ?? ''));
        if ($override !== '') {
            return rtrim($override, '/');
        }

        $env = strtolower((string) ($c['environment'] ?? 'sandbox'));

        return $env === 'production'
            ? 'https://air.thebestagent.pro'
            : 'https://air.sandbox.thebestagent.pro';
    }
}
