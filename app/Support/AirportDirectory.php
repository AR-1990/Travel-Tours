<?php

namespace App\Support;

class AirportDirectory
{
    /** @var list<array{code: string, name: string, city: string, country: string, type: string}>|null */
    private static ?array $all = null;

    /** @var array<string, array{code: string, name: string, city: string, country: string, type: string}>|null */
    private static ?array $byCode = null;

    /**
     * OurAirports often stores the runway municipality instead of the marketed city.
     *
     * @var array<string, string>
     */
    private const CITY_OVERRIDES = [
        'ISB' => 'Islamabad',
        'GWD' => 'Gwadar',
        'VVO' => 'Vladivostok',
        'EBL' => 'Erbil',
        'FLR' => 'Florence',
        'SKP' => 'Skopje',
        'TZL' => 'Tuzla',
        'ILO' => 'Iloilo',
        'RAR' => 'Rarotonga',
        'CJU' => 'Jeju',
        'SPN' => 'Saipan',
        'COK' => 'Kochi',
        'MME' => 'Teesside',
        'KIH' => 'Kish',
        'KSA' => 'Kosrae',
        'PNI' => 'Pohnpei',
        'TKK' => 'Chuuk',
        'INU' => 'Nauru',
        'USM' => 'Samui',
        'JTR' => 'Santorini',
        'IKU' => 'Issyk-Kul',
        'OZH' => 'Zaporizhzhia',
        'EZE' => 'Buenos Aires',
        'VCP' => 'Campinas',
        'DXN' => 'Noida',
        'KNO' => 'Medan',
        'CGK' => 'Jakarta',
        'DPS' => 'Denpasar',
        'SUB' => 'Surabaya',
        'JOG' => 'Yogyakarta',
        'SOC' => 'Solo',
        'BWN' => 'Bandar Seri Begawan',
        'ULN' => 'Ulaanbaatar',
        'TIA' => 'Tirana',
        'NAP' => 'Naples',
        'NCL' => 'Newcastle',
        'MLA' => 'Malta',
        'KEF' => 'Reykjavik',
        'KIX' => 'Osaka',
        'ITM' => 'Osaka',
        'CTS' => 'Sapporo',
        'FUK' => 'Fukuoka',
        'GMP' => 'Seoul',
        'ICN' => 'Seoul',
        'PEK' => 'Beijing',
        'PKX' => 'Beijing',
        'PVG' => 'Shanghai',
        'SHA' => 'Shanghai',
        'CAN' => 'Guangzhou',
        'SZX' => 'Shenzhen',
        'HKG' => 'Hong Kong',
        'MFM' => 'Macau',
        'TPE' => 'Taipei',
        'TSA' => 'Taipei',
        'SIN' => 'Singapore',
        'KUL' => 'Kuala Lumpur',
        'BKK' => 'Bangkok',
        'DMK' => 'Bangkok',
        'SGN' => 'Ho Chi Minh City',
        'HAN' => 'Hanoi',
        'MNL' => 'Manila',
        'CEB' => 'Cebu',
        'SYD' => 'Sydney',
        'MEL' => 'Melbourne',
        'BNE' => 'Brisbane',
        'PER' => 'Perth',
        'AKL' => 'Auckland',
        'CHC' => 'Christchurch',
        'YVR' => 'Vancouver',
        'YYZ' => 'Toronto',
        'YUL' => 'Montreal',
        'YYC' => 'Calgary',
        'LHR' => 'London',
        'LGW' => 'London',
        'STN' => 'London',
        'LTN' => 'London',
        'LCY' => 'London',
        'CDG' => 'Paris',
        'ORY' => 'Paris',
        'BVA' => 'Paris',
        'FRA' => 'Frankfurt',
        'MUC' => 'Munich',
        'AMS' => 'Amsterdam',
        'MAD' => 'Madrid',
        'BCN' => 'Barcelona',
        'FCO' => 'Rome',
        'CIA' => 'Rome',
        'MXP' => 'Milan',
        'LIN' => 'Milan',
        'IST' => 'Istanbul',
        'SAW' => 'Istanbul',
        'ESB' => 'Ankara',
        'AYT' => 'Antalya',
        'DXB' => 'Dubai',
        'DWC' => 'Dubai',
        'AUH' => 'Abu Dhabi',
        'DOH' => 'Doha',
        'BAH' => 'Bahrain',
        'MCT' => 'Muscat',
        'KWI' => 'Kuwait City',
        'RUH' => 'Riyadh',
        'JED' => 'Jeddah',
        'MED' => 'Madinah',
        'DMM' => 'Dammam',
        'CAI' => 'Cairo',
        'SSH' => 'Sharm El Sheikh',
        'HRG' => 'Hurghada',
        'ADD' => 'Addis Ababa',
        'NBO' => 'Nairobi',
        'JNB' => 'Johannesburg',
        'CPT' => 'Cape Town',
        'LOS' => 'Lagos',
        'ACC' => 'Accra',
        'DEL' => 'New Delhi',
        'BOM' => 'Mumbai',
        'BLR' => 'Bengaluru',
        'MAA' => 'Chennai',
        'HYD' => 'Hyderabad',
        'CCU' => 'Kolkata',
        'GOI' => 'Goa',
        'KHI' => 'Karachi',
        'LHE' => 'Lahore',
        'PEW' => 'Peshawar',
        'SKT' => 'Sialkot',
        'MUX' => 'Multan',
        'LYP' => 'Faisalabad',
        'UET' => 'Quetta',
        'JFK' => 'New York',
        'LGA' => 'New York',
        'EWR' => 'New York',
        'NYC' => 'New York',
        'LAX' => 'Los Angeles',
        'SFO' => 'San Francisco',
        'ORD' => 'Chicago',
        'MDW' => 'Chicago',
        'CHI' => 'Chicago',
        'MIA' => 'Miami',
        'DFW' => 'Dallas',
        'DAL' => 'Dallas',
        'ATL' => 'Atlanta',
        'SEA' => 'Seattle',
        'BOS' => 'Boston',
        'IAD' => 'Washington',
        'DCA' => 'Washington',
        'BWI' => 'Baltimore',
        'LAS' => 'Las Vegas',
        'PHX' => 'Phoenix',
        'DEN' => 'Denver',
        'IAH' => 'Houston',
        'HOU' => 'Houston',
        'MCO' => 'Orlando',
        'FLL' => 'Fort Lauderdale',
        'SAN' => 'San Diego',
        'PDX' => 'Portland',
        'MSP' => 'Minneapolis',
        'DTW' => 'Detroit',
        'PHL' => 'Philadelphia',
        'CLT' => 'Charlotte',
        'SLC' => 'Salt Lake City',
        'HNL' => 'Honolulu',
        'GRU' => 'São Paulo',
        'CGH' => 'São Paulo',
        'GIG' => 'Rio de Janeiro',
        'SDU' => 'Rio de Janeiro',
        'AEP' => 'Buenos Aires',
        'SCL' => 'Santiago',
        'LIM' => 'Lima',
        'BOG' => 'Bogotá',
        'MEX' => 'Mexico City',
        'CUN' => 'Cancún',
        'SVO' => 'Moscow',
        'DME' => 'Moscow',
        'VKO' => 'Moscow',
        'LED' => 'Saint Petersburg',
        'HEL' => 'Helsinki',
        'ARN' => 'Stockholm',
        'CPH' => 'Copenhagen',
        'OSL' => 'Oslo',
        'VIE' => 'Vienna',
        'ZRH' => 'Zurich',
        'GVA' => 'Geneva',
        'BRU' => 'Brussels',
        'DUB' => 'Dublin',
        'MAN' => 'Manchester',
        'EDI' => 'Edinburgh',
        'BHX' => 'Birmingham',
        'GLA' => 'Glasgow',
        'ATH' => 'Athens',
        'LIS' => 'Lisbon',
        'OPO' => 'Porto',
        'WAW' => 'Warsaw',
        'PRG' => 'Prague',
        'BUD' => 'Budapest',
        'OTP' => 'Bucharest',
        'SOF' => 'Sofia',
        'BEG' => 'Belgrade',
        'ZAG' => 'Zagreb',
        'LJU' => 'Ljubljana',
        'SPU' => 'Split',
        'DBV' => 'Dubrovnik',
        'TLV' => 'Tel Aviv',
        'AMM' => 'Amman',
        'BEY' => 'Beirut',
        'DAM' => 'Damascus',
        'ALA' => 'Almaty',
        'NQZ' => 'Astana',
        'TAS' => 'Tashkent',
        'FRU' => 'Bishkek',
        'GYD' => 'Baku',
        'TBS' => 'Tbilisi',
        'EVN' => 'Yerevan',
        'KTM' => 'Kathmandu',
        'DAC' => 'Dhaka',
        'CMB' => 'Colombo',
        'MLE' => 'Malé',
        'SEZ' => 'Mahé',
        'MRU' => 'Mauritius',
        'RUN' => 'Réunion',
        'PPT' => 'Tahiti',
        'NAN' => 'Nadi',
        'APW' => 'Apia',
        'NOU' => 'Nouméa',
    ];

