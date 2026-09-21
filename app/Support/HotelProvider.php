<?php

namespace App\Support;

use App\Services\DowntownTravel\DowntownTravelHotelsIntegrationConfig;
use App\Services\Xconnect\XconnectIntegrationConfig;

class HotelProvider
{
    public const XCONNECT = 'xconnect';

    public const DOWNTOWN_TRAVEL_HOTELS = 'downtown_travel_hotels';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [self::XCONNECT, self::DOWNTOWN_TRAVEL_HOTELS];
    }

    public static function current(): string
    {
        $fromSession = (string) session('hotel.provider', '');
        if (in_array($fromSession, self::all(), true)) {
            return $fromSession;
        }

        if (DowntownTravelHotelsIntegrationConfig::isReadyForHotels()
            && ! XconnectIntegrationConfig::isReadyForHotels()) {
            return self::DOWNTOWN_TRAVEL_HOTELS;
        }

        return self::XCONNECT;
    }

    public static function set(string $provider): void
    {
        $provider = strtolower(trim($provider));
        if (! in_array($provider, self::all(), true)) {
            $provider = self::XCONNECT;
        }
        session(['hotel.provider' => $provider]);
    }

    public static function isXconnect(): bool
    {
        return self::current() === self::XCONNECT;
    }

    public static function isDowntownTravel(): bool
    {
        return self::current() === self::DOWNTOWN_TRAVEL_HOTELS;
    }

    public static function isReady(): bool
    {
        return match (self::current()) {
            self::DOWNTOWN_TRAVEL_HOTELS => DowntownTravelHotelsIntegrationConfig::isReadyForHotels(),
            default => XconnectIntegrationConfig::isReadyForHotels(),
        };
    }

    public static function anyReady(): bool
    {
        return XconnectIntegrationConfig::isReadyForHotels()
            || DowntownTravelHotelsIntegrationConfig::isReadyForHotels();
    }

    public static function label(?string $provider = null): string
    {
        return match (strtolower((string) ($provider ?? self::current()))) {
            self::DOWNTOWN_TRAVEL_HOTELS => 'Downtown Travel',
            default => 'Xconnect',
        };
    }

    /**
     * @return array{key: string, label: string, css: string}
     */
    public static function normalizeEnvironment(mixed $raw): array
    {
        $value = strtolower(trim((string) $raw));
        $isLive = in_array($value, ['production', 'prod', 'live'], true);

        return [
            'key' => $isLive ? 'live' : 'sandbox',
            'label' => $isLive ? 'Live' : 'Sandbox',
            'css' => $isLive ? 'env-badge env-badge--live' : 'env-badge env-badge--sandbox',
        ];
    }

    /**
     * Effective Live / Sandbox mode for a hotel provider.
     *
     * @return array{key: string, label: string, css: string}
     */
    public static function environmentMode(?string $provider = null): array
    {
        $provider = strtolower((string) ($provider ?? self::current()));

        try {
            $raw = match ($provider) {
                self::DOWNTOWN_TRAVEL_HOTELS => DowntownTravelHotelsIntegrationConfig::merged()['environment'] ?? 'sandbox',
                default => XconnectIntegrationConfig::merged()['environment'] ?? 'sandbox',
            };
        } catch (\Throwable) {
            $raw = 'sandbox';
        }

        return self::normalizeEnvironment($raw);
    }

    /**
     * @return array{id: string, label: string, short: string, css: string}
     */
    public static function badge(?string $provider = null): array
    {
        $id = strtolower((string) ($provider ?? self::current()));
        if (! in_array($id, self::all(), true)) {
            $id = self::XCONNECT;
        }

        return match ($id) {
            self::DOWNTOWN_TRAVEL_HOTELS => [
                'id' => self::DOWNTOWN_TRAVEL_HOTELS,
                'label' => 'Downtown Travel',
                'short' => 'Downtown Travel',
                'css' => 'provider-badge provider-badge--downtown',
            ],
            default => [
                'id' => self::XCONNECT,
                'label' => 'Xconnect',
                'short' => 'Xconnect',
                'css' => 'provider-badge provider-badge--xconnect',
            ],
        };
    }

    /**
     * @return list<array{id: string, label: string, ready: bool}>
     */
    public static function options(): array
    {
        return [
            [
                'id' => self::DOWNTOWN_TRAVEL_HOTELS,
                'label' => 'Downtown Travel',
                'ready' => DowntownTravelHotelsIntegrationConfig::isReadyForHotels(),
            ],
            [
                'id' => self::XCONNECT,
                'label' => 'Xconnect (Rimo / Technoheaven)',
                'ready' => XconnectIntegrationConfig::isReadyForHotels(),
            ],
        ];
    }
}
