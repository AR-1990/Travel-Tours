<?php

namespace App\Services\SunSpring;

use Illuminate\Support\Facades\Cache;

class SunSpringAirService
{
    public function __construct(
        protected SunSpringClient $client,
        protected SunSpringFlightParser $parser,
    ) {}

    public function isReady(): bool
    {
        return SunSpringIntegrationConfig::isReadyForAir();
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
                'message' => 'SunSpring is not configured. Set credentials under Admin → Integrations → SunSpring.',
                'solutions' => [],
                'total_found' => 0,
                'provider' => 'sunspring',
            ];
        }

        $body = $this->searchBody($params);
        $origin = strtoupper((string) data_get($body, 'outbound.departure', ''));
        $destination = strtoupper((string) data_get($body, 'outbound.arrival', ''));
        if ($origin === '' || $destination === ''
            || ! \App\Support\SunSpringAirports::isAllowed($origin)
            || ! \App\Support\SunSpringAirports::isAllowed($destination)) {
            return [
                'ok' => false,
                'message' => 'SunSpring only supports Sepehran network airports (e.g. THR, MHD, SYZ). Switch to Travelport for worldwide search.',
                'solutions' => [],
                'total_found' => 0,
                'provider' => 'sunspring',
            ];
        }

        $response = $this->client->post('/api/v2/flight/FlightSearch', $body);

        if (! ($response['ok'] ?? false)) {
            // API may return HTTP 200 with status=error inside body — client marks ok false.
            $data = is_array($response['data'] ?? null) ? $response['data'] : [];
            if ($data !== []) {
                $parsed = $this->parser->parseSearch($data, $params);
                $parsed['http_status'] = $response['http_status'] ?? null;
                $parsed['response_excerpt'] = $response['response_excerpt'] ?? null;
                $parsed['provider'] = 'sunspring';
                if ($parsed['ok'] ?? false) {
                    $this->rememberSearchContext($params, $data, $parsed);

                    return $parsed;
                }
            }

            return [
                'ok' => false,
                'message' => $response['message'] ?? 'SunSpring search failed.',
                'solutions' => [],
                'total_found' => 0,
                'http_status' => $response['http_status'] ?? null,
                'response_excerpt' => $response['response_excerpt'] ?? null,
                'provider' => 'sunspring',
            ];
        }

        $data = is_array($response['data'] ?? null) ? $response['data'] : [];
        // FlightSearch returns outbound/return at top level (not always under data).
        if (! isset($data['outbound']) && isset($data['status'])) {
            // already top-level shaped
        } elseif (! isset($data['outbound']) && is_array($response['data'] ?? null) === false) {
            $data = [];
        }

        // Client stores decoded JSON in data — for success responses the whole body is there.
        if (! isset($data['outbound']) && isset($data['status'])) {
            // ok
        }

        $parsed = $this->parser->parseSearch($data, $params);
        $parsed['http_status'] = $response['http_status'] ?? null;
        $parsed['response_excerpt'] = $response['response_excerpt'] ?? null;
        $parsed['provider'] = 'sunspring';

        if ($parsed['ok'] ?? false) {
            $this->rememberSearchContext($params, $data, $parsed);
        }

        return $parsed;
    }

    /**
     * Price a selected solution_key (outbound ref, or outbound|return).
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function airPrice(array $params): array
    {
        $solutionKey = (string) ($params['solution_key'] ?? '');
        [$outboundRef, $returnRef] = $this->splitSolutionKey($solutionKey);
        $adults = max(1, (int) ($params['adults'] ?? 1));
        $children = max(0, (int) ($params['children'] ?? 0));
        $infants = max(0, (int) ($params['infants'] ?? $params['inf'] ?? 0));

        if ($outboundRef === '') {
            return ['ok' => false, 'message' => 'Missing flight selection (ref_number).', 'solutions' => [], 'provider' => 'sunspring'];
        }

        $body = [
            'outbound' => ['ref_number' => $outboundRef],
            'adult' => $adults,
            'child' => $children,
            'inf' => $infants,
        ];
        if ($returnRef !== '') {
            $body['return'] = ['ref_number' => $returnRef];
        }

        $response = $this->client->post('/api/v2/flight/AirPrice', $body);
        $data = is_array($response['data'] ?? null) ? $response['data'] : [];

        if (! ($response['ok'] ?? false) && $data === []) {
            return [
                'ok' => false,
                'message' => $response['message'] ?? 'SunSpring air price failed.',
                'solutions' => [],
                'http_status' => $response['http_status'] ?? null,
                'response_excerpt' => $response['response_excerpt'] ?? null,
                'provider' => 'sunspring',
            ];
        }

        $selected = $this->findCachedSolution($solutionKey) ?? [
            'key' => $solutionKey,
            'outbound_ref' => $outboundRef,
            'return_ref' => $returnRef !== '' ? $returnRef : null,
            'provider' => 'sunspring',
            'segments' => [],
            'journeys' => [],
        ];

        $parsed = $this->parser->parsePrice($data, $selected, $adults);
        $parsed['http_status'] = $response['http_status'] ?? null;
        $parsed['response_excerpt'] = $response['response_excerpt'] ?? null;

        if ($parsed['ok'] ?? false) {
            session([
                'sunspring.last_price' => [
                    'solution_key' => $solutionKey,
                    'request' => $body,
                    'response' => $data,
                    'solution' => $parsed['solutions'][0] ?? $selected,
                    'adults' => $adults,
                ],
            ]);
        }

        return $parsed;
    }

    /**
     * Book → Confirm → (optional) ticket later.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function book(array $params): array
    {
        $price = session('sunspring.last_price');
        if (! is_array($price) || empty($price['solution'])) {
            return ['ok' => false, 'message' => 'Price a SunSpring fare before booking.', 'provider' => 'sunspring'];
        }

        $solution = $price['solution'];
        $outboundRef = (string) ($solution['outbound_ref'] ?? $solution['key'] ?? '');
        $returnRef = (string) ($solution['return_ref'] ?? '');
        if (str_contains($outboundRef, '|')) {
            [$outboundRef, $returnRef] = $this->splitSolutionKey($outboundRef);
        }

        $rawOutbound = data_get($solution, 'raw_flight.outbound', data_get($solution, 'raw_flight'));
        if (! is_array($rawOutbound)) {
            $rawOutbound = [];
        }

        $depCode = (string) data_get($rawOutbound, 'departure.location_code', $solution['segments'][0]['origin'] ?? '');
        $arrCode = (string) data_get($rawOutbound, 'arrival.location_code', $solution['segments'][0]['destination'] ?? '');
        $depDate = (string) data_get($rawOutbound, 'departure.date', '');
        $depTime = (string) data_get($rawOutbound, 'departure.time', '');
        $depDateTime = trim($depDate.' '.substr($depTime, 0, 8));

        $passengers = $params['passengers'] ?? [];
        if (! is_array($passengers) || $passengers === []) {
            return ['ok' => false, 'message' => 'At least one passenger is required.', 'provider' => 'sunspring'];
        }

        $first = $passengers[0];
        $phone = (string) ($first['phone'] ?? $first['telephone'] ?? '');
        $mobile = preg_replace('/\D+/', '', $phone) ?: '9151231231';
        $countryCode = (string) ($params['country_code'] ?? '+96');

        $totalNumeric = $this->parseMoneyAmount((string) ($solution['total_price'] ?? '0'));
        $currency = (string) ($solution['currency_code'] ?? 'USD');

        $body = [
            'outbound' => [
                'ref_number' => $outboundRef,
                'departure' => $depCode,
                'arrival' => $arrCode,
                'date' => $depDateTime !== '' ? $depDateTime : $depDate,
            ],
            'receiver' => [
                'mobile' => $mobile,
                'country_code' => $countryCode,
            ],
            'price' => [
                'currency_code' => $currency,
                'total' => $totalNumeric,
            ],
            'passengers' => array_map(fn (array $p) => $this->mapPassenger($p), $passengers),
        ];

        if ($returnRef !== '') {
            $rawReturn = data_get($solution, 'raw_flight.return', []);
            $body['return'] = [
                'ref_number' => $returnRef,
                'departure' => (string) data_get($rawReturn, 'departure.location_code', ''),
                'arrival' => (string) data_get($rawReturn, 'arrival.location_code', ''),
                'date' => trim(
                    (string) data_get($rawReturn, 'departure.date', '').' '.
                    substr((string) data_get($rawReturn, 'departure.time', ''), 0, 8)
                ),
            ];
        }

        $bookResponse = $this->client->post('/api/v2/flight/Book', $body);
        $bookData = is_array($bookResponse['data'] ?? null) ? $bookResponse['data'] : [];

        if (! ($bookResponse['ok'] ?? false) && ! $this->parser->errIsClear($bookData['err'] ?? 1)) {
            return [
                'ok' => false,
                'message' => $bookResponse['message'] ?? $this->parser->errorMessage($bookData) ?? 'SunSpring book failed.',
                'http_status' => $bookResponse['http_status'] ?? null,
                'response_excerpt' => $bookResponse['response_excerpt'] ?? null,
                'raw' => $bookData,
                'provider' => 'sunspring',
            ];
        }

        // Treat HTTP success body even when client flagged err quirks.
        if (strtolower((string) ($bookData['status'] ?? '')) !== 'success' && ! isset($bookData['refrence_id']) && ! isset($bookData['reference_id'])) {
            return [
                'ok' => false,
                'message' => $this->parser->errorMessage($bookData) ?? $bookResponse['message'] ?? 'SunSpring book failed.',
                'raw' => $bookData,
                'provider' => 'sunspring',
            ];
        }

        $referenceId = (int) ($bookData['refrence_id'] ?? $bookData['reference_id'] ?? 0);

        $confirm = $this->client->post('/api/v2/flight/Confirm', [
            'refrence_id' => $referenceId,
        ]);
        $confirmData = is_array($confirm['data'] ?? null) ? $confirm['data'] : [];

        $booking = [
            'ok' => true,
            'message' => 'Booking created'.(($confirm['ok'] ?? false) ? ' and confirm requested.' : ' (confirm pending).'),
            'provider' => 'sunspring',
            'universal_locator' => (string) $referenceId,
            'air_reservation_locator' => (string) $referenceId,
            'provider_locator' => (string) $referenceId,
            'reference_id' => $referenceId,
            'book_raw' => $bookData,
            'confirm_raw' => $confirmData,
            'payment' => $bookData['payment'] ?? null,
            'flights' => $bookData['flights'] ?? null,
            'solution' => $solution,
            'passengers' => $passengers,
        ];

        session(['sunspring.last_booking' => $booking]);

        return $booking;
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function issueTicket(array $params = []): array
    {
        $reference = (int) ($params['reference'] ?? $params['reference_id'] ?? data_get(session('sunspring.last_booking'), 'reference_id', 0));
        if ($reference <= 0) {
            return ['ok' => false, 'message' => 'Missing SunSpring booking reference for ticketing.', 'provider' => 'sunspring'];
        }

        $response = $this->client->post('/api/v2/flight/AirDemandTicket', [
            'reference' => $reference,
        ]);
        $data = is_array($response['data'] ?? null) ? $response['data'] : [];

        if (! ($response['ok'] ?? false) && strtolower((string) ($data['status'] ?? '')) !== 'success') {
            return [
                'ok' => false,
                'message' => $response['message'] ?? $this->parser->errorMessage($data) ?? 'Ticketing failed.',
                'raw' => $data,
                'provider' => 'sunspring',
            ];
        }

        $tickets = is_array($data['tickets'] ?? null) ? $data['tickets'] : [];
        $numbers = [];
        foreach ($tickets as $t) {
            if (is_array($t) && ! empty($t['ticket_number'])) {
                $numbers[] = (string) $t['ticket_number'];
            }
        }

        return [
            'ok' => true,
            'message' => $numbers === [] ? 'Ticket request accepted.' : 'Ticketed: '.implode(', ', $numbers),
            'ticket_numbers' => $numbers,
            'tickets' => $tickets,
            'flights' => $data['flights'] ?? [],
            'reference_id' => $data['refrence_id'] ?? $reference,
            'raw' => $data,
            'provider' => 'sunspring',
        ];
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function ticketInfo(array $params): array
    {
        $reference = (string) ($params['reference'] ?? $params['reference_id'] ?? '');
        if ($reference === '') {
            return ['ok' => false, 'message' => 'reference is required.', 'provider' => 'sunspring'];
        }

        return $this->client->post('/api/v2/flight/TicketInfo', ['reference' => $reference]);
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function cancel(array $params): array
    {
        $body = array_filter([
            'reference' => (string) ($params['reference'] ?? ''),
            'voucher' => $params['voucher'] ?? [],
            'tickets' => $params['tickets'] ?? [],
            'type' => (string) ($params['type'] ?? 'General'),
        ], static fn ($v) => $v !== '' && $v !== null);

        return $this->client->post('/api/v2/flight/Cancel', $body);
    }

    public function myCredit(): array
    {
        return $this->client->myCredit();
    }

    public function hasStoredPricingContext(): bool
    {
        $search = session('sunspring.last_search');
        $price = session('sunspring.last_price');

        return (is_array($search) && ! empty($search['solutions'])) || is_array($price);
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    protected function searchBody(array $params): array
    {
        $origin = strtoupper((string) ($params['origin'] ?? ''));
        $destination = strtoupper((string) ($params['destination'] ?? ''));
        $departure = (string) ($params['departure_date'] ?? '');
        $returnDate = (string) ($params['return_date'] ?? '');
        $adults = max(1, (int) ($params['adults'] ?? $params['adult'] ?? 1));
        $children = max(0, (int) ($params['children'] ?? $params['child'] ?? 0));
        $infants = max(0, (int) ($params['infants'] ?? $params['inf'] ?? 0));

        // Multi-city: use first and last legs as a simple approximation for SunSpring (single-airline API).
        if (($params['trip_type'] ?? '') === 'multicity' && is_array($params['legs'] ?? null) && count($params['legs']) >= 2) {
            $legs = array_values($params['legs']);
            $first = $legs[0];
            $last = $legs[array_key_last($legs)];
            $origin = strtoupper((string) ($first['origin'] ?? $origin));
            $destination = strtoupper((string) ($last['destination'] ?? $destination));
            $departure = (string) ($first['departure_date'] ?? $departure);
            $returnDate = '';
        }

        $body = [
            'outbound' => [
                'departure' => $origin,
                'arrival' => $destination,
                'date' => $departure,
            ],
            'adult' => (string) $adults,
            'child' => (string) $children,
            'inf' => (string) $infants,
        ];

        if ($returnDate !== '' && ($params['trip_type'] ?? '') === 'roundtrip') {
            $body['return'] = [
                'departure' => $destination,
                'arrival' => $origin,
                'date' => $returnDate,
            ];
        }

        return $body;
    }

    /**
     * @param  array<string, mixed>  $params
     * @param  array<string, mixed>  $raw
     * @param  array<string, mixed>  $parsed
     */
    protected function rememberSearchContext(array $params, array $raw, array $parsed): void
    {
        session([
            'sunspring.last_search' => [
                'input' => $params,
                'raw' => $raw,
                'solutions' => $parsed['solutions'] ?? [],
            ],
        ]);
        Cache::put('sunspring.solutions.'.session()->getId(), $parsed['solutions'] ?? [], now()->addHours(2));
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function splitSolutionKey(string $key): array
    {
        if (str_contains($key, '|')) {
            [$a, $b] = explode('|', $key, 2);

            return [trim($a), trim($b)];
        }

        return [trim($key), ''];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function findCachedSolution(string $key): ?array
    {
        $solutions = session('sunspring.last_search.solutions');
        if (! is_array($solutions)) {
            $solutions = Cache::get('sunspring.solutions.'.session()->getId(), []);
        }
        foreach ($solutions as $solution) {
            if (is_array($solution) && (string) ($solution['key'] ?? '') === $key) {
                return $solution;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $p
     * @return array<string, mixed>
     */
    protected function mapPassenger(array $p): array
    {
        $gender = strtoupper(substr((string) ($p['gender'] ?? 'M'), 0, 1)) === 'F' ? 'F' : 'M';
        $prefix = (string) ($p['prefix'] ?? ($gender === 'F' ? 'Mrs' : 'Mr'));

        return [
            'type' => (string) ($p['type'] ?? 'ADT'),
            'gender' => $gender,
            'accompanied' => (string) ($p['accompanied'] ?? ''),
            'prefix' => $prefix,
            'given_name' => (string) ($p['first'] ?? $p['given_name'] ?? ''),
            'surname' => (string) ($p['last'] ?? $p['surname'] ?? ''),
            'birthdate' => (string) ($p['dob'] ?? $p['birthdate'] ?? ''),
            'telephone' => (string) ($p['phone'] ?? $p['telephone'] ?? ''),
            'email' => (string) ($p['email'] ?? ''),
            'nationality' => (string) ($p['nationality'] ?? 'USA'),
            'national_id' => (string) ($p['national_id'] ?? '0000000000'),
            'passport' => [
                'id' => (string) data_get($p, 'passport.id', $p['passport_number'] ?? ''),
                'expire_date' => (string) data_get($p, 'passport.expire_date', $p['passport_expire'] ?? ''),
                'doc_issue_country' => (string) data_get($p, 'passport.doc_issue_country', $p['nationality'] ?? 'USA'),
            ],
        ];
    }

    protected function parseMoneyAmount(string $price): float
    {
        if (preg_match('/([0-9]+(?:\.[0-9]+)?)/', $price, $m)) {
            return (float) $m[1];
        }

        return 0.0;
    }
}
