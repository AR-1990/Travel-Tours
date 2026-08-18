<?php

namespace App\Services\Xconnect;

use Carbon\Carbon;
use Illuminate\Support\Str;

class XconnectHotelService
{
    public function __construct(
        protected XconnectClient $client,
        protected XconnectHotelParser $parser,
    ) {}

    public function isReady(): bool
    {
        return XconnectIntegrationConfig::isReadyForHotels();
    }

    /**
     * @return array{ok: bool, message: string, countries?: list<array{id: string, name: string}>, raw?: mixed}
     */
    public function countries(): array
    {
        $res = $this->client->post('Countries', []);
        if (! $res['ok']) {
            return $res;
        }

        $rows = [];
        foreach (is_array($res['raw']) ? $res['raw'] : [] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $rows[] = [
                'id' => (string) ($row['CountryId'] ?? ''),
                'name' => (string) ($row['Name'] ?? ''),
            ];
        }

        return [
            'ok' => true,
            'message' => 'Countries loaded.',
            'countries' => $rows,
            'raw' => $res['raw'],
        ];
    }

    /**
     * @return array{ok: bool, message: string, cities?: list<array{id: string, name: string}>, raw?: mixed}
     */
    public function cities(string $countryId): array
    {
        $res = $this->client->post('Cities', [
            'CountryId' => $countryId,
        ]);
        if (! $res['ok']) {
            return $res;
        }

        $rows = [];
        foreach (is_array($res['raw']) ? $res['raw'] : [] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $rows[] = [
                'id' => (string) ($row['CityId'] ?? ''),
                'name' => (string) ($row['Name'] ?? ''),
            ];
        }

        return [
            'ok' => true,
            'message' => 'Cities loaded.',
            'cities' => $rows,
            'raw' => $res['raw'],
        ];
    }

    /**
     * Search hotel availability (with cancellation policies when available).
     *
     * @param  array<string, mixed>  $params
     * @return array{ok: bool, message: string, solutions?: list<array<string, mixed>>, search_key?: string, provider: string, raw?: mixed}
     */
    public function searchAvailability(array $params): array
    {
        $cityId = (string) ($params['city_id'] ?? '');
        $checkIn = (string) ($params['check_in'] ?? '');
        $checkOut = (string) ($params['check_out'] ?? '');
        $nationality = (string) ($params['nationality'] ?? config('xconnect.default_nationality', 'india'));
        $currency = (string) ($params['currency'] ?? config('xconnect.default_currency', 'USD'));
        $rooms = $params['rooms'] ?? [['adults' => 2, 'children' => 0, 'child_ages' => []]];
        if (! is_array($rooms) || $rooms === []) {
            $rooms = [['adults' => 2, 'children' => 0, 'child_ages' => []]];
        }

        if ($cityId === '' || $checkIn === '' || $checkOut === '') {
            return [
                'ok' => false,
                'message' => 'City, check-in and check-out are required.',
                'provider' => 'xconnect',
            ];
        }

        try {
            $in = Carbon::parse($checkIn)->startOfDay();
            $out = Carbon::parse($checkOut)->startOfDay();
        } catch (\Throwable) {
            return [
                'ok' => false,
                'message' => 'Invalid check-in or check-out date.',
                'provider' => 'xconnect',
            ];
        }

        if ($out->lte($in)) {
            return [
                'ok' => false,
                'message' => 'Check-out must be after check-in.',
                'provider' => 'xconnect',
            ];
        }

        $nights = $in->diffInDays($out);
        $roomPayload = [];
        foreach (array_values($rooms) as $i => $room) {
            $adults = max(1, (int) ($room['adults'] ?? 2));
            $children = max(0, (int) ($room['children'] ?? $room['child'] ?? 0));
            $ages = $room['child_ages'] ?? $room['ChildAge'] ?? [];
            if (! is_array($ages)) {
                $ages = [];
            }
            $ages = array_values(array_map('intval', $ages));
            while (count($ages) < $children) {
                $ages[] = 5;
            }
            $roomPayload[] = [
                'RoomNo' => $i + 1,
                'NoofAdults' => $adults,
                'NoOfChild' => $children,
                'ChildAge' => array_slice($ages, 0, $children),
            ];
        }

        $filters = [
            'IsRecommendedOnly' => (string) ($params['recommended_only'] ?? '0'),
            'IsShowRooms' => '1',
            'IsOnlyAvailable' => '1',
            'StarRating' => [
                'Min' => (int) ($params['star_min'] ?? 0),
                'Max' => (int) ($params['star_max'] ?? 5),
            ],
        ];
        if (! empty($params['hotel_ids'])) {
            $filters['HotelIds'] = (string) $params['hotel_ids'];
        }

        $request = [
            'Rooms' => $roomPayload,
            'CityID' => $cityId,
            'CheckInDate' => $in->format('m-d-Y'),
            'CheckOutDate' => $out->format('m-d-Y'),
            'NoofNights' => (string) $nights,
            'Nationality' => $nationality,
            'Filters' => $filters,
        ];

        $withCancel = (bool) ($params['with_cancellation'] ?? true);
        $endpoint = $withCancel ? 'AvailabilityWithCancellation' : 'Availability';
        $res = $this->client->post($endpoint, $request, [
            'Currency' => $currency,
        ]);

        if (! $res['ok']) {
            return [
                'ok' => false,
                'message' => $res['message'],
                'provider' => 'xconnect',
                'raw' => $res['raw'] ?? null,
            ];
        }

        $raw = is_array($res['raw']) ? $res['raw'] : [];
        $meta = [
            'city_id' => $cityId,
            'check_in' => $in->toDateString(),
            'check_out' => $out->toDateString(),
            'nights' => $nights,
            'nationality' => $nationality,
            'currency' => $currency,
            'rooms' => $roomPayload,
        ];
        $solutions = $this->parser->parseAvailability($raw, $meta);

        session([
            'xconnect.last_search' => [
                'meta' => $meta,
                'solutions' => $solutions,
                'raw' => $raw,
                'search_key' => (string) data_get($raw, 'AvailabilityRS.SearchKey', ''),
            ],
        ]);

        return [
            'ok' => true,
            'message' => count($solutions) > 0
                ? 'Found '.count($solutions).' hotel option(s).'
                : 'No hotel availability for these dates.',
            'solutions' => $solutions,
            'search_key' => (string) data_get($raw, 'AvailabilityRS.SearchKey', ''),
            'provider' => 'xconnect',
            'raw' => $raw,
        ];
    }

