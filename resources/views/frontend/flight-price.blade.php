@extends('frontend.layouts.tavelo')

@section('title', 'Flight Price — Tavelo')

@section('content')
    <div class="hero-section">
        <div class="hero-single" style="background: url({{ asset('assets/img/hero/hero-1.jpg') }})">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-12 mx-auto">
                        <div class="hero-content text-center">
                            <div class="hero-content-wrapper">
                                <h1 class="hero-title">Confirm your fare</h1>
                                <p>Review pricing before booking</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="flight-booking flight-list pt-80 pb-120">
        <div class="container">
            @include('frontend.partials.flight-workflow-steps', ['workflowStep' => 'price'])

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if(!($travelportReady ?? false))
                <div class="alert alert-warning">Flight pricing is not configured. Contact the agency.</div>
            @endif

            @include('frontend.partials.flight-price-summary', ['searchResult' => $flightPriceResult ?? null])

            @if(!empty($flightPriceResult['ok']))
                <div class="flight-next-actions mt-4">
                    <h3 class="h6 mb-3">Next steps</h3>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('frontend.flights.operation', ['operation' => 'air_fare_rules']) }}" class="theme-btn">
                            Fare rules<i class="fas fa-file-alt"></i>
                        </a>
                        <a href="{{ route('frontend.flights.operation', ['operation' => 'seat_map']) }}" class="theme-btn theme-btn-outline">
                            Seat map<i class="fas fa-chair"></i>
                        </a>
                        <a href="{{ route('frontend.flights.operation', ['operation' => 'air_create_reservation']) }}" class="theme-btn theme-btn2">
                            Create booking<i class="fas fa-ticket-alt"></i>
                        </a>
                        <a href="{{ route('frontend.flights.hub') }}" class="theme-btn theme-btn-outline">
                            All flight APIs<i class="fas fa-th"></i>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