    /**
     * Extra search terms that should strongly match a code.
     *
     * @var array<string, list<string>>
     */
    private const SEARCH_ALIASES = [
        'ISB' => ['islamabad', 'rawalpindi', 'isb'],
        'GWD' => ['gwadar'],
        'KHI' => ['karachi'],
        'LHE' => ['lahore'],
        'PEW' => ['peshawar'],
        'SKT' => ['sialkot'],
        'MUX' => ['multan'],
        'LYP' => ['faisalabad'],
        'UET' => ['quetta'],
        'DXB' => ['dubai'],
        'AUH' => ['abu dhabi'],
        'DOH' => ['doha'],
        'JFK' => ['new york', 'nyc', 'kennedy'],
        'LGA' => ['new york', 'nyc', 'laguardia'],
        'EWR' => ['new york', 'nyc', 'newark'],
        'LHR' => ['london', 'heathrow'],
        'LGW' => ['london', 'gatwick'],
        'CDG' => ['paris', 'charles de gaulle'],
        'ORY' => ['paris', 'orly'],
        'DEL' => ['delhi', 'new delhi'],
        'BOM' => ['mumbai', 'bombay'],
        'BLR' => ['bangalore', 'bengaluru'],
        'MAA' => ['chennai', 'madras'],
        'CCU' => ['kolkata', 'calcutta'],
        'CGK' => ['jakarta'],
        'DPS' => ['bali', 'denpasar'],
        'BKK' => ['bangkok'],
        'SGN' => ['ho chi minh', 'saigon'],
        'PEK' => ['beijing', 'peking'],
        'PVG' => ['shanghai'],
        'ICN' => ['seoul', 'incheon'],
        'NRT' => ['tokyo', 'narita'],
        'HND' => ['tokyo', 'haneda'],
        'SYD' => ['sydney'],
        'MEL' => ['melbourne'],
        'YYZ' => ['toronto'],
        'YVR' => ['vancouver'],
        'GRU' => ['sao paulo', 'são paulo'],
        'EZE' => ['buenos aires'],
        'MEX' => ['mexico city'],
        'CAI' => ['cairo'],
        'JNB' => ['johannesburg'],
        'CPT' => ['cape town'],
        'NBO' => ['nairobi'],
        'ADD' => ['addis ababa'],
        'RUH' => ['riyadh'],
        'JED' => ['jeddah'],
        'IST' => ['istanbul'],
        'FRA' => ['frankfurt'],
        'AMS' => ['amsterdam'],
        'MAD' => ['madrid'],
        'BCN' => ['barcelona'],
        'FCO' => ['rome', 'roma'],
        'MXP' => ['milan', 'milano'],
        'VVO' => ['vladivostok'],
        'EBL' => ['erbil', 'arbil', 'hawler'],
        'COK' => ['kochi', 'cochin'],
        'KEF' => ['reykjavik', 'iceland'],
        'KIX' => ['osaka', 'kansai'],
        'MFM' => ['macau', 'macao'],
    ];

