<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait BuildsFlightOperationParams
{
    /**
     * @return array<string, mixed>
     */
    protected function flightOperationParams(Request $request, string $operation): array
    {
        $search = session('public.flight_search.input', session('travelport.flight_search.input', []));

        $params = array_filter($request->only([
            'origin', 'destination', 'departure_date', 'return_date', 'adults', 'trip_type',
            'fare_basis', 'carrier', 'flight_number', 'departure_time', 'class_of_service', 'segment_key',
            'universal_locator', 'air_reservation_locator', 'version', 'ticket_number',
            'solution_key', 'passenger_prefix', 'passenger_first', 'passenger_last',
            'passenger_email', 'passenger_phone', 'passenger_dob', 'passenger_gender',
            'form_of_payment',
        ]), fn ($v) => $v !== null && $v !== '');

        if (empty($params['adults'])) {
            $params['adults'] = (int) ($search['adults'] ?? 1);
        }

        if ($operation === 'air_create_reservation') {
            $params['passengers'] = [[
                'prefix' => (string) ($params['passenger_prefix'] ?? 'Mr'),
                'first' => (string) ($params['passenger_first'] ?? ''),
                'last' => (string) ($params['passenger_last'] ?? ''),
                'email' => (string) ($params['passenger_email'] ?? ''),
                'phone' => (string) ($params['passenger_phone'] ?? ''),
                'dob' => (string) ($params['passenger_dob'] ?? ''),
                'gender' => (string) ($params['passenger_gender'] ?? 'M'),
                'type' => 'ADT',
            ]];
        }

        $booking = session('travelport.last_booking', []);
        if (is_array($booking)) {
            if (empty($params['universal_locator']) && ! empty($booking['universal_locator'])) {
                $params['universal_locator'] = $booking['universal_locator'];
            }
            if (empty($params['air_reservation_locator']) && ! empty($booking['air_reservation_locator'])) {
                $params['air_reservation_locator'] = $booking['air_reservation_locator'];
            }
        }

        return $params;
    }

    /**
     * @param  array<string, mixed>  $searchSession
     * @param  array<string, mixed>  $priceSession
     * @return array<string, mixed>
     */
    protected function defaultFlightOperationInput(string $operation, array $searchSession = [], array $priceSession = []): array
    {
        $input = is_array($searchSession['input'] ?? null) ? $searchSession['input'] : [];
        $priced = is_array($priceSession['result']['solutions'][0] ?? null)
            ? $priceSession['result']['solutions'][0]
            : [];
        $firstSeg = $priced['segments'][0]
            ?? ($priced['journeys'][0]['segments'][0] ?? null)
            ?? ($searchSession['result']['solutions'][0]['segments'][0] ?? null);

        $defaults = [
            'origin' => $input['origin'] ?? '',
            'destination' => $input['destination'] ?? '',
            'departure_date' => $input['departure_date'] ?? '',
            'return_date' => $input['return_date'] ?? '',
            'adults' => (int) ($input['adults'] ?? 1),
            'fare_basis' => $priced['fare_basis'] ?? '',
            'carrier' => $firstSeg['carrier'] ?? '',
            'flight_number' => $firstSeg['flight_number'] ?? '',
            'departure_time' => $firstSeg['departure'] ?? '',
            'class_of_service' => $firstSeg['class_of_service'] ?? 'Y',
        ];

        $booking = session('travelport.last_booking', []);
        if (is_array($booking)) {
            $defaults['universal_locator'] = $booking['universal_locator'] ?? '';
            $defaults['air_reservation_locator'] = $booking['air_reservation_locator'] ?? '';
        }

        return $defaults;
    }
}
