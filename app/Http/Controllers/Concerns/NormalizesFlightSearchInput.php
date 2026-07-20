<?php

namespace App\Http\Controllers\Concerns;

trait NormalizesFlightSearchInput
{
    protected function normalizeTripType(string $tripType): string
    {
        $value = strtolower(trim($tripType));

        return in_array($value, ['roundtrip', 'round-way', 'round_way'], true) ? 'roundtrip' : 'oneway';
    }

    protected function normalizeDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        foreach (['Y-m-d', 'n/j/Y', 'm/d/Y'] as $format) {
            $dt = \DateTime::createFromFormat($format, $value);
            if ($dt instanceof \DateTime) {
                return $dt->format('Y-m-d');
            }
        }

        $ts = strtotime($value);

        return $ts ? date('Y-m-d', $ts) : null;
    }

    protected function resolveAirportCode(string $value): ?string
    {
        $value = strtoupper(trim($value));
        if ($value === '') {
            return null;
        }

        if (preg_match('/\b([A-Z]{3})\b/', $value, $m)) {
            return $m[1];
        }

        $matches = \App\Support\AirportDirectory::search($value, 1);
        if ($matches !== [] && ! empty($matches[0]['code'])) {
            return strtoupper((string) $matches[0]['code']);
        }

        return null;
    }

    /**
     * @return array{origin: string, destination: string, departure_date: string, return_date: ?string, adults: int, trip_type: string}|null
     */
    protected function validatedFlightSearchInput(\Illuminate\Http\Request $request): ?array
    {
        $tripType = $this->normalizeTripType((string) $request->input('trip_type', 'oneway'));
        $departureDate = $this->normalizeDate((string) $request->input('departure_date', (string) $request->input('journey-date')));
        $returnDateRaw = (string) $request->input('return_date', (string) $request->input('return-date'));
        $returnDate = $tripType === 'roundtrip' ? $this->normalizeDate($returnDateRaw) : null;

        $origin = $this->resolveAirportCode((string) $request->input('origin', (string) $request->input('from-destination')));
        $destination = $this->resolveAirportCode((string) $request->input('destination', (string) $request->input('to-destination')));
        $adults = max(1, min(9, (int) $request->input('adults', (int) $request->input('adult', 1))));

        if ($origin === null || $destination === null || $departureDate === null) {
            return null;
        }

        if ($tripType === 'roundtrip' && $returnDate === null) {
            return null;
        }

        return [
            'origin' => $origin,
            'destination' => $destination,
            'departure_date' => $departureDate,
            'return_date' => $returnDate,
            'adults' => $adults,
            'trip_type' => $tripType,
        ];
    }
}
