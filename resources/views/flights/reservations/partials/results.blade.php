<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>Booked</th>
                <th>Passenger</th>
                <th>Route</th>
                <th>Provider</th>
                <th>Airline</th>
                <th>Locator</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($reservations as $reservation)
                <tr>
                    <td class="small text-muted">{{ optional($reservation->booked_at)->format('d M Y H:i') ?? '—' }}</td>
                    <td>
                        <div class="fw-semibold">{{ $reservation->passengerName() ?: '—' }}</div>
                        <div class="small text-muted">{{ $reservation->passenger_email }}</div>
                    </td>
                    <td>
                        <div>{{ $reservation->routeLabel() }}</div>
                        <div class="small text-muted">{{ optional($reservation->departure_date)->format('d M Y') }}</div>
                    </td>
                    <td>
                        @include('flights.partials.provider-badge', [
                            'provider' => $reservation->provider(),
                            'reservation' => $reservation,
                            'size' => 'sm',
                        ])
                    </td>
                    <td class="small">{{ $reservation->airlineLabel() }}</td>
                    <td><code>{{ $reservation->universal_locator ?? $reservation->air_reservation_locator ?? '—' }}</code></td>
                    <td><span class="badge {{ $reservation->statusBadgeClass() }}">{{ $reservation->statusLabel() }}</span></td>
                    <td class="text-end">
                        <a href="{{ route($flightsRoutePrefix . '.flights.reservations.show', $reservation) }}" class="btn btn-sm btn-outline-primary">Open</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">
                        No reservations yet. <a href="{{ route($flightsRoutePrefix . '.flights.search') }}">Search and book</a> to create one.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($reservations->hasPages())
    <div class="card-footer bg-white js-ajax-filter-pager">{{ $reservations->links() }}</div>
@endif
