@extends('frontend.layouts.tavelo')

@section('title', 'Booking confirmation — Tavelo')

@section('content')
    <div class="hero-section">
        <div class="hero-single" style="background: url({{ asset('assets/img/hero/hero-1.jpg') }})">
            <div class="container">
                <div class="hero-content text-center">
                    <h1 class="hero-title">
                        @if(!empty($flightTicket['ticket_numbers']))
                            Booking complete
                        @else
                            Booking confirmed
                        @endif
                    </h1>
                    <p class="mb-0">Your reservation details</p>
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

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3">Reservation</h2>
                    @if(!empty($flightBooking['universal_locator']))
                        <p class="mb-2"><strong>Universal Record:</strong> <code>{{ $flightBooking['universal_locator'] }}</code></p>
                    @endif
                    @if(!empty($flightBooking['air_reservation_locator']))
                        <p class="mb-2"><strong>Air reservation:</strong> <code>{{ $flightBooking['air_reservation_locator'] }}</code></p>
                    @endif
                    @if(!empty($flightSearchInput))
                        <p class="text-muted small mb-0">
                            {{ \App\Support\FlightDisplay::tripSummary(
                                $flightSearchInput['origin'] ?? null,
                                $flightSearchInput['destination'] ?? null,
                                $flightSearchInput['departure_date'] ?? null,
                                $flightSearchInput['return_date'] ?? null,
                                (int) ($flightSearchInput['adults'] ?? 1)
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
                    <div class="card-body p-4">
                        <h2 class="h5 mb-3 text-success"><i class="fas fa-ticket-alt me-2"></i>E-tickets issued</h2>
                        <ul class="mb-0">
                            @foreach($flightTicket['ticket_numbers'] as $number)
                                <li><code>{{ $number }}</code></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @else
                <div class="flight-next-actions mt-4">
                    <h3 class="h6 mb-3">Issue e-ticket</h3>
                    <p class="text-muted small">Your PNR is saved. Issue the ticket to complete the sale.</p>
                    <form method="POST" action="{{ route('frontend.flights.ticket') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="theme-btn" @disabled(!($travelportReady ?? false))>
                            Issue ticket<i class="fas fa-receipt"></i>
                        </button>
                    </form>
                </div>
            @endif

            <div class="mt-4 d-flex flex-wrap gap-2">
                <a href="{{ route('home') }}" class="theme-btn theme-btn-outline">New search<i class="fas fa-search"></i></a>
                <a href="{{ route('frontend.flights.hub') }}" class="theme-btn theme-btn-outline">Flight APIs<i class="fas fa-th"></i></a>
            </div>
        </div>
    </div>
@endsection
