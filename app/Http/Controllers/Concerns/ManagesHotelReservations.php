<?php

namespace App\Http\Controllers\Concerns;

use App\Models\HotelReservation;
use App\Services\DowntownTravel\DowntownTravelHotelService;
use App\Services\Xconnect\XconnectHotelService;
use App\Support\HotelProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait ManagesHotelReservations
{
    abstract protected function hotelsRoutePrefix(): string;

    abstract protected function ensureHotelAccess(): void;

    public function reservationsIndex(Request $request)
    {
        $this->ensureHotelAccess();

        $query = HotelReservation::query()->latest('booked_at')->latest('id');
        $this->scopeHotelReservationsQuery($query);

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($builder) use ($q) {
                $builder->where('reference_no', 'like', "%{$q}%")
                    ->orWhere('internal_reference', 'like', "%{$q}%")
                    ->orWhere('booking_id', 'like', "%{$q}%")
                    ->orWhere('hotel_name', 'like', "%{$q}%")
                    ->orWhere('city_id', 'like', "%{$q}%")
                    ->orWhere('passenger_first', 'like', "%{$q}%")
                    ->orWhere('passenger_last', 'like', "%{$q}%")
                    ->orWhere('passenger_email', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('provider')) {
            $query->where('provider', $request->input('provider'));
        }

        $reservations = $query->paginate(20)->withQueryString();

        $payload = [
            'reservations' => $reservations,
            'hotelsRoutePrefix' => $this->hotelsRoutePrefix(),
            'panelLabel' => method_exists($this, 'panelLabel') ? $this->panelLabel() : 'Admin',
            'filters' => [
                'q' => $request->input('q'),
                'status' => $request->input('status'),
                'provider' => $request->input('provider'),
            ],
            'providerOptions' => HotelProvider::options(),
        ];

        if ($request->ajax()) {
            return view('hotels.reservations.partials.results', $payload);
        }

        return view('hotels.reservations.index', $payload);
    }

    public function reservationsShow(int $id, XconnectHotelService $hotels, DowntownTravelHotelService $downtown)
    {
        $this->ensureHotelAccess();

        $reservation = $this->findAccessibleHotelReservation($id);
        $detail = null;

        if ($reservation->isXconnect() && ($reservation->internal_reference || $reservation->reference_no)) {
            try {
                $detailResult = $hotels->bookingDetail(
                    $reservation->internal_reference,
                    $reservation->reference_no,
                    $reservation->booking_id
                );
                if ($detailResult['ok'] ?? false) {
                    $detail = $detailResult['detail'] ?? null;
                    if (is_array($detail)) {
                        $reservation->provider_snapshot = $detail;
                        $reservation->save();
                    }
                }
            } catch (\Throwable) {
                // Supplier detail is best-effort in admin.
            }
        } elseif ($reservation->isDowntownTravel()) {
            try {
                $orderId = $downtown->resolveOrderId(
                    $reservation->booking_id,
                    $reservation->internal_reference,
                    is_array($reservation->raw_result) ? $reservation->raw_result : null
                );
                if ($orderId !== '') {
                    $detailResult = $downtown->getOrderDetails($orderId);
                    if ($detailResult['ok'] ?? false) {
                        $detail = $detailResult['order'] ?? null;
                        if (is_array($detail)) {
                            $reservation->provider_snapshot = $detail;
                            $reservation->save();
                        }
                    }
                }
            } catch (\Throwable) {
                // Supplier detail is best-effort in admin.
            }
        }

        return view('hotels.reservations.show', [
            'reservation' => $reservation,
            'detail' => $detail,
            'hotelsRoutePrefix' => $this->hotelsRoutePrefix(),
            'panelLabel' => method_exists($this, 'panelLabel') ? $this->panelLabel() : 'Admin',
            'cancelActionRoute' => route($this->hotelsRoutePrefix().'.hotels.reservations.cancel', $reservation),
        ]);
    }

    public function reservationsCancel(int $id, Request $request, XconnectHotelService $hotels, DowntownTravelHotelService $downtown)
    {
        $this->ensureHotelAccess();

        $reservation = $this->findAccessibleHotelReservation($id);
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
                $reservation->forceFill([
                    'status' => HotelReservation::STATUS_CANCELLED,
                    'cancelled_at' => now(),
                ])->save();

                return redirect()
                    ->route($this->hotelsRoutePrefix().'.hotels.reservations.show', $reservation)
                    ->with('success', 'Local hotel hold cancelled (no supplier order id).');
            }

            $cancel = $downtown->cancelOrder($orderId);
            if (! ($cancel['ok'] ?? false)) {
                return back()->with('error', $cancel['message'] ?? 'Downtown Travel hotel cancel failed.');
            }

            $reservation->forceFill([
                'status' => HotelReservation::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'raw_result' => array_merge(
                    is_array($reservation->raw_result) ? $reservation->raw_result : [],
                    ['cancel' => $cancel['raw'] ?? $cancel]
                ),
                'provider_snapshot' => $cancel['order'] ?? $reservation->provider_snapshot,
            ])->save();

            return redirect()
                ->route($this->hotelsRoutePrefix().'.hotels.reservations.show', $reservation)
                ->with('success', $cancel['message'] ?? 'Downtown Travel hotel booking cancelled.');
        }

        if (! $reservation->isXconnect()) {
            $reservation->forceFill([
                'status' => HotelReservation::STATUS_CANCELLED,
                'cancelled_at' => now(),
            ])->save();

            return redirect()
                ->route($this->hotelsRoutePrefix().'.hotels.reservations.show', $reservation)
                ->with('success', 'Hotel booking cancelled locally.');
        }

        $charges = $hotels->checkCancellationCharges(
            $reservation->internal_reference,
            $reservation->reference_no,
            $reservation->booking_id
        );
        if (! ($charges['ok'] ?? false) || empty($charges['cancel_code'])) {
            return back()->with('error', $charges['message'] ?? 'Could not load cancellation charges.');
        }

        $cancel = $hotels->cancel(
            (int) ($reservation->booking_id ?: data_get($charges, 'charges.BookingId', 0)),
            (string) $charges['cancel_code'],
            null,
            (string) $request->input('reason', 'Admin cancel'),
            true
        );

        if (! ($cancel['ok'] ?? false)) {
            return back()->with('error', $cancel['message'] ?? 'Cancel failed.');
        }

        $reservation->forceFill([
            'status' => HotelReservation::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'raw_result' => array_merge(
                is_array($reservation->raw_result) ? $reservation->raw_result : [],
                ['cancel' => $cancel['raw'] ?? null, 'charges' => $charges['charges'] ?? null]
            ),
        ])->save();

        return redirect()
            ->route($this->hotelsRoutePrefix().'.hotels.reservations.show', $reservation)
            ->with('success', 'Hotel booking cancelled.');
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\HotelReservation>  $query
     */
    protected function scopeHotelReservationsQuery($query): void
    {
        $user = Auth::user();
        if ($user && $user->user_type === 'super_admin') {
            return;
        }

        if ($user && $user->user_type === 'tenant_admin') {
            $query->where('tenant_id', $user->tenant_id);

            return;
        }

        if ($user && $user->user_type === 'sub_agent') {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere(function ($inner) use ($user) {
                        $inner->where('tenant_id', $user->tenant_id)->where('channel', 'subagent');
                    });
            });

            return;
        }

        $query->where('user_id', $user?->id);
    }

    protected function findAccessibleHotelReservation(int $id): HotelReservation
    {
        $query = HotelReservation::query()->whereKey($id);
        $this->scopeHotelReservationsQuery($query);
        $reservation = $query->first();
        if (! $reservation) {
            abort(404);
        }

        return $reservation;
    }
}
