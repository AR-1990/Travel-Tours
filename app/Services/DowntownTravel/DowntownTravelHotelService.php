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
     * Downtown hotel “select” — store offer for guest form (full Get Offers/Validate can follow).
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

        $prebook = [
            'hotel_name' => (string) ($solution['hotel_name'] ?? 'Hotel'),
            'city' => (string) ($solution['city'] ?? $solution['destination_label'] ?? ''),
            'booking_token' => 'dth:'.$solutionKey,
            'total_price' => $solution['total_price'] ?? 0,
            'currency' => $solution['currency'] ?? 'USD',
            'check_in' => $solution['check_in'] ?? null,
            'check_out' => $solution['check_out'] ?? null,
        ];

        $priced = [
            'solution_key' => $solutionKey,
            'solution' => $solution,
            'prebook' => $prebook,
            'total_price' => $solution['total_price'] ?? 0,
            'currency' => $solution['currency'] ?? 'USD',
            'provider' => 'downtown_travel_hotels',
        ];

        session([
            'downtown_travel_hotels.last_prebook' => $priced,
        ]);

        return [
            'ok' => true,
            'message' => 'Hotel option selected. Continue with guest details.',
            'solution' => $solution,
            'prebook' => $prebook,
            'total_price' => $priced['total_price'],
            'currency' => $priced['currency'],
            'provider' => 'downtown_travel_hotels',
        ];
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function book(array $params): array
    {
        $prebook = session('downtown_travel_hotels.last_prebook');
        $solution = is_array($prebook['solution'] ?? null) ? $prebook['solution'] : null;
        if ($solution === null) {
            return ['ok' => false, 'message' => 'Select a hotel option before booking.', 'provider' => 'downtown_travel_hotels'];
        }

        // Full Create Order → Book Order needs offer_id/room_id from Get Offers + Validate.
        // Persist a local hold with provider reference so the UI flow works; deepen API steps next.
        $localRef = 'DTH-'.strtoupper(Str::random(8));

        $guests = is_array($params['guests'] ?? null) ? $params['guests'] : [];
        $lead = $guests[0] ?? [];

        $booking = [
            'booking_id' => $localRef,
            'reference_no' => $localRef,
            'internal_reference' => $localRef,
            'hotel_id' => (string) ($solution['property_id'] ?? $solution['hotel_id'] ?? ''),
            'hotel_name' => (string) ($solution['hotel_name'] ?? ''),
            'city_id' => (string) ($solution['city'] ?? $solution['destination_label'] ?? ''),
            'nationality' => null,
            'check_in' => $solution['check_in'] ?? null,
            'check_out' => $solution['check_out'] ?? null,
            'nights' => $solution['nights'] ?? null,
            'passenger_prefix' => (string) ($lead['prefix'] ?? $params['prefix'] ?? ''),
            'passenger_first' => (string) ($lead['first_name'] ?? $params['first_name'] ?? ''),
            'passenger_last' => (string) ($lead['last_name'] ?? $params['last_name'] ?? ''),
            'passenger_email' => (string) ($params['email'] ?? ''),
            'passenger_phone' => (string) ($params['phone'] ?? ''),
            'total_price' => isset($solution['total_price']) ? (string) $solution['total_price'] : null,
            'currency' => $solution['currency'] ?? null,
            'guests' => $guests,
            'price_snapshot' => $solution,
            'raw_result' => [
                'note' => 'preliminary_local_hold',
                'property_id' => $solution['property_id'] ?? null,
                'session_id' => $solution['session_id'] ?? null,
            ],
        ];

        session([
            'downtown_travel_hotels.last_booking' => [
                'reference' => $localRef,
                'solution' => $solution,
                'guest' => $params,
                'booking' => $booking,
            ],
        ]);

        return [
            'ok' => true,
            'message' => 'Downtown Travel hotel reservation recorded. Supplier offer validate/order can be completed next.',
            'provider' => 'downtown_travel_hotels',
            'booking' => $booking,
        ];
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
