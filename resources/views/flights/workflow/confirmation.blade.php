@extends('admin.layouts.main')

@section('title', 'Reservation details')

@push('styles')
    @include('flights.partials.styles')
@endpush

@section('content')
<div class="container-fluid flights-page">
    @include('flights.partials.nav')

    <nav aria-label="breadcrumb" class="mb-2">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route($flightsRoutePrefix . '.flights.index') }}">Flights</a></li>
            <li class="breadcrumb-item active">Reservation</li>
        </ol>
    </nav>

    <div class="flights-hero">
        <h1><i class="fas fa-file-alt me-2"></i>Reservation details</h1>
        <p class="mb-0">Booking file for this session — route, passenger, fare, and ticketing.</p>
    </div>

    @include('flights.partials.workflow-steps', ['workflowStep' => $workflowStep ?? 'ticket'])

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm">
            <div>{{ session('error') }}</div>
            @if(session('travelport_last_error_reason'))
                <div class="small text-muted mt-1">Reason: {{ session('travelport_last_error_reason') }}</div>
            @endif
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @include('flights.partials.reservation-detail', [
        'flightBooking' => $flightBooking ?? [],
        'flightPriceResult' => $flightPriceResult ?? null,
        'searchInput' => $searchInput ?? [],
        'flightTicket' => $flightTicket ?? null,
        'canBookFlights' => $canBookFlights ?? false,
        'travelportReady' => $travelportReady ?? false,
        'ticketActionRoute' => route($flightsRoutePrefix . '.flights.ticket'),
        'ticketButtonClass' => 'btn btn-primary btn-sm',
        'compact' => true,
    ])

    <div class="mt-2 d-flex flex-wrap gap-2">
        <a href="{{ route($flightsRoutePrefix . '.flights.search') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-search me-1"></i> New search
        </a>
        <a href="{{ route($flightsRoutePrefix . '.flights.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-th me-1"></i> Flights
        </a>
    </div>
</div>
@endsection
