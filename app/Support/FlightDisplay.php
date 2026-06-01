<?php

namespace App\Support;

class FlightDisplay
{
    /** @var array<string, string> */
    private const AIRPORTS = [
        'LHR' => 'London Heathrow',
        'LGW' => 'London Gatwick',
        'STN' => 'London Stansted',
        'LON' => 'London (all)',
        'JFK' => 'New York JFK',
        'EWR' => 'Newark',
        'NYC' => 'New York (all)',
        'DXB' => 'Dubai',
        'CDG' => 'Paris Charles de Gaulle',
        'AMS' => 'Amsterdam',
        'FRA' => 'Frankfurt',
        'IST' => 'Istanbul',
        'SIN' => 'Singapore',
        'HKG' => 'Hong Kong',
        'DEL' => 'Delhi',
        'BOM' => 'Mumbai',
        'SYD' => 'Sydney',
        'LAX' => 'Los Angeles',
        'ORD' => 'Chicago O\'Hare',
        'MIA' => 'Miami',
    ];

    public static function airportLabel(?string $code): string
    {
        $found = AirportDirectory::find((string) $code);

        return $found['label'] ?? self::airportShort($code);
    }

    public static function airportShort(?string $code): string
    {
        $code = strtoupper(trim((string) $code));

        return $code !== '' ? $code : '—';
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
            self::airportShort($origin).' → '.self::airportShort($destination),
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
}
