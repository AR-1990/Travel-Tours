<?php

namespace App\Support;

use App\Services\SunSpring\SunSpringAirService;
use App\Services\Travelport\TravelportAirService;
use App\Services\Travelport\TravelportIntegrationConfig;
use Throwable;

class AggregatedFlightSearch
{
    /**
     * Run Travelport and SunSpring against the same user search, then merge fares.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function search(array $input, TravelportAirService $travelport, SunSpringAirService $sunspring): array
    {
        $travelportResult = null;
        $sunspringResult = null;

        if (TravelportIntegrationConfig::isReadyForAir()) {
            try {
                $travelportResult = $travelport->lowFareSearch($input);
            } catch (Throwable $e) {
                $travelportResult = [
                    'ok' => false,
                    'message' => 'Travelport search failed: '.$e->getMessage(),
                    'solutions' => [],
                    'provider' => FlightProvider::TRAVELPORT,
                ];
            }
        }

        if ($sunspring->isReady() && SunSpringAirports::supportsSearch($input)) {
            try {
                $sunspringResult = $sunspring->lowFareSearch($input);
            } catch (Throwable $e) {
                $sunspringResult = [
                    'ok' => false,
                    'message' => 'SunSpring search failed: '.$e->getMessage(),
                    'solutions' => [],
                    'provider' => FlightProvider::SUNSPRING,
                ];
            }
        } elseif ($sunspring->isReady()) {
            $sunspringResult = [
                'ok' => true,
                'message' => 'SunSpring does not cover this route.',
                'solutions' => [],
                'provider' => FlightProvider::SUNSPRING,
            ];
        }

        return self::merge($travelportResult, $sunspringResult);
    }

    /**
     * @param  array<string, mixed>|null  $travelport
     * @param  array<string, mixed>|null  $sunspring
     * @return array<string, mixed>
     */
    public static function merge(?array $travelport, ?array $sunspring): array
    {
        $sources = [];
        $solutions = [];

        foreach ([
            FlightProvider::TRAVELPORT => $travelport,
            FlightProvider::SUNSPRING => $sunspring,
        ] as $provider => $result) {
            if ($result === null) {
                continue;
            }

            $rows = is_array($result['solutions'] ?? null) ? $result['solutions'] : [];
            foreach ($rows as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $row['provider'] = $provider;
                $solutions[] = $row;
            }

            $sources[$provider] = [
                'ok' => (bool) ($result['ok'] ?? false),
                'message' => (string) ($result['message'] ?? ''),
                'count' => count($rows),
            ];
        }

        if ($sources === []) {
            return [
                'ok' => false,
                'message' => 'No flight API is configured. Ask the admin to enable Travelport or SunSpring.',
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

        return [
            'ok' => $ok,
            'message' => $message,
            'solutions' => $solutions,
            'provider' => count($sources) > 1 ? 'mixed' : (string) array_key_first($sources),
            'sources' => $sources,
        ];
    }

    /**
     * @param  array<string, array{ok: bool, message: string, count: int}>  $sources
     */
    protected static function failureMessage(array $sources): string
    {
        if ($sources === []) {
            return 'No flight API is configured. Ask the admin to enable Travelport or SunSpring.';
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
