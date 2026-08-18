<?php

namespace App\Services\Xconnect;

class XconnectHotelParser
{
    /**
     * Normalize Availability / AvailabilityWithCancellation into UI cards.
     *
     * @param  array<string, mixed>  $raw
     * @param  array<string, mixed>  $meta  search context (city, dates, rooms…)
     * @return list<array<string, mixed>>
     */
    public function parseAvailability(array $raw, array $meta = []): array
    {
        $rs = $raw['AvailabilityRS'] ?? null;
        if (! is_array($rs)) {
            return [];
        }

        $searchKey = (string) ($rs['SearchKey'] ?? '');
        $currency = (string) ($rs['Currency'] ?? $meta['currency'] ?? 'USD');
        $hotels = $rs['HotelResult'] ?? [];
        if (! is_array($hotels)) {
            return [];
        }

        $out = [];
        foreach ($hotels as $hotel) {
            if (! is_array($hotel)) {
                continue;
            }
            $hotelId = (string) ($hotel['HotelId'] ?? '');
            $startPrice = $hotel['StartPrice'] ?? null;
            $options = $hotel['HotelOption'] ?? [];
            if (! is_array($options)) {
                continue;
            }

            foreach ($options as $option) {
                if (! is_array($option)) {
                    continue;
                }
                $optionId = (string) ($option['HotelOptionId'] ?? '');
                $minPrice = $option['MinPrice'] ?? $startPrice;
                $roomGroups = $option['HotelRooms'] ?? [];
                $rooms = $this->flattenRooms($roomGroups);

                $key = $this->optionKey($searchKey, $hotelId, $optionId);
                $out[] = [
                    'key' => $key,
                    'provider' => 'xconnect',
                    'search_key' => $searchKey,
                    'hotel_id' => $hotelId,
                    'hotel_name' => (string) ($hotel['HotelName'] ?? $meta['hotel_names'][$hotelId] ?? ('Hotel #'.$hotelId)),
                    'hotel_option_id' => $optionId,
                    'is_combine_room' => (bool) ($option['IsCombineRoom'] ?? false),
                    'currency' => $currency,
                    'total_price' => is_numeric($minPrice) ? (float) $minPrice : null,
                    'start_price' => is_numeric($startPrice) ? (float) $startPrice : null,
                    'rooms' => $rooms,
                    'room_tokens' => array_values(array_map(fn ($r) => [
                        'RoomNo' => (string) ($r['RoomNo'] ?? ''),
                        'RoomToken' => (string) ($r['RoomToken'] ?? ''),
                    ], $rooms)),
                    'check_in' => $meta['check_in'] ?? null,
                    'check_out' => $meta['check_out'] ?? null,
                    'nights' => $meta['nights'] ?? null,
                    'city_id' => $meta['city_id'] ?? null,
                    'nationality' => $meta['nationality'] ?? null,
                    'raw_option' => $option,
                ];
            }
        }

        return $out;
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function flattenRooms(mixed $roomGroups): array
    {
        if (! is_array($roomGroups)) {
            return [];
        }

        $rooms = [];
        foreach ($roomGroups as $group) {
            if (is_array($group) && array_is_list($group) && isset($group[0]) && is_array($group[0]) && ! isset($group['RoomNo'])) {
                foreach ($group as $room) {
                    if (is_array($room)) {
                        $rooms[] = $room;
                    }
                }

                continue;
            }
            if (is_array($group)) {
                $rooms[] = $group;
            }
        }

        return $rooms;
    }

    public function optionKey(string $searchKey, string $hotelId, string $optionId): string
    {
        return hash('sha256', $searchKey.'|'.$hotelId.'|'.$optionId);
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    public function parsePreBook(array $raw): array
    {
        $rs = is_array($raw['PreBookRS'] ?? null) ? $raw['PreBookRS'] : [];
        $option = is_array($rs['HotelOption'] ?? null) ? $rs['HotelOption'] : [];
        $rooms = is_array($option['HotelRooms'] ?? null) ? $option['HotelRooms'] : [];
        $total = 0.0;
        foreach ($rooms as $room) {
            if (is_array($room) && is_numeric($room['Price'] ?? null)) {
                $total += (float) $room['Price'];
            }
        }

        return [
            'provider' => 'xconnect',
            'booking_token' => (string) ($option['BookingToken'] ?? ''),
            'hotel_name' => (string) ($option['HotelName'] ?? ''),
            'nationality' => (string) ($option['Nationality'] ?? ''),
            'currency' => (string) ($rs['Currency'] ?? 'USD'),
            'total_price' => $total > 0 ? $total : null,
            'rooms' => $rooms,
            'raw' => $raw,
        ];
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array{booking_id: int|string|null, reference_no: string, internal_reference: string, currency: string}
     */
    public function parseBook(array $raw): array
    {
        $rs = is_array($raw['BookRS'] ?? null) ? $raw['BookRS'] : [];

        return [
            'booking_id' => $rs['BookingId'] ?? null,
            'reference_no' => (string) ($rs['ReferenceNo'] ?? ''),
            'internal_reference' => (string) ($rs['InternalReference'] ?? ''),
            'currency' => (string) ($rs['Currency'] ?? 'USD'),
        ];
    }
}
