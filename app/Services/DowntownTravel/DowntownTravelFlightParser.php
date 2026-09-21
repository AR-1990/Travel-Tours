<?php

namespace App\Services\DowntownTravel;

use Illuminate\Support\Str;

class DowntownTravelFlightParser
{
    /**
     * @param  array<string, mixed>  $payload  Search RS (offers + flight_segments…)
     * @param  array<string, mixed>  $params   Original search input
     * @return array{ok: bool, message: string, solutions: list<array<string, mixed>>, total_found: int, provider: string}
     */
    public function parseSearch(array $payload, array $params = []): array
    {
        $offers = is_array($payload['offers'] ?? null) ? $payload['offers'] : [];
        $segmentsCatalog = is_array($payload['flight_segments'] ?? null) ? $payload['flight_segments'] : [];
        $priceClasses = is_array($payload['price_classes'] ?? null) ? $payload['price_classes'] : [];

        $solutions = [];
        foreach ($offers as $index => $offer) {
            if (! is_array($offer)) {
                continue;
            }
            $parsed = $this->parseOffer($offer, (int) $index, $segmentsCatalog, $priceClasses);
            if ($parsed !== null) {
                $solutions[] = $parsed;
            }
        }

        return [
            'ok' => true,
            'message' => $solutions === []
                ? 'No Downtown Travel fares for this route.'
                : 'Found '.count($solutions).' Downtown Travel fare(s).',
            'solutions' => $solutions,
            'total_found' => count($solutions),
            'provider' => 'downtown_travel',
        ];
    }

    /**
     * @param  array<string, mixed>  $offer
     * @param  list<array<string, mixed>>  $segmentsCatalog
     * @param  list<array<string, mixed>>  $priceClasses
     * @return array<string, mixed>|null
     */
    public function parseOffer(array $offer, int $index, array $segmentsCatalog, array $priceClasses = []): ?array
    {
        $digest = trim((string) ($offer['digest'] ?? ''));
        if ($digest === '') {
            return null;
        }

        $currency = strtoupper((string) data_get($offer, 'price.currency_code', 'USD'));
        $passengerTotal = (float) (
            data_get($offer, 'price.pricing_options.agent_cash.passenger_total')
            ?? data_get($offer, 'price.pricing_options.passenger_cc.passenger_total')
            ?? data_get($offer, 'price.airline_total')
            ?? 0
        );
        $agentNetTotal = (float) (
            data_get($offer, 'price.pricing_options.agent_cash.agent_net_total')
            ?? data_get($offer, 'price.pricing_options.passenger_cc.agent_net_total')
            ?? $passengerTotal
        );
        $base = (float) (data_get($offer, 'price.airline_base_fare') ?? 0);
        $carrierIata = strtoupper((string) data_get($offer, 'validating_carrier.iata', ''));
        $carrierName = trim((string) data_get($offer, 'validating_carrier.name', $carrierIata));

        $journeys = [];
        $flatSegments = [];
        $legs = is_array($offer['flights'] ?? null) ? $offer['flights'] : [];
        foreach ($legs as $leg) {
            if (! is_array($leg)) {
                continue;
            }
            $legSegments = [];
            foreach ((array) ($leg['segments'] ?? []) as $segIndex) {
                $raw = $segmentsCatalog[(int) $segIndex] ?? null;
                if (! is_array($raw)) {
                    continue;
                }
                $mapped = $this->mapSegment($raw);
                $legSegments[] = $mapped;
                $flatSegments[] = $mapped;
            }
            if ($legSegments !== []) {
                $priceClassIdx = $leg['price_class'] ?? null;
                $cabinLabel = is_int($priceClassIdx) && isset($priceClasses[$priceClassIdx]['name'])
                    ? (string) $priceClasses[$priceClassIdx]['name']
                    : '';
                $journeys[] = [
                    'travel_time' => '',
                    'cabin' => $cabinLabel,
                    'segments' => $legSegments,
                ];
            }
        }

        if ($flatSegments === []) {
            return null;
        }

        return [
            'key' => 'dt:'.$index.':'.substr(sha1($digest), 0, 12),
            'provider' => 'downtown_travel',
            'digest' => $digest,
            'offer_index' => $index,
            'total_price' => $this->money($passengerTotal, $currency),
            'base_price' => $base > 0 ? $this->money($base, $currency) : null,
            'currency' => $currency,
            'total_amount' => $passengerTotal,
            'agent_net_total' => $agentNetTotal,
            'passenger_total' => $passengerTotal,
            'travel_document_required' => (bool) ($offer['travel_document_required'] ?? false),
            'date_of_birth_required' => (bool) ($offer['date_of_birth_required'] ?? true),
            'plating_carrier' => $carrierIata,
            'airline_name' => $carrierName !== '' ? $carrierName : $carrierIata,
            'booking_supported' => (bool) ($offer['booking_supported'] ?? true),
            'segments' => $flatSegments,
            'journeys' => $journeys,
            'raw_offer' => $offer,
        ];
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    protected function mapSegment(array $raw): array
    {
        $depIata = strtoupper((string) data_get($raw, 'departure_point.iata', ''));
        $arrIata = strtoupper((string) data_get($raw, 'arrival_point.iata', ''));
        $depCity = trim((string) data_get($raw, 'departure_point.city', ''));
        $arrCity = trim((string) data_get($raw, 'arrival_point.city', ''));
        $depTs = (string) data_get($raw, 'departure_point.timestamp', '');
        $arrTs = (string) data_get($raw, 'arrival_point.timestamp', '');
        $carrier = strtoupper((string) data_get($raw, 'marketing_carrier.iata', ''));
        $carrierName = trim((string) data_get($raw, 'marketing_carrier.name', $carrier));
        $flightCode = trim((string) ($raw['flight_code'] ?? ''));

        return [
            'carrier' => $carrier,
            'airline_name' => $carrierName,
            'flight_number' => $flightCode,
            'origin' => $depIata,
            'destination' => $arrIata,
            'origin_city' => $depCity !== '' ? "{$depCity} ({$depIata})" : $depIata,
            'destination_city' => $arrCity !== '' ? "{$arrCity} ({$arrIata})" : $arrIata,
            'departure' => $depTs,
            'arrival' => $arrTs,
            'cabin' => (string) ($raw['cabin'] ?? ''),
            'booking_class' => (string) ($raw['booking_class'] ?? ''),
            'equipment' => (string) data_get($raw, 'vehicle.name', data_get($raw, 'vehicle.code', '')),
            'terminal_dep' => data_get($raw, 'departure_point.terminal'),
            'terminal_arr' => data_get($raw, 'arrival_point.terminal'),
        ];
    }

    protected function money(float $amount, string $currency): string
    {
        return strtoupper($currency).' '.number_format($amount, 2, '.', '');
    }
}
