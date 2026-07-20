@extends('frontend.layouts.tavelo')

@section('title', 'My reservations — Tavelo')

@section('content')
    <div class="hero-section">
        <div class="hero-single" style="background: url({{ asset('assets/img/hero/hero-1.jpg') }})">
            <div class="container">
                <div class="hero-content text-center">
                    <h1 class="hero-title">Reservations</h1>
                    <p class="mb-0">Your booking files</p>
                </div>
            </div>
        </div>
    </div>

    <div class="flight-booking flight-list pt-80 pb-120">
        <div class="container">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form method="GET" class="mb-4">
                <div class="row g-2">
                    <div class="col-md-8">
                        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control" placeholder="Search by locator, name, or route">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="theme-btn w-100">Search</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive bg-white shadow-sm rounded">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Booked</th>
                            <th>Passenger</th>
                            <th>Route</th>
                            <th>Locator</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reservations as $reservation)
                            <tr>
                                <td>{{ optional($reservation->booked_at)->format('d M Y') ?? '—' }}</td>
                                <td>{{ $reservation->passengerName() ?: '—' }}</td>
                                <td>
                                    {{ $reservation->routeLabel() }}
                                    <div class="small text-muted">{{ optional($reservation->departure_date)->format('d M Y') }}</div>
                                </td>
                                <td><code>{{ $reservation->universal_locator ?? $reservation->air_reservation_locator ?? '—' }}</code></td>
                                <td><span class="badge {{ $reservation->statusBadgeClass() }}">{{ $reservation->statusLabel() }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('frontend.flights.reservations.show', $reservation) }}" class="theme-btn theme-btn2">Open</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    No reservations yet. <a href="{{ route('home') }}">Search flights</a> to book.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($reservations->hasPages())
                <div class="mt-3">{{ $reservations->links() }}</div>
            @endif
        </div>
    </div>
@endsection
