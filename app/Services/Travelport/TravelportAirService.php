<?php

namespace App\Services\Travelport;

use Illuminate\Support\Str;

class TravelportAirService extends TravelportSoapClient
{
    private const AIR_SERVICE_SUFFIX = '/B2BGateway/connect/uAPI/AirService';

    private const UNIVERSAL_RECORD_SUFFIX = '/B2BGateway/connect/uAPI/UniversalRecordService';

    public function airServiceUrl(): string
    {
        return $this->serviceUrl(self::AIR_SERVICE_SUFFIX);
    }

    public function universalRecordServiceUrl(): string
    {
        return $this->serviceUrl(self::UNIVERSAL_RECORD_SUFFIX);
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
            'trace_id' => $result['trace_id'],
            'response_excerpt' => $result['response_excerpt'],
            'endpoint' => $result['endpoint'],
            'operation' => 'low_fare_search',
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
            $params['_pricing_solution_xml'] = $params['_pricing_solution_xml']
                ?? TravelportAirXmlBuilder::extractFirstPricingSolution((string) session('travelport.last_lfs_xml', ''));
            if ($params['_pricing_solution_xml'] === null || $params['_pricing_solution_xml'] === '') {
                return $this->failResult($operation, 'Run Low Fare Search first (same session) or pricing solution is missing from the last response.', $this->airServiceUrl());
            }
        }

        $endpoint = $this->endpointFor($meta);
        $responseTag = (string) ($meta['response'] ?? '');
        $builder = new TravelportAirXmlBuilder;
        $lastFail = null;

        foreach ($this->schemaVersionsToTry() as $ver) {
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

            if ($operation === 'low_fare_search') {
                session(['travelport.last_lfs_xml' => $http['body']]);
            }

            $parsed = $operation === 'low_fare_search'
                ? (new TravelportFlightParser)->parseLowFareSearch($http['body'])
                : ['solutions' => [], 'trace_id' => $this->extractTraceId($http['body'])];

            $count = count($parsed['solutions']);
            $message = $operation === 'low_fare_search'
                ? ($count > 0 ? "Found {$count} fare option(s)." : 'Search completed — see response for details.')
                : ($meta['label'] ?? $operation).' completed successfully.';

            return [
                'ok' => true,
                'http_status' => $http['http_status'],
                'message' => $message.' (v'.$ver.')',
                'solutions' => $parsed['solutions'],
                'trace_id' => $parsed['trace_id'],
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
        return ($meta['service'] ?? 'air') === 'universal_record'
            ? $this->universalRecordServiceUrl()
            : $this->airServiceUrl();
    }

    /**
     * @return list<int>
     */
    private function schemaVersionsToTry(): array
    {
        $preferred = $this->schemaVersion();
        $candidates = [$preferred, 52, 50, 48, 37, 34, 33, 32];

        return array_values(array_unique(array_filter($candidates, fn ($v) => $v >= 30 && $v <= 99)));
    }

    private function isSchemaVersionFault(string $message): bool
    {
        return Str::contains(Str::lower($message), [
            'invalid version',
            'configured air_v',
            'validation failed',
            'marshal',
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
        return TravelportAirXmlBuilder::extractFirstPricingSolution((string) session('travelport.last_lfs_xml', '')) !== null;
    }
}
