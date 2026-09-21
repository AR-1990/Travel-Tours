<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>Booked</th>
                <th>Guest</th>
                <th>Hotel</th>
                <th>Dates</th>
                <th>Provider</th>
                <th>Reference</th>
                <th>Total</th>
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
                        <div class="fw-semibold">{{ $reservation->hotel_name ?: '—' }}</div>
                        @if($reservation->city_id)
                            <div class="small text-muted">{{ $reservation->city_id }}</div>
                        @endif
                    </td>
                    <td class="small">{{ $reservation->datesLabel() }}</td>
                    <td>
                        @php $badge = \App\Support\HotelProvider::badge($reservation->provider); @endphp
                        <span class="{{ $badge['css'] }} provider-badge--sm">{{ $badge['label'] }}</span>
                    </td>
                    <td><code>{{ $reservation->referenceLabel() }}</code></td>
                    <td class="small">{{ $reservation->total_price }} {{ $reservation->currency }}</td>
                    <td><span class="badge {{ $reservation->statusBadgeClass() }}">{{ $reservation->statusLabel() }}</span></td>
                    <td class="text-end">
                        <a href="{{ route($hotelsRoutePrefix . '.hotels.reservations.show', $reservation) }}" class="btn btn-sm btn-outline-primary">Open</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">No hotel reservations yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($reservations->hasPages())
    <div class="card-footer bg-white js-ajax-filter-pager">{{ $reservations->links() }}</div>
@endif
