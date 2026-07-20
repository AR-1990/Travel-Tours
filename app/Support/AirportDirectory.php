<?php

namespace App\Support;

class AirportDirectory
{
    /** @var list<array{code: string, name: string, city: string, country: string, type: string}>|null */
    private static ?array $all = null;

    /** @var array<string, array{code: string, name: string, city: string, country: string, type: string}>|null */
    private static ?array $byCode = null;

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
            self::$byCode = [];

            return self::$all;
        }

        $json = file_get_contents($path);
        $data = json_decode($json ?: '[]', true);
        self::$all = is_array($data) ? $data : [];
        self::$byCode = [];
        foreach (self::$all as $row) {
            $code = strtoupper((string) ($row['code'] ?? ''));
            if ($code !== '') {
                self::$byCode[$code] = $row;
            }
        }

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

        self::all();
        if (isset(self::$byCode[$code])) {
            return self::withLabel(self::$byCode[$code]);
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
        $popularCodes = array_flip([
            'LHR', 'LGW', 'JFK', 'EWR', 'LAX', 'DXB', 'CDG', 'AMS', 'FRA', 'IST',
            'SIN', 'HKG', 'DEL', 'BOM', 'SYD', 'MIA', 'ORD', 'BCN', 'MAD', 'FCO',
            'KHI', 'LHE', 'ISB', 'DOH', 'AUH', 'BKK', 'NRT', 'ICN', 'YYZ', 'GRU',
            'NYC', 'LON', 'PAR', 'CHI', 'ORY', 'EWR', 'ATL', 'DFW', 'DEN', 'SEA',
        ]);
        $scored = [];

        foreach (self::all() as $row) {
            $code = mb_strtolower((string) ($row['code'] ?? ''));
            $city = mb_strtolower((string) ($row['city'] ?? ''));
            $name = mb_strtolower((string) ($row['name'] ?? ''));
            $country = mb_strtolower((string) ($row['country'] ?? ''));
            $hay = $code.' '.$city.' '.$name.' '.$country;
            $upperCode = strtoupper((string) ($row['code'] ?? ''));

            $score = 0;
            if ($code === $q) {
                $score += 200;
            } elseif (str_starts_with($code, $q)) {
                $score += 120;
            }
            if ($city === $q) {
                $score += 110;
            } elseif (str_starts_with($city, $q)) {
                $score += 80;
            } elseif (str_contains($city, $q)) {
                $score += 35;
            }
            if (str_starts_with($name, $q)) {
                $score += 50;
            } elseif (str_contains($name, $q)) {
                $score += 20;
            }
            if (str_contains($country, $q)) {
                $score += 8;
            }
            if ($score === 0 && str_contains($hay, $q)) {
                $score += 12;
            }

            if ($score > 0 && (($row['type'] ?? '') === 'city')) {
                $score += 25;
            }
            if ($score > 0 && isset($popularCodes[$upperCode])) {
                $score += 60;
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
        $codes = [
            'LHR', 'LGW', 'JFK', 'EWR', 'LAX', 'DXB', 'CDG', 'AMS', 'FRA', 'IST',
            'SIN', 'HKG', 'DEL', 'BOM', 'SYD', 'MIA', 'ORD', 'BCN', 'MAD', 'FCO',
            'KHI', 'LHE', 'ISB', 'DOH', 'AUH', 'BKK', 'NRT', 'ICN', 'YYZ', 'GRU',
            'NYC', 'LON', 'PAR', 'CHI',
        ];
        $out = [];
        foreach ($codes as $code) {
            $found = self::find($code);
            if ($found !== null && ($found['city'] ?? '') !== '') {
                $out[] = $found;
            }
        }

        return $out;
    }

    public static function count(): int
    {
        return count(self::all());
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

        if ($type === 'city') {
            $label = $city !== '' ? "{$city} — All airports ({$code})" : "{$name} ({$code})";
        } elseif ($city !== '' && $name !== '' && strcasecmp($city, $name) !== 0) {
            $label = "{$city} — {$name} ({$code})";
        } elseif ($city !== '') {
            $label = "{$city} ({$code})";
        } else {
            $label = "{$name} ({$code})";
        }

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
