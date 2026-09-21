<?php

namespace App\Http\Controllers\Concerns;

use App\Models\HotelReservation;
use App\Services\DowntownTravel\DowntownTravelHotelService;
use App\Services\DowntownTravel\DowntownTravelHotelsIntegrationConfig;
use App\Services\Xconnect\XconnectHotelService;
use App\Services\Xconnect\XconnectIntegrationConfig;
use App\Support\HotelProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

trait HandlesHotelWorkflow
{
    abstract protected function hotelsRoutePrefix(): string;

    abstract protected function ensureHotelAccess(): void;

    public function hub()
    {
        $this->ensureHotelAccess();

        return redirect()->route($this->hotelsRoutePrefix().'.hotels.search');
    }

    public function search(Request $request, XconnectHotelService $xconnect, DowntownTravelHotelService $downtown)
    {
        $this->ensureHotelAccess();

        if ($request->isMethod('post')) {
            return $this->runHotelSearch($request, $xconnect, $downtown);
        }

        $stored = session($this->hotelSearchSessionKey(), []);

        return view('hotels.search', $this->panelHotelViewBase([
            'hotelSearchInput' => is_array($stored['input'] ?? null) ? $stored['input'] : [],
            'hotelSearchResult' => is_array($stored['result'] ?? null) ? $stored['result'] : null,
            'workflowStep' => 'search',
        ]));
    }

    public function prebook(Request $request, XconnectHotelService $xconnect, DowntownTravelHotelService $downtown)
    {
        $this->ensureHotelAccess();

        $data = $request->validate([
            'solution_key' => ['required', 'string', 'max:128'],
        ]);

        $result = HotelProvider::isDowntownTravel()
            ? $downtown->recheckAndPreBook($data['solution_key'])
            : $xconnect->recheckAndPreBook($data['solution_key']);

        if (! ($result['ok'] ?? false)) {
            return redirect()
                ->route($this->hotelsRoutePrefix().'.hotels.search')
                ->with('error', $result['message'] ?? 'Could not select this hotel rate.');
        }

        session([$this->hotelPrebookSessionKey() => $result]);

        return redirect()
            ->route($this->hotelsRoutePrefix().'.hotels.book')
            ->with('success', $result['message']);
    }

    public function bookShow()
    {
        $this->ensureHotelAccess();

        $priced = session($this->hotelPrebookSessionKey());
        if (! is_array($priced) || empty($priced['prebook'])) {
            return redirect()
                ->route($this->hotelsRoutePrefix().'.hotels.search')
                ->with('error', 'Select a hotel rate first.');
        }

        $bookProvider = strtolower((string) ($priced['provider'] ?? ''));
        if (! in_array($bookProvider, HotelProvider::all(), true)) {
            $bookProvider = HotelProvider::current();
        }
        HotelProvider::set($bookProvider);

        return view('hotels.book', $this->panelHotelViewBase([
            'priced' => $priced,
            'hotelProvider' => $bookProvider,
            'workflowStep' => 'book',
        ]));
    }

