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

    /**
     * Normalize an IATA / marketing carrier code for filters (e.g. "fz" → "FZ", "flydubai" → "FZ").
     */
    public static function airlineCode(?string $code): string
    {
        $normalized = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $code) ?? '');
        if ($normalized === '') {
            return '';
        }
        if (isset(self::AIRLINES[$normalized])) {
            return $normalized;
        }

        static $byName = null;
        if ($byName === null) {
            $byName = [];
            foreach (self::AIRLINES as $iata => $name) {
                $key = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $name) ?? '');
                if ($key !== '') {
                    $byName[$key] = $iata;
                }
            }
            // Common marketing aliases that hosts sometimes send instead of IATA.
            $byName['FLYDUBAI'] = 'FZ';
            $byName['FRONTIER'] = 'F9';
            $byName['FRONTIERAIRLINES'] = 'F9';
        }

        if (isset($byName[$normalized])) {
            return $byName[$normalized];
        }

        // Prefer short IATA-like codes; drop long unknown names so filters stay clean.
        return strlen($normalized) <= 3 ? $normalized : '';
    }

    /**
     * Convert a host price label for on-screen display when a rate exists.
     * Does not mutate stored API fares — returns host currency when no rate is configured.
     *
     * @return array{currency: string, amount: string, raw: ?string}|null
     */
    public static function forDisplayCurrency(?string $raw, ?string $displayCurrency = null): ?array
    {
        $parsed = self::parsePrice($raw);
        if ($parsed === null) {
            return null;
        }

        $to = strtoupper(trim((string) $displayCurrency));
        $from = strtoupper(trim((string) ($parsed['currency'] ?? '')));
        if ($to === '' || $from === '' || $to === $from) {
            return $parsed;
        }

        $rate = app(\App\Services\Finance\CurrencyRateService::class)->configuredRate($from, $to);
        if ($rate === null) {
            return $parsed;
        }

        $amount = round((float) str_replace(',', '', (string) $parsed['amount']) * $rate, 2);

        return [
            'currency' => $to,
            'amount' => number_format($amount, 2, '.', ''),
            'raw' => $raw,
        ];
    }

    /**
     * Outbound (first journey) stop count for filters: 0 / 1 / 2+.
     * Uses journey segments so round-trip return legs do not inflate stops.
     *
     * @param  array<string, mixed>  $solution
     */
    public static function stopBucket(array $solution): string
    {
        $segments = [];
        if (is_array($solution['journeys'][0]['segments'] ?? null) && $solution['journeys'][0]['segments'] !== []) {
            $segments = $solution['journeys'][0]['segments'];
        } elseif (is_array($solution['segments'] ?? null)) {
            $segments = $solution['segments'];
        }

        $stops = max(0, count($segments) - 1);

        return $stops === 0 ? '0' : ($stops === 1 ? '1' : '2');
    }

    /**
     * Primary display carrier (plating / validating), then marketing carriers on segments.
     *
     * @param  array<string, mixed>  $solution
     * @return list<string>
     */
    public static function airlineCodes(array $solution): array
    {
        $codes = [];
        $push = static function (?string $raw) use (&$codes): void {
            $code = self::airlineCode($raw);
            if ($code !== '' && $code !== 'XX') {
                $codes[$code] = true;
            }
        };

        $push($solution['plating_carrier'] ?? null);
        $push($solution['airline_name'] ?? null);

        $segmentLists = [];
        if (is_array($solution['segments'] ?? null)) {
            $segmentLists[] = $solution['segments'];
        }
        foreach ((array) ($solution['journeys'] ?? []) as $journey) {
            if (is_array($journey['segments'] ?? null)) {
                $segmentLists[] = $journey['segments'];
            }
        }

        foreach ($segmentLists as $segments) {
            foreach ($segments as $segment) {
                if (! is_array($segment)) {
                    continue;
                }
                $push($segment['carrier'] ?? null);
                $push($segment['marketing_carrier'] ?? null);
                $push($segment['operating_carrier'] ?? null);
                $push($segment['airline_name'] ?? null);
            }
        }

        return array_keys($codes);
    }

    /**
     * Primary airline code for card badge / default filter key.
     *
     * @param  array<string, mixed>  $solution
     */
    public static function primaryAirlineCode(array $solution): string
    {
        $codes = self::airlineCodes($solution);
        if ($codes !== []) {
            return $codes[0];
        }

        return 'XX';
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

        // Travelport: "USD900.00" · Downtown / display: "USD 900.00"
        if (preg_match('/^([A-Z]{3})\s*([\d,.]+)$/i', trim($raw), $m)) {
            return [
                'currency' => strtoupper($m[1]),
                'amount' => number_format((float) str_replace(',', '', $m[2]), 2),
                'raw' => $raw,
            ];
        }

        if (preg_match('/([A-Z]{3})\s*([\d,.]+)/i', trim($raw), $m)) {
            return [
                'currency' => strtoupper($m[1]),
                'amount' => number_format((float) str_replace(',', '', $m[2]), 2),
                'raw' => $raw,
            ];
        }

        return ['currency' => '', 'amount' => $raw, 'raw' => $raw];
    }

    /**
     * Numeric amount for sorting fares. Missing or unreadable prices sink to the end.
     */
    public static function priceSortKey(mixed $raw): float
    {
        if (is_int($raw) || is_float($raw)) {
            return (float) $raw;
        }

        $value = trim((string) $raw);
        if ($value === '') {
            return PHP_FLOAT_MAX;
        }

        $cleaned = preg_replace('/[^\d.]/', '', $value) ?? '';
        if ($cleaned === '' || $cleaned === '.') {
            return PHP_FLOAT_MAX;
        }

        return (float) $cleaned;
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

    /**
     * Human label for segment / journey cabin (Economy, Business…).
     */
    public static function cabinLabel(?string $cabin): string
    {
        $raw = trim((string) $cabin);
        if ($raw === '') {
            return '';
        }

        $key = strtolower(preg_replace('/[\s_]+/', ' ', $raw) ?? $raw);
        $map = [
            'y' => 'Economy',
            'economy' => 'Economy',
            'coach' => 'Economy',
            'w' => 'Premium Economy',
            'premium economy' => 'Premium Economy',
            'premiumeconomy' => 'Premium Economy',
            'c' => 'Business',
            'business' => 'Business',
            'j' => 'Business',
            'f' => 'First',
            'first' => 'First',
            'first class' => 'First',
        ];

        return $map[$key] ?? ucwords($key);
    }

    /**
     * Booking / RBD class letter (T, L, Y…).
     *
     * @param  array<string, mixed>  $segment
     */
    public static function bookingClass(array $segment): string
    {
        foreach (['booking_class', 'class_of_service', 'rbd'] as $key) {
            $value = trim((string) ($segment[$key] ?? ''));
            if ($value !== '') {
                return strtoupper($value);
            }
        }

        return '';
    }

    /**
     * Format Travelport ISO duration or minute counts for the results card.
     */
    public static function formatDuration(?string $raw): string
    {
        $value = trim((string) $raw);
        if ($value === '') {
            return '';
        }

        if (preg_match('/^P(?:(\d+)D)?(?:T(?:(\d+)H)?(?:(\d+)M)?(?:(\d+(?:\.\d+)?)S)?)?$/i', $value, $m)) {
            $hours = ((int) ($m[1] ?? 0)) * 24 + (int) ($m[2] ?? 0);
            $minutes = (int) ($m[3] ?? 0);

            return self::hoursMinutesLabel($hours, $minutes);
        }

        if (ctype_digit($value)) {
            $total = (int) $value;
            return self::hoursMinutesLabel(intdiv($total, 60), $total % 60);
        }

        return $value;
    }

    private static function hoursMinutesLabel(int $hours, int $minutes): string
    {
        if ($hours <= 0 && $minutes <= 0) {
            return '';
        }
        if ($hours <= 0) {
            return $minutes.'m';
        }
        if ($minutes <= 0) {
            return $hours.'h';
        }

        return $hours.'h '.$minutes.'m';
    }

    /**
     * @param  list<array{origin?: string, destination?: string, departure_date?: string}>|null  $legs
     */
    public static function tripSummary(
        ?string $origin,
        ?string $destination,
        ?string $departure,
        ?string $returnDate,
        int $adults,
        ?array $legs = null,
        int $children = 0,
        int $infants = 0
    ): string {
        if (is_array($legs) && count($legs) >= 2) {
            $cities = [];
            foreach ($legs as $index => $leg) {
                if (! is_array($leg)) {
                    continue;
                }
                if ($index === 0) {
                    $cities[] = self::airportCity($leg['origin'] ?? null);
                }
                $cities[] = self::airportCity($leg['destination'] ?? null);
            }
            $parts = [implode(' → ', array_filter($cities))];
            $dates = array_values(array_filter(array_map(
                static fn ($leg) => is_array($leg) ? ($leg['departure_date'] ?? null) : null,
                $legs
            )));
            if ($dates !== []) {
                $parts[] = implode(', ', $dates);
            }
        } else {
            $parts = [
                self::airportCity($origin).' → '.self::airportCity($destination),
            ];

            if ($departure) {
                $parts[] = $departure;
            }

            if ($returnDate) {
                $parts[] = 'return '.$returnDate;
            }
        }

        $pax = [max(1, $adults).' adult'.(max(1, $adults) > 1 ? 's' : '')];
        if ($children > 0) {
            $pax[] = $children.' child'.($children > 1 ? 'ren' : '');
        }
        if ($infants > 0) {
            $pax[] = $infants.' infant'.($infants > 1 ? 's' : '');
        }
        $parts[] = implode(', ', $pax);

        return implode(' · ', $parts);
    }

    /**
     * Format Downtown (or similar) adult/child/infant fare lines for result cards.
     *
     * @param  array<string, mixed>|null  $perPax  e.g. ['adult' => 100, 'child' => 80, 'infant' => 20]
     * @return list<string>
     */
    public static function perPaxPriceLines(?array $perPax, string $currency = 'USD'): array
    {
        if (! is_array($perPax) || $perPax === []) {
            return [];
        }

        $currency = strtoupper(trim($currency)) ?: 'USD';
        $labels = [
            'adult' => 'Adult',
            'child' => 'Child',
            'infant' => 'Infant',
        ];
        $lines = [];
        foreach ($labels as $key => $label) {
            if (! array_key_exists($key, $perPax) || $perPax[$key] === null || $perPax[$key] === '') {
                continue;
            }
            if (is_numeric($perPax[$key])) {
                $amount = (float) $perPax[$key];
            } else {
                $parsed = self::parsePrice((string) $perPax[$key]);
                if ($parsed === null) {
                    continue;
                }
                $amount = (float) $parsed['amount'];
            }
            $lines[] = $label.' '.$currency.' '.number_format($amount, 2, '.', '');
        }

        return $lines;
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
        $journeyCount = count($journeys);
        foreach ($journeys as $index => $journey) {
            if ($journeyCount > 2) {
                $label = 'Leg '.($index + 1);
            } else {
                $label = match ($index) {
                    0 => 'Outbound',
                    1 => 'Return',
                    default => 'Leg '.($index + 1),
                };
            }
            $out[] = [
                'label' => $journeyCount > 1 ? $label : 'Itinerary',
                'travel_time' => $journey['travel_time'] ?? null,
                'cabin' => $journey['cabin'] ?? ($solution['cabin'] ?? null),
                'segments' => $journey['segments'] ?? [],
            ];
        }

        return $out;
    }
}
