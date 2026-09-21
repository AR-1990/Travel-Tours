@extends('admin.layouts.main')

@section('title', 'Reservation #'.$reservation->id)

@push('styles')
    @include('flights.partials.styles')
@endpush

@section('content')
<div class="container-fluid panel-page flights-page">
    @include('flights.partials.nav')

    <nav aria-label="breadcrumb" class="mb-2">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route($flightsRoutePrefix . '.flights.index') }}">Flights</a></li>
            <li class="breadcrumb-item"><a href="{{ route($flightsRoutePrefix . '.flights.reservations.index') }}">Reservations</a></li>
            <li class="breadcrumb-item active">#{{ $reservation->id }}</li>
        </ol>
    </nav>

    @include('admin.partials.page-header', [
        'title' => 'Reservation details',
        'subtitle' => $reservation->routeLabel().' · '.($reservation->passengerName() ?: 'Passenger'),
        'icon' => 'fas fa-file-alt',
    ])

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
        'reservation' => $reservation,
        'flightBooking' => $flightBooking ?? [],
        'flightPriceResult' => $flightPriceResult ?? null,
        'searchInput' => $searchInput ?? [],
        'flightTicket' => $flightTicket ?? null,
        'gdsSnapshot' => $gdsSnapshot ?? null,
        'canBookFlights' => $canBookFlights ?? false,
        'travelportReady' => $travelportReady ?? false,
        'providerReady' => $providerReady ?? $travelportReady ?? false,
        'ticketActionRoute' => $ticketActionRoute ?? null,
        'retrieveActionRoute' => $retrieveActionRoute ?? null,
        'cancelActionRoute' => $cancelActionRoute ?? null,
        'voidActionRoute' => $voidActionRoute ?? null,
        'refundActionRoute' => $refundActionRoute ?? null,
        'ticketButtonClass' => 'btn btn-primary btn-sm',
        'compact' => true,
    ])

    <div class="mt-2 d-flex flex-wrap gap-2">
        <a href="{{ route($flightsRoutePrefix . '.flights.reservations.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-list me-1"></i> All reservations
        </a>
        <a href="{{ route($flightsRoutePrefix . '.flights.search') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-search me-1"></i> New search
        </a>
    </div>
</div>
@endsection