    public function bookStore(Request $request, XconnectHotelService $xconnect, DowntownTravelHotelService $downtown)
    {
        $this->ensureHotelAccess();

        $priced = session($this->hotelPrebookSessionKey());
        $bookProvider = strtolower(trim((string) $request->input('provider', '')));
        if (! in_array($bookProvider, HotelProvider::all(), true) && is_array($priced)) {
            $bookProvider = strtolower((string) ($priced['provider'] ?? ''));
        }
        if (! in_array($bookProvider, HotelProvider::all(), true)) {
            $bookProvider = HotelProvider::current();
        }
        HotelProvider::set($bookProvider);

        $data = $request->validate(HotelProvider::bookValidationRules($bookProvider));

        $guests = [[
            'room_no' => '1',
            'is_lead' => '1',
            'pax_type' => 'Adult',
            'prefix' => $data['prefix'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'child_age' => '0',
        ]];

        if (($data['guest2_first'] ?? '') !== '' && ($data['guest2_last'] ?? '') !== '') {
            $guests[] = [
                'room_no' => '1',
                'is_lead' => '0',
                'pax_type' => 'Adult',
                'prefix' => 'Mr.',
                'first_name' => $data['guest2_first'],
                'last_name' => $data['guest2_last'],
                'child_age' => '0',
            ];
        }

        $bookParams = [
            'guests' => $guests,
            'email' => $data['email'] ?? '',
            'phone' => $data['phone'] ?? '',
            'prefix' => $data['prefix'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'nationality' => $data['nationality'] ?? null,
        ];

        $result = $bookProvider === HotelProvider::DOWNTOWN_TRAVEL_HOTELS
            ? $downtown->book($bookParams)
            : $xconnect->book($bookParams);

        if (! ($result['ok'] ?? false) || empty($result['booking'])) {
            return redirect()
                ->route($this->hotelsRoutePrefix().'.hotels.book')
                ->with('error', $result['message'] ?? 'Hotel booking failed.');
        }

        $booking = $result['booking'];
        $user = Auth::user();
        $reservation = HotelReservation::query()->create([
            'tenant_id' => $user?->tenant_id,
            'user_id' => Auth::id(),
            'channel' => $this->hotelBookingChannel(),
            'provider' => $bookProvider,
            'status' => HotelReservation::STATUS_CONFIRMED,
            'booking_id' => isset($booking['booking_id']) ? (string) $booking['booking_id'] : null,
            'reference_no' => $booking['reference_no'] ?? null,
            'internal_reference' => $booking['internal_reference'] ?? null,
            'hotel_id' => $booking['hotel_id'] ?? null,
            'hotel_name' => $booking['hotel_name'] ?? null,
            'city_id' => isset($booking['city_id']) ? (string) $booking['city_id'] : null,
            'nationality' => $booking['nationality'] ?? null,
            'check_in' => $booking['check_in'] ?? null,
            'check_out' => $booking['check_out'] ?? null,
            'nights' => $booking['nights'] ?? null,
            'rooms_count' => 1,
            'passenger_prefix' => $booking['passenger_prefix'] ?? null,
            'passenger_first' => $booking['passenger_first'] ?? null,
            'passenger_last' => $booking['passenger_last'] ?? null,
            'passenger_email' => $booking['passenger_email'] ?? null,
            'passenger_phone' => $booking['passenger_phone'] ?? null,
            'total_price' => isset($booking['total_price']) ? (string) $booking['total_price'] : null,
            'currency' => $booking['currency'] ?? null,
            'guests' => $booking['guests'] ?? $guests,
            'price_snapshot' => $booking['price_snapshot'] ?? null,
            'raw_result' => $booking['raw_result'] ?? null,
            'booked_at' => now(),
        ]);

        session([
            $this->hotelBookingSessionKey() => [
                'reservation_id' => $reservation->id,
                'booking' => $booking,
            ],
        ]);

        return redirect()
            ->route($this->hotelsRoutePrefix().'.hotels.confirmation')
            ->with('success', $result['message']);
    }

    public function confirmation()
    {
        $this->ensureHotelAccess();

        $stored = session($this->hotelBookingSessionKey());
        if (! is_array($stored) || empty($stored['reservation_id'])) {
            return redirect()
                ->route($this->hotelsRoutePrefix().'.hotels.search')
                ->with('error', 'No recent hotel booking in this session.');
        }

        $reservation = HotelReservation::query()->find($stored['reservation_id']);

        return view('hotels.confirmation', $this->panelHotelViewBase([
            'reservation' => $reservation,
            'booking' => $stored['booking'] ?? [],
            'workflowStep' => 'confirmation',
        ]));
    }

    protected function runHotelSearch(Request $request, XconnectHotelService $xconnect, DowntownTravelHotelService $downtown)
    {
        $providerIds = HotelProvider::all();
        $data = $request->validate([
            'provider' => ['nullable', Rule::in($providerIds)],
            'destination' => ['nullable', 'string', 'max:64'],
            'city_id' => ['nullable', 'string', 'max:64'],
            'check_in' => ['required', 'date'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'nationality' => ['nullable', 'string', 'max:80'],
            'currency' => ['nullable', 'string', 'max:8'],
            'adults' => ['nullable', 'integer', 'min:1', 'max:8'],
            'children' => ['nullable', 'integer', 'min:0', 'max:6'],
            'star_min' => ['nullable', 'integer', 'min:0', 'max:5'],
            'star_max' => ['nullable', 'integer', 'min:0', 'max:5'],
        ]);

        $provider = trim((string) ($data['provider'] ?? ''));
        if ($provider === '') {
            $hasCityId = trim((string) ($data['city_id'] ?? '')) !== '';
            $hasDestination = trim((string) ($data['destination'] ?? '')) !== '';
            if ($hasCityId && ! $hasDestination) {
                $provider = HotelProvider::XCONNECT;
            } elseif ($hasDestination && ! $hasCityId) {
                $provider = HotelProvider::DOWNTOWN_TRAVEL_HOTELS;
            } else {
                $provider = HotelProvider::current();
            }
        }
        HotelProvider::set($provider);

        $adults = (int) ($data['adults'] ?? 2);
        $children = (int) ($data['children'] ?? 0);
        $searchRoute = $this->hotelsRoutePrefix().'.hotels.search';

        if (HotelProvider::isDowntownTravel()) {
            if (! $downtown->isReady()) {
                return redirect()
                    ->route($searchRoute)
                    ->with('error', 'Downtown Travel Hotels is not configured. Set credentials under Admin → Integrations.');
            }

            $destination = strtolower(trim((string) ($data['destination'] ?? '')));
            if ($destination === '' || ! isset(DowntownTravelHotelService::DESTINATIONS[$destination])) {
                return redirect()
                    ->route($searchRoute)
                    ->withInput()
                    ->with('error', 'Choose a city destination for Downtown Travel hotel search.');
            }

            $result = $downtown->searchAvailability([
                'destination' => $destination,
                'check_in' => $data['check_in'],
                'check_out' => $data['check_out'],
                'adults' => $adults,
                'children' => $children,
            ]);
        } else {
            if (! $xconnect->isReady()) {
                return redirect()
                    ->route($searchRoute)
                    ->with('error', 'Xconnect Hotels is not configured. Set Token + Base URL under Integrations.');
            }

            $cityId = trim((string) ($data['city_id'] ?? ''));
            if ($cityId === '') {
                return redirect()
                    ->route($searchRoute)
                    ->withInput()
                    ->with('error', 'City ID is required for Xconnect hotel search.');
            }

            $result = $xconnect->searchAvailability([
                'city_id' => $cityId,
                'check_in' => $data['check_in'],
                'check_out' => $data['check_out'],
                'nationality' => $data['nationality'] ?? config('xconnect.default_nationality'),
                'currency' => $data['currency'] ?? config('xconnect.default_currency'),
                'star_min' => $data['star_min'] ?? 0,
                'star_max' => $data['star_max'] ?? 5,
                'rooms' => [[
                    'adults' => $adults,
                    'children' => $children,
                    'child_ages' => array_fill(0, $children, 5),
                ]],
            ]);
        }

        session([
            $this->hotelSearchSessionKey() => [
                'input' => $data + [
                    'adults' => $adults,
                    'children' => $children,
                    'provider' => HotelProvider::current(),
                ],
                'result' => $result,
            ],
        ]);

        return redirect()
            ->route($searchRoute)
            ->with(($result['ok'] ?? false) ? 'success' : 'error', $result['message'] ?? 'Hotel search finished.');
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    protected function panelHotelViewBase(array $extra = []): array
    {
        return array_merge([
            'hotelsRoutePrefix' => $this->hotelsRoutePrefix(),
            'panelLabel' => method_exists($this, 'panelLabel') ? $this->panelLabel() : 'Admin',
            'hotelReady' => HotelProvider::anyReady(),
            'providerReady' => HotelProvider::isReady(),
            'providerOptions' => HotelProvider::options(),
            'hotelProvider' => HotelProvider::current(),
            'downtownDestinations' => DowntownTravelHotelService::destinationOptions(),
            'xconnectReady' => XconnectIntegrationConfig::isReadyForHotels(),
            'downtownHotelsReady' => DowntownTravelHotelsIntegrationConfig::isReadyForHotels(),
        ], $extra);
    }

    protected function hotelBookingChannel(): string
    {
        return match ($this->hotelsRoutePrefix()) {
            'admin' => 'admin',
            'agent' => 'agent',
            'subagent' => 'subagent',
            default => 'public',
        };
    }

    protected function hotelSearchSessionKey(): string
    {
        return 'panel.hotel_search.'.$this->hotelsRoutePrefix();
    }

    protected function hotelPrebookSessionKey(): string
    {
        return 'panel.hotel_prebook.'.$this->hotelsRoutePrefix();
    }

    protected function hotelBookingSessionKey(): string
    {
        return 'panel.hotel_booking.'.$this->hotelsRoutePrefix();
    }
}
