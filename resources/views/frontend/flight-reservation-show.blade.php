@extends('frontend.layouts.tavelo')

@section('title', 'Reservation details — Tavelo')

@section('content')
    <div class="hero-section">
        <div class="hero-single" style="background: url({{ asset('assets/img/hero/hero-1.jpg') }})">
            <div class="container">
                <div class="hero-content text-center">
                    <h1 class="hero-title">Reservation details</h1>
                    <p class="mb-0">{{ $reservation->routeLabel() }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="flight-booking flight-list pt-80 pb-120">
        <div class="container">
            @include('frontend.partials.flight-workflow-steps', ['workflowStep' => $workflowStep ?? 'ticket'])

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @include('flights.partials.reservation-detail', [
                'flightBooking' => $flightBooking ?? [],
                'flightPriceResult' => $flightPriceResult ?? null,
                'flightSearchInput' => $flightSearchInput ?? [],
                'flightTicket' => $flightTicket ?? null,
                'canBookFlights' => true,
                'travelportReady' => $travelportReady ?? false,
                'ticketActionRoute' => $ticketActionRoute ?? route('frontend.flights.reservations.ticket', $reservation),
                'ticketButtonClass' => 'theme-btn',
            ])

            <div class="mt-4 d-flex flex-wrap gap-2">
                <a href="{{ route('frontend.flights.reservations.index') }}" class="theme-btn theme-btn-outline">All reservations<i class="fas fa-list"></i></a>
                <a href="{{ route('home') }}" class="theme-btn theme-btn-outline">New search<i class="fas fa-search"></i></a>
            </div>
        </div>
    </div>
@endsection
