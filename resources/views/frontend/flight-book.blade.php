@extends('frontend.layouts.tavelo')

@section('title', 'Book flight | Wise Trust Travel & Tourism')

@section('content')
    <div class="hero-section">
        <div class="hero-single" style="background: url({{ asset('assets/img/hero/hero-1.jpg') }})">
            <div class="container">
                <div class="hero-content text-center">
                    <h1 class="hero-title">Passenger details</h1>
                    <p class="mb-0">Complete your booking</p>
                </div>
            </div>
        </div>
    </div>

    <div class="flight-booking flight-list pt-80 pb-120">
        <div class="container">
            @include('frontend.partials.flight-workflow-steps', [
                'workflowStep' => 'book',
                'flightPriceResult' => $flightPriceResult ?? null,
            ])

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @include('frontend.partials.flight-price-summary', ['searchResult' => $flightPriceResult ?? null])

            @php
                $bookProvider = $flightProvider ?? \App\Support\FlightProvider::fromResult($flightPriceResult ?? null);
            @endphp
            <div class="mb-3 mt-3">
                @include('flights.partials.provider-badge', [
                    'provider' => $bookProvider,
                ])
            </div>
            <p class="small text-muted mb-3">{{ \App\Support\FlightProvider::postBookFlowHint($bookProvider) }}</p>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('frontend.flights.book.store') }}">
                        @csrf
                        @include('flights.partials.passenger-book-fields', [
                            'flightPriceResult' => $flightPriceResult ?? null,
                            'flightProvider' => $bookProvider,
                            'passengerSlots' => $passengerSlots ?? [],
                            'bookInput' => $bookInput ?? [],
                            'compact' => false,
                        ])
                        <div class="mt-4 d-flex flex-wrap gap-2">
                            <button type="submit" class="theme-btn" @disabled(!($providerReady ?? $travelportReady ?? false))>
                                @if($bookProvider === \App\Support\FlightProvider::DOWNTOWN_TRAVEL)
                                    Confirm Downtown booking
                                @elseif($bookProvider === \App\Support\FlightProvider::SUNSPRING)
                                    Confirm SunSpring booking
                                @else
                                    Confirm booking &amp; view reservation
                                @endif
                                <i class="fas fa-check"></i>
                            </button>
                            <a href="{{ route('frontend.flights.price.show') }}" class="theme-btn theme-btn-outline">
                                Back to price<i class="fas fa-arrow-left"></i>
                            </a>
                        </div>
                        <p class="small text-muted mt-2 mb-0">
                            @if($bookProvider === \App\Support\FlightProvider::DOWNTOWN_TRAVEL)
                                After confirm you can issue Downtown tickets, refresh the order, cancel, void, or refund from the reservation page.
                            @elseif($bookProvider === \App\Support\FlightProvider::SUNSPRING)
                                After confirm you can issue the SunSpring e-ticket from the reservation page.
                            @else
                                After you confirm, reservation details open automatically.
                            @endif
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
