<?php

namespace App\Support;

use App\Services\DowntownTravel\DowntownTravelAirService;
use App\Services\DowntownTravel\DowntownTravelIntegrationConfig;
use App\Services\SunSpring\SunSpringAirService;
use App\Services\Travelport\TravelportAirService;
use App\Services\Travelport\TravelportIntegrationConfig;
use Throwable;

class AggregatedFlightSearch
{
    /**
     * Run configured flight APIs against the same user search, then merge fares.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function search(
        array $input,
        TravelportAirService $travelport,
        SunSpringAirService $sunspring,
        ?DowntownTravelAirService $downtown = null,
    ): array {
        $downtown ??= app(DowntownTravelAirService::class);
        $results = [];

        if (TravelportIntegrationConfig::isReadyForAir()) {
            try {
                $results[FlightProvider::TRAVELPORT] = $travelport->lowFareSearch($input);
            } catch (Throwable $e) {
                $results[FlightProvider::TRAVELPORT] = [
                    'ok' => false,
                    'message' => 'Travelport search failed: '.$e->getMessage(),
                    'solutions' => [],
                    'provider' => FlightProvider::TRAVELPORT,
                ];
            }
        }

        if ($sunspring->isReady() && SunSpringAirports::supportsSearch($input)) {
            try {
                $results[FlightProvider::SUNSPRING] = $sunspring->lowFareSearch($input);
            } catch (Throwable $e) {
                $results[FlightProvider::SUNSPRING] = [
                    'ok' => false,
                    'message' => 'SunSpring search failed: '.$e->getMessage(),
                    'solutions' => [],
                    'provider' => FlightProvider::SUNSPRING,
                ];
            }
        } elseif ($sunspring->isReady()) {
            $results[FlightProvider::SUNSPRING] = [
                'ok' => true,
                'message' => 'SunSpring does not cover this route.',
                'solutions' => [],
                'provider' => FlightProvider::SUNSPRING,
            ];
        }

        if (DowntownTravelIntegrationConfig::isReadyForAir() && $downtown->isReady()) {
            try {
                $results[FlightProvider::DOWNTOWN_TRAVEL] = $downtown->lowFareSearch($input);
            } catch (Throwable $e) {
                $results[FlightProvider::DOWNTOWN_TRAVEL] = [
                    'ok' => false,
                    'message' => 'Downtown Travel search failed: '.$e->getMessage(),
                    'solutions' => [],
                    'provider' => FlightProvider::DOWNTOWN_TRAVEL,
                ];
            }
        }

        return self::merge($results);
    }

    /**
     * @param  array<string, array<string, mixed>|null>  $results
     * @return array<string, mixed>
     */
    public static function merge(array $results): array
    {
        $sources = [];
        $solutions = [];

        foreach ($results as $provider => $result) {
            if ($result === null) {
                continue;
            }

            $rows = is_array($result['solutions'] ?? null) ? $result['solutions'] : [];
            $env = FlightProvider::environmentMode((string) $provider);
            foreach ($rows as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $row['provider'] = $provider;
                $row['environment'] = $env['key'];
                $row['environment_label'] = $env['label'];
                $solutions[] = $row;
            }

            $sources[$provider] = [
                'ok' => (bool) ($result['ok'] ?? false),
                'message' => (string) ($result['message'] ?? ''),
                'count' => count($rows),
                'environment' => $env['key'],
                'environment_label' => $env['label'],
            ];
        }

        if ($sources === []) {
            return [
                'ok' => false,
                'message' => 'No flight API is configured. Ask the admin to enable Travelport, SunSpring, or Downtown Travel.',
                'solutions' => [],
                'provider' => FlightProvider::TRAVELPORT,
                'sources' => [],
            ];
        }

        $anyOk = false;
        foreach ($sources as $meta) {
            if (! empty($meta['ok'])) {
                $anyOk = true;
                break;
            }
        }
        $ok = $solutions !== [] || $anyOk;

        $counts = [];
        foreach ($sources as $provider => $meta) {
            $counts[] = FlightProvider::label($provider).': '.(int) $meta['count'];
        }

        $message = $solutions !== []
            ? 'Found '.count($solutions).' fare(s) ('.implode(', ', $counts).').'
            : self::failureMessage($sources);

        $solutions = self::sortByPrice($solutions);

        return [
            'ok' => $ok,
            'message' => $message,
            'solutions' => $solutions,
            'provider' => count($sources) > 1 ? 'mixed' : (string) array_key_first($sources),
            'sources' => $sources,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $solutions
     * @return list<array<string, mixed>>
     */
    public static function sortByPrice(array $solutions, string $direction = 'asc'): array
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        usort($solutions, static function (array $a, array $b) use ($direction): int {
            $left = FlightDisplay::priceSortKey($a['total_price'] ?? null);
            $right = FlightDisplay::priceSortKey($b['total_price'] ?? null);
            $cmp = $left <=> $right;

            return $direction === 'desc' ? -$cmp : $cmp;
        });

        return array_values($solutions);
    }

    /**
     * @param  array<string, array{ok: bool, message: string, count: int}>  $sources
     */
    protected static function failureMessage(array $sources): string
    {
        if ($sources === []) {
            return 'No flight API is configured. Ask the admin to enable Travelport, SunSpring, or Downtown Travel.';
        }

        $parts = [];
        foreach ($sources as $provider => $meta) {
            $label = FlightProvider::label($provider);
            $text = trim((string) ($meta['message'] ?? ''));
            $parts[] = $label.($text !== '' ? ': '.$text : ' returned no fares.');
        }

        return implode(' ', $parts);
    }
}