    public function findSolution(string $key): ?array
    {
        $solutions = session('xconnect.last_search.solutions', []);
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
     * ReCheck price/status then PreBook to obtain BookingToken.
     *
     * @return array{ok: bool, message: string, priced?: array<string, mixed>, prebook?: array<string, mixed>, solution?: array<string, mixed>, provider: string}
     */
    public function recheckAndPreBook(string $solutionKey): array
    {
        $solution = $this->findSolution($solutionKey);
        if (! $solution) {
            return [
                'ok' => false,
                'message' => 'Hotel option expired. Please search again.',
                'provider' => 'xconnect',
            ];
        }

        $hotelOption = [
            'HotelOptionId' => (string) ($solution['hotel_option_id'] ?? ''),
            'HotelRooms' => $solution['room_tokens'] ?? [],
        ];
        $searchKey = (string) ($solution['search_key'] ?? '');
        $currency = (string) ($solution['currency'] ?? 'USD');

        $recheck = $this->client->post('ReCheck', [
            'SearchKey' => $searchKey,
            'HotelOption' => $hotelOption,
        ], ['Currency' => $currency]);

        if (! $recheck['ok']) {
            return [
                'ok' => false,
                'message' => $recheck['message'],
                'provider' => 'xconnect',
                'raw' => $recheck['raw'] ?? null,
            ];
        }

        $prebookRes = $this->client->post('PreBook', [
            'SearchKey' => $searchKey,
            'HotelOption' => $hotelOption,
        ], ['Currency' => $currency]);

        if (! $prebookRes['ok']) {
            return [
                'ok' => false,
                'message' => $prebookRes['message'],
                'provider' => 'xconnect',
                'raw' => $prebookRes['raw'] ?? null,
            ];
        }

        $prebook = $this->parser->parsePreBook(is_array($prebookRes['raw']) ? $prebookRes['raw'] : []);
        if ($prebook['booking_token'] === '') {
            return [
                'ok' => false,
                'message' => 'PreBook did not return a BookingToken.',
                'provider' => 'xconnect',
                'raw' => $prebookRes['raw'] ?? null,
            ];
        }

        $priced = [
            'provider' => 'xconnect',
            'solution_key' => $solutionKey,
            'search_key' => $searchKey,
            'solution' => $solution,
            'recheck_raw' => $recheck['raw'] ?? null,
            'prebook' => $prebook,
            'total_price' => $prebook['total_price'] ?? $solution['total_price'],
            'currency' => $prebook['currency'] ?? $currency,
        ];

        session(['xconnect.last_prebook' => $priced]);

        return [
            'ok' => true,
            'message' => 'Rate rechecked and pre-booked. Continue to guest details.',
            'priced' => $priced,
            'prebook' => $prebook,
            'solution' => $solution,
            'provider' => 'xconnect',
        ];
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array{ok: bool, message: string, booking?: array<string, mixed>, provider: string, raw?: mixed}
     */
    public function book(array $params): array
    {
        $priced = session('xconnect.last_prebook');
        if (! is_array($priced) || empty($priced['prebook']['booking_token'])) {
            return [
                'ok' => false,
                'message' => 'No pre-booked rate in session. Re-select a hotel option.',
                'provider' => 'xconnect',
            ];
        }

        $prebook = $priced['prebook'];
        $solution = is_array($priced['solution'] ?? null) ? $priced['solution'] : [];
        $searchKey = (string) ($priced['search_key'] ?? '');
        $currency = (string) ($priced['currency'] ?? 'USD');
        $total = (float) ($params['total_price'] ?? $priced['total_price'] ?? 0);
        $internalRef = (string) ($params['internal_reference'] ?? '');
        if ($internalRef === '') {
            $internalRef = 'XC'.now()->format('YmdHis').Str::upper(Str::random(6));
        }

        $guests = $params['guests'] ?? [];
        if (! is_array($guests) || $guests === []) {
            return [
                'ok' => false,
                'message' => 'At least one guest is required.',
                'provider' => 'xconnect',
            ];
        }

        // Map guests onto UniqueId from PreBook rooms when available.
        $prebookRooms = is_array($prebook['rooms'] ?? null) ? $prebook['rooms'] : [];
        $hotelRooms = [];
        foreach (array_values($guests) as $i => $guest) {
            if (! is_array($guest)) {
                continue;
            }
            $roomNo = (string) ($guest['room_no'] ?? '1');
            $uniqueId = $guest['unique_id'] ?? null;
            if ($uniqueId === null) {
                foreach ($prebookRooms as $pr) {
                    if (is_array($pr) && (string) ($pr['RoomNo'] ?? '') === $roomNo) {
                        $uniqueId = $pr['UniqueId'] ?? null;
                        break;
                    }
                }
            }
            if ($uniqueId === null && isset($prebookRooms[0]['UniqueId'])) {
                $uniqueId = $prebookRooms[0]['UniqueId'];
            }

            $hotelRooms[] = [
                'UniqueId' => (int) $uniqueId,
                'RoomNo' => $roomNo,
                'IsLead' => ((string) ($guest['is_lead'] ?? ($i === 0 ? '1' : '0'))),
                'PaxType' => (string) ($guest['pax_type'] ?? 'Adult'),
                'Prefix' => (string) ($guest['prefix'] ?? 'Mr.'),
                'FirstName' => (string) ($guest['first_name'] ?? ''),
                'LastName' => (string) ($guest['last_name'] ?? ''),
                'ChildAge' => (string) ($guest['child_age'] ?? '0'),
            ];
        }

        $res = $this->client->post('Book', [
            'SearchKey' => $searchKey,
            'BookRQ' => [
                'BookingToken' => (string) $prebook['booking_token'],
                'TotalPrice' => $total,
                'InternalReference' => $internalRef,
                'HotelRooms' => $hotelRooms,
            ],
        ], ['Currency' => $currency]);

        if (! $res['ok']) {
            return [
                'ok' => false,
                'message' => $res['message'],
                'provider' => 'xconnect',
                'raw' => $res['raw'] ?? null,
            ];
        }

        $booked = $this->parser->parseBook(is_array($res['raw']) ? $res['raw'] : []);
        $lead = $guests[0] ?? [];

        $booking = [
            'provider' => 'xconnect',
            'booking_id' => $booked['booking_id'],
            'reference_no' => $booked['reference_no'],
            'internal_reference' => $booked['internal_reference'] ?: $internalRef,
            'currency' => $booked['currency'] ?: $currency,
            'total_price' => $total,
            'hotel_name' => (string) ($prebook['hotel_name'] ?? $solution['hotel_name'] ?? ''),
            'hotel_id' => (string) ($solution['hotel_id'] ?? ''),
            'check_in' => $solution['check_in'] ?? null,
            'check_out' => $solution['check_out'] ?? null,
            'nights' => $solution['nights'] ?? null,
            'city_id' => $solution['city_id'] ?? null,
            'nationality' => $solution['nationality'] ?? $prebook['nationality'] ?? null,
            'passenger_prefix' => (string) ($lead['prefix'] ?? ''),
            'passenger_first' => (string) ($lead['first_name'] ?? ''),
            'passenger_last' => (string) ($lead['last_name'] ?? ''),
            'passenger_email' => (string) ($params['email'] ?? ''),
            'passenger_phone' => (string) ($params['phone'] ?? ''),
            'guests' => $guests,
            'price_snapshot' => $priced,
            'raw_result' => array_merge(is_array($res['raw']) ? $res['raw'] : [], [
                'provider' => 'xconnect',
                'internal_reference' => $booked['internal_reference'] ?: $internalRef,
            ]),
        ];

        session(['xconnect.last_booking' => $booking]);

        return [
            'ok' => true,
            'message' => 'Hotel booked'.($booked['reference_no'] !== '' ? ' — '.$booked['reference_no'] : '').'.',
            'booking' => $booking,
            'provider' => 'xconnect',
            'raw' => $res['raw'] ?? null,
        ];
    }

    /**
     * @return array{ok: bool, message: string, detail?: array<string, mixed>, raw?: mixed}
     */
    public function bookingDetail(?string $internalReference = null, ?string $referenceNo = null, int|string|null $bookingId = null): array
    {
        $res = $this->client->post('BookingDetail', [
            'BookingDetailRQ' => [
                'BookingId' => (string) ($bookingId ?? '0'),
                'ReferenceNo' => (string) ($referenceNo ?? ''),
                'InternalReference' => (string) ($internalReference ?? ''),
            ],
        ]);

        if (! $res['ok']) {
            return $res;
        }

        return [
            'ok' => true,
            'message' => 'Booking detail loaded.',
            'detail' => is_array($res['raw']['BookingDetailRS'] ?? null) ? $res['raw']['BookingDetailRS'] : [],
            'raw' => $res['raw'],
        ];
    }

    /**
     * @return array{ok: bool, message: string, charges?: array<string, mixed>, cancel_code?: string, raw?: mixed}
     */
    public function checkCancellationCharges(?string $internalReference = null, ?string $referenceNo = null, int|string|null $bookingId = null): array
    {
        $res = $this->client->post('CheckHotelCancellationCharges', [
            'CheckHotelCancellationChargesRQ' => [
                'BookingId' => (int) ($bookingId ?? 0),
                'InternalReference' => (string) ($internalReference ?? ''),
                'ReferenceNo' => (string) ($referenceNo ?? ''),
            ],
        ]);

        if (! $res['ok']) {
            return $res;
        }

        $option = data_get($res['raw'], 'CheckHotelCancellationChargesRS.HotelOption', []);

        return [
            'ok' => true,
            'message' => 'Cancellation charges loaded.',
            'charges' => is_array($option) ? $option : [],
            'cancel_code' => (string) data_get($option, 'CancelCode', ''),
            'raw' => $res['raw'],
        ];
    }

    /**
     * @return array{ok: bool, message: string, raw?: mixed}
     */
    public function cancel(int|string $bookingId, string $cancelCode, int|string|null $bookingDetailId = null, string $reason = 'Customer request', bool $cancelAll = true): array
    {
        $payload = [
            'CancelRQ' => [
                'BookingId' => (int) $bookingId,
                'CancelCode' => $cancelCode,
                'CancelAll' => $cancelAll ? 1 : 0,
                'Reason' => $reason,
            ],
        ];
        if ($bookingDetailId !== null && $bookingDetailId !== '') {
            $payload['CancelRQ']['BookingDetailId'] = (int) $bookingDetailId;
        }

        $res = $this->client->post('CancelBooking', $payload);
        if (! $res['ok']) {
            return $res;
        }

        return [
            'ok' => true,
            'message' => 'Cancellation request completed.',
            'raw' => $res['raw'],
        ];
    }

    public function hasStoredSearch(): bool
    {
        return is_array(session('xconnect.last_search'));
    }

    public function hasStoredPrebook(): bool
    {
        return is_array(session('xconnect.last_prebook'));
    }
}
