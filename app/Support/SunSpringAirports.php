<?php

namespace App\Support;

/**
 * Airports / sample routes supported by the SunSpring (Sepehran-scoped) sandbox.
 * Travelport keeps the full world directory; SunSpring pickers use this allow-list.
 */
class SunSpringAirports
{
    /**
     * Sepehran / SunSpring network IATA codes (domestic + common regional).
     *
     * @var list<string>
     */
    public const CODES = [
        'THR', // Tehran Mehrabad
        'IKA', // Tehran Imam Khomeini
        'MHD', // Mashhad
        'SYZ', // Shiraz
        'AWZ', // Ahvaz
        'TBZ', // Tabriz
        'IFN', // Isfahan
        'KIH', // Kish
        'BND', // Bandar Abbas
        'GSM', // Qeshm
        'ABD', // Abadan
        'RAS', // Rasht
        'KSH', // Kermanshah
        'ZBR', // Chabahar
        'BUZ', // Bushehr
        'OMH', // Urmia
        'NJF', // Najaf
        'BGW', // Baghdad
        'MCT', // Muscat
        'KWI', // Kuwait
        'DXB', // Dubai
    ];

    /**
     * Popular SunSpring route chips for the search UI.
     *
     * @var list<array{0: string, 1: string}>
     */
    public const POPULAR_ROUTES = [
        ['THR', 'MHD'],
        ['MHD', 'THR'],
        ['THR', 'SYZ'],
        ['THR', 'AWZ'],
        ['THR', 'TBZ'],
        ['IKA', 'MHD'],
        ['MHD', 'DXB'],
        ['SYZ', 'MHD'],
    ];

    public static function isAllowed(string $code): bool
    {
        $code = strtoupper(trim($code));

        return $code !== '' && in_array($code, self::CODES, true);
    }

    /**
     * @return list<array{code: string, name: string, city: string, country: string, type: string, label: string}>
     */
    public static function all(): array
    {
        $out = [];
        foreach (self::CODES as $code) {
            $row = AirportDirectory::find($code);
            if ($row !== null) {
                $out[] = $row;

                continue;
            }
            $out[] = [
                'code' => $code,
                'name' => $code.' Airport',
                'city' => $code,
                'country' => 'IR',
                'type' => 'airport',
                'label' => $code.' ('.$code.')',
            ];
        }

        return $out;
    }

    /**
     * @return list<array{code: string, name: string, city: string, country: string, type: string, label: string}>
     */
    public static function search(string $q, int $limit = 15): array
    {
        $q = trim($q);
        $all = self::all();
        if ($q === '') {
            return array_slice($all, 0, $limit);
        }

        $needle = mb_strtolower($q);
        $scored = [];
        foreach ($all as $row) {
            $hay = mb_strtolower(implode(' ', [
                $row['code'] ?? '',
                $row['city'] ?? '',
                $row['name'] ?? '',
                $row['label'] ?? '',
                $row['country'] ?? '',
            ]));
            if (! str_contains($hay, $needle) && ! str_starts_with(mb_strtolower((string) ($row['code'] ?? '')), $needle)) {
                continue;
            }
            $score = 0;
            if (strcasecmp((string) ($row['code'] ?? ''), $q) === 0) {
                $score += 100;
            } elseif (str_starts_with(mb_strtolower((string) ($row['code'] ?? '')), $needle)) {
                $score += 80;
            }
            if (str_contains(mb_strtolower((string) ($row['city'] ?? '')), $needle)) {
                $score += 40;
            }
            $scored[] = ['score' => $score, 'row' => $row];
        }

        usort($scored, static fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        return array_map(static fn (array $x) => $x['row'], array_slice($scored, 0, $limit));
    }

    public static function defaultOrigin(): string
    {
        return 'THR';
    }

    public static function defaultDestination(): string
    {
        return 'MHD';
    }
}
