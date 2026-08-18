<?php

namespace App\Support;

use App\Services\Xconnect\XconnectIntegrationConfig;

class HotelProvider
{
    public const XCONNECT = 'xconnect';

    public static function current(): string
    {
        $fromSession = (string) session('hotel.provider', '');
        if ($fromSession === self::XCONNECT) {
            return $fromSession;
        }

        return self::XCONNECT;
    }

    public static function set(string $provider): void
    {
        $provider = strtolower(trim($provider));
        if ($provider !== self::XCONNECT) {
            $provider = self::XCONNECT;
        }
        session(['hotel.provider' => $provider]);
    }

    public static function isXconnect(): bool
    {
        return self::current() === self::XCONNECT;
    }

    public static function isReady(): bool
    {
        return XconnectIntegrationConfig::isReadyForHotels();
    }

    public static function label(?string $provider = null): string
    {
        return match (strtolower((string) ($provider ?? self::current()))) {
            self::XCONNECT => 'Xconnect',
            default => 'Xconnect',
        };
    }

    /**
     * @return array{id: string, label: string, short: string, css: string}
     */
    public static function badge(?string $provider = null): array
    {
        return [
            'id' => self::XCONNECT,
            'label' => 'Xconnect',
            'short' => 'API: Xconnect',
            'css' => 'provider-badge provider-badge--xconnect',
        ];
    }

    /**
     * @return list<array{id: string, label: string, ready: bool}>
     */
    public static function options(): array
    {
        return [
            [
                'id' => self::XCONNECT,
                'label' => 'Xconnect (Rimo / Technoheaven)',
                'ready' => XconnectIntegrationConfig::isReadyForHotels(),
            ],
        ];
    }
}
