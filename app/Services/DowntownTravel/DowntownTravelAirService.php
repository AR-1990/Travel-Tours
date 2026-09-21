<?php

namespace App\Services\DowntownTravel;

class DowntownTravelAirService
{
    public function __construct(
        protected DowntownTravelClient $client,
        protected DowntownTravelFlightParser $parser,
    ) {}

    public function isReady(): bool
    {
        return DowntownTravelIntegrationConfig::isReadyForAir();
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function lowFareSearch(array $params): array
    {
        if (! $this->isReady()) {
            return [
                'ok' => false,
                'message' => 'Downtown Travel Air is not configured. Set credentials under Admin → Integrations.',
                'solutions' => [],
                'total_found' => 0,
                'provider' => 'downtown_travel',
            ];
        }

        $body = $this->searchBody($params);
        if ($body === null) {
            return [
                'ok' => false,
                'message' => 'Provide valid origin, destination, and departure date for Downtown Travel search.',
                'solutions' => [],
                'total_found' => 0,
                'provider' => 'downtown_travel',
            ];
        }

        $response = $this->client->postAir('/api/public/v2/search', $body);
        if (! ($response['ok'] ?? false)) {
            return [
                'ok' => false,
                'message' => $response['message'] ?? 'Downtown Travel search failed.',
                'solutions' => [],
                'total_found' => 0,
                'http_status' => $response['http_status'] ?? null,
                'response_excerpt' => $response['response_excerpt'] ?? null,
                'provider' => 'downtown_travel',
            ];
        }

        $data = is_array($response['data'] ?? null) ? $response['data'] : [];
        $parsed = $this->parser->parseSearch($data, $params);
        $parsed['http_status'] = $response['http_status'] ?? null;
        $parsed['response_excerpt'] = $response['response_excerpt'] ?? null;

        if ($parsed['ok'] ?? false) {
            session([
                'downtown_travel.last_search' => [
                    'input' => $params,
                    'request' => $body,
                    'response' => $data,
                    'solutions' => $parsed['solutions'],
                ],
            ]);
        }

        return $parsed;
    }

    /**
     * Downtown search already includes priced offers — confirm selection from cache.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function airPrice(array $params): array
    {
        $key = (string) ($params['solution_key'] ?? '');
        $solution = $this->findCachedSolution($key);
        if ($solution === null) {
            return [
                'ok' => false,
                'message' => 'Selected Downtown Travel fare expired. Please search again.',
                'solutions' => [],
                'provider' => 'downtown_travel',
            ];
        }

        session([
            'downtown_travel.last_price' => [
                'solution_key' => $key,
                'solution' => $solution,
                'adults' => max(1, (int) ($params['adults'] ?? 1)),
                'children' => max(0, (int) ($params['children'] ?? 0)),
                'infants' => max(0, (int) ($params['infants'] ?? 0)),
            ],
        ]);

        return [
            'ok' => true,
            'message' => 'Fare confirmed with Downtown Travel.',
            'solutions' => [$solution],
            'provider' => 'downtown_travel',
        ];
    }

    public function hasStoredPricingContext(): bool
    {
        $search = session('downtown_travel.last_search');
        $price = session('downtown_travel.last_price');

        return (is_array($search) && ! empty($search['solutions'])) || is_array($price);
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function book(array $params): array
    {
        $price = session('downtown_travel.last_price');
        $solution = is_array($price['solution'] ?? null) ? $price['solution'] : null;
        if ($solution === null) {
            return ['ok' => false, 'message' => 'Price a Downtown Travel fare before booking.', 'provider' => 'downtown_travel'];
        }

        $searchDigest = (string) ($solution['digest'] ?? '');
        if ($searchDigest === '') {
            return ['ok' => false, 'message' => 'Missing Downtown Travel offer digest.', 'provider' => 'downtown_travel'];
        }

        $passengers = $this->mapPassengers(
            is_array($params['passengers'] ?? null) ? $params['passengers'] : [],
            (bool) ($solution['travel_document_required'] ?? false)
        );
        if ($passengers === []) {
            return ['ok' => false, 'message' => 'At least one passenger is required.', 'provider' => 'downtown_travel'];
        }

        $email = trim((string) ($params['email'] ?? data_get($params, 'passengers.0.email', '')));
        $phone = $this->normalizePhone((string) ($params['phone'] ?? data_get($params, 'passengers.0.phone', '')));

        // Preliminary renews pricing and returns a fresh digest that booking must use.
        $prelim = $this->client->postAir('/api/public/v2/orders/preliminary', [
            'digest' => $searchDigest,
            'run_upsell' => false,
        ]);
        if (! ($prelim['ok'] ?? false)) {
            return [
                'ok' => false,
                'message' => $prelim['message'] ?? 'Downtown Travel preliminary booking failed.',
                'provider' => 'downtown_travel',
                'raw' => $prelim,
            ];
        }

        $prelimData = is_array($prelim['data'] ?? null) ? $prelim['data'] : [];
        $bookOffer = is_array($prelimData['offers'][0] ?? null) ? $prelimData['offers'][0] : null;
        $bookDigest = trim((string) ($bookOffer['digest'] ?? $searchDigest));
        if ($bookDigest === '') {
            return [
                'ok' => false,
                'message' => 'Downtown Travel preliminary did not return a bookable digest. Search and price again.',
                'provider' => 'downtown_travel',
                'raw' => $prelim,
            ];
        }

        $agentNet = (float) (
            data_get($bookOffer, 'price.pricing_options.agent_cash.agent_net_total')
            ?? data_get($bookOffer, 'price.pricing_options.passenger_cc.agent_net_total')
            ?? $solution['agent_net_total']
            ?? $solution['total_amount']
            ?? 0
        );

        $docsRequired = (bool) (
            data_get($bookOffer, 'travel_document_required')
            ?? $solution['travel_document_required']
            ?? false
        );
        if ($docsRequired) {
            foreach ($passengers as $row) {
                $person = $row['adult'] ?? $row['child'] ?? $row['infant'] ?? null;
                if (! is_array($person) || empty($person['travel_document']['number'] ?? null)) {
                    return [
                        'ok' => false,
                        'message' => 'This fare requires a passport or travel document for every passenger.',
                        'provider' => 'downtown_travel',
                    ];
                }
            }
        }

        $bookBody = [
            'digest' => $bookDigest,
            'email' => $email !== '' ? $email : 'booking@wisetrust.com',
            'expected_agent_net_price' => round($agentNet, 2),
            'passengers' => $passengers,
            'payment_option' => 'agent_cash',
            'phone' => $phone !== '' ? $phone : '+10000000000',
        ];

        $book = $this->client->postAir('/api/public/v2/orders/booking', $bookBody);
        if (! ($book['ok'] ?? false)) {
            return [
                'ok' => false,
                'message' => $book['message'] ?? 'Downtown Travel booking failed.',
                'provider' => 'downtown_travel',
                'raw' => ['preliminary' => $prelimData, 'book' => $book, 'request' => $bookBody],
            ];
        }

        $data = is_array($book['data'] ?? null) ? $book['data'] : [];
        $orderId = (string) ($data['order_id'] ?? $data['id'] ?? data_get($data, 'order.id', ''));

        session([
            'downtown_travel.last_booking' => [
                'order_id' => $orderId,
                'digest' => $bookDigest,
                'response' => $data,
                'solution' => $solution,
            ],
        ]);

        return [
            'ok' => true,
            'message' => $orderId !== '' ? 'Downtown Travel booking created (order '.$orderId.').' : 'Downtown Travel booking created.',
            'provider' => 'downtown_travel',
            'universal_locator' => $orderId,
            'provider_locator' => $orderId,
            'air_locator' => $orderId,
            'raw' => $data,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function findCachedSolution(string $key): ?array
    {
        $solutions = session('downtown_travel.last_search.solutions', []);
        if (! is_array($solutions)) {
            return null;
        }
        foreach ($solutions as $solution) {
            if (is_array($solution) && (string) ($solution['key'] ?? '') === $key) {
                return $solution;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>|null
     */
    protected function searchBody(array $params): ?array
    {
        $origin = strtoupper((string) ($params['origin'] ?? ''));
        $destination = strtoupper((string) ($params['destination'] ?? ''));
        $departure = (string) ($params['departure_date'] ?? '');
        $returnDate = (string) ($params['return_date'] ?? '');
        $adults = max(1, (int) ($params['adults'] ?? $params['adult'] ?? 1));
        $children = max(0, (int) ($params['children'] ?? $params['child'] ?? 0));
        $infants = max(0, (int) ($params['infants'] ?? $params['inf'] ?? 0));

        if (($params['trip_type'] ?? '') === 'multicity' && is_array($params['legs'] ?? null) && count($params['legs']) >= 2) {
            $flights = [];
            foreach (array_values($params['legs']) as $leg) {
                if (! is_array($leg)) {
                    continue;
                }
                $from = strtoupper((string) ($leg['origin'] ?? ''));
                $to = strtoupper((string) ($leg['destination'] ?? ''));
                $date = (string) ($leg['departure_date'] ?? '');
                if ($from === '' || $to === '' || $date === '') {
                    continue;
                }
                $flights[] = ['date' => $date, 'from' => $from, 'to' => $to];
            }
            if (count($flights) < 2) {
                return null;
            }

            return [
                'cabin' => ['economy'],
                'carriers' => 'any',
                'exclude_basic_economy' => true,
                'fare_types' => 'any',
                'passengers' => [
                    'adults' => $adults,
                    'children' => $children,
                    'infants' => $infants,
                ],
                'flights' => $flights,
                'sources' => ['amadeus'],
            ];
        }

        if ($origin === '' || $destination === '' || $departure === '') {
            return null;
        }

        $flights = [['date' => $departure, 'from' => $origin, 'to' => $destination]];
        if ($returnDate !== '' && ($params['trip_type'] ?? '') === 'roundtrip') {
            $flights[] = ['date' => $returnDate, 'from' => $destination, 'to' => $origin];
        }

        return [
            'cabin' => ['economy'],
            'carriers' => 'any',
            'exclude_basic_economy' => true,
            'fare_types' => 'any',
            'passengers' => [
                'adults' => $adults,
                'children' => $children,
                'infants' => $infants,
            ],
            'flights' => $flights,
            'sources' => ['amadeus'],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    protected function mapPassengers(array $rows, bool $travelDocumentRequired = false): array
    {
        $out = [];
        foreach ($rows as $p) {
            if (! is_array($p)) {
                continue;
            }
            $type = strtoupper((string) ($p['type'] ?? 'ADT'));
            $sex = strtoupper(substr((string) ($p['gender'] ?? 'M'), 0, 1)) === 'F' ? 'female' : 'male';
            $person = [
                'birthday' => (string) ($p['dob'] ?? $p['birthdate'] ?? '1990-01-01'),
                'first_name' => trim((string) ($p['first'] ?? $p['given_name'] ?? '')),
                'last_name' => trim((string) ($p['last'] ?? $p['surname'] ?? '')),
                'nationality' => $this->normalizeNationality((string) ($p['nationality'] ?? 'USA')),
                'sex' => $sex,
            ];
            $middle = trim((string) ($p['middle'] ?? ''));
            if ($middle !== '') {
                $person['middle_name'] = $middle;
            }

            $passportNumber = trim((string) ($p['passport_number'] ?? data_get($p, 'travel_document.number', '')));
            $passportExpire = trim((string) ($p['passport_expire'] ?? data_get($p, 'travel_document.expires_at', '')));
            if ($passportNumber !== '' && $passportExpire !== '') {
                $person['travel_document'] = [
                    'type' => 'passport',
                    'number' => $passportNumber,
                    'expires_at' => $passportExpire,
                    'issued_by_country' => $this->normalizeNationality((string) (
                        $p['passport_country'] ?? $p['nationality'] ?? $person['nationality']
                    )),
                ];
            } elseif ($travelDocumentRequired) {
                // Leave without document — caller validates and returns a clear error.
            }

            if ($person['first_name'] === '' || $person['last_name'] === '') {
                continue;
            }

            if (in_array($type, ['CNN', 'CHD', 'CHILD'], true)) {
                $out[] = ['child' => $person];
            } elseif (in_array($type, ['INF', 'INFANT'], true)) {
                if ($out !== [] && isset($out[count($out) - 1]['adult']) && ! isset($out[count($out) - 1]['infant'])) {
                    $out[count($out) - 1]['infant'] = $person;
                } else {
                    $out[] = ['adult' => $person, 'infant' => $person];
                }
            } else {
                $out[] = ['adult' => $person];
            }
        }

        return $out;
    }

    protected function normalizePhone(string $phone): string
    {
        $phone = trim($phone);
        if ($phone === '') {
            return '';
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if ($digits === '') {
            return $phone;
        }

        if (str_starts_with(trim($phone), '+')) {
            return '+'.$digits;
        }

        // US-style numbers without country code.
        if (strlen($digits) === 10) {
            return '+1'.$digits;
        }

        return '+'.$digits;
    }

    /**
     * Downtown Person.nationality accepts ISO country codes; map common alpha-3 → alpha-2.
     */
    protected function normalizeNationality(string $value): string
    {
        $value = strtoupper(trim($value));
        if ($value === '') {
            return 'US';
        }

        static $map = [
            'USA' => 'US',
            'ARE' => 'AE',
            'GBR' => 'GB',
            'IRN' => 'IR',
            'PAK' => 'PK',
            'IND' => 'IN',
            'CAN' => 'CA',
            'AUS' => 'AU',
            'SAU' => 'SA',
            'QAT' => 'QA',
            'BHR' => 'BH',
            'KWT' => 'KW',
            'OMN' => 'OM',
            'EGY' => 'EG',
            'TUR' => 'TR',
            'FRA' => 'FR',
            'DEU' => 'DE',
        ];

        if (isset($map[$value])) {
            return $map[$value];
        }

        return strlen($value) > 2 ? substr($value, 0, 2) : $value;
    }
}
