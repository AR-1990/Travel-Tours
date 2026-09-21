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

        $digest = (string) ($solution['digest'] ?? '');
        if ($digest === '') {
            return ['ok' => false, 'message' => 'Missing Downtown Travel offer digest.', 'provider' => 'downtown_travel'];
        }

        $passengers = $this->mapPassengers(is_array($params['passengers'] ?? null) ? $params['passengers'] : []);
        if ($passengers === []) {
            return ['ok' => false, 'message' => 'At least one passenger is required.', 'provider' => 'downtown_travel'];
        }

        $email = (string) ($params['email'] ?? $passengers[0]['email'] ?? data_get($params, 'passengers.0.email', ''));
        $phone = (string) ($params['phone'] ?? data_get($params, 'passengers.0.phone', data_get($params, 'passengers.0.telephone', '')));
        $expected = (float) ($solution['total_amount'] ?? 0);

        $prelim = $this->client->postAir('/api/public/v2/orders/preliminary', [
            'digest' => $digest,
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

        $bookBody = [
            'digest' => $digest,
            'email' => $email !== '' ? $email : 'booking@wisetrust.com',
            'expected_agent_net_price' => $expected,
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
                'raw' => ['preliminary' => $prelim['data'] ?? null, 'book' => $book],
            ];
        }

        $data = is_array($book['data'] ?? null) ? $book['data'] : [];
        $orderId = (string) ($data['order_id'] ?? $data['id'] ?? data_get($data, 'order.id', ''));

        session([
            'downtown_travel.last_booking' => [
                'order_id' => $orderId,
                'digest' => $digest,
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
    protected function mapPassengers(array $rows): array
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
                'first_name' => (string) ($p['first'] ?? $p['given_name'] ?? ''),
                'last_name' => (string) ($p['last'] ?? $p['surname'] ?? ''),
                'middle_name' => (string) ($p['middle'] ?? ''),
                'nationality' => strtoupper((string) ($p['nationality'] ?? 'USA')),
                'sex' => $sex,
            ];
            if ($person['first_name'] === '' || $person['last_name'] === '') {
                continue;
            }

            if (in_array($type, ['CNN', 'CHD', 'CHILD'], true)) {
                $out[] = ['child' => $person];
            } elseif (in_array($type, ['INF', 'INFANT'], true)) {
                // Attach infant to previous adult when possible.
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
}
