<?php

namespace App\Services\DowntownTravel;

use Illuminate\Support\Str;

class DowntownTravelHotelService
{
    /**
     * Friendly destination presets for public search (lat/lng for Check Availability).
     *
     * @var array<string, array{label: string, latitude: float, longitude: float, radius: int}>
     */
    public const DESTINATIONS = [
        'london' => ['label' => 'London, United Kingdom', 'latitude' => 51.50735, 'longitude' => -0.12776, 'radius' => 30000],
        'new_york' => ['label' => 'New York, USA', 'latitude' => 40.7128, 'longitude' => -74.0060, 'radius' => 25000],
        'dubai' => ['label' => 'Dubai, UAE', 'latitude' => 25.2048, 'longitude' => 55.2708, 'radius' => 30000],
        'paris' => ['label' => 'Paris, France', 'latitude' => 48.8566, 'longitude' => 2.3522, 'radius' => 25000],
        'istanbul' => ['label' => 'Istanbul, Turkey', 'latitude' => 41.0082, 'longitude' => 28.9784, 'radius' => 25000],
        'rome' => ['label' => 'Rome, Italy', 'latitude' => 41.9028, 'longitude' => 12.4964, 'radius' => 20000],
        'singapore' => ['label' => 'Singapore', 'latitude' => 1.3521, 'longitude' => 103.8198, 'radius' => 20000],
        'bangkok' => ['label' => 'Bangkok, Thailand', 'latitude' => 13.7563, 'longitude' => 100.5018, 'radius' => 25000],
        'kuwait' => ['label' => 'Kuwait City, Kuwait', 'latitude' => 29.3759, 'longitude' => 47.9774, 'radius' => 25000],
        'cairo' => ['label' => 'Cairo, Egypt', 'latitude' => 30.0444, 'longitude' => 31.2357, 'radius' => 25000],
    ];

    public function __construct(
        protected DowntownTravelHotelsClient $client,
    ) {}

    public function isReady(): bool
    {
        return DowntownTravelHotelsIntegrationConfig::isReadyForHotels();
    }

