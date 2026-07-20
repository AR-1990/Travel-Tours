<?php

namespace App\Support;

class FlightDisplay
{
    /** @var array<string, string> */
    private const AIRLINES = [
        'AA' => 'American Airlines',
        'UA' => 'United Airlines',
        'DL' => 'Delta Air Lines',
        'B6' => 'JetBlue',
        'NK' => 'Spirit Airlines',
        'F9' => 'Frontier Airlines',
        'AS' => 'Alaska Airlines',
        'WN' => 'Southwest Airlines',
        'BA' => 'British Airways',
        'VS' => 'Virgin Atlantic',
        'AF' => 'Air France',
        'KL' => 'KLM',
        'LH' => 'Lufthansa',
        'LX' => 'Swiss',
        'OS' => 'Austrian Airlines',
        'SK' => 'SAS',
        'IB' => 'Iberia',
        'AZ' => 'ITA Airways',
        'TP' => 'TAP Air Portugal',
        'EI' => 'Aer Lingus',
        'AY' => 'Finnair',
        'LO' => 'LOT Polish Airlines',
        'RO' => 'TAROM',
        'TK' => 'Turkish Airlines',
        'PC' => 'Pegasus Airlines',
        'XQ' => 'SunExpress',
        'EK' => 'Emirates',
        'EY' => 'Etihad Airways',
        'QR' => 'Qatar Airways',
        'SV' => 'Saudia',
        'GF' => 'Gulf Air',
        'WY' => 'Oman Air',
        'FZ' => 'flydubai',
        'XY' => 'flynas',
        'G9' => 'Air Arabia',
        'AI' => 'Air India',
        '6E' => 'IndiGo',
        'UK' => 'Vistara',
        'PK' => 'Pakistan International Airlines',
        'PA' => 'Airblue',
        'ER' => 'SereneAir',
        'CZ' => 'China Southern',
        'CA' => 'Air China',
        'MU' => 'China Eastern',
        'CX' => 'Cathay Pacific',
        'SQ' => 'Singapore Airlines',
        'MH' => 'Malaysia Airlines',
        'TG' => 'Thai Airways',
        'NH' => 'All Nippon Airways',
        'JL' => 'Japan Airlines',
        'KE' => 'Korean Air',
        'OZ' => 'Asiana Airlines',
        'QF' => 'Qantas',
        'NZ' => 'Air New Zealand',
        'AC' => 'Air Canada',
        'WS' => 'WestJet',
        'SA' => 'South African Airways',
        'ET' => 'Ethiopian Airlines',
        'MS' => 'EgyptAir',
        'AT' => 'Royal Air Maroc',
        'RJ' => 'Royal Jordanian',
        'ME' => 'Middle East Airlines',
        'LY' => 'El Al',
        'FI' => 'Icelandair',
        'DY' => 'Norwegian',
        'FR' => 'Ryanair',
        'U2' => 'easyJet',
        'W6' => 'Wizz Air',
        'VY' => 'Vueling',
    ];

    public static function airportLabel(?string $code): string
    {
        $found = AirportDirectory::find((string) $code);

        return $found['label'] ?? self::airportShort($code);
    }

    public static function airportCity(?string $code): string
    {
        $found = AirportDirectory::find((string) $code);
        $city = trim((string) ($found['city'] ?? ''));
        $short = self::airportShort($code);

        if ($city !== '' && $short !== '—') {
            return "{$city} ({$short})";
        }

        return $found['label'] ?? $short;
    }

    public static function airportShort(?string $code): string
    {
        $code = strtoupper(trim((string) $code));

        return $code !== '' ? $code : '—';
    }

    public static function airlineName(?string $code): string
    {
        $code = strtoupper(trim((string) $code));
        if ($code === '') {
            return '—';
        }

        return self::AIRLINES[$code] ?? $code;
    }

    public static function flightLabel(?string $carrier, ?string $flightNumber): string
    {
        $airline = self::airlineName($carrier);
        $number = trim((string) $flightNumber);
        $code = strtoupper(trim((string) $carrier));

        if ($number === '') {
            return $airline;
        }

        if ($airline !== $code && $airline !== '—') {
            return "{$airline} {$code}{$number}";
        }

        return "{$code}{$number}";
    }

    /**
     * @return array{currency: string, amount: string, raw: ?string}|null
     */
    public static function parsePrice(?string $raw): ?array
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        if (preg_match('/^([A-Z]{3})([\d,.]+)$/i', trim($raw), $m)) {
            return [
                'currency' => strtoupper($m[1]),
                'amount' => number_format((float) str_replace(',', '', $m[2]), 2),
                'raw' => $raw,
            ];
        }

        return ['currency' => '', 'amount' => $raw, 'raw' => $raw];
    }

    /**
     * @return array{date: string, time: string, weekday: string}|null
     */
    public static function parseDateTime(?string $value): ?array
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        try {
            $dt = new \DateTimeImmutable($value);

            return [
                'date' => $dt->format('d M Y'),
                'time' => $dt->format('H:i'),
                'weekday' => $dt->format('D'),
            ];
        } catch (\Throwable) {
            return ['date' => $value, 'time' => '', 'weekday' => ''];
        }
    }

    public static function tripSummary(?string $origin, ?string $destination, ?string $departure, ?string $returnDate, int $adults): string
    {
        $parts = [
            self::airportCity($origin).' → '.self::airportCity($destination),
        ];

        if ($departure) {
            $parts[] = $departure;
        }

        if ($returnDate) {
            $parts[] = 'return '.$returnDate;
        }

        $parts[] = $adults.' adult'.($adults > 1 ? 's' : '');

        return implode(' · ', $parts);
    }

    /**
     * @param  array<string, mixed>  $solution
     * @return list<array{label: string, travel_time: ?string, segments: list<array<string, mixed>>}>
     */
    public static function solutionJourneys(array $solution): array
    {
        $journeys = $solution['journeys'] ?? [];
        if ($journeys === [] && ! empty($solution['segments'])) {
            $journeys = [['travel_time' => null, 'segments' => $solution['segments']]];
        }

        $out = [];
        foreach ($journeys as $index => $journey) {
            $label = match ($index) {
                0 => 'Outbound',
                1 => 'Return',
                default => 'Leg '.($index + 1),
            };
            $out[] = [
                'label' => count($journeys) > 1 ? $label : 'Itinerary',
                'travel_time' => $journey['travel_time'] ?? null,
                'segments' => $journey['segments'] ?? [],
            ];
        }

        return $out;
    }
}
