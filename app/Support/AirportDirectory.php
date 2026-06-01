<?php

namespace App\Support;

class AirportDirectory
{
    /** @var list<array{code: string, name: string, city: string, country: string, type: string}>|null */
    private static ?array $all = null;

    /**
     * @return list<array{code: string, name: string, city: string, country: string, type: string}>
     */
    public static function all(): array
    {
        if (self::$all !== null) {
            return self::$all;
        }

        $path = resource_path('data/airports.json');
        if (! is_file($path)) {
            self::$all = [];

            return self::$all;
        }

        $json = file_get_contents($path);
        $data = json_decode($json ?: '[]', true);
        self::$all = is_array($data) ? $data : [];

        return self::$all;
    }

    /**
     * @return array{code: string, name: string, city: string, country: string, type: string, label: string}|null
     */
    public static function find(string $code): ?array
    {
        $code = strtoupper(trim($code));
        if ($code === '') {
            return null;
        }

        foreach (self::all() as $row) {
            if (strtoupper((string) ($row['code'] ?? '')) === $code) {
                return self::withLabel($row);
            }
        }

        return [
            'code' => $code,
            'name' => $code,
            'city' => '',
            'country' => '',
            'type' => 'airport',
            'label' => $code,
        ];
    }

    /**
     * @return list<array{code: string, name: string, city: string, country: string, type: string, label: string}>
     */
    public static function search(string $query, int $limit = 20): array
    {
        $query = trim($query);
        if ($query === '') {
            return array_slice(self::popular(), 0, $limit);
        }

        $q = mb_strtolower($query);
        $scored = [];

        foreach (self::all() as $row) {
            $hay = mb_strtolower(implode(' ', [
                $row['code'] ?? '',
                $row['name'] ?? '',
                $row['city'] ?? '',
                $row['country'] ?? '',
            ]));

            $score = 0;
            if (str_starts_with(mb_strtolower((string) ($row['code'] ?? '')), $q)) {
                $score += 100;
            }
            if (str_contains($hay, $q)) {
                $score += 50;
            }
            if (str_starts_with(mb_strtolower((string) ($row['city'] ?? '')), $q)) {
                $score += 40;
            }
            if (str_starts_with(mb_strtolower((string) ($row['name'] ?? '')), $q)) {
                $score += 30;
            }

            if ($score > 0) {
                $scored[] = ['score' => $score, 'row' => self::withLabel($row)];
            }
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_values(array_map(
            fn ($item) => $item['row'],
            array_slice($scored, 0, $limit)
        ));
    }

    /**
     * @return list<array{code: string, name: string, city: string, country: string, type: string, label: string}>
     */
    public static function popular(): array
    {
        $codes = ['LHR', 'LGW', 'JFK', 'EWR', 'LAX', 'DXB', 'CDG', 'AMS', 'FRA', 'IST', 'SIN', 'HKG', 'DEL', 'BOM', 'SYD', 'MIA', 'ORD', 'BCN', 'MAD', 'FCO'];
        $out = [];
        foreach ($codes as $code) {
            $found = self::find($code);
            if ($found !== null) {
                $out[] = $found;
            }
        }

        return $out;
    }

    /**
     * @param  array<string, string>  $row
     * @return array{code: string, name: string, city: string, country: string, type: string, label: string}
     */
    private static function withLabel(array $row): array
    {
        $code = strtoupper((string) ($row['code'] ?? ''));
        $city = (string) ($row['city'] ?? '');
        $name = (string) ($row['name'] ?? '');
        $country = (string) ($row['country'] ?? '');
        $type = (string) ($row['type'] ?? 'airport');

        $label = $city !== '' && $name !== ''
            ? "{$city} — {$name} ({$code})"
            : ($city !== '' ? "{$city} ({$code})" : "{$name} ({$code})");

        return [
            'code' => $code,
            'name' => $name,
            'city' => $city,
            'country' => $country,
            'type' => $type,
            'label' => $label,
        ];
    }
}