    /**
     * @return list<array{id: string, label: string}>
     */
    public static function destinationOptions(): array
    {
        $rows = [];
        foreach (self::DESTINATIONS as $id => $meta) {
            $rows[] = ['id' => $id, 'label' => $meta['label']];
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function searchAvailability(array $params): array
    {
        if (! $this->isReady()) {
            return [
                'ok' => false,
                'message' => 'Downtown Travel Hotels is not configured.',
                'solutions' => [],
                'provider' => 'downtown_travel_hotels',
            ];
        }

        $destination = strtolower(trim((string) ($params['destination'] ?? $params['city'] ?? '')));
        $meta = self::DESTINATIONS[$destination] ?? null;
        if ($meta === null && isset($params['latitude'], $params['longitude'])) {
            $meta = [
                'label' => (string) ($params['destination_label'] ?? 'Selected area'),
                'latitude' => (float) $params['latitude'],
                'longitude' => (float) $params['longitude'],
                'radius' => (int) ($params['radius'] ?? 25000),
            ];
        }
        if ($meta === null) {
            return [
                'ok' => false,
                'message' => 'Choose a destination city for Downtown Travel Hotels search.',
                'solutions' => [],
                'provider' => 'downtown_travel_hotels',
            ];
        }

        $checkIn = (string) ($params['check_in'] ?? '');
        $checkOut = (string) ($params['check_out'] ?? '');
        $adults = max(1, (int) ($params['adults'] ?? 2));
        $children = max(0, (int) ($params['children'] ?? 0));
        $sessionId = (string) Str::uuid();

        $room = ['adults_count' => $adults];
        if ($children > 0) {
            $ages = $params['child_ages'] ?? array_fill(0, $children, 5);
            if (! is_array($ages)) {
                $ages = array_fill(0, $children, 5);
            }
            $room['kids_ages'] = array_values(array_map('intval', array_slice($ages, 0, $children)));
        }

        $body = [
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'rooms' => [$room],
            'search_by' => [
                'center' => [
                    'latitude' => $meta['latitude'],
                    'longitude' => $meta['longitude'],
                ],
                'radius' => $meta['radius'],
            ],
            'session_id' => $sessionId,
        ];

        $response = $this->client->postHotels('/partner/v2/check_availability', $body);
        if (! ($response['ok'] ?? false)) {
            return [
                'ok' => false,
                'message' => $response['message'] ?? 'Downtown Travel hotel search failed.',
                'solutions' => [],
                'provider' => 'downtown_travel_hotels',
                'raw' => $response['data'] ?? null,
            ];
        }

        $rows = is_array($response['data'] ?? null) ? $response['data'] : [];
        if (! array_is_list($rows)) {
            $rows = is_array($rows['hotels'] ?? null) ? $rows['hotels'] : [];
        }

        // Cap for UI + property-details name lookup.
        $rows = array_slice($rows, 0, 40);
        $propertyIds = [];
        foreach ($rows as $row) {
            if (is_array($row) && ! empty($row['property_id'])) {
                $propertyIds[] = (string) $row['property_id'];
            }
        }
        $details = $this->fetchPropertyDetails($propertyIds);

        $solutions = [];
        $envMode = \App\Support\HotelProvider::environmentMode('downtown_travel_hotels');
        foreach ($rows as $i => $row) {
            if (! is_array($row)) {
                continue;
            }
            $propertyId = (string) ($row['property_id'] ?? '');
            if ($propertyId === '') {
                continue;
            }
            $detail = is_array($details[$propertyId] ?? null) ? $details[$propertyId] : [];
            $hotelName = trim((string) ($detail['name'] ?? ''));
            $cityName = trim((string) data_get($detail, 'address.city.name', $meta['label']));
            $currency = strtoupper((string) data_get($row, 'rate.pricing_details.currency_code', 'USD'));
            $total = (float) data_get($row, 'rate.pricing_details.total_rate', 0);
            $meal = (string) data_get($row, 'rate.meal_plan', '');
            $stars = (string) ($detail['star_rating'] ?? '');

            $solutions[] = [
                'key' => 'dth:'.$propertyId.':'.$i,
                'provider' => 'downtown_travel_hotels',
                'environment' => $envMode['key'],
                'environment_label' => $envMode['label'],
                'property_id' => $propertyId,
                'hotel_id' => $propertyId,
                'hotel_name' => $hotelName !== '' ? $hotelName : ('Hotel '.$propertyId),
                'city' => $cityName,
                'destination_label' => $meta['label'],
                'star_rating' => $stars,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'nights' => max(1, (int) ((strtotime($checkOut) - strtotime($checkIn)) / 86400)),
                'currency' => $currency,
                'total_price' => $total,
                'meal_plan' => $meal,
                'session_id' => $sessionId,
                'adults' => $adults,
                'children' => $children,
                'child_ages' => $room['kids_ages'] ?? [],
                'rooms' => [[
                    'RoomNo' => 1,
                    'RoomTypeName' => $meal !== '' ? ucwords(str_replace('_', ' ', $meal)) : 'Room',
                    'MappedMealName' => $meal !== '' ? ucwords(str_replace('_', ' ', $meal)) : 'Room only',
                    'Price' => number_format($total, 2, '.', ''),
                ]],
                'raw' => $row,
                'details' => $detail,
            ];
        }

        session([
            'downtown_travel_hotels.last_search' => [
                'meta' => [
                    'destination' => $destination,
                    'destination_label' => $meta['label'],
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'session_id' => $sessionId,
                    'adults' => $adults,
                    'children' => $children,
                    'child_ages' => $room['kids_ages'] ?? [],
                    'rooms' => [$room],
                ],
                'solutions' => $solutions,
            ],
        ]);

        return [
            'ok' => true,
            'message' => count($solutions) > 0
                ? 'Found '.count($solutions).' hotel(s) in '.$meta['label'].'.'
                : 'No hotels found in '.$meta['label'].' for these dates.',
            'solutions' => $solutions,
            'search_key' => $sessionId,
            'provider' => 'downtown_travel_hotels',
        ];
    }

    /**
     * Select hotel → Get Offers → Validate Offer Price (locks offer_id + room_id + agency_net).
     *
     * @return array<string, mixed>
     */
    public function recheckAndPreBook(string $solutionKey): array
    {
        $solution = $this->findCachedSolution($solutionKey);
        if ($solution === null) {
            return [
                'ok' => false,
                'message' => 'Selected hotel expired. Please search again.',
                'provider' => 'downtown_travel_hotels',
            ];
        }

        $propertyId = trim((string) ($solution['property_id'] ?? $solution['hotel_id'] ?? ''));
        $sessionId = trim((string) ($solution['session_id'] ?? session('downtown_travel_hotels.last_search.meta.session_id', '')));
        if ($propertyId === '' || $sessionId === '') {
            return [
                'ok' => false,
                'message' => 'Missing Downtown Travel property or session. Search again.',
                'provider' => 'downtown_travel_hotels',
            ];
        }

        $searchMeta = session('downtown_travel_hotels.last_search.meta', []);
        $offersBody = ['session_id' => $sessionId];
        if (is_array($searchMeta) && ! empty($searchMeta['check_in']) && ! empty($searchMeta['check_out'])) {
            $rooms = is_array($searchMeta['rooms'] ?? null) ? $searchMeta['rooms'] : [[
                'adults_count' => max(1, (int) ($searchMeta['adults'] ?? $solution['adults'] ?? 2)),
            ]];
            if (! empty($searchMeta['child_ages']) && is_array($searchMeta['child_ages']) && isset($rooms[0]) && is_array($rooms[0])) {
                $rooms[0]['kids_ages'] = array_values(array_map('intval', $searchMeta['child_ages']));
            }
            $offersBody['search_input'] = [
                'check_in' => (string) $searchMeta['check_in'],
                'check_out' => (string) $searchMeta['check_out'],
                'rooms' => $rooms,
            ];
        }

        $offers = $this->client->postHotels(
            '/partner/v2/check_availability/properties/'.$propertyId.'/offers',
            $offersBody
        );
        if (! ($offers['ok'] ?? false)) {
            return [
                'ok' => false,
                'message' => $offers['message'] ?? 'Could not load Downtown Travel hotel offers.',
                'provider' => 'downtown_travel_hotels',
                'raw' => $offers['data'] ?? null,
            ];
        }

        $offersData = is_array($offers['data'] ?? null) ? $offers['data'] : [];
        $offerList = is_array($offersData['offers'] ?? null) ? $offersData['offers'] : [];
        $picked = $this->pickBestOffer($offerList, (float) ($solution['total_price'] ?? 0));
        if ($picked === null) {
            return [
                'ok' => false,
                'message' => 'No bookable Downtown Travel offer for this property. Try another hotel.',
                'provider' => 'downtown_travel_hotels',
                'raw' => $offersData,
            ];
        }

        $offerId = trim((string) ($picked['offer_id'] ?? ''));
        if ($offerId === '') {
            return [
                'ok' => false,
                'message' => 'Downtown Travel offer is missing offer_id.',
                'provider' => 'downtown_travel_hotels',
                'raw' => $picked,
            ];
        }

        $validated = $this->client->getHotels(
            '/partner/v2/check_availability/'.$sessionId.'/offers/'.$offerId.'/price'
        );
        if (! ($validated['ok'] ?? false)) {
            return [
                'ok' => false,
                'message' => $validated['message'] ?? 'Could not validate Downtown Travel offer price.',
                'provider' => 'downtown_travel_hotels',
                'raw' => $validated['data'] ?? null,
            ];
        }

        $priceData = is_array($validated['data'] ?? null) ? $validated['data'] : [];
        $rooms = is_array($priceData['rooms'] ?? null) ? $priceData['rooms'] : [];
        $roomId = trim((string) (data_get($rooms, '0.id') ?? data_get($picked, 'rooms.0.id') ?? ''));
        if ($roomId === '') {
            return [
                'ok' => false,
                'message' => 'Downtown Travel validate price did not return a room_id.',
                'provider' => 'downtown_travel_hotels',
                'raw' => $priceData,
            ];
        }

        $agencyNet = (float) data_get($priceData, 'payment_options.check.agency_net_price',
            data_get($picked, 'payment_options.check.agency_net_price', $solution['total_price'] ?? 0)
        );
        $agencyClient = (float) data_get($priceData, 'payment_options.check.agency_client_price', $agencyNet);
        $currency = strtoupper((string) data_get($priceData, 'pricing_details.currency_code',
            data_get($picked, 'pricing_details.currency_code', $solution['currency'] ?? 'USD')
        ));
        $roomName = trim((string) (data_get($rooms, '0.room_name') ?? data_get($picked, 'rooms.0.room_name') ?? ''));
        $meal = trim((string) (data_get($rooms, '0.meal_plan') ?? data_get($picked, 'rooms.0.meal_plan') ?? $solution['meal_plan'] ?? ''));

        $solution['offer_id'] = $offerId;
        $solution['room_id'] = $roomId;
        $solution['session_id'] = $sessionId;
        $solution['total_price'] = $agencyNet;
        $solution['currency'] = $currency;
        $solution['meal_plan'] = $meal !== '' ? $meal : ($solution['meal_plan'] ?? '');
        $solution['room_name'] = $roomName;
        $solution['validated_offer'] = $priceData;
        $solution['offers_raw'] = $offersData;

        $prebook = [
            'hotel_name' => (string) ($solution['hotel_name'] ?? 'Hotel'),
            'city' => (string) ($solution['city'] ?? $solution['destination_label'] ?? ''),
            'booking_token' => 'dth:'.$solutionKey,
            'total_price' => $agencyNet,
            'currency' => $currency,
            'check_in' => $solution['check_in'] ?? null,
            'check_out' => $solution['check_out'] ?? null,
            'offer_id' => $offerId,
            'room_id' => $roomId,
            'session_id' => $sessionId,
            'room_name' => $roomName,
            'agency_net_price' => $agencyNet,
            'agency_client_price' => $agencyClient,
            'payment_method' => 'check',
            'refundability' => $priceData['refundability'] ?? ($picked['refundability'] ?? null),
        ];

        $priced = [
            'solution_key' => $solutionKey,
            'solution' => $solution,
            'prebook' => $prebook,
            'total_price' => $agencyNet,
            'currency' => $currency,
            'provider' => 'downtown_travel_hotels',
        ];

        session([
            'downtown_travel_hotels.last_prebook' => $priced,
        ]);

        return [
            'ok' => true,
            'message' => 'Offer validated. Continue with guest details to book with Downtown Travel.',
            'solution' => $solution,
            'prebook' => $prebook,
            'total_price' => $agencyNet,
            'currency' => $currency,
            'provider' => 'downtown_travel_hotels',
        ];
    }

    /**
     * Create Order → Book Order (payment_method=check).
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function book(array $params): array
    {
        $prebook = session('downtown_travel_hotels.last_prebook');
        $solution = is_array($prebook['solution'] ?? null) ? $prebook['solution'] : null;
        $pre = is_array($prebook['prebook'] ?? null) ? $prebook['prebook'] : [];
        if ($solution === null) {
            return ['ok' => false, 'message' => 'Select a hotel option before booking.', 'provider' => 'downtown_travel_hotels'];
        }

        $offerId = trim((string) ($pre['offer_id'] ?? $solution['offer_id'] ?? ''));
        $roomId = trim((string) ($pre['room_id'] ?? $solution['room_id'] ?? ''));
        $sessionId = trim((string) ($pre['session_id'] ?? $solution['session_id'] ?? ''));
        $propertyId = trim((string) ($solution['property_id'] ?? $solution['hotel_id'] ?? ''));
        $totalPrice = (float) ($pre['agency_net_price'] ?? $pre['total_price'] ?? $solution['total_price'] ?? 0);

        if ($offerId === '' || $roomId === '' || $sessionId === '' || $propertyId === '') {
            return [
                'ok' => false,
                'message' => 'Validated offer expired. Select the hotel again to refresh price.',
                'provider' => 'downtown_travel_hotels',
            ];
        }

        $guests = is_array($params['guests'] ?? null) ? $params['guests'] : [];
        $lead = $guests[0] ?? [];
        $nationality = strtoupper(trim((string) ($params['nationality'] ?? $lead['nationality'] ?? 'US')));
        if (strlen($nationality) !== 2) {
            $nationality = 'US';
        }
        $email = trim((string) ($params['email'] ?? ''));
        $phone = $this->normalizePhone((string) ($params['phone'] ?? ''));
        if ($email === '' || $phone === '') {
            return ['ok' => false, 'message' => 'Email and phone are required for Downtown Travel hotel booking.', 'provider' => 'downtown_travel_hotels'];
        }

        $knownGuests = [];
        foreach ($guests as $index => $guest) {
            if (! is_array($guest)) {
                continue;
            }
            $first = trim((string) ($guest['first_name'] ?? ''));
            $last = trim((string) ($guest['last_name'] ?? ''));
            if ($first === '' || $last === '') {
                continue;
            }
            $prefix = (string) ($guest['prefix'] ?? ($index === 0 ? ($params['prefix'] ?? 'Mr.') : 'Mr.'));
            $gender = $this->genderFromPrefix($prefix);
            $isChild = strtolower((string) ($guest['pax_type'] ?? 'Adult')) === 'child'
                || (int) ($guest['child_age'] ?? 0) > 0;
            if ($isChild) {
                $knownGuests[] = [
                    'child' => [
                        'first_name' => $first,
                        'last_name' => $last,
                        'gender' => $gender,
                        'nationality' => $nationality,
                        'age' => max(0, min(17, (int) ($guest['child_age'] ?? 5))),
                    ],
                ];
            } else {
                $knownGuests[] = [
                    'adult' => [
                        'first_name' => $first,
                        'last_name' => $last,
                        'gender' => $gender,
                        'nationality' => $nationality,
                    ],
                ];
            }
        }

        if ($knownGuests === []) {
            $knownGuests[] = [
                'adult' => [
                    'first_name' => trim((string) ($params['first_name'] ?? 'Guest')),
                    'last_name' => trim((string) ($params['last_name'] ?? 'Guest')),
                    'gender' => $this->genderFromPrefix((string) ($params['prefix'] ?? 'Mr.')),
                    'nationality' => $nationality,
                ],
            ];
        }

        $createBody = [
            'booking' => [
                'property_id' => $propertyId,
                'offer_id' => $offerId,
                'guests' => [
                    'contact_data' => [
                        'email' => $email,
                        'phone' => $phone,
                    ],
                    'room_guests' => [[
                        'room_id' => $roomId,
                        'known_guests' => $knownGuests,
                    ]],
                ],
            ],
            'session_id' => $sessionId,
            'payment_method' => 'check',
            'total_price' => round($totalPrice, 2),
        ];

        $created = $this->client->postHotels('/partner/v2/orders', $createBody);
        if (! ($created['ok'] ?? false)) {
            return [
                'ok' => false,
                'message' => $created['message'] ?? 'Downtown Travel create order failed.',
                'provider' => 'downtown_travel_hotels',
                'raw' => $created['data'] ?? null,
            ];
        }

        $orderId = $this->extractOrderId($created['data'] ?? null);
        if ($orderId === '') {
            return [
                'ok' => false,
                'message' => 'Downtown Travel create order did not return an order id.',
                'provider' => 'downtown_travel_hotels',
                'raw' => $created['data'] ?? null,
            ];
        }

        $booked = $this->client->postHotels('/partner/v2/orders/'.$orderId.'/book', [
            'payment_method' => 'check',
            'total_price' => round($totalPrice, 2),
        ]);

        // 5xx means status unknown — still persist order id and let Get Order Details resolve.
        $bookOk = (bool) ($booked['ok'] ?? false);
        $bookStatus = (int) ($booked['http_status'] ?? 0);
        if (! $bookOk && $bookStatus < 500) {
            return [
                'ok' => false,
                'message' => $booked['message'] ?? 'Downtown Travel book order failed.',
                'provider' => 'downtown_travel_hotels',
                'order_id' => $orderId,
                'raw' => $booked['data'] ?? null,
            ];
        }

        $details = $this->getOrderDetails($orderId);
        $order = is_array($details['order'] ?? null) ? $details['order'] : [];
        $readable = (string) ($order['readable_id'] ?? '');
        $statusHint = strtolower((string) data_get($order, 'rooms.0.booking_status', $bookOk ? 'processing_booking' : 'unknown'));

        $booking = [
            'booking_id' => $orderId,
            'reference_no' => $readable !== '' ? $readable : $orderId,
            'internal_reference' => $orderId,
            'hotel_id' => $propertyId,
            'hotel_name' => (string) ($solution['hotel_name'] ?? data_get($order, 'property.name', '')),
            'city_id' => (string) ($solution['city'] ?? $solution['destination_label'] ?? ''),
            'nationality' => $nationality,
            'check_in' => $solution['check_in'] ?? data_get($order, 'check_in_date'),
            'check_out' => $solution['check_out'] ?? data_get($order, 'check_out_date'),
            'nights' => $solution['nights'] ?? null,
            'passenger_prefix' => (string) ($lead['prefix'] ?? $params['prefix'] ?? ''),
            'passenger_first' => (string) ($lead['first_name'] ?? $params['first_name'] ?? ''),
            'passenger_last' => (string) ($lead['last_name'] ?? $params['last_name'] ?? ''),
            'passenger_email' => $email,
            'passenger_phone' => $phone,
            'total_price' => number_format($totalPrice, 2, '.', ''),
            'currency' => $solution['currency'] ?? null,
            'guests' => $guests,
            'price_snapshot' => $solution,
            'raw_result' => [
                'order_id' => $orderId,
                'create' => $created['data'] ?? null,
                'book' => $booked['data'] ?? null,
                'order' => $order,
                'booking_status' => $statusHint,
                'http_status_book' => $bookStatus,
            ],
        ];

        session([
            'downtown_travel_hotels.last_booking' => [
                'reference' => $orderId,
                'solution' => $solution,
                'guest' => $params,
                'booking' => $booking,
            ],
        ]);

        $message = $bookOk
            ? ($readable !== ''
                ? 'Downtown Travel hotel booked (order '.$readable.').'
                : 'Downtown Travel hotel booking submitted.')
            : 'Downtown Travel booking request sent; confirm final status with order details (supplier sync may take a few minutes).';

        return [
            'ok' => true,
            'message' => $message,
            'provider' => 'downtown_travel_hotels',
            'booking' => $booking,
            'order_id' => $orderId,
        ];
    }

    /**
     * GET /partner/v2/orders/{order_id}
     *
     * @return array<string, mixed>
     */
    public function getOrderDetails(string $orderId): array
    {
        $orderId = trim($orderId);
        if ($orderId === '') {
            return ['ok' => false, 'message' => 'Downtown Travel hotel order id is required.', 'provider' => 'downtown_travel_hotels'];
        }

        $response = $this->client->getHotels('/partner/v2/orders/'.$orderId);
        if (! ($response['ok'] ?? false)) {
            return [
                'ok' => false,
                'message' => $response['message'] ?? 'Could not load Downtown Travel hotel order.',
                'provider' => 'downtown_travel_hotels',
                'http_status' => $response['http_status'] ?? null,
                'raw' => $response['data'] ?? null,
            ];
        }

        $order = is_array($response['data'] ?? null) ? $response['data'] : [];

        return [
            'ok' => true,
            'message' => 'Downtown Travel hotel order retrieved.',
            'provider' => 'downtown_travel_hotels',
            'order' => $order,
            'order_id' => (string) ($order['id'] ?? $orderId),
            'readable_id' => $order['readable_id'] ?? null,
            'booking_status' => data_get($order, 'rooms.0.booking_status'),
            'raw' => $order,
            'detail' => $order,
        ];
    }

    /**
     * POST /partner/v2/orders/{order_id}/cancel
     *
     * @return array<string, mixed>
     */
    public function cancelOrder(string $orderId): array
    {
        $orderId = trim($orderId);
        if ($orderId === '') {
            return ['ok' => false, 'message' => 'Downtown Travel hotel order id is required.', 'provider' => 'downtown_travel_hotels'];
        }

        $response = $this->client->postHotels('/partner/v2/orders/'.$orderId.'/cancel', []);
        if (! ($response['ok'] ?? false)) {
            return [
                'ok' => false,
                'message' => $response['message'] ?? 'Downtown Travel hotel cancel failed.',
                'provider' => 'downtown_travel_hotels',
                'http_status' => $response['http_status'] ?? null,
                'raw' => $response['data'] ?? null,
            ];
        }

        $order = is_array($response['data'] ?? null) ? $response['data'] : [];

        return [
            'ok' => true,
            'message' => 'Downtown Travel hotel order cancelled.',
            'provider' => 'downtown_travel_hotels',
            'order' => $order,
            'order_id' => (string) ($order['id'] ?? $orderId),
            'cancelled' => true,
            'raw' => $order,
        ];
    }

    /**
     * Resolve supplier order UUID from a stored reservation.
     *
     * @param  array<string, mixed>|null  $raw
     */
    public function resolveOrderId(?string $bookingId, ?string $internalReference, ?array $raw = null): string
    {
        foreach ([
            $bookingId,
            $internalReference,
            data_get($raw, 'order_id'),
            data_get($raw, 'order.id'),
            data_get($raw, 'id'),
        ] as $candidate) {
            $value = trim((string) $candidate);
            if ($value !== '' && preg_match('/^[0-9a-fA-F-]{32,36}$/', $value)) {
                return $value;
            }
        }

        return trim((string) ($bookingId ?: $internalReference ?: ''));
    }

    /**
     * @param  list<array<string, mixed>>  $offers
     * @return array<string, mixed>|null
     */
    protected function pickBestOffer(array $offers, float $preferredTotal): ?array
    {
        $best = null;
        $bestDelta = PHP_FLOAT_MAX;
        foreach ($offers as $offer) {
            if (! is_array($offer) || empty($offer['offer_id'])) {
                continue;
            }
            $net = (float) data_get($offer, 'payment_options.check.agency_net_price',
                data_get($offer, 'pricing_details.total_rate', 0)
            );
            $delta = $preferredTotal > 0 ? abs($net - $preferredTotal) : $net;
            if ($best === null || $delta < $bestDelta) {
                $best = $offer;
                $bestDelta = $delta;
            }
        }

        return $best;
    }

    protected function extractOrderId(mixed $data): string
    {
        if (is_string($data)) {
            return trim($data, "\" \t\n\r");
        }
        if (is_array($data)) {
            foreach (['id', 'order_id', 'uuid'] as $key) {
                $value = trim((string) ($data[$key] ?? ''));
                if ($value !== '') {
                    return $value;
                }
            }
        }

        return '';
    }

    protected function genderFromPrefix(string $prefix): string
    {
        $normalized = strtolower(rtrim(trim($prefix), '.'));

        return in_array($normalized, ['mrs', 'ms', 'miss'], true) ? 'female' : 'male';
    }

    protected function normalizePhone(string $phone): string
    {
        $phone = trim($phone);
        if ($phone === '') {
            return '';
        }
        $digits = preg_replace('/[^\d+]/', '', $phone) ?? '';
        if ($digits === '') {
            return '';
        }
        if (! str_starts_with($digits, '+')) {
            $digits = '+'.$digits;
        }

        return $digits;
    }

    /**
     * @param  list<string>  $propertyIds
     * @return array<string, array<string, mixed>>
     */
    protected function fetchPropertyDetails(array $propertyIds): array
    {
        $propertyIds = array_values(array_unique(array_filter($propertyIds)));
        if ($propertyIds === []) {
            return [];
        }

        $auth = $this->client->getToken();
        if (! ($auth['ok'] ?? false)) {
            return [];
        }

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(60)
                ->withToken((string) ($auth['token'] ?? ''))
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json;charset=utf-8',
                ])
                ->withBody(json_encode(array_values($propertyIds)), 'application/json;charset=utf-8')
                ->post($this->client->hotelsBaseUrl().'/partner/v2/properties/details');
        } catch (\Throwable) {
            return [];
        }

        $json = $response->json();

        return is_array($json) ? $json : [];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function findCachedSolution(string $key): ?array
    {
        $solutions = session('downtown_travel_hotels.last_search.solutions', []);
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
}