    private const POPULAR_CODES = [
        'LHR', 'LGW', 'JFK', 'EWR', 'LAX', 'DXB', 'CDG', 'AMS', 'FRA', 'IST',
        'SIN', 'HKG', 'DEL', 'BOM', 'SYD', 'MIA', 'ORD', 'BCN', 'MAD', 'FCO',
        'KHI', 'LHE', 'ISB', 'DOH', 'AUH', 'BKK', 'NRT', 'ICN', 'YYZ', 'GRU',
        'NYC', 'LON', 'PAR', 'CHI', 'ORY', 'ATL', 'DFW', 'DEN', 'SEA',
        'PEW', 'SKT', 'MUX', 'LYP', 'UET', 'GWD', 'BLR', 'MAA', 'HYD', 'CCU',
        'CGK', 'DPS', 'KUL', 'MNL', 'PEK', 'PVG', 'HND', 'MEL', 'AKL', 'YVR',
        'MAN', 'EDI', 'DUB', 'ZRH', 'VIE', 'CPH', 'OSL', 'ARN', 'HEL', 'LIS',
        'ATH', 'CAI', 'RUH', 'JED', 'MCT', 'BAH', 'KWI', 'NBO', 'JNB', 'CPT',
        'LOS', 'ACC', 'ADD', 'EZE', 'GIG', 'MEX', 'CUN', 'BOG', 'LIM', 'SCL',
    ];

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

