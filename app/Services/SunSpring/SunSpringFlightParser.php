<?php

namespace App\Services\SunSpring;

/**
 * Maps SunSpring Airline API payloads to the shared flight solution shape used by the UI.
 */
class SunSpringFlightParser
{
    /**
     * @param  array<string, mixed>  $response
     * @param  array<string, mixed>  $input
     * @return array{ok: bool, message: string, solutions: list<array<string, mixed>>, total_found: int, request_id?: mixed, raw?: array<string, mixed>}
     */
    public function parseSearch(array $response, array $input = []): array
    {
        $status = strtolower((string) ($response['status'] ?? ''));
        $err = $response['err'] ?? null;
        $ok = $status === 'success' && $this->errIsClear($err);

        if (! $ok) {
            return [
                'ok' => false,
                'message' => $this->errorMessage($response) ?: 'SunSpring flight search failed.',
                'solutions' => [],
                'total_found' => 0,
                'raw' => $response,
            ];
        }

        $outbound = is_array($response['outbound'] ?? null) ? $response['outbound'] : [];
        $returns = is_array($response['return'] ?? null) ? $response['return'] : [];
        $adults = max(1, (int) ($input['adults'] ?? $input['adult'] ?? 1));
        $isRound = ($input['trip_type'] ?? '') === 'roundtrip' && $returns !== [];

        $solutions = [];
        $index = 0;

        if ($isRound) {
            foreach ($outbound as $ob) {
                if (! is_array($ob)) {
                    continue;
                }
                $join = $ob['return_flights'] ?? $ob['join'] ?? [];
                $join = is_array($join) ? $join : [];
                foreach ($returns as $rb) {
                    if (! is_array($rb)) {
                        continue;
                    }
                    $retRef = (string) ($rb['ref_number'] ?? '');
                    if ($join !== [] && $retRef !== '' && ! in_array($retRef, $join, true)) {
                        continue;
                    }
                    $solutions[] = $this->buildRoundSolution($ob, $rb, $index++, $adults);
                }
            }
        } else {
            foreach ($outbound as $ob) {
                if (! is_array($ob)) {
                    continue;
                }
                $solutions[] = $this->buildOneWaySolution($ob, $index++, $adults);
            }
        }

        return [
            'ok' => true,
            'message' => $solutions === []
                ? 'Search completed — no flights found for this route/date.'
                : 'Found '.count($solutions).' fare option(s).',
            'solutions' => $solutions,
            'total_found' => count($solutions),
            'request_id' => $response['request_id'] ?? null,
            'raw' => $response,
            'provider' => 'sunspring',
        ];
    }

    /**
     * @param  array<string, mixed>  $priceResponse
     * @param  array<string, mixed>  $selected
     * @return array{ok: bool, message: string, solutions: list<array<string, mixed>>, raw?: array<string, mixed>}
     */
    public function parsePrice(array $priceResponse, array $selected, int $adults = 1): array
    {
        $status = strtolower((string) ($priceResponse['status'] ?? ''));
        $ok = $status === 'success' && $this->errIsClear($priceResponse['err'] ?? null);

        if (! $ok) {
            return [
                'ok' => false,
                'message' => $this->errorMessage($priceResponse) ?: 'SunSpring air price failed.',
                'solutions' => [],
                'raw' => $priceResponse,
            ];
        }

        $currency = (string) data_get($selected, 'currency_code', 'USD');
        $total = $priceResponse['total'] ?? data_get($priceResponse, 'adult.total');
        $adultFare = data_get($priceResponse, 'adult.fare');
        $adultTax = data_get($priceResponse, 'adult.tax');

        $solution = $selected;
        $solution['total_price'] = $this->money($total, $currency);
        $solution['base_price'] = $this->money($adultFare !== null ? ((float) $adultFare) * $adults : null, $currency);
        $solution['taxes'] = $this->money($adultTax !== null ? ((float) $adultTax) * $adults : null, $currency);
        $solution['price_snapshot'] = $priceResponse;
        $solution['provider'] = 'sunspring';

        return [
            'ok' => true,
            'message' => 'Fare priced successfully.',
            'solutions' => [$solution],
            'raw' => $priceResponse,
            'provider' => 'sunspring',
        ];
    }

    /**
     * @param  array<string, mixed>  $flight
     * @return array<string, mixed>
     */
    public function buildOneWaySolution(array $flight, int $index, int $adults): array
    {
        $ref = (string) ($flight['ref_number'] ?? '');
        $segment = $this->segmentFromFlight($flight);
        $currency = (string) ($flight['currency_code'] ?? 'USD');
        $unit = (float) ($flight['price'] ?? data_get($flight, 'passenger_fare.adult.fare', 0));
        $taxUnit = (float) data_get($flight, 'passenger_fare.adult.tax', 0);
        $total = ($unit + $taxUnit) * max(1, $adults);
        if (isset($flight['price']) && is_numeric($flight['price'])) {
            // Many responses already quote a per-ADT all-in price.
            $total = (float) $flight['price'] * max(1, $adults);
        }

        return [
            'index' => $index,
            'key' => $ref,
            'provider' => 'sunspring',
            'outbound_ref' => $ref,
            'return_ref' => null,
            'total_price' => $this->money($total, $currency),
            'base_price' => $this->money($unit * $adults, $currency),
            'taxes' => $this->money($taxUnit * $adults, $currency),
            'fare_basis' => (string) data_get($flight, 'flight_details.farebasis_code', ''),
            'plating_carrier' => (string) data_get($flight, 'flight_details.airline', ''),
            'currency_code' => $currency,
            'capacity' => (int) ($flight['capacity'] ?? 0),
            'segments' => [$segment],
            'journeys' => [[
                'travel_time' => (string) ($flight['duration'] ?? ''),
                'segments' => [$segment],
            ]],
            'raw_flight' => $flight,
        ];
    }

