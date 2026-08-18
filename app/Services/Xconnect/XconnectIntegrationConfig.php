<?php

namespace App\Services\Xconnect;

use App\Models\Integration;

class XconnectIntegrationConfig
{
    /**
     * @return array<string, mixed>
     */
    public static function merged(): array
    {
        $base = config('xconnect');
        if (! is_array($base)) {
            $base = [];
        }

        $row = Integration::query()
            ->where('slug', Integration::SLUG_XCONNECT)
            ->first();

        if (! $row || ! is_array($row->payload) || ! $row->is_enabled) {
            return $base;
        }

        foreach ($row->payload as $key => $value) {
            if ($value === null) {
                continue;
            }
            if ($value === '' && in_array($key, ['base_url', 'base_url_override', 'default_nationality'], true)) {
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
            ->where('slug', Integration::SLUG_XCONNECT)
            ->first();

        if ($row) {
            return (bool) $row->is_enabled;
        }

        return (string) config('xconnect.token', '') !== ''
            && self::resolveBaseUrlFrom(config('xconnect', [])) !== '';
    }

    public static function isReadyForHotels(): bool
    {
        if (! self::isEnabled()) {
            return false;
        }

        $c = self::merged();

        return (string) ($c['token'] ?? '') !== ''
            && self::resolveBaseUrlFrom($c) !== '';
    }

    public static function baseUrl(): string
    {
        return self::resolveBaseUrlFrom(self::merged());
    }

    /**
     * @param  array<string, mixed>  $c
     */
    public static function resolveBaseUrlFrom(array $c): string
    {
        $override = trim((string) ($c['base_url_override'] ?? ''));
        if ($override !== '') {
            return rtrim($override, '/');
        }

        return rtrim(trim((string) ($c['base_url'] ?? '')), '/');
    }
}
