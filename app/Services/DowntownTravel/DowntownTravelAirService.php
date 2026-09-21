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
            $confirmed = $this->confirmPriceChangeIfNeeded($book);
            if ($confirmed !== null) {
                $book = $confirmed;
            }
        }

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
        $readable = trim((string) ($data['readable_id'] ?? ''));
        $airlinePnr = trim((string) (
            data_get($data, 'booking_records.0.airline_record_locator')
            ?? data_get($data, 'booking_records.0.record_locator')
            ?? data_get($data, 'booking_records.0.source_reference')
            ?? ''
        ));

        session([
            'downtown_travel.last_booking' => [
                'order_id' => $orderId,
                'digest' => $bookDigest,
                'response' => $data,
                'solution' => $solution,
            ],
        ]);

        $bookingRecordId = trim((string) data_get($data, 'booking_records.0.id', ''));
        $label = $readable !== '' ? $readable : ($airlinePnr !== '' ? $airlinePnr : $orderId);

        return [
            'ok' => true,
            'message' => $label !== ''
                ? 'Downtown Travel booking created (order '.$label.').'
                : 'Downtown Travel booking created.',
            'provider' => 'downtown_travel',
            // Keep the Downtown order UUID as the universal key; prefer airline PNR for GDS-style fields.
            'universal_locator' => $orderId !== '' ? $orderId : ($readable !== '' ? $readable : $airlinePnr),
            'provider_locator' => $airlinePnr !== '' ? $airlinePnr : ($readable !== '' ? $readable : $orderId),
            'air_reservation_locator' => $airlinePnr !== '' ? $airlinePnr : ($readable !== '' ? $readable : $orderId),
            'air_locator' => $airlinePnr !== '' ? $airlinePnr : ($readable !== '' ? $readable : $orderId),
            'booking_record_id' => $bookingRecordId !== '' ? $bookingRecordId : null,
            'raw' => $data,
        ];
    }

    /**
     * GET /api/public/v2/orders/{order_id}
     *
     * @return array<string, mixed>
     */
    public function getOrder(string $orderId): array
    {
        $orderId = trim($orderId);
        if ($orderId === '') {
            return ['ok' => false, 'message' => 'Downtown Travel order id is required.', 'provider' => 'downtown_travel'];
        }

        $response = $this->client->getAir('/api/public/v2/orders/'.$orderId);
        if (! ($response['ok'] ?? false)) {
            return [
                'ok' => false,
                'message' => $response['message'] ?? 'Could not load Downtown Travel order.',
                'provider' => 'downtown_travel',
                'http_status' => $response['http_status'] ?? null,
                'raw' => $response,
            ];
        }

        $data = is_array($response['data'] ?? null) ? $response['data'] : [];

        return [
            'ok' => true,
            'message' => 'Downtown Travel order retrieved.',
            'provider' => 'downtown_travel',
            'order' => $data,
            'order_id' => (string) ($data['id'] ?? $orderId),
            'readable_id' => $data['readable_id'] ?? null,
            'booking_record_id' => data_get($data, 'booking_records.0.id'),
            'can_ticket' => (bool) data_get($data, 'booking_records.0.can_ticket'),
            'can_cancel' => (bool) data_get($data, 'booking_records.0.can_cancel'),
            'can_void' => (bool) data_get($data, 'booking_records.0.can_void'),
            'can_refund' => (bool) data_get($data, 'booking_records.0.can_refund'),
            'ticket_numbers' => $this->extractTicketNumbers($data),
            'airline_pnr' => trim((string) (
                data_get($data, 'booking_records.0.airline_record_locator')
                ?? data_get($data, 'booking_records.0.record_locator')
                ?? ''
            )),
            'raw' => $data,
        ];
    }

    /**
     * POST /api/public/v2/booking_records/{id}/issue
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function issueTickets(array $params): array
    {
        $recordId = trim((string) ($params['booking_record_id'] ?? ''));
        if ($recordId === '') {
            return ['ok' => false, 'message' => 'Downtown Travel booking record id is required to issue tickets.', 'provider' => 'downtown_travel'];
        }

        $passengers = $this->mapPassengers(
            is_array($params['passengers'] ?? null) ? $params['passengers'] : [],
            false
        );
        if ($passengers === []) {
            return ['ok' => false, 'message' => 'Passenger details are required to issue Downtown Travel tickets.', 'provider' => 'downtown_travel'];
        }

        $phone = $this->normalizePhone((string) ($params['phone'] ?? ''));
        $body = [
            'passengers' => $passengers,
            'payment_option' => (string) ($params['payment_option'] ?? 'agent_cash'),
            'phone' => $phone !== '' ? $phone : '+10000000000',
            'send_itinerary' => (bool) ($params['send_itinerary'] ?? true),
        ];
        if (isset($params['expected_agent_net_price']) && is_numeric($params['expected_agent_net_price'])) {
            $body['expected_agent_net_price'] = round((float) $params['expected_agent_net_price'], 2);
        }
        $email = trim((string) ($params['email'] ?? ''));
        if ($email !== '') {
            $body['email'] = $email;
        }

        $response = $this->client->postAir('/api/public/v2/booking_records/'.$recordId.'/issue', $body);
        if (! ($response['ok'] ?? false)) {
            return [
                'ok' => false,
                'message' => $response['message'] ?? 'Downtown Travel ticketing failed.',
                'provider' => 'downtown_travel',
                'http_status' => $response['http_status'] ?? null,
                'raw' => $response,
            ];
        }

        $data = is_array($response['data'] ?? null) ? $response['data'] : [];
        $tickets = $this->extractTicketNumbers(['booking_records' => [$data]]);

        return [
            'ok' => true,
            'message' => $tickets !== []
                ? 'Downtown Travel tickets issued ('.implode(', ', $tickets).').'
                : 'Downtown Travel ticketing request completed.',
            'provider' => 'downtown_travel',
            'ticket_numbers' => $tickets,
            'booking_record' => $data,
            'raw' => $data,
        ];
    }

    /**
     * POST /api/public/v2/booking_records/{id}/cancel
     *
     * @return array<string, mixed>
     */
    public function cancelBookingRecord(string $bookingRecordId): array
    {
        return $this->bookingRecordAction($bookingRecordId, 'cancel', 'Downtown Travel booking cancelled.');
    }

    /**
     * POST /api/public/v2/booking_records/{id}/void
     *
     * @return array<string, mixed>
     */
    public function voidBookingRecord(string $bookingRecordId): array
    {
        return $this->bookingRecordAction($bookingRecordId, 'void', 'Downtown Travel tickets voided.');
    }

    /**
     * POST /api/public/v2/booking_records/{id}/create_refund_offer
     *
     * @return array<string, mixed>
     */
    public function createRefundOffer(string $bookingRecordId): array
    {
        $bookingRecordId = trim($bookingRecordId);
        if ($bookingRecordId === '') {
            return ['ok' => false, 'message' => 'Downtown Travel booking record id is required.', 'provider' => 'downtown_travel'];
        }

        $response = $this->client->postAir('/api/public/v2/booking_records/'.$bookingRecordId.'/create_refund_offer', []);
        if (! ($response['ok'] ?? false)) {
            return [
                'ok' => false,
                'message' => $response['message'] ?? 'Could not create Downtown Travel refund offer.',
                'provider' => 'downtown_travel',
                'http_status' => $response['http_status'] ?? null,
                'raw' => $response,
            ];
        }

        $data = is_array($response['data'] ?? null) ? $response['data'] : [];
        $offerId = trim((string) (data_get($data, 'offer.id') ?? data_get($data, 'id') ?? ''));

        return [
            'ok' => true,
            'message' => $offerId !== ''
                ? 'Downtown Travel refund offer created ('.$offerId.').'
                : 'Downtown Travel refund offer created.',
            'provider' => 'downtown_travel',
            'offer_id' => $offerId !== '' ? $offerId : null,
            'offer' => $data,
            'raw' => $data,
        ];
    }

    /**
     * POST /api/public/v2/booking_records/{id}/refund
     *
     * @return array<string, mixed>
     */
    public function refundBookingRecord(string $bookingRecordId, string $offerId): array
    {
        $bookingRecordId = trim($bookingRecordId);
        $offerId = trim($offerId);
        if ($bookingRecordId === '' || $offerId === '') {
            return ['ok' => false, 'message' => 'Downtown Travel booking record id and refund offer id are required.', 'provider' => 'downtown_travel'];
        }

        $response = $this->client->postAir('/api/public/v2/booking_records/'.$bookingRecordId.'/refund', [
            'id' => $offerId,
        ]);
        if (! ($response['ok'] ?? false)) {
            return [
                'ok' => false,
                'message' => $response['message'] ?? 'Downtown Travel refund failed.',
                'provider' => 'downtown_travel',
                'http_status' => $response['http_status'] ?? null,
                'raw' => $response,
            ];
        }

        $data = is_array($response['data'] ?? null) ? $response['data'] : [];

        return [
            'ok' => true,
            'message' => 'Downtown Travel refund completed.',
            'provider' => 'downtown_travel',
            'booking_record' => $data,
            'raw' => $data,
        ];
    }

    /**
     * POST /api/public/v2/booking_records/{id}/instant_issue
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function instantIssue(array $params): array
    {
        $recordId = trim((string) ($params['booking_record_id'] ?? ''));
        if ($recordId === '') {
            return ['ok' => false, 'message' => 'Downtown Travel booking record id is required for instant issue.', 'provider' => 'downtown_travel'];
        }

        $body = [
            'payment_option' => (string) ($params['payment_option'] ?? 'agent_cash'),
        ];
        if (isset($params['agency_fee']) && is_numeric($params['agency_fee'])) {
            $body['agency_fee'] = round((float) $params['agency_fee'], 2);
        }
        if (isset($params['airline_cc_payment']) && is_numeric($params['airline_cc_payment'])) {
            $body['airline_cc_payment'] = round((float) $params['airline_cc_payment'], 2);
        }
        if (is_array($params['billing_address'] ?? null)) {
            $body['billing_address'] = $params['billing_address'];
        }
        if (is_array($params['credit_card'] ?? null)) {
            $body['credit_card'] = $params['credit_card'];
        }

        $response = $this->client->postAir('/api/public/v2/booking_records/'.$recordId.'/instant_issue', $body);
        if (! ($response['ok'] ?? false)) {
            return [
                'ok' => false,
                'message' => $response['message'] ?? 'Downtown Travel instant issue failed.',
                'provider' => 'downtown_travel',
                'http_status' => $response['http_status'] ?? null,
                'raw' => $response,
            ];
        }

        $data = is_array($response['data'] ?? null) ? $response['data'] : [];

        return [
            'ok' => true,
            'message' => 'Downtown Travel instant issue completed.',
            'provider' => 'downtown_travel',
            'booking_record' => $data,
            'ticket_numbers' => $this->extractTicketNumbers(['booking_records' => [$data]]),
            'raw' => $data,
        ];
    }

    /**
     * GET /api/public/v2/orders
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function listOrders(array $filters = []): array
    {
        $query = [];
        foreach (['sort_by', 'sort_order', 'limit', 'created_on_start_date', 'created_on_end_date'] as $key) {
            if (isset($filters[$key]) && $filters[$key] !== '' && $filters[$key] !== null) {
                $query[$key] = $filters[$key];
            }
        }

        $response = $this->client->getAir('/api/public/v2/orders', $query);
        if (! ($response['ok'] ?? false)) {
            return [
                'ok' => false,
                'message' => $response['message'] ?? 'Could not list Downtown Travel orders.',
                'provider' => 'downtown_travel',
                'orders' => [],
                'http_status' => $response['http_status'] ?? null,
                'raw' => $response,
            ];
        }

        $data = $response['data'] ?? null;
        $orders = [];
        if (is_array($data)) {
            $orders = array_is_list($data) ? $data : (is_array($data['orders'] ?? null) ? $data['orders'] : [$data]);
        }

        return [
            'ok' => true,
            'message' => 'Downtown Travel orders loaded ('.count($orders).').',
            'provider' => 'downtown_travel',
            'orders' => $orders,
            'raw' => $data,
        ];
    }

    /**
     * GET /api/public/v2/orders/comments?order={order_id}
     *
     * @return array<string, mixed>
     */
    public function listOrderComments(string $orderId): array
    {
        $orderId = trim($orderId);
        if ($orderId === '') {
            return ['ok' => false, 'message' => 'Order id is required.', 'provider' => 'downtown_travel', 'comments' => []];
        }

        $response = $this->client->getAir('/api/public/v2/orders/comments', ['order' => $orderId]);
        if (! ($response['ok'] ?? false)) {
            return [
                'ok' => false,
                'message' => $response['message'] ?? 'Could not load Downtown Travel order comments.',
                'provider' => 'downtown_travel',
                'comments' => [],
                'raw' => $response,
            ];
        }

        $data = $response['data'] ?? null;
        $comments = is_array($data) ? (array_is_list($data) ? $data : (is_array($data['comments'] ?? null) ? $data['comments'] : [])) : [];

        return [
            'ok' => true,
            'message' => 'Downtown Travel order comments loaded.',
            'provider' => 'downtown_travel',
            'comments' => $comments,
            'raw' => $data,
        ];
    }

    /**
     * POST /api/public/v2/orders/comments
     *
     * @return array<string, mixed>
     */
    public function addOrderComment(string $orderId, string $comment): array
    {
        $orderId = trim($orderId);
        $comment = trim($comment);
        if ($orderId === '' || $comment === '') {
            return ['ok' => false, 'message' => 'Order id and comment are required.', 'provider' => 'downtown_travel'];
        }

        $response = $this->client->postAir('/api/public/v2/orders/comments', [
            'order_id' => $orderId,
            'comment' => $comment,
        ]);
        if (! ($response['ok'] ?? false)) {
            return [
                'ok' => false,
                'message' => $response['message'] ?? 'Could not add Downtown Travel order comment.',
                'provider' => 'downtown_travel',
                'raw' => $response,
            ];
        }

        return [
            'ok' => true,
            'message' => 'Downtown Travel order comment added.',
            'provider' => 'downtown_travel',
            'comment' => $response['data'] ?? null,
            'raw' => $response['data'] ?? null,
        ];
    }

    /**
     * Resolve booking_record_id from a stored order / reservation raw payload.
     *
     * @param  array<string, mixed>  $raw
     */
    public function resolveBookingRecordId(array $raw): string
    {
        foreach ([
            'booking_record_id',
            'booking_records.0.id',
            'raw.booking_records.0.id',
            'order.booking_records.0.id',
            'response.booking_records.0.id',
        ] as $path) {
            $value = trim((string) data_get($raw, $path, ''));
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    /**
     * @param  array<string, mixed>  $orderOrRecord
     * @return list<string>
     */
    public function extractTicketNumbers(array $orderOrRecord): array
    {
        $numbers = [];
        $records = data_get($orderOrRecord, 'booking_records');
        if (! is_array($records)) {
            $records = isset($orderOrRecord['passengers']) ? [$orderOrRecord] : [];
        }

        foreach ($records as $record) {
            if (! is_array($record)) {
                continue;
            }
            foreach ((array) ($record['passengers'] ?? []) as $row) {
                if (! is_array($row)) {
                    continue;
                }
                foreach (['adult', 'child', 'infant'] as $key) {
                    $person = $row[$key] ?? null;
                    if (! is_array($person)) {
                        continue;
                    }
                    $num = trim((string) data_get($person, 'air_ticket.ticket_number', ''));
                    if ($num !== '') {
                        $numbers[] = $num;
                    }
                }
            }
        }

        return array_values(array_unique($numbers));
    }

    /**
     * @return array<string, mixed>
     */
    protected function bookingRecordAction(string $bookingRecordId, string $action, string $successMessage): array
    {
        $bookingRecordId = trim($bookingRecordId);
        if ($bookingRecordId === '') {
            return ['ok' => false, 'message' => 'Downtown Travel booking record id is required.', 'provider' => 'downtown_travel'];
        }

        $response = $this->client->postAir('/api/public/v2/booking_records/'.$bookingRecordId.'/'.$action, []);
        if (! ($response['ok'] ?? false)) {
            return [
                'ok' => false,
                'message' => $response['message'] ?? ('Downtown Travel '.$action.' failed.'),
                'provider' => 'downtown_travel',
                'http_status' => $response['http_status'] ?? null,
                'raw' => $response,
            ];
        }

        $data = is_array($response['data'] ?? null) ? $response['data'] : [];

        return [
            'ok' => true,
            'message' => $successMessage,
            'provider' => 'downtown_travel',
            'booking_record' => $data,
            'raw' => $data,
            'cancelled' => $action === 'cancel',
            'voided' => $action === 'void',
        ];
    }

    /**
     * Downtown returns HTTP 400 + reason=price_changed with a temporary order_id.
     * Confirm with the new agent_net from the response pricing.
     *
     * @param  array<string, mixed>  $book
     * @return array<string, mixed>|null  Successful confirm response, or null if not applicable / failed
     */
    protected function confirmPriceChangeIfNeeded(array $book): ?array
    {
        $payload = is_array($book['data'] ?? null) ? $book['data'] : null;
        if ($payload === null || ($payload['reason'] ?? '') !== 'price_changed') {
            return null;
        }

        $tmpOrderId = trim((string) ($payload['order_id'] ?? ''));
        if ($tmpOrderId === '') {
            return null;
        }

        $newNet = data_get($payload, 'pricing.pricing_options.agent_cash.agent_net_total');
        if ($newNet === null) {
            $newNet = data_get($payload, 'pricing.pricing_options.passenger_cc.agent_net_total');
        }
        if ($newNet === null) {
            $newNet = data_get($payload, 'pricing.pricing_options.agent_cash.passenger_total');
        }
        if ($newNet === null) {
            return [
                'ok' => false,
                'message' => 'Fare price changed, but Downtown Travel did not return the new agent net. Search and price again.',
                'http_status' => $book['http_status'] ?? 400,
                'data' => $payload,
            ];
        }

        $confirm = $this->client->postAir('/api/public/v2/orders/confirm_price_change/'.$tmpOrderId, [
            'expected_agent_net_price' => round((float) $newNet, 2),
        ]);

        if (! ($confirm['ok'] ?? false)) {
            $oldMsg = (string) ($payload['display_message'] ?? $payload['message'] ?? 'Price changed');
            $confirmMsg = (string) ($confirm['message'] ?? 'Could not confirm the new fare.');

            return [
                'ok' => false,
                'message' => $oldMsg.': '.$confirmMsg,
                'http_status' => $confirm['http_status'] ?? null,
                'data' => ['price_changed' => $payload, 'confirm' => $confirm],
            ];
        }

        return $confirm;
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
            $first = $this->normalizePersonName((string) ($p['first'] ?? $p['given_name'] ?? ''), 30);
            $last = $this->normalizePersonName((string) ($p['last'] ?? $p['surname'] ?? ''), 30);
            // Downtown: whole name (first + space + last) max 50 characters.
            $combinedMax = 50;
            $fullLen = mb_strlen(trim($first.' '.$last));
            if ($fullLen > $combinedMax && $first !== '' && $last !== '') {
                $roomForLast = max(1, $combinedMax - 1 - mb_strlen($first));
                $last = mb_substr($last, 0, $roomForLast);
            }
            $person = [
                'birthday' => (string) ($p['dob'] ?? $p['birthdate'] ?? '1990-01-01'),
                'first_name' => $first,
                'last_name' => $last,
                'nationality' => $this->normalizeNationality((string) ($p['nationality'] ?? 'USA')),
                'sex' => $sex,
            ];
            // Do not send middle_name — it counts toward Downtown's whole-name limit.

            $passportNumber = trim((string) ($p['passport_number'] ?? data_get($p, 'travel_document.number', '')));
            $passportExpire = trim((string) ($p['passport_expire'] ?? data_get($p, 'travel_document.expires_at', '')));
            if (strlen($passportNumber) >= 5 && $passportExpire !== '') {
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
     * Letters / spaces / hyphen / apostrophe only; truncate to Downtown first/last limits.
     */
    protected function normalizePersonName(string $name, int $maxLen = 30): string
    {
        $name = trim(preg_replace('/\s+/', ' ', $name) ?? '');
        $name = preg_replace("/[^A-Za-z \\-']+/", '', $name) ?? '';
        $name = trim($name);
        if ($name === '') {
            return '';
        }

        if (mb_strlen($name) > $maxLen) {
            return mb_substr($name, 0, $maxLen);
        }

        return $name;
    }

    /**
     * Downtown Person.nationality accepts ISO country codes; map common alpha-3 → alpha-2.
     */
    protected function normalizeNationality(string $value): string
    {
        $value = strtoupper(trim($value));
        $value = preg_replace('/[^A-Z]/', '', $value) ?? '';
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

        if (strlen($value) === 2 && in_array($value, \App\Support\FlightProvider::isoAlpha2Nationalities(), true)) {
            return $value;
        }

        // Unknown / invalid codes → default US (form validation should block these first).
        return 'US';
    }
}
