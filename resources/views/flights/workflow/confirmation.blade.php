@extends('admin.layouts.main')

@section('title', 'Booking confirmation')

@push('styles')
    @include('flights.partials.styles')
@endpush

@section('content')
<div class="container-fluid flights-page">
    @include('flights.partials.nav')

    <nav aria-label="breadcrumb" class="mb-2">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route($flightsRoutePrefix . '.flights.index') }}">Flight APIs</a></li>
            <li class="breadcrumb-item active">Confirmation</li>
        </ol>
    </nav>

    <div class="flights-hero">
        <h1>
            <i class="fas fa-check-circle me-2"></i>
            @if(!empty($flightTicket['ticket_numbers']))
                Booking complete
            @else
                Booking confirmed
            @endif
        </h1>
        <p class="mb-0">Reservation and ticketing details for this session.</p>
    </div>

    @include('flights.partials.workflow-steps', ['workflowStep' => $workflowStep ?? 'ticket'])

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h2 class="h6 mb-3">Reservation</h2>
            @if(!empty($flightBooking['universal_locator']))
                <p class="mb-2"><strong>Universal Record:</strong> <code>{{ $flightBooking['universal_locator'] }}</code></p>
            @endif
            @if(!empty($flightBooking['air_reservation_locator']))
                <p class="mb-2"><strong>Air reservation:</strong> <code>{{ $flightBooking['air_reservation_locator'] }}</code></p>
            @endif
            @if(!empty($searchInput))
                <p class="text-muted small mb-0">
                    {{ \App\Support\FlightDisplay::tripSummary(
                        $searchInput['origin'] ?? null,
                        $searchInput['destination'] ?? null,
                        $searchInput['departure_date'] ?? null,
                        $searchInput['return_date'] ?? null,
                        (int) ($searchInput['adults'] ?? 1)
                    ) }}
                </p>
            @endif
        </div>
    </div>

    @if(!empty($flightPriceResult))
        @include('frontend.partials.flight-price-summary', ['searchResult' => $flightPriceResult])
    @endif

    @if(!empty($flightTicket['ticket_numbers']))
        <div class="card border-0 shadow-sm mt-4 border-success">
            <div class="card-body">
                <h2 class="h6 mb-3 text-success"><i class="fas fa-ticket-alt me-2"></i>E-tickets issued</h2>
                <ul class="mb-0">
                    @foreach($flightTicket['ticket_numbers'] as $number)
                        <li><code>{{ $number }}</code></li>
                    @endforeach
                </ul>
            </div>
        </div>
    @elseif($canBookFlights ?? false)
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body">
                <h2 class="h6 mb-2">Issue e-ticket</h2>
                <p class="text-muted small mb-3">The PNR is saved in this session. Issue the ticket to complete the sale.</p>
                <form method="POST" action="{{ route($flightsRoutePrefix . '.flights.ticket') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm" @disabled(!($travelportReady ?? false))>
                        <i class="fas fa-receipt me-1"></i> Issue ticket
                    </button>
                </form>
            </div>
        </div>
    @endif

    <div class="mt-4 d-flex flex-wrap gap-2">
        <a href="{{ route($flightsRoutePrefix . '.flights.search') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-search me-1"></i> New search
        </a>
        <a href="{{ route($flightsRoutePrefix . '.flights.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-th me-1"></i> Flight APIs
        </a>
    </div>
</div>
@endsection
