<?php

namespace App\Services\Travelport;

use Illuminate\Support\Str;

class TravelportAirService extends TravelportSoapClient
{
    private const AIR_SERVICE_SUFFIX = '/B2BGateway/connect/uAPI/AirService';

    private const UNIVERSAL_RECORD_SUFFIX = '/B2BGateway/connect/uAPI/UniversalRecordService';

    private const FLIGHT_SERVICE_SUFFIX = '/B2BGateway/connect/uAPI/FlightService';

    public function airServiceUrl(): string
    {
        return $this->serviceUrl(self::AIR_SERVICE_SUFFIX);
    }

    public function universalRecordServiceUrl(): string
    {
        return $this->serviceUrl(self::UNIVERSAL_RECORD_SUFFIX);
    }

    public function flightServiceUrl(): string
    {
        return $this->serviceUrl(self::FLIGHT_SERVICE_SUFFIX);
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array{ok: bool, http_status: ?int, message: string, solutions: list<array<string, mixed>>, trace_id: ?string, response_excerpt: ?string, endpoint: string, operation: string}
     */
    public function lowFareSearch(array $params): array
    {
        $result = $this->execute('low_fare_search', $params);

        return [
            'ok' => $result['ok'],
            'http_status' => $result['http_status'],
            'message' => $result['message'],
            'solutions' => $result['solutions'] ?? [],
            'total_found' => $result['total_found'] ?? count($result['solutions'] ?? []),
            'trace_id' => $result['trace_id'],
            'response_excerpt' => $result['response_excerpt'],
            'endpoint' => $result['endpoint'],
            'operation' => 'low_fare_search',
            'schema_version' => $result['schema_version'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array{ok: bool, http_status: ?int, message: string, solutions?: list<array<string, mixed>>, trace_id: ?string, response_excerpt: ?string, endpoint: string, operation: string, schema_version: ?int}
     */
    public function execute(string $operation, array $params = []): array
    {
        $meta = TravelportAirCatalog::get($operation);
        if ($meta === null) {
            return $this->failResult($operation, 'Unknown air operation.', $this->airServiceUrl());
        }

        $blocked = $this->integrationBlockedMessage();
        if ($blocked !== null) {
            return $this->failResult($operation, $blocked, $this->endpointFor($meta));
        }

        if (trim((string) ($this->cfg()['target_branch'] ?? '')) === '') {
            return $this->failResult($operation, 'Target branch is required. Set it under Admin → Integrations → Travelport.', $this->endpointFor($meta));
        }

        if ($operation === 'air_price') {
            $solutionKey = (string) ($params['solution_key'] ?? '');
            $lastLfsXml = (string) session('travelport.last_lfs_xml', '');
            $params['_lfs_xml'] = $lastLfsXml;
            $params['_pricing_solution_xml'] = $params['_pricing_solution_xml']
                ?? TravelportAirXmlBuilder::extractPricingSolution($lastLfsXml, $solutionKey !== '' ? $solutionKey : null);
            if ($params['_pricing_solution_xml'] === null || $params['_pricing_solution_xml'] === '') {
                return $this->failResult($operation, 'Run Low Fare Search first (same session) or pricing solution is missing from the last response.', $this->airServiceUrl());
            }

            $detected = $this->extractSchemaVersionFromXml($lastLfsXml);
            if ($detected === null) {
                $detected = $this->extractSchemaVersionFromXml((string) $params['_pricing_solution_xml']);
            }
            if ($detected !== null) {
                $params['_preferred_schema_version'] = $detected;
            }
        }

        if ($operation === 'air_fare_rules') {
            $lastLfsXml = (string) session('travelport.last_lfs_xml', '');
            $params['_fare_rule_key_xml'] = $this->extractFirstFareRuleKeyXml($lastLfsXml);
            $detected = $this->extractSchemaVersionFromXml($lastLfsXml);
            if ($detected !== null) {
                $params['_preferred_schema_version'] = $detected;
            }
        }

        if ($operation === 'seat_map' && ! isset($params['carrier'])) {
            $lastLfsXml = (string) session('travelport.last_lfs_xml', '');
            $seg = $this->extractFirstAirSegmentForSeatMap($lastLfsXml);
            if ($seg !== null) {
                $params = array_merge($seg, $params);
            }
            $detected = $this->extractSchemaVersionFromXml($lastLfsXml);
            if ($detected !== null) {
                $params['_preferred_schema_version'] = $detected;
            }
        }

        if (in_array($operation, ['air_create_reservation', 'air_merchandising'], true)) {
            $priceXml = (string) session('travelport.last_air_price_xml', '');
            $solution = TravelportAirXmlBuilder::prepareAirPricingSolutionForBooking($priceXml);
            if ($solution === null || $solution === '') {
                return $this->failResult(
                    $operation,
                    'Run Air Price first in this session — booking needs the priced itinerary.',
                    $this->endpointFor($meta)
                );
            }
            $params['_air_pricing_solution_xml'] = $solution;
            $detected = $this->extractSchemaVersionFromXml($priceXml);
            if ($detected !== null) {
                $params['_preferred_schema_version'] = $detected;
                $params['_booking_only_schema'] = true;
            }
        }

        if (in_array($operation, ['flight_details', 'flight_information'], true)) {
            $lastLfsXml = (string) session('travelport.last_lfs_xml', '');
            if (empty($params['carrier'])) {
                $seg = $this->extractFirstAirSegmentForSeatMap($lastLfsXml);
                if ($seg !== null) {
                    $params['carrier'] = $seg['carrier'] ?? '';
                    $params['flight_number'] = $params['flight_number'] ?? ($seg['flight_number'] ?? '');
                    $params['origin'] = $params['origin'] ?? ($seg['origin'] ?? '');
                    $params['destination'] = $params['destination'] ?? ($seg['destination'] ?? '');
                    if (empty($params['departure_date']) && ! empty($seg['departure_time'])) {
                        $params['departure_date'] = substr((string) $seg['departure_time'], 0, 10);
                    }
                }
            }
        }

        $endpoint = $this->endpointFor($meta);
        $responseTag = (string) ($meta['response'] ?? '');
        $builder = new TravelportAirXmlBuilder;
        $lastFail = null;

        foreach ($this->schemaVersionsToTry($params) as $ver) {
            $body = $builder->build($operation, $params, $ver);
            if ($body === null || $body === '') {
                continue;
            }

            $http = $this->postSoap($endpoint, $body);

            if (! $http['ok']) {
                $lastFail = $http;
                if ($this->isSchemaVersionFault($http['message'])) {
                    continue;
                }

                return $this->failResult($operation, $http['message'].' (v'.$ver.')', $endpoint, $http);
            }

            if ($responseTag !== '' && ! Str::contains($http['body'], $responseTag)) {
                $lastFail = $http;
                continue;
            }

            // AirTicketing can return a 200 AirTicketingRsp that actually failed to issue.
            if ($operation === 'air_ticketing' && Str::contains($http['body'], 'TicketFailureInfo')) {
                $reason = 'Ticketing failed at the airline host.';
                if (preg_match('/<(?:[\w]+:)?TicketFailureInfo\b[^>]*\bMessage="([^"]+)"/', $http['body'], $tm)) {
                    $reason = trim(html_entity_decode($tm[1], ENT_XML1 | ENT_QUOTES, 'UTF-8'));
                }

                return $this->failResult($operation, $reason.' (v'.$ver.')', $endpoint, $http);
            }

            if ($operation === 'low_fare_search') {
                session(['travelport.last_lfs_xml' => $http['body']]);
            }

            if ($operation === 'air_price') {
                session(['travelport.last_air_price_xml' => $http['body']]);
            }

            $parser = new TravelportFlightParser;
            $parsed = match ($operation) {
                'low_fare_search' => $parser->parseLowFareSearch($http['body']),
                'air_fare_display' => $parser->parseFareDisplay($http['body']),
                'air_price' => $parser->parseAirPrice($http['body']),
                'air_create_reservation', 'universal_record_retrieve' => $parser->parseLocators($http['body']),
                'air_retrieve_document' => array_merge(
                    ['solutions' => [], 'trace_id' => $this->extractTraceId($http['body']), 'total_found' => 0],
                    ['ticket_numbers' => $parser->parseTicketNumbers($http['body'])]
                ),
                default => ['solutions' => [], 'trace_id' => $this->extractTraceId($http['body']), 'total_found' => 0],
            };

            if ($operation === 'air_create_reservation' && ! empty($parsed['universal_locator'])) {
                session([
                    'travelport.last_booking' => [
                        'universal_locator' => $parsed['universal_locator'],
                        'air_reservation_locator' => $parsed['air_reservation_locator'] ?? '',
                    ],
                ]);
            }

            $count = count($parsed['solutions']);
            $totalFound = (int) ($parsed['total_found'] ?? $count);
            $message = match ($operation) {
                'low_fare_search' => $count > 0
                    ? "Showing {$count} of {$totalFound} fare option(s) (sorted by price)."
                    : 'Search completed — see response for details.',
                'air_fare_display' => $count > 0
                    ? "Showing {$count} of {$totalFound} published fare(s) (sorted by amount)."
                    : 'Fare display completed — no fares in response (check dates or market).',
                'air_price' => $count > 0
                    ? 'Air Price completed successfully.'
                    : 'Air Price completed successfully.',
                'air_create_reservation' => ! empty($parsed['universal_locator'])
                    ? 'Booking created. Universal Record: '.$parsed['universal_locator']
                    : 'Create Reservation completed — see response for locator.',
                'air_ticketing' => 'Ticket issue request completed.',
                'air_retrieve_document' => ! empty($parsed['ticket_numbers'])
                    ? 'Tickets: '.implode(', ', $parsed['ticket_numbers'])
                    : 'Retrieve document completed.',
                default => ($meta['label'] ?? $operation).' completed successfully.',
            };

            return [
                'ok' => true,
                'http_status' => $http['http_status'],
                'message' => $message.' (v'.$ver.')',
                'solutions' => $parsed['solutions'],
                'total_found' => $totalFound,
                'trace_id' => $parsed['trace_id'],
                'universal_locator' => $parsed['universal_locator'] ?? null,
                'air_reservation_locator' => $parsed['air_reservation_locator'] ?? null,
                'ticket_numbers' => $parsed['ticket_numbers'] ?? [],
                'response_excerpt' => $http['response_excerpt'],
                'endpoint' => $endpoint,
                'operation' => $operation,
                'schema_version' => $ver,
            ];
        }

        $msg = $lastFail['message'] ?? 'Request failed.';
        if (($meta['status'] ?? '') === 'beta') {
            $msg .= ' This operation may need additional fields — check Travelport samples or raw response.';
        }

        return $this->failResult($operation, $msg.' Try another schema version under Integrations.', $endpoint, $lastFail);
    }

    /**
     * @param  array<string, mixed>|null  $http
     * @return array{ok: bool, http_status: ?int, message: string, solutions: list<array<string, mixed>>, trace_id: ?string, response_excerpt: ?string, endpoint: string, operation: string, schema_version?: int}
     */
    private function failResult(string $operation, string $message, string $endpoint, ?array $http = null): array
    {
        return [
            'ok' => false,
            'http_status' => $http['http_status'] ?? null,
            'message' => $message,
            'solutions' => [],
            'trace_id' => null,
            'response_excerpt' => $http['response_excerpt'] ?? null,
            'endpoint' => $endpoint,
            'operation' => $operation,
        ];
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function endpointFor(array $meta): string
    {
        return match ($meta['service'] ?? 'air') {
            'universal_record' => $this->universalRecordServiceUrl(),
            'flight' => $this->flightServiceUrl(),
            default => $this->airServiceUrl(),
        };
    }

    /**
     * @return list<int>
     */
    /**
     * @param  array<string, mixed>  $params
     * @return list<int>
     */
    private function schemaVersionsToTry(array $params = []): array
    {
        $preferred = $this->schemaVersion();
        $detected = (int) ($params['_preferred_schema_version'] ?? 0);
        $candidates = [$preferred, 52, 51, 50, 48, 37, 34, 33, 32];
        if ($detected >= 30 && $detected <= 99) {
            array_unshift($candidates, $detected);

            if (($params['_booking_only_schema'] ?? false) === true) {
                return [$detected];
            }
        }

        return array_values(array_unique(array_filter($candidates, fn ($v) => $v >= 30 && $v <= 99)));
    }

    private function extractSchemaVersionFromXml(string $xml): ?int
    {
        if ($xml === '') {
            return null;
        }

        if (preg_match('/schema\/air_v(\d+)_0/i', $xml, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    private function extractFirstFareRuleKeyXml(string $xml): string
    {
        if ($xml === '') {
            return '';
        }

        if (preg_match('/<(?:[\w]+:)?FareRuleKey\b([^>]*)>(.*?)<\/(?:[\w]+:)?FareRuleKey>/s', $xml, $m)) {
            $attrs = $m[1];
            $value = trim($m[2]);
            $provider = '';
            $fareInfoRef = '';
            if (preg_match('/\bProviderCode="([^"]+)"/', $attrs, $a)) {
                $provider = $a[1];
            }
            if (preg_match('/\bFareInfoRef="([^"]+)"/', $attrs, $a)) {
                $fareInfoRef = $a[1];
            }

            $attrXml = '';
            if ($fareInfoRef !== '') {
                $attrXml .= ' FareInfoRef="'.$this->xmlEscape($fareInfoRef).'"';
            }
            if ($provider !== '') {
                $attrXml .= ' ProviderCode="'.$this->xmlEscape($provider).'"';
            }

            return '      <air:FareRuleKey'.$attrXml.'>'.$this->xmlEscape($value).'</air:FareRuleKey>';
        }

        return '';
    }

    /**
     * @return array<string, string>|null
     */
    private function extractFirstAirSegmentForSeatMap(string $xml): ?array
    {
        if ($xml === '') {
            return null;
        }

        if (! preg_match('/<(?:[\w]+:)?AirSegment\b([^>]*?)\/?>/s', $xml, $m)) {
            return null;
        }

        $attrs = $m[1];
        $carrier = $this->extractAttr($attrs, 'Carrier');
        $flight = $this->extractAttr($attrs, 'FlightNumber');
        $origin = $this->extractAttr($attrs, 'Origin');
        $destination = $this->extractAttr($attrs, 'Destination');
        $departure = $this->extractAttr($attrs, 'DepartureTime');
        $segmentKey = $this->extractAttr($attrs, 'Key');
        $classOfService = $this->extractAttr($attrs, 'ClassOfService');

        if ($carrier === '' || $flight === '' || $origin === '' || $destination === '' || $departure === '') {
            return null;
        }

        return [
            'carrier' => $carrier,
            'flight_number' => $flight,
            'origin' => $origin,
            'destination' => $destination,
            'departure_time' => $departure,
            'segment_key' => $segmentKey !== '' ? $segmentKey : 'SEAT1',
            'class_of_service' => $classOfService,
        ];
    }

    private function extractAttr(string $attrs, string $name): string
    {
        if (preg_match('/\b'.preg_quote($name, '/').'="([^"]+)"/', $attrs, $m)) {
            return $m[1];
        }

        return '';
    }

    private function isSchemaVersionFault(string $message): bool
    {
        $lower = Str::lower($message);

        if (Str::contains($lower, [
            'unmarshalling message body',
            'unable to parse xml stream',
            'airsegmentref',
        ])) {
            return false;
        }

        return Str::contains($lower, [
            'invalid version',
            'configured air_v',
            'unrecognized document version',
            'schema',
        ]);
    }

    private function extractTraceId(string $xml): ?string
    {
        if (preg_match('/TraceId="([^"]+)"/', $xml, $m)) {
            return $m[1];
        }

        return null;
    }

    public function hasStoredPricingContext(): bool
    {
        return TravelportAirXmlBuilder::extractPricingSolution((string) session('travelport.last_lfs_xml', '')) !== null;
    }
}
