<?php

namespace App\Http\Controllers\Concerns;

use App\Models\FlightReservation;
use App\Services\Travelport\TravelportAirService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait ManagesFlightReservations
{
    abstract protected function flightsRoutePrefix(): string;

    abstract protected function ensureFlightAccess(): void;

    public function reservationsIndex(Request $request)
    {
        $this->ensureFlightAccess();
        $this->ensureFlightSearchPermission();

        $query = FlightReservation::query()->latest('booked_at')->latest('id');

        $user = Auth::user();
        if ($user && $user->user_type === 'super_admin') {
            // all reservations
        } elseif ($user && $user->user_type === 'tenant_admin') {
            $query->where('tenant_id', $user->tenant_id);
        } elseif ($user && $user->user_type === 'sub_agent') {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere(function ($inner) use ($user) {
                        $inner->where('tenant_id', $user->tenant_id)->where('channel', 'subagent');
                    });
            });
        } else {
            $query->where('user_id', $user?->id);
        }

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($builder) use ($q) {
                $builder->where('universal_locator', 'like', "%{$q}%")
                    ->orWhere('air_reservation_locator', 'like', "%{$q}%")
                    ->orWhere('passenger_first', 'like', "%{$q}%")
                    ->orWhere('passenger_last', 'like', "%{$q}%")
                    ->orWhere('passenger_email', 'like', "%{$q}%")
                    ->orWhere('origin', 'like', "%{$q}%")
                    ->orWhere('destination', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $reservations = $query->paginate(20)->withQueryString();

        return view('flights.reservations.index', array_merge($this->travelportViewBase(), [
            'reservations' => $reservations,
            'filters' => [
                'q' => $request->input('q'),
                'status' => $request->input('status'),
            ],
        ]));
    }

    public function reservationsShow(int $id)
    {
        $this->ensureFlightAccess();
        $this->ensureFlightSearchPermission();

        $reservation = $this->findAccessibleReservation($id);

        return view('flights.reservations.show', array_merge($this->travelportViewBase(), [
            'reservation' => $reservation,
            'flightBooking' => $reservation->toWorkflowBookingArray(),
            'flightPriceResult' => $reservation->toPriceResultArray(),
            'searchInput' => [
                'origin' => $reservation->origin,
                'destination' => $reservation->destination,
                'departure_date' => optional($reservation->departure_date)?->format('Y-m-d'),
                'return_date' => optional($reservation->return_date)?->format('Y-m-d'),
                'adults' => $reservation->adults,
            ],
            'flightTicket' => [
                'ticket_numbers' => $reservation->ticket_numbers ?? [],
            ],
            'workflowStep' => $reservation->status === FlightReservation::STATUS_TICKETED ? 'done' : 'ticket',
            'canBookFlights' => $this->userCanBookFlights(),
            'ticketActionRoute' => route($this->flightsRoutePrefix().'.flights.reservations.ticket', $reservation),
        ]));
    }

    public function reservationsTicket(int $id, TravelportAirService $air)
    {
        $this->ensureFlightAccess();
        $this->ensureFlightBookPermission();

        $reservation = $this->findAccessibleReservation($id);

        $locators = array_filter([
            'universal_locator' => $reservation->universal_locator,
            'air_reservation_locator' => $reservation->air_reservation_locator,
        ]);

        if ($locators === []) {
            return redirect()
                ->route($this->flightsRoutePrefix().'.flights.reservations.show', $reservation)
                ->with('error', 'No booking locator found on this reservation.');
        }

        $result = $this->runIssueTicketFlow($air, $locators, $reservation);

        return redirect()
            ->route($this->flightsRoutePrefix().'.flights.reservations.show', $reservation)
            ->with(($result['ok'] ?? false) ? 'success' : 'error', $result['message'] ?? 'Ticketing complete.')
            ->with('travelport_last_error_reason', ($result['ok'] ?? false) ? null : ($result['technical_message'] ?? $result['message'] ?? null));
    }

    protected function findAccessibleReservation(int $id): FlightReservation
    {
        $reservation = FlightReservation::query()->findOrFail($id);
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        if ($user->user_type === 'super_admin') {
            return $reservation;
        }

        if ($user->user_type === 'tenant_admin' && (int) $reservation->tenant_id === (int) $user->tenant_id) {
            return $reservation;
        }

        if ($user->user_type === 'sub_agent') {
            if ((int) $reservation->user_id === (int) $user->id) {
                return $reservation;
            }
            if ((int) $reservation->tenant_id === (int) $user->tenant_id && $reservation->channel === 'subagent') {
                return $reservation;
            }
        }

        if ((int) $reservation->user_id === (int) $user->id) {
            return $reservation;
        }

        abort(403, 'You do not have access to this reservation.');
    }
}
