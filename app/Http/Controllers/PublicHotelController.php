<?php

namespace App\Http\Controllers;

use App\Models\HotelReservation;
use App\Services\DowntownTravel\DowntownTravelHotelService;
use App\Services\DowntownTravel\DowntownTravelHotelsIntegrationConfig;
use App\Services\Xconnect\XconnectHotelService;
use App\Services\Xconnect\XconnectIntegrationConfig;
use App\Support\HotelProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PublicHotelController extends Controller
{
    public function hotelHub()
    {
        return view('frontend.hotels.hub', $this->hotelViewBase([
            'hotelSearchInput' => session('public.hotel_search.input', []),
        ]));
    }

    public function hotelSearch(Request $request, XconnectHotelService $xconnect, DowntownTravelHotelService $downtown)
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

        if (HotelProvider::isDowntownTravel()) {
            if (! $downtown->isReady()) {
                return redirect()
                    ->route('frontend.hotels.hub')
                    ->with('error', 'Downtown Travel Hotels is not configured yet. Ask the admin to set credentials under Integrations.');
            }

            $destination = strtolower(trim((string) ($data['destination'] ?? '')));
            if ($destination === '' || ! isset(DowntownTravelHotelService::DESTINATIONS[$destination])) {
                return redirect()
                    ->route('frontend.hotels.hub')
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
                    ->route('frontend.hotels.hub')
                    ->with('error', 'Hotel API is not configured yet. Ask the admin to set Xconnect Token + Base URL.');
            }

            $cityId = trim((string) ($data['city_id'] ?? ''));
            if ($cityId === '') {
                return redirect()
                    ->route('frontend.hotels.hub')
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
            'public.hotel_search' => [
                'input' => $data + [
                    'adults' => $adults,
                    'children' => $children,
                    'provider' => HotelProvider::current(),
                ],
                'result' => $result,
            ],
        ]);

        return redirect()
            ->route('frontend.hotels.results')
            ->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function hotelResults()
    {
        $stored = session('public.hotel_search');
        if (! is_array($stored)) {
            return redirect()->route('frontend.hotels.hub')->with('error', 'Search for hotels first.');
        }

        return view('frontend.hotels.results', $this->hotelViewBase([
            'hotelSearchInput' => $stored['input'] ?? [],
            'hotelSearchResult' => $stored['result'] ?? [],
        ]));
    }

    public function hotelPrebook(Request $request, XconnectHotelService $xconnect, DowntownTravelHotelService $downtown)
    {
        $data = $request->validate([
            'solution_key' => ['required', 'string', 'max:128'],
        ]);

        $result = HotelProvider::isDowntownTravel()
            ? $downtown->recheckAndPreBook($data['solution_key'])
            : $xconnect->recheckAndPreBook($data['solution_key']);

        if (! ($result['ok'] ?? false)) {
            return redirect()
                ->route('frontend.hotels.results')
                ->with('error', $result['message'] ?? 'Could not select this hotel rate.');
        }

        session(['public.hotel_prebook' => $result]);

        return redirect()
            ->route('frontend.hotels.book')
            ->with('success', $result['message']);
    }

    public function hotelBookShow()
    {
        $priced = session('public.hotel_prebook');
        if (! is_array($priced) || empty($priced['prebook'])) {
            return redirect()->route('frontend.hotels.hub')->with('error', 'Select a hotel rate first.');
        }

        $bookProvider = strtolower((string) ($priced['provider'] ?? ''));
        if (! in_array($bookProvider, HotelProvider::all(), true)) {
            $bookProvider = HotelProvider::current();
        }
        HotelProvider::set($bookProvider);

        return view('frontend.hotels.book', $this->hotelViewBase([
            'priced' => $priced,
            'hotelProvider' => $bookProvider,
        ]));
    }

    public function hotelBookStore(Request $request, XconnectHotelService $xconnect, DowntownTravelHotelService $downtown)
    {
        $priced = session('public.hotel_prebook');
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
                ->route('frontend.hotels.book')
                ->with('error', $result['message'] ?? 'Hotel booking failed.');
        }

        $booking = $result['booking'];
        $user = Auth::user();
        $reservation = HotelReservation::query()->create([
            'tenant_id' => $user?->tenant_id,
            'user_id' => Auth::id(),
            'channel' => 'public',
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
            'public.hotel_booking' => [
                'reservation_id' => $reservation->id,
                'booking' => $booking,
            ],
        ]);

        return redirect()
            ->route('frontend.hotels.confirmation')
            ->with('success', $result['message']);
    }

    public function hotelConfirmation()
    {
        $stored = session('public.hotel_booking');
        if (! is_array($stored)) {
            return redirect()->route('frontend.hotels.hub')->with('error', 'No booking confirmation in session.');
        }

        $reservation = null;
        if (! empty($stored['reservation_id'])) {
            $reservation = HotelReservation::query()->find($stored['reservation_id']);
        }

        return view('frontend.hotels.confirmation', [
            'booking' => $stored['booking'] ?? [],
            'reservation' => $reservation,
        ]);
    }

    public function reservationsIndex(Request $request)
    {
        $query = HotelReservation::query()->latest('booked_at')->latest('id');
        if (Auth::check()) {
            $query->where('user_id', Auth::id());
        } else {
            $query->where('channel', 'public')->limit(25);
        }

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($builder) use ($q) {
                $builder->where('reference_no', 'like', "%{$q}%")
                    ->orWhere('internal_reference', 'like', "%{$q}%")
                    ->orWhere('booking_id', 'like', "%{$q}%")
                    ->orWhere('hotel_name', 'like', "%{$q}%")
                    ->orWhere('city_id', 'like', "%{$q}%")
                    ->orWhere('passenger_first', 'like', "%{$q}%")
                    ->orWhere('passenger_last', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('provider')) {
            $query->where('provider', $request->input('provider'));
        }

        return view('frontend.hotels.reservations-index', [
            'reservations' => $query->paginate(20)->withQueryString(),
            'providerOptions' => HotelProvider::options(),
            'filters' => [
                'q' => $request->input('q'),
                'status' => $request->input('status'),
                'provider' => $request->input('provider'),
            ],
        ]);
    }

    public function reservationsShow(int $id, XconnectHotelService $hotels, DowntownTravelHotelService $downtown)
    {
        $reservation = HotelReservation::query()->findOrFail($id);
        $detail = null;

        if ($reservation->isXconnect() && ($reservation->internal_reference || $reservation->reference_no)) {
            $detailResult = $hotels->bookingDetail(
                $reservation->internal_reference,
                $reservation->reference_no,
                $reservation->booking_id
            );
            if ($detailResult['ok']) {
                $detail = $detailResult['detail'] ?? null;
                $reservation->provider_snapshot = $detail;
                $reservation->save();
            }
        } elseif ($reservation->isDowntownTravel()) {
            $orderId = $downtown->resolveOrderId(
                $reservation->booking_id,
                $reservation->internal_reference,
                is_array($reservation->raw_result) ? $reservation->raw_result : null
            );
            if ($orderId !== '') {
                $detailResult = $downtown->getOrderDetails($orderId);
                if ($detailResult['ok'] ?? false) {
                    $detail = $detailResult['order'] ?? null;
                    $reservation->provider_snapshot = $detail;
                    $reservation->save();
                }
            }
        }

        return view('frontend.hotels.reservations-show', [
            'reservation' => $reservation,
            'detail' => $detail,
        ]);
    }

    public function reservationsCancel(int $id, Request $request, XconnectHotelService $hotels, DowntownTravelHotelService $downtown)
    {
        $reservation = HotelReservation::query()->findOrFail($id);
        if ($reservation->isCancelled()) {
            return back()->with('error', 'This booking is already cancelled.');
        }

        if ($reservation->isDowntownTravel()) {
            $orderId = $downtown->resolveOrderId(
                $reservation->booking_id,
                $reservation->internal_reference,
                is_array($reservation->raw_result) ? $reservation->raw_result : null
            );
            if ($orderId === '' || str_starts_with(strtoupper($orderId), 'DTH-')) {
                $reservation->status = HotelReservation::STATUS_CANCELLED;
                $reservation->cancelled_at = now();
                $reservation->save();

                return back()->with('success', 'Local hotel hold cancelled (no supplier order id).');
            }

            $cancel = $downtown->cancelOrder($orderId);
            if (! ($cancel['ok'] ?? false)) {
                return back()->with('error', $cancel['message'] ?? 'Downtown Travel hotel cancel failed.');
            }

            $reservation->status = HotelReservation::STATUS_CANCELLED;
            $reservation->cancelled_at = now();
            $reservation->raw_result = array_merge(
                is_array($reservation->raw_result) ? $reservation->raw_result : [],
                ['cancel' => $cancel['raw'] ?? $cancel]
            );
            $reservation->provider_snapshot = $cancel['order'] ?? $reservation->provider_snapshot;
            $reservation->save();

            return back()->with('success', $cancel['message'] ?? 'Downtown Travel hotel booking cancelled.');
        }

        if (! $reservation->isXconnect()) {
            $reservation->status = HotelReservation::STATUS_CANCELLED;
            $reservation->cancelled_at = now();
            $reservation->save();

            return back()->with('success', 'Hotel booking cancelled locally.');
        }

        $charges = $hotels->checkCancellationCharges(
            $reservation->internal_reference,
            $reservation->reference_no,
            $reservation->booking_id
        );
        if (! $charges['ok'] || empty($charges['cancel_code'])) {
            return back()->with('error', $charges['message'] ?? 'Could not load cancellation charges.');
        }

        $cancel = $hotels->cancel(
            (int) ($reservation->booking_id ?: data_get($charges, 'charges.BookingId', 0)),
            (string) $charges['cancel_code'],
            null,
            (string) $request->input('reason', 'Customer request'),
            true
        );

        if (! $cancel['ok']) {
            return back()->with('error', $cancel['message']);
        }

        $reservation->status = HotelReservation::STATUS_CANCELLED;
        $reservation->cancelled_at = now();
        $reservation->raw_result = array_merge(
            is_array($reservation->raw_result) ? $reservation->raw_result : [],
            ['cancel' => $cancel['raw'] ?? null, 'charges' => $charges['charges'] ?? null]
        );
        $reservation->save();

        return back()->with('success', 'Hotel booking cancelled.');
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    protected function hotelViewBase(array $extra = []): array
    {
        return array_merge([
            'hotelReady' => HotelProvider::anyReady(),
            'providerReady' => HotelProvider::isReady(),
            'providerOptions' => HotelProvider::options(),
            'hotelProvider' => HotelProvider::current(),
            'downtownDestinations' => DowntownTravelHotelService::destinationOptions(),
            'xconnectReady' => XconnectIntegrationConfig::isReadyForHotels(),
            'downtownHotelsReady' => DowntownTravelHotelsIntegrationConfig::isReadyForHotels(),
        ], $extra);
    }
}