    /**
     * @param  array<string, mixed>  $outbound
     * @param  array<string, mixed>  $return
     * @return array<string, mixed>
     */
    public function buildRoundSolution(array $outbound, array $return, int $index, int $adults): array
    {
        $obRef = (string) ($outbound['ref_number'] ?? '');
        $retRef = (string) ($return['ref_number'] ?? '');
        $obSeg = $this->segmentFromFlight($outbound);
        $retSeg = $this->segmentFromFlight($return);
        $currency = (string) ($outbound['currency_code'] ?? $return['currency_code'] ?? 'USD');
        $obPrice = (float) ($outbound['price'] ?? 0);
        $retPrice = (float) ($return['price'] ?? 0);
        $total = ($obPrice + $retPrice) * max(1, $adults);

        return [
            'index' => $index,
            'key' => $obRef.'|'.$retRef,
            'provider' => 'sunspring',
            'outbound_ref' => $obRef,
            'return_ref' => $retRef,
            'total_price' => $this->money($total, $currency),
            'base_price' => $this->money($total, $currency),
            'taxes' => null,
            'fare_basis' => (string) data_get($outbound, 'flight_details.farebasis_code', ''),
            'plating_carrier' => (string) data_get($outbound, 'flight_details.airline', ''),
            'currency_code' => $currency,
            'capacity' => min((int) ($outbound['capacity'] ?? 0), (int) ($return['capacity'] ?? 0)),
            'segments' => [$obSeg, $retSeg],
            'journeys' => [
                ['travel_time' => (string) ($outbound['duration'] ?? ''), 'segments' => [$obSeg]],
                ['travel_time' => (string) ($return['duration'] ?? ''), 'segments' => [$retSeg]],
            ],
            'raw_flight' => ['outbound' => $outbound, 'return' => $return],
        ];
    }

    /**
     * @param  array<string, mixed>  $flight
     * @return array<string, mixed>
     */
    public function segmentFromFlight(array $flight): array
    {
        $depDate = (string) data_get($flight, 'departure.date', '');
        $depTime = (string) data_get($flight, 'departure.time', '');
        $arrDate = (string) data_get($flight, 'arrival.date', $depDate);
        $arrTime = (string) data_get($flight, 'arrival.time', '');

        return [
            'key' => (string) ($flight['ref_number'] ?? ''),
            'carrier' => (string) data_get($flight, 'flight_details.airline', ''),
            'flight_number' => (string) data_get($flight, 'flight_details.flight_number', ''),
            'origin' => (string) data_get($flight, 'departure.location_code', ''),
            'destination' => (string) data_get($flight, 'arrival.location_code', ''),
            'departure' => trim($depDate.'T'.$depTime, 'T'),
            'arrival' => trim($arrDate.'T'.$arrTime, 'T'),
            'equipment' => (string) data_get($flight, 'flight_details.airplane', ''),
            'class_of_service' => (string) data_get($flight, 'flight_details.class', ''),
            'cabin' => (string) data_get($flight, 'flight_details.cabin', ''),
            'duration' => (string) ($flight['duration'] ?? ''),
            'airline_name' => (string) data_get($flight, 'flight_details.airline_name_en', data_get($flight, 'flight_details.airline_name', '')),
        ];
    }

    /**
     * @param  mixed  $err
     */
    public function errIsClear(mixed $err): bool
    {
        if ($err === null || $err === 0 || $err === '0') {
            return true;
        }
        if (is_array($err) && $err === []) {
            return true;
        }
        if (is_array($err) && array_is_list($err)) {
            foreach ($err as $item) {
                if (is_array($item) && (int) ($item['code'] ?? 1) !== 0) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $response
     */
    public function errorMessage(array $response): ?string
    {
        $err = $response['err'] ?? null;
        if (is_array($err) && array_is_list($err)) {
            $parts = [];
            foreach ($err as $item) {
                if (! is_array($item)) {
                    continue;
                }
                $msg = (string) ($item['msg'] ?? $item['message'] ?? '');
                if ($msg !== '') {
                    $parts[] = $msg;
                }
            }
            if ($parts !== []) {
                return implode('; ', $parts);
            }
        }

        $message = (string) ($response['message'] ?? $response['msg'] ?? '');

        return $message !== '' ? $message : null;
    }

    private function money(mixed $amount, string $currency): ?string
    {
        if ($amount === null || $amount === '') {
            return null;
        }

        return strtoupper($currency).number_format((float) $amount, 2, '.', '');
    }
}
