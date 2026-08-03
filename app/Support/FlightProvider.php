<?php

namespace App\Support;

use App\Services\SunSpring\SunSpringAirService;
use App\Services\SunSpring\SunSpringIntegrationConfig;
use App\Services\Travelport\TravelportAirService;
use App\Services\Travelport\TravelportIntegrationConfig;

class FlightProvider
{
    public const TRAVELPORT = 'travelport';

    public const SUNSPRING = 'sunspring';

    public static function current(): string
    {
        $fromSession = (string) session('flight.provider', '');
        if (in_array($fromSession, [self::TRAVELPORT, self::SUNSPRING], true)) {
            return $fromSession;
        }

        if (SunSpringIntegrationConfig::isReadyForAir() && ! TravelportIntegrationConfig::isReadyForAir()) {
            return self::SUNSPRING;
        }

        return self::TRAVELPORT;
    }

    public static function set(string $provider): void
    {
        $provider = strtolower(trim($provider));
        if (! in_array($provider, [self::TRAVELPORT, self::SUNSPRING], true)) {
            $provider = self::TRAVELPORT;
        }
        session(['flight.provider' => $provider]);
    }

    public static function isSunSpring(): bool
    {
        return self::current() === self::SUNSPRING;
    }

    public static function isReady(): bool
    {
        return self::isSunSpring()
            ? SunSpringIntegrationConfig::isReadyForAir()
            : TravelportIntegrationConfig::isReadyForAir();
    }

    public static function label(?string $provider = null): string
    {
        return match (strtolower((string) ($provider ?? self::current()))) {
            self::SUNSPRING => 'SunSpring',
            default => 'Travelport',
        };
    }

    /**
     * Resolve provider id from a search/price result payload or session fallback.
     *
     * @param  array<string, mixed>|null  $result
     */
    public static function fromResult(?array $result = null): string
    {
        $fromResult = strtolower((string) ($result['provider'] ?? ''));
        if (in_array($fromResult, [self::TRAVELPORT, self::SUNSPRING], true)) {
            return $fromResult;
        }

        $fromSolution = strtolower((string) data_get($result, 'solutions.0.provider', ''));
        if (in_array($fromSolution, [self::TRAVELPORT, self::SUNSPRING], true)) {
            return $fromSolution;
        }

        return self::current();
    }

    /**
     * @return array{id: string, label: string, short: string, css: string}
     */
    public static function badge(?string $provider = null): array
    {
        $id = strtolower((string) ($provider ?? self::current()));
        if (! in_array($id, [self::TRAVELPORT, self::SUNSPRING], true)) {
            $id = self::TRAVELPORT;
        }

        return match ($id) {
            self::SUNSPRING => [
                'id' => self::SUNSPRING,
                'label' => 'SunSpring',
                'short' => 'API: SunSpring',
                'css' => 'provider-badge provider-badge--sunspring',
            ],
            default => [
                'id' => self::TRAVELPORT,
                'label' => 'Travelport',
                'short' => 'API: Travelport',
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
        ];
    }
}
