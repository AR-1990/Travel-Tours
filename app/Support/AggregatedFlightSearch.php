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

        // Drop identical fare cards (same API + itinerary + price). Keep real variants
        // (different arrival, stops, or amount) and keep the same flight from two APIs.
        $solutions = self::dedupeIdenticalFares($solutions);

        foreach ($sources as $provider => &$meta) {
            $meta['count'] = 0;
            foreach ($solutions as $row) {
                if (($row['provider'] ?? '') === $provider) {
                    $meta['count']++;
                }
            }
        }
        unset($meta);

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
     * Remove duplicate fare cards that share provider, price, and full segment schedule.
     * Different arrivals / prices / providers are kept.
     *
     * @param  list<array<string, mixed>>  $solutions
     * @return list<array<string, mixed>>
     */
    public static function dedupeIdenticalFares(array $solutions): array
    {
        $seen = [];
        $seenKeys = [];
        $seenDigests = [];
        $out = [];

        foreach ($solutions as $row) {
            if (! is_array($row)) {
                continue;
            }

            $provider = strtolower((string) ($row['provider'] ?? ''));
            $key = trim((string) ($row['key'] ?? ''));
            if ($key !== '') {
                $keyId = $provider.'|key:'.$key;
                if (isset($seenKeys[$keyId])) {
                    continue;
                }
                $seenKeys[$keyId] = true;
            }

            $digest = trim((string) ($row['digest'] ?? ''));
            if ($digest !== '') {
                $digestId = $provider.'|digest:'.$digest;
                if (isset($seenDigests[$digestId])) {
                    continue;
                }
                $seenDigests[$digestId] = true;
            }

            $fingerprint = self::fareFingerprint($row);
            if (isset($seen[$fingerprint])) {
                continue;
            }
            $seen[$fingerprint] = true;
            $out[] = $row;
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $solution
     */
    public static function fareFingerprint(array $solution): string
    {
        $parts = [
            strtolower((string) ($solution['provider'] ?? '')),
            number_format(FlightDisplay::priceSortKey($solution['total_price'] ?? null), 2, '.', ''),
            FlightDisplay::airlineCode((string) ($solution['plating_carrier'] ?? $solution['airline_name'] ?? '')),
            strtolower(trim((string) data_get($solution, 'journeys.0.cabin', $solution['cabin'] ?? ''))),
            strtoupper(trim((string) ($solution['fare_basis'] ?? ''))),
        ];

        $segments = [];
        if (is_array($solution['journeys'] ?? null) && $solution['journeys'] !== []) {
            foreach ($solution['journeys'] as $journey) {
                if (! is_array($journey['segments'] ?? null)) {
                    continue;
                }
                foreach ($journey['segments'] as $segment) {
                    if (is_array($segment)) {
                        $segments[] = $segment;
                    }
                }
            }
        }
        if ($segments === [] && is_array($solution['segments'] ?? null)) {
            $segments = $solution['segments'];
        }

        foreach ($segments as $segment) {
            if (! is_array($segment)) {
                continue;
            }
            $parts[] = implode('~', [
                FlightDisplay::airlineCode((string) ($segment['carrier'] ?? '')),
                strtoupper(trim((string) ($segment['flight_number'] ?? ''))),
                strtoupper(trim((string) ($segment['origin'] ?? ''))),
                strtoupper(trim((string) ($segment['destination'] ?? ''))),
                self::normalizeScheduleStamp($segment['departure'] ?? null),
                self::normalizeScheduleStamp($segment['arrival'] ?? null),
            ]);
        }

        return hash('sha256', implode('|', $parts));
    }

    protected static function normalizeScheduleStamp(mixed $value): string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return '';
        }

        try {
            return (new \DateTimeImmutable($raw))->format('Y-m-d\TH:i');
        } catch (\Throwable) {
            return $raw;
        }
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
