<?php

namespace App\Services\Travelport;

use Illuminate\Support\Str;

class TravelportFlightParser
{
    private const MAX_SOLUTIONS = 50;

    /**
     * @return array{solutions: list<array<string, mixed>>, trace_id: ?string, total_found: int}
     */
    public function parseLowFareSearch(string $xml): array
    {
        if (! Str::contains($xml, 'LowFareSearchRsp')) {
            return ['solutions' => [], 'trace_id' => null, 'total_found' => 0];
        }

        $traceId = null;
        if (preg_match('/TraceId="([^"]+)"/', $xml, $m)) {
            $traceId = $m[1];
        }

        $segmentMap = $this->parseAirSegmentMap($xml);
        $solutions = [];

        if (preg_match_all(
            '/<(?:[\w]+:)?AirPricingSolution\b([^>]*)>(.*?)<\/(?:[\w]+:)?AirPricingSolution>/s',
            $xml,
            $blocks,
            PREG_SET_ORDER
        )) {
            foreach ($blocks as $i => $block) {
                $solution = $this->parsePricingSolution($block[1], $block[2], $segmentMap, $i + 1);
                if ($solution !== null) {
                    $solutions[] = $solution;
                }
            }
        }

        $totalFound = count($solutions);
        $solutions = $this->sortByPrice($solutions);
        $solutions = array_slice($solutions, 0, self::MAX_SOLUTIONS);

        foreach ($solutions as $idx => &$sol) {
            $sol['index'] = $idx + 1;
        }
        unset($sol);

        return [
            'solutions' => $solutions,
            'trace_id' => $traceId,
            'total_found' => $totalFound,
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function parseAirSegmentMap(string $xml): array
    {
        $map = [];

        if (! preg_match('/<(?:[\w]+:)?AirSegmentList>(.*?)<\/(?:[\w]+:)?AirSegmentList>/s', $xml, $list)) {
            return $map;
        }

        if (! preg_match_all(
            '/<(?:[\w]+:)?AirSegment\b([^>]*)(?:\/>|>.*?<\/(?:[\w]+:)?AirSegment>)/s',
            $list[1],
            $matches,
            PREG_SET_ORDER
        )) {
            return $map;
        }

        foreach ($matches as $m) {
            $seg = $this->airSegment($m[1]);
            $key = $seg['key'] ?? null;
            if ($key !== null) {
                $map[$key] = $seg;
            }
        }

        return $map;
    }

    /**
     * @param  array<string, array<string, mixed>>  $segmentMap
     * @return array<string, mixed>|null
     */
    private function parsePricingSolution(string $attrs, string $inner, array $segmentMap, int $index): ?array
    {
        $segmentKeys = [];
        if (preg_match_all('/<(?:[\w]+:)?AirSegmentRef\b[^>]*Key="([^"]+)"/', $inner, $refs)) {
            $segmentKeys = $refs[1];
        }

        $segments = [];
        foreach ($segmentKeys as $key) {
            if (isset($segmentMap[$key])) {
                $segments[] = $segmentMap[$key];
            }
        }

        if ($segments === []) {
            return null;
        }

        $journeys = $this->parseJourneys($inner, $segmentMap);

        return [
            'index' => $index,
            'key' => $this->attr($attrs, 'Key'),
            'total_price' => $this->attr($attrs, 'TotalPrice'),
            'base_price' => $this->attr($attrs, 'BasePrice'),
            'taxes' => $this->attr($attrs, 'Taxes'),
            'segments' => $segments,
            'journeys' => $journeys,
            'plating_carrier' => $this->attrFromInner($inner, 'PlatingCarrier'),
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $segmentMap
     * @return list<array{travel_time: ?string, segments: list<array<string, mixed>>}>
     */
    private function parseJourneys(string $inner, array $segmentMap): array
    {
        $journeys = [];

        if (! preg_match_all(
            '/<(?:[\w]+:)?Journey\b([^>]*)>(.*?)<\/(?:[\w]+:)?Journey>/s',
            $inner,
            $jBlocks,
            PREG_SET_ORDER
        )) {
            return $journeys;
        }

        foreach ($jBlocks as $jb) {
            $jSegs = [];
            if (preg_match_all('/<(?:[\w]+:)?AirSegmentRef\b[^>]*Key="([^"]+)"/', $jb[2], $refs)) {
                foreach ($refs[1] as $key) {
                    if (isset($segmentMap[$key])) {
                        $jSegs[] = $segmentMap[$key];
                    }
                }
            }
            if ($jSegs !== []) {
                $journeys[] = [
                    'travel_time' => $this->attr($jb[1], 'TravelTime'),
                    'segments' => $jSegs,
                ];
            }
        }

        return $journeys;
    }

    /**
     * @param  list<array<string, mixed>>  $solutions
     * @return list<array<string, mixed>>
     */
    private function sortByPrice(array $solutions): array
    {
        usort($solutions, function ($a, $b) {
            return $this->priceSortKey($a['total_price'] ?? '') <=> $this->priceSortKey($b['total_price'] ?? '');
        });

        return $solutions;
    }

    private function priceSortKey(string $raw): float
    {
        if (preg_match('/[\d.]+$/', $raw, $m)) {
            return (float) $m[0];
        }

        return PHP_FLOAT_MAX;
    }

    /**
     * @return array<string, mixed>
     */
    private function airSegment(string $attrs): array
    {
        return [
            'key' => $this->attr($attrs, 'Key'),
            'carrier' => $this->attr($attrs, 'Carrier'),
            'flight_number' => $this->attr($attrs, 'FlightNumber'),
            'origin' => $this->attr($attrs, 'Origin'),
            'destination' => $this->attr($attrs, 'Destination'),
            'departure' => $this->attr($attrs, 'DepartureTime'),
            'arrival' => $this->attr($attrs, 'ArrivalTime'),
            'equipment' => $this->attr($attrs, 'Equipment'),
            'class_of_service' => $this->attr($attrs, 'ClassOfService'),
        ];
    }

    /**
     * @return array{solutions: list<array<string, mixed>>, trace_id: ?string, total_found: int}
     */
    public function parseFareDisplay(string $xml): array
    {
        if (! Str::contains($xml, 'AirFareDisplayRsp')) {
            return ['solutions' => [], 'trace_id' => null, 'total_found' => 0];
        }

        $traceId = null;
        if (preg_match('/TraceId="([^"]+)"/', $xml, $m)) {
            $traceId = $m[1];
        }

        $fares = [];
        if (preg_match_all(
            '/<(?:[\w]+:)?FareDisplay\b([^>]*)(?:\/>|>(.*?)<\/(?:[\w]+:)?FareDisplay>)/s',
            $xml,
            $blocks,
            PREG_SET_ORDER
        )) {
            foreach ($blocks as $i => $block) {
                $attrs = $block[1];
                $carrier = $this->attr($attrs, 'Carrier');
                $fareBasis = $this->attr($attrs, 'FareBasis');
                $amount = $this->attr($attrs, 'Amount');
                $tripType = $this->attr($attrs, 'TripType');

                if ($carrier === null && $amount === null) {
                    continue;
                }

                $fares[] = [
                    'key' => (string) ($i + 1),
                    'plating_carrier' => $carrier,
                    'fare_basis' => $fareBasis,
                    'total_price' => $amount,
                    'trip_type' => $tripType,
                    'origin' => $this->attr($attrs, 'Origin'),
                    'destination' => $this->attr($attrs, 'Destination'),
                ];
            }
        }

        usort($fares, function (array $a, array $b): int {
            $pa = $this->numericAmount((string) ($a['total_price'] ?? ''));
            $pb = $this->numericAmount((string) ($b['total_price'] ?? ''));

            return $pa <=> $pb;
        });

        $total = count($fares);
        $capped = array_slice($fares, 0, self::MAX_SOLUTIONS);

        return [
            'solutions' => $capped,
            'trace_id' => $traceId,
            'total_found' => $total,
        ];
    }

    /**
     * @return array{solutions: list<array<string, mixed>>, trace_id: ?string, total_found: int}
     */
    public function parseAirPrice(string $xml): array
    {
        if (! Str::contains($xml, 'AirPriceRsp')) {
            return ['solutions' => [], 'trace_id' => null, 'total_found' => 0];
        }

        $traceId = null;
        if (preg_match('/TraceId="([^"]+)"/', $xml, $m)) {
            $traceId = $m[1];
        }

        $segmentMap = $this->parseAirPriceSegmentMap($xml);
        $solutions = [];

        if (preg_match_all(
            '/<(?:[\w]+:)?AirPricingSolution\b([^>]*)>(.*?)<\/(?:[\w]+:)?AirPricingSolution>/s',
            $xml,
            $blocks,
            PREG_SET_ORDER
        )) {
            foreach ($blocks as $i => $block) {
                $attrs = $block[1];
                $inner = $block[2];
                $segmentKeys = [];
                if (preg_match_all('/<(?:[\w]+:)?AirSegmentRef\b[^>]*Key="([^"]+)"/', $inner, $refs)) {
                    $segmentKeys = $refs[1];
                }

                $segments = [];
                foreach ($segmentKeys as $key) {
                    if (isset($segmentMap[$key])) {
                        $segments[] = $segmentMap[$key];
                    }
                }

                $fareBasis = null;
                if (preg_match('/<(?:[\w]+:)?FareInfo\b[^>]*FareBasis="([^"]+)"/', $inner, $fb)) {
                    $fareBasis = $fb[1];
                }
                $latestTicketing = null;
                if (preg_match('/<(?:[\w]+:)?AirPricingInfo\b[^>]*LatestTicketingTime="([^"]+)"/', $inner, $lt)) {
                    $latestTicketing = $lt[1];
                }
                $plating = $this->attrFromInner($inner, 'PlatingCarrier');

                $solutions[] = [
                    'index' => $i + 1,
                    'key' => $this->attr($attrs, 'Key'),
                    'total_price' => $this->attr($attrs, 'TotalPrice'),
                    'base_price' => $this->attr($attrs, 'BasePrice'),
                    'taxes' => $this->attr($attrs, 'Taxes'),
                    'plating_carrier' => $plating,
                    'fare_basis' => $fareBasis,
                    'latest_ticketing_time' => $latestTicketing,
                    'segments' => $segments,
                ];
            }
        }

        $solutions = $this->sortByPrice($solutions);
        $total = count($solutions);

        return [
            'solutions' => array_slice($solutions, 0, self::MAX_SOLUTIONS),
            'trace_id' => $traceId,
            'total_found' => $total,
        ];
    }

    /**
     * @return array{solutions: list<array<string, mixed>>, trace_id: ?string, total_found: int, universal_locator: ?string, air_reservation_locator: ?string}
     */
    public function parseLocators(string $xml): array
    {
        $traceId = null;
        if (preg_match('/TraceId="([^"]+)"/', $xml, $m)) {
            $traceId = $m[1];
        }

        $universal = null;
        if (preg_match('/UniversalRecordLocatorCode="([^"]+)"/', $xml, $m)) {
            $universal = $m[1];
        } elseif (preg_match('/<(?:[\w]+:)?UniversalRecord\b[^>]*\bLocatorCode="([^"]+)"/', $xml, $m)) {
            $universal = $m[1];
        }

        $air = null;
        if (preg_match('/<(?:[\w]+:)?AirReservationLocatorCode>([^<]+)</', $xml, $m)) {
            $air = trim($m[1]);
        } elseif (preg_match('/AirReservationLocatorCode="([^"]+)"/', $xml, $m)) {
            $air = $m[1];
        }

        return [
            'solutions' => [],
            'trace_id' => $traceId,
            'total_found' => 0,
            'universal_locator' => $universal,
            'air_reservation_locator' => $air,
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function parseAirPriceSegmentMap(string $xml): array
    {
        $map = [];
        if (! preg_match('/<(?:[\w]+:)?AirItinerary>(.*?)<\/(?:[\w]+:)?AirItinerary>/s', $xml, $itinerary)) {
            return $map;
        }

        if (! preg_match_all('/<(?:[\w]+:)?AirSegment\b([^>]*)(?:\/>|>.*?<\/(?:[\w]+:)?AirSegment>)/s', $itinerary[1], $matches, PREG_SET_ORDER)) {
            return $map;
        }

        foreach ($matches as $m) {
            $seg = $this->airSegment($m[1]);
            $key = $seg['key'] ?? null;
            if ($key !== null) {
                $map[$key] = $seg;
            }
        }

        return $map;
    }

    private function numericAmount(string $amount): float
    {
        if (preg_match('/[\d.]+/', $amount, $m)) {
            return (float) $m[0];
        }

        return PHP_FLOAT_MAX;
    }

    private function attr(string $attrString, string $name): ?string
    {
        if (preg_match('/\b'.preg_quote($name, '/').'="([^"]*)"/', $attrString, $m)) {
            return $m[1];
        }

        return null;
    }

    private function attrFromInner(string $inner, string $name): ?string
    {
        if (preg_match('/\b'.preg_quote($name, '/').'="([^"]*)"/', $inner, $m)) {
            return $m[1];
        }

        return null;
    }
}
