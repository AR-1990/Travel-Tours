<?php

namespace App\Services\Travelport;

use Illuminate\Support\Str;

class TravelportFlightParser
{
    /**
     * @return array{solutions: list<array<string, mixed>>, trace_id: ?string}
     */
    public function parseLowFareSearch(string $xml): array
    {
        $solutions = [];
        $traceId = null;

        if (! Str::contains($xml, 'LowFareSearchRsp')) {
            return ['solutions' => [], 'trace_id' => null];
        }

        if (preg_match('/TraceId="([^"]+)"/', $xml, $m)) {
            $traceId = $m[1];
        }

        if (! preg_match_all(
            '/<(?:[\w]+:)?AirPricingSolution\b([^>]*)>(.*?)<\/(?:[\w]+:)?AirPricingSolution>/s',
            $xml,
            $blocks,
            PREG_SET_ORDER
        )) {
            return ['solutions' => [], 'trace_id' => $traceId];
        }

        foreach ($blocks as $i => $block) {
            $attrs = $block[1];
            $inner = $block[2];

            $solution = [
                'index' => $i + 1,
                'key' => $this->attr($attrs, 'Key'),
                'total_price' => $this->attr($attrs, 'TotalPrice'),
                'base_price' => $this->attr($attrs, 'BasePrice'),
                'taxes' => $this->attr($attrs, 'Taxes'),
                'segments' => $this->parseSegments($inner),
            ];

            if ($solution['segments'] !== []) {
                $solutions[] = $solution;
            }
        }

        if ($solutions === [] && preg_match_all(
            '/<(?:[\w]+:)?FlightDetails\b([^>]*)\/>/s',
            $xml,
            $fdMatches,
            PREG_SET_ORDER
        )) {
            $segments = [];
            foreach ($fdMatches as $fd) {
                $segments[] = $this->flightDetailsSegment($fd[1]);
            }
            if ($segments !== []) {
                $solutions[] = [
                    'index' => 1,
                    'key' => null,
                    'total_price' => null,
                    'base_price' => null,
                    'taxes' => null,
                    'segments' => $segments,
                ];
            }
        }

        return ['solutions' => array_slice($solutions, 0, 25), 'trace_id' => $traceId];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function parseSegments(string $xml): array
    {
        $segments = [];

        if (preg_match_all(
            '/<(?:[\w]+:)?AirSegment\b([^>]*)(?:\/>|>.*?<\/(?:[\w]+:)?AirSegment>)/s',
            $xml,
            $matches,
            PREG_SET_ORDER
        )) {
            foreach ($matches as $m) {
                $segments[] = $this->airSegment($m[1]);
            }
        }

        if ($segments === [] && preg_match_all(
            '/<(?:[\w]+:)?FlightDetails\b([^>]*)\/>/s',
            $xml,
            $fdMatches,
            PREG_SET_ORDER
        )) {
            foreach ($fdMatches as $fd) {
                $segments[] = $this->flightDetailsSegment($fd[1]);
            }
        }

        return $segments;
    }

    /**
     * @return array<string, mixed>
     */
    private function airSegment(string $attrs): array
    {
        return [
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
     * @return array<string, mixed>
     */
    private function flightDetailsSegment(string $attrs): array
    {
        return [
            'carrier' => $this->attr($attrs, 'Carrier'),
            'flight_number' => $this->attr($attrs, 'FlightNumber'),
            'origin' => $this->attr($attrs, 'Origin'),
            'destination' => $this->attr($attrs, 'Destination'),
            'departure' => $this->attr($attrs, 'DepartureTime'),
            'arrival' => $this->attr($attrs, 'ArrivalTime'),
            'equipment' => $this->attr($attrs, 'Equipment'),
            'class_of_service' => null,
        ];
    }

    private function attr(string $attrString, string $name): ?string
    {
        if (preg_match('/\b'.preg_quote($name, '/').'="([^"]*)"/', $attrString, $m)) {
            return $m[1];
        }

        return null;
    }
}
