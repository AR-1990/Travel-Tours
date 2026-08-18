<?php

namespace App\Http\Controllers;

use App\Models\HotelReservation;
use App\Services\Xconnect\XconnectHotelService;
use App\Support\HotelProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PublicHotelController extends Controller
{
    public function hotelHub()
    {
        return view('frontend.hotels.hub', [
            'hotelReady' => HotelProvider::isReady(),
            'providerOptions' => HotelProvider::options(),
            'hotelSearchInput' => session('public.hotel_search.input', []),
        ]);
    }

    public function hotelSearch(Request $request, XconnectHotelService $hotels)
    {
        $data = $request->validate([
            'provider' => ['nullable', Rule::in(['xconnect'])],
            'city_id' => ['required', 'string', 'max:64'],
            'check_in' => ['required', 'date'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'nationality' => ['nullable', 'string', 'max:80'],
            'currency' => ['nullable', 'string', 'max:8'],
            'adults' => ['nullable', 'integer', 'min:1', 'max:8'],
            'children' => ['nullable', 'integer', 'min:0', 'max:6'],
            'star_min' => ['nullable', 'integer', 'min:0', 'max:5'],
            'star_max' => ['nullable', 'integer', 'min:0', 'max:5'],
        ]);

        HotelProvider::set((string) ($data['provider'] ?? HotelProvider::XCONNECT));

        if (! $hotels->isReady()) {
            return redirect()
                ->route('frontend.hotels.hub')
                ->with('error', 'Hotel API is not configured yet. Ask the admin to set Xconnect Token + Base URL.');
        }

        $adults = (int) ($data['adults'] ?? 2);
        $children = (int) ($data['children'] ?? 0);
        $result = $hotels->searchAvailability([
            'city_id' => $data['city_id'],
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

        session([
            'public.hotel_search' => [
                'input' => $data + ['adults' => $adults, 'children' => $children],
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

        return view('frontend.hotels.results', [
            'hotelSearchInput' => $stored['input'] ?? [],
            'hotelSearchResult' => $stored['result'] ?? [],
            'hotelReady' => HotelProvider::isReady(),
        ]);
    }

    public function hotelPrebook(Request $request, XconnectHotelService $hotels)
    {
        $data = $request->validate([
            'solution_key' => ['required', 'string', 'max:128'],
        ]);

        $result = $hotels->recheckAndPreBook($data['solution_key']);
        if (! $result['ok']) {
            return redirect()
                ->route('frontend.hotels.results')
                ->with('error', $result['message']);
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

        return view('frontend.hotels.book', [
            'priced' => $priced,
            'hotelReady' => HotelProvider::isReady(),
        ]);
    }

    public function hotelBookStore(Request $request, XconnectHotelService $hotels)
    {
        $data = $request->validate([
            'prefix' => ['required', 'string', 'max:10'],
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => ['nullable', 'email', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'guest2_first' => ['nullable', 'string', 'max:80'],
            'guest2_last' => ['nullable', 'string', 'max:80'],
        ]);

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

        $result = $hotels->book([
            'guests' => $guests,
            'email' => $data['email'] ?? '',
            'phone' => $data['phone'] ?? '',
        ]);

        if (! $result['ok'] || empty($result['booking'])) {
            return redirect()
                ->route('frontend.hotels.book')
                ->with('error', $result['message']);
        }

        $booking = $result['booking'];
        $reservation = HotelReservation::query()->create([
            'user_id' => Auth::id(),
            'channel' => 'public',
            'provider' => 'xconnect',
            'status' => 'confirmed',
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

    public function reservationsIndex()
    {
        $query = HotelReservation::query()->latest();
        if (Auth::check()) {
            $query->where('user_id', Auth::id());
        } else {
            $query->where('channel', 'public')->limit(25);
        }

        return view('frontend.hotels.reservations-index', [
            'reservations' => $query->paginate(20),
        ]);
    }

    public function reservationsShow(int $id, XconnectHotelService $hotels)
    {
        $reservation = HotelReservation::query()->findOrFail($id);
        $detail = null;
        if ($reservation->internal_reference || $reservation->reference_no) {
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
        }

        return view('frontend.hotels.reservations-show', [
            'reservation' => $reservation,
            'detail' => $detail,
        ]);
    }

    public function reservationsCancel(int $id, Request $request, XconnectHotelService $hotels)
    {
        $reservation = HotelReservation::query()->findOrFail($id);
        if ($reservation->isCancelled()) {
            return back()->with('error', 'This booking is already cancelled.');
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

        $reservation->status = 'cancelled';
        $reservation->cancelled_at = now();
        $reservation->raw_result = array_merge(
            is_array($reservation->raw_result) ? $reservation->raw_result : [],
            ['cancel' => $cancel['raw'] ?? null, 'charges' => $charges['charges'] ?? null]
        );
        $reservation->save();

        return back()->with('success', 'Hotel booking cancelled.');
    }
}
