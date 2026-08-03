<?php

namespace App\Services\SunSpring;

use App\Models\Integration;

class SunSpringIntegrationConfig
{
    /**
     * Effective SunSpring options: `.env` defaults, overridden by Admin → Integrations (DB).
     * When the SunSpring integration row exists and is disabled, only `.env` applies.
     *
     * @return array<string, mixed>
     */
    public static function merged(): array
    {
        $base = config('sunspring');
        if (! is_array($base)) {
            $base = [];
        }

        $row = Integration::query()
            ->where('slug', Integration::SLUG_SUNSPRING)
            ->first();

        if (! $row || ! is_array($row->payload) || ! $row->is_enabled) {
            return $base;
        }

        foreach ($row->payload as $key => $value) {
            if ($value === null) {
                continue;
            }
            if ($value === '' && in_array($key, ['agency_code', 'office_id', 'base_url_override'], true)) {
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
            ->where('slug', Integration::SLUG_SUNSPRING)
            ->first();

        if ($row) {
            return (bool) $row->is_enabled;
        }

        return (string) config('sunspring.username', '') !== ''
            && (string) config('sunspring.password', '') !== '';
    }

    /**
     * Ready for authenticated Airline API calls (token + flight endpoints).
     */
    public static function isReadyForAir(): bool
    {
        if (! self::isEnabled()) {
            return false;
        }

        $c = self::merged();

        return (string) ($c['username'] ?? '') !== ''
            && (string) ($c['password'] ?? '') !== '';
    }

    public static function baseUrl(): string
    {
        $c = self::merged();
        $override = trim((string) ($c['base_url_override'] ?? ''));
        if ($override !== '') {
            return rtrim($override, '/');
        }

        $env = strtolower((string) ($c['environment'] ?? 'sandbox'));

        return $env === 'production'
            ? 'https://api.sunspring.ae'
            : 'https://sandbox.sunspring.ae';
    }
}
