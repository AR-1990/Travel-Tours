@extends('admin.layouts.main')

@section('title', 'Reservations')

@push('styles')
    @include('flights.partials.styles')
@endpush

@section('content')
<div class="container-fluid flights-page">
    @include('flights.partials.nav')

    <nav aria-label="breadcrumb" class="mb-2">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route($flightsRoutePrefix . '.flights.index') }}">Flights</a></li>
            <li class="breadcrumb-item active">Reservations</li>
        </ol>
    </nav>

    <div class="flights-hero d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h1><i class="fas fa-folder-open me-2"></i>Reservations</h1>
            <p class="mb-0">Bookings created from Search → Price → Book. Open a file for passenger, itinerary, and ticketing.</p>
        </div>
        <a href="{{ route($flightsRoutePrefix . '.flights.search') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-search me-1"></i> New search
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <form method="GET" class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label small text-muted mb-1">Search</label>
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control form-control-sm" placeholder="Locator, passenger, route…">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="reserved" @selected(($filters['status'] ?? '') === 'reserved')>Reserved</option>
                        <option value="ticketed" @selected(($filters['status'] ?? '') === 'ticketed')>Ticketed</option>
                        <option value="cancelled" @selected(($filters['status'] ?? '') === 'cancelled')>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-primary btn-sm w-100">Filter</button>
                </div>
            </div>
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Booked</th>
                        <th>Passenger</th>
                        <th>Route</th>
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
                            <td class="small">{{ $reservation->airlineLabel() }}</td>
                            <td><code>{{ $reservation->universal_locator ?? $reservation->air_reservation_locator ?? '—' }}</code></td>
                            <td><span class="badge {{ $reservation->statusBadgeClass() }}">{{ $reservation->statusLabel() }}</span></td>
                            <td class="text-end">
                                <a href="{{ route($flightsRoutePrefix . '.flights.reservations.show', $reservation) }}" class="btn btn-sm btn-outline-primary">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                No reservations yet. <a href="{{ route($flightsRoutePrefix . '.flights.search') }}">Search and book</a> to create one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reservations->hasPages())
            <div class="card-footer bg-white">{{ $reservations->links() }}</div>
        @endif
    </div>
</div>
@endsection
