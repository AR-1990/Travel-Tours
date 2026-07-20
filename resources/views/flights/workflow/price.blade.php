@extends('admin.layouts.main')

@section('title', 'Flight price')

@push('styles')
    @include('flights.partials.styles')
@endpush

@section('content')
<div class="container-fluid flights-page">
    @include('flights.partials.nav')

    <nav aria-label="breadcrumb" class="mb-2">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route($flightsRoutePrefix . '.flights.index') }}">Flights</a></li>
            <li class="breadcrumb-item"><a href="{{ route($flightsRoutePrefix . '.flights.search') }}">Search</a></li>
            <li class="breadcrumb-item active">Price</li>
        </ol>
    </nav>

    <div class="flights-hero">
        <h1><i class="fas fa-tag me-2"></i>Confirm fare</h1>
        <p class="mb-0">Review pricing before creating a reservation.</p>
    </div>

    @include('flights.partials.workflow-steps', ['workflowStep' => 'price'])

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    @include('flights.partials.status')

    @include('frontend.partials.flight-price-summary', ['searchResult' => $flightPriceResult ?? null])

    @if(!empty($flightPriceResult['ok']))
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body">
                <h2 class="h6 mb-3">Continue booking</h2>
                <div class="d-flex flex-wrap gap-2">
                    @if($canBookFlights ?? false)
                        <a href="{{ route($flightsRoutePrefix . '.flights.book') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-user me-1"></i> Continue to book
                        </a>
                    @else
                        <span class="text-muted small align-self-center">You need <code>flights.book</code> permission to create reservations.</span>
                    @endif
                    <a href="{{ route($flightsRoutePrefix . '.flights.operation', ['operation' => 'air_fare_rules']) }}" class="btn btn-outline-secondary btn-sm">Fare rules</a>
                    <a href="{{ route($flightsRoutePrefix . '.flights.operation', ['operation' => 'seat_map']) }}" class="btn btn-outline-secondary btn-sm">Seat map</a>
                    <a href="{{ route($flightsRoutePrefix . '.flights.search') }}" class="btn btn-outline-secondary btn-sm">Back to search</a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
