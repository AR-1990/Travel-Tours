<?php

namespace App\Support;

use App\Services\DowntownTravel\DowntownTravelIntegrationConfig;
use App\Services\SunSpring\SunSpringIntegrationConfig;
use App\Services\Travelport\TravelportIntegrationConfig;

class FlightProvider
{
    public const TRAVELPORT = 'travelport';

    public const SUNSPRING = 'sunspring';

    public const DOWNTOWN_TRAVEL = 'downtown_travel';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [self::TRAVELPORT, self::SUNSPRING, self::DOWNTOWN_TRAVEL];
    }

    public static function current(): string
    {
        $fromSession = (string) session('flight.provider', '');
        if (in_array($fromSession, self::all(), true)) {
            return $fromSession;
        }

        if (DowntownTravelIntegrationConfig::isReadyForAir()
            && ! TravelportIntegrationConfig::isReadyForAir()
            && ! SunSpringIntegrationConfig::isReadyForAir()) {
            return self::DOWNTOWN_TRAVEL;
        }

        if (SunSpringIntegrationConfig::isReadyForAir() && ! TravelportIntegrationConfig::isReadyForAir()) {
            return self::SUNSPRING;
        }

        return self::TRAVELPORT;
    }

    public static function set(string $provider): void
    {
        $provider = strtolower(trim($provider));
        if (! in_array($provider, self::all(), true)) {
            $provider = self::TRAVELPORT;
        }
        session(['flight.provider' => $provider]);
    }

    public static function isSunSpring(): bool
    {
        return self::current() === self::SUNSPRING;
    }

    public static function isDowntownTravel(): bool
    {
        return self::current() === self::DOWNTOWN_TRAVEL;
    }

    public static function isReady(): bool
    {
        return match (self::current()) {
            self::SUNSPRING => SunSpringIntegrationConfig::isReadyForAir(),
            self::DOWNTOWN_TRAVEL => DowntownTravelIntegrationConfig::isReadyForAir(),
            default => TravelportIntegrationConfig::isReadyForAir(),
        };
    }

    public static function label(?string $provider = null): string
    {
        return match (strtolower((string) ($provider ?? self::current()))) {
            self::SUNSPRING => 'SunSpring',
            self::DOWNTOWN_TRAVEL => 'Downtown Travel',
            default => 'Travelport',
        };
    }

    /**
     * @param  array<string, mixed>|null  $result
     */
    public static function fromResult(?array $result = null): string
    {
        $fromResult = strtolower((string) ($result['provider'] ?? ''));
        if (in_array($fromResult, self::all(), true)) {
            return $fromResult;
        }

        if ($fromResult === 'mixed') {
            $fromSolution = strtolower((string) data_get($result, 'solutions.0.provider', ''));
            if (in_array($fromSolution, self::all(), true)) {
                return $fromSolution;
            }
        }

        $fromSolution = strtolower((string) data_get($result, 'solutions.0.provider', ''));
        if (in_array($fromSolution, self::all(), true)) {
            return $fromSolution;
        }

        return self::current();
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
     * Effective Live / Sandbox mode for a flight provider.
     *
     * @return array{key: string, label: string, css: string}
     */
    public static function environmentMode(?string $provider = null): array
    {
        $provider = strtolower((string) ($provider ?? self::current()));

        try {
            $raw = match ($provider) {
                self::SUNSPRING => SunSpringIntegrationConfig::merged()['environment'] ?? 'sandbox',
                self::DOWNTOWN_TRAVEL => DowntownTravelIntegrationConfig::merged()['environment'] ?? 'sandbox',
                default => TravelportIntegrationConfig::merged()['environment'] ?? 'pp',
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
            $id = self::TRAVELPORT;
        }

        return match ($id) {
            self::SUNSPRING => [
                'id' => self::SUNSPRING,
                'label' => 'SunSpring',
                'short' => 'SunSpring',
                'css' => 'provider-badge provider-badge--sunspring',
            ],
            self::DOWNTOWN_TRAVEL => [
                'id' => self::DOWNTOWN_TRAVEL,
                'label' => 'Downtown Travel',
                'short' => 'Downtown Travel',
                'css' => 'provider-badge provider-badge--downtown',
            ],
            default => [
                'id' => self::TRAVELPORT,
                'label' => 'Travelport',
                'short' => 'Travelport',
                'css' => 'provider-badge provider-badge--travelport',
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
                'id' => self::TRAVELPORT,
                'label' => 'Travelport',
                'ready' => TravelportIntegrationConfig::isReadyForAir(),
            ],
            [
                'id' => self::SUNSPRING,
                'label' => 'SunSpring',
                'ready' => SunSpringIntegrationConfig::isReadyForAir(),
            ],
            [
                'id' => self::DOWNTOWN_TRAVEL,
                'label' => 'Downtown Travel',
                'ready' => DowntownTravelIntegrationConfig::isReadyForAir(),
            ],
        ];
    }
}
