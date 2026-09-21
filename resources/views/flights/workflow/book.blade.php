@extends('admin.layouts.main')

@section('title', 'Book flight')

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
            <li class="breadcrumb-item"><a href="{{ route($flightsRoutePrefix . '.flights.price.show') }}">Price</a></li>
            <li class="breadcrumb-item active">Book</li>
        </ol>
    </nav>

    <div class="flights-hero">
        <h1><i class="fas fa-user me-2"></i>Passenger details</h1>
        <p class="mb-0">Enter traveler information to create the reservation.</p>
    </div>

    @include('flights.partials.workflow-steps', [
        'workflowStep' => 'book',
        'flightPriceResult' => $flightPriceResult ?? null,
    ])

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm">
            <div>{{ session('error') }}</div>
            @if(session('travelport_last_error_reason'))
                <div class="small text-muted mt-1">Reason: {{ session('travelport_last_error_reason') }}</div>
            @endif
            @if(($showDevPanel ?? false) && session('travelport_last_error_excerpt'))
                <details class="mt-2 small">
                    <summary>Technical details (admin)</summary>
                    <pre class="mt-2 mb-0 small bg-light p-2 rounded" style="max-height:200px;overflow:auto;">{{ session('travelport_last_error_excerpt') }}</pre>
                </details>
            @endif
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @include('flights.partials.status')

    @php
        $bookProvider = $flightProvider ?? \App\Support\FlightProvider::fromResult($flightPriceResult ?? null);
    @endphp

    <div class="mb-3">
        @include('flights.partials.provider-badge', [
            'provider' => $bookProvider,
        ])
    </div>
    <p class="small text-muted mb-3">{{ \App\Support\FlightProvider::postBookFlowHint($bookProvider) }}</p>

    @include('frontend.partials.flight-price-summary', ['searchResult' => $flightPriceResult ?? null])

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body">
            <form method="POST" action="{{ route($flightsRoutePrefix . '.flights.book.store') }}">
                @csrf
                @include('flights.partials.passenger-book-fields', [
                    'flightPriceResult' => $flightPriceResult ?? null,
                    'flightProvider' => $bookProvider,
                    'passengerSlots' => $passengerSlots ?? [],
                    'bookInput' => $bookInput ?? [],
                    'compact' => true,
                ])
                <div class="mt-4 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary btn-sm" @disabled(!($providerReady ?? $travelportReady ?? false))>
                        <i class="fas fa-check me-1"></i>
                        @if($bookProvider === \App\Support\FlightProvider::DOWNTOWN_TRAVEL)
                            Confirm Downtown booking
                        @elseif($bookProvider === \App\Support\FlightProvider::SUNSPRING)
                            Confirm SunSpring booking
                        @else
                            Confirm booking &amp; view reservation
                        @endif
                    </button>
                    <a href="{{ route($flightsRoutePrefix . '.flights.price.show') }}" class="btn btn-outline-secondary btn-sm">Back to price</a>
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
@endsection