        foreach (self::$all as $i => $row) {
            $normalized = self::normalizeRow($row);
            self::$all[$i] = $normalized;
            $code = strtoupper((string) ($normalized['code'] ?? ''));
            if ($code !== '') {
                self::$byCode[$code] = $normalized;
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

        // Prefer embedded IATA codes: "Islamabad (ISB)" / "ISB - Islamabad"
        if (preg_match('/\b([A-Za-z]{3})\b/', $query, $codeMatch)) {
            $embedded = strtoupper($codeMatch[1]);
            self::all();
            if (isset(self::$byCode[$embedded]) && preg_match('/^[A-Za-z]{3}$/', trim($query))) {
                return [self::withLabel(self::$byCode[$embedded])];
            }
        }

        $q = self::normalizeSearchText($query);
        $isShort = mb_strlen($q) <= 3;
        $isExactCodeQuery = (bool) preg_match('/^[a-z]{3}$/', $q);
        $popularCodes = array_flip(self::POPULAR_CODES);
        $scored = [];

        // Exact IATA short-circuit: return that airport alone.
        if ($isExactCodeQuery) {
            $exact = strtoupper($q);
            self::all();
            if (isset(self::$byCode[$exact])) {
                return [self::withLabel(self::$byCode[$exact])];
            }
        }

        foreach (self::all() as $row) {
            $code = self::normalizeSearchText((string) ($row['code'] ?? ''));
            $city = self::normalizeSearchText((string) ($row['city'] ?? ''));
            $name = self::normalizeSearchText((string) ($row['name'] ?? ''));
            $country = self::normalizeSearchText((string) ($row['country'] ?? ''));
            $upperCode = strtoupper((string) ($row['code'] ?? ''));
            $hay = trim($code.' '.$city.' '.$name.' '.$country);

            $score = 0;

            if ($code === $q) {
                $score += 1000;
            } elseif (str_starts_with($code, $q)) {
                $score += 160;
            }

            if ($city === $q) {
                $score += 140;
            } elseif (str_starts_with($city, $q)) {
                $score += 100;
            } elseif (! $isShort && self::containsWordOrPhrase($city, $q)) {
                $score += 55;
            }

            if ($name === $q) {
                $score += 120;
            } elseif (str_starts_with($name, $q)) {
                $score += 95;
            } elseif (! $isShort && self::containsWordOrPhrase($name, $q)) {
                $score += 50;
            }

            foreach (self::SEARCH_ALIASES[$upperCode] ?? [] as $alias) {
                $alias = self::normalizeSearchText($alias);
                if ($alias === $q) {
                    $score += 220;
                } elseif (str_starts_with($alias, $q)) {
                    $score += 110;
                } elseif (! $isShort && self::containsWordOrPhrase($alias, $q)) {
                    $score += 80;
                }
            }

            if (! $isShort && str_contains($country, $q)) {
                $score += 8;
            }

            if ($score === 0 && ! $isShort && str_contains($hay, $q)) {
                $score += 12;
            }

            // Multi-token queries: "new york", "islamabad international"
            if ($score === 0 && ! $isShort && str_contains($q, ' ')) {
                $tokens = preg_split('/\s+/', $q) ?: [];
                $tokenHits = 0;
                foreach ($tokens as $token) {
                    if (mb_strlen($token) < 2) {
                        continue;
                    }
                    if (str_contains($hay, $token)) {
                        $tokenHits++;
                    }
                }
                if ($tokenHits > 0 && $tokenHits === count(array_filter($tokens, fn ($t) => mb_strlen($t) >= 2))) {
                    $score += 40 + ($tokenHits * 15);
                }
            }

            if ($score > 0 && (($row['type'] ?? '') === 'city')) {
                $score += 25;
            }
            if ($score > 0 && isset($popularCodes[$upperCode])) {
                $score += 40;
            }

            if ($score > 0) {
                $scored[] = ['score' => $score, 'row' => self::withLabel($row)];
            }
        }

        usort($scored, static function (array $a, array $b): int {
            $byScore = $b['score'] <=> $a['score'];
            if ($byScore !== 0) {
                return $byScore;
            }

            $hubRank = [
                'LHR' => 1, 'JFK' => 1, 'DXB' => 1, 'CDG' => 1, 'FRA' => 1, 'AMS' => 1,
                'IST' => 1, 'SIN' => 1, 'HKG' => 1, 'NRT' => 1, 'ICN' => 1, 'DEL' => 1,
                'BOM' => 1, 'KHI' => 1, 'LHE' => 1, 'ISB' => 1, 'DOH' => 1, 'AUH' => 1,
                'ORD' => 1, 'LAX' => 1, 'SYD' => 1, 'YYZ' => 1, 'GRU' => 1, 'PEK' => 1,
            ];
            $aHub = $hubRank[$a['row']['code'] ?? ''] ?? 50;
            $bHub = $hubRank[$b['row']['code'] ?? ''] ?? 50;
            $byHub = $aHub <=> $bHub;
            if ($byHub !== 0) {
                return $byHub;
            }

            return strcmp($a['row']['label'] ?? '', $b['row']['label'] ?? '');
        });

        return array_values(array_map(
            static fn (array $item) => $item['row'],
            array_slice($scored, 0, $limit)
        ));
    }

    /**
     * @return list<array{code: string, name: string, city: string, country: string, type: string, label: string}>
     */
    public static function popular(): array
    {
        $out = [];
        foreach (self::POPULAR_CODES as $code) {
            $found = self::find($code);
            if ($found !== null && ($found['city'] ?? '') !== '') {
                $out[] = $found;
            }
            if (count($out) >= 30) {
                break;
            }
        }

        return $out;
    }

    public static function count(): int
    {
        return count(self::all());
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{code: string, name: string, city: string, country: string, type: string}
     */
    private static function normalizeRow(array $row): array
    {
        $code = strtoupper(trim((string) ($row['code'] ?? '')));
        $name = trim((string) ($row['name'] ?? ''));
        $city = trim((string) ($row['city'] ?? ''));
        $country = trim((string) ($row['country'] ?? ''));
        $type = trim((string) ($row['type'] ?? 'airport'));

        if ($code !== '' && isset(self::CITY_OVERRIDES[$code])) {
            $city = self::CITY_OVERRIDES[$code];
        }

        return [
            'code' => $code,
            'name' => $name,
            'city' => $city,
            'country' => $country,
            'type' => $type !== '' ? $type : 'airport',
        ];
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

    private static function normalizeSearchText(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = strtr($value, [
            'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a', 'ã' => 'a',
            'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e',
            'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o', 'õ' => 'o',
            'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u',
            'ñ' => 'n', 'ç' => 'c', 'ş' => 's', 'ğ' => 'g', 'ı' => 'i',
            'ý' => 'y', 'ř' => 'r', 'č' => 'c', 'ć' => 'c', 'ž' => 'z', 'š' => 's',
        ]);

        return preg_replace('/\s+/', ' ', $value) ?? $value;
    }

    private static function containsWordOrPhrase(string $haystack, string $needle): bool
    {
        if ($needle === '' || $haystack === '') {
            return false;
        }

        if (str_contains($needle, ' ')) {
            return str_contains($haystack, $needle);
        }

        // Avoid "isb" matching inside "brisbane" — require token/boundary style match.
        return (bool) preg_match('/(?:^|[^a-z0-9])'.preg_quote($needle, '/').'(?:[^a-z0-9]|$)/u', $haystack);
    }
}
