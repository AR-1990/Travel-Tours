@extends('admin.layouts.main')

@section('title', ($currentOperation['label'] ?? 'Air API') . ' — Flights')

@push('styles')
    @include('flights.partials.styles')
@endpush

@section('content')
<div class="container-fluid flights-page">
    @include('flights.partials.nav')

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route($flightsRoutePrefix . '.flights.index') }}">Flights</a></li>
            <li class="breadcrumb-item active">{{ $currentOperation['label'] ?? $operationKey }}</li>
        </ol>
    </nav>

    <div class="flights-hero py-3">
        <h1 class="h4 mb-1">{{ $currentOperation['label'] ?? $operationKey }}</h1>
        <p class="small mb-0 opacity-90">{{ $currentOperation['description'] ?? '' }}</p>
    </div>

    @include('flights.partials.operation-help')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    @include('flights.partials.status')

    <div class="flight-search-card">
        <form method="POST" action="{{ route($flightsRoutePrefix . '.flights.operation', ['operation' => $operationKey]) }}">
            @csrf
            <div class="row g-3 align-items-end">
                @include('flights.partials.operation-form')
            </div>
            <div class="mt-3 d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary" @disabled(!$travelportReady)>
                    <i class="fas fa-paper-plane me-2"></i>Send request
                </button>
                <a href="{{ route($flightsRoutePrefix . '.flights.index') }}" class="btn btn-outline-secondary">Flights</a>
            </div>
        </form>
    </div>

    @if($searchResult)
        <section class="mt-4">
            @if($searchResult['ok'])
                <span class="status-pill ok mb-3"><i class="fas fa-check"></i> {{ $searchResult['message'] }}</span>
            @else
                <span class="status-pill err mb-3"><i class="fas fa-times"></i> {{ $searchResult['message'] }}</span>
            @endif

            @if($operationKey === 'low_fare_search' && !empty($searchResult['solutions']))
                @include('flights.partials.result-cards')
            @elseif($operationKey === 'air_fare_display' && !empty($searchResult['solutions']))
                @include('flights.partials.fare-display-results')
            @elseif($operationKey === 'air_price')
                @include('flights.partials.air-price-results')
            @elseif(!empty($searchResult['universal_locator']) || !empty($searchResult['air_reservation_locator']))
                <div class="card-modern p-4">
                    @if(!empty($searchResult['universal_locator']))
                        <p class="mb-1"><strong>Universal Record:</strong> <code>{{ $searchResult['universal_locator'] }}</code></p>
                    @endif
                    @if(!empty($searchResult['air_reservation_locator']))
                        <p class="mb-1"><strong>Air reservation:</strong> <code>{{ $searchResult['air_reservation_locator'] }}</code></p>
                    @endif
                    @if($operationKey === 'air_create_reservation')
                        <form method="POST" action="{{ route($flightsRoutePrefix . '.flights.operation', ['operation' => 'air_ticketing']) }}" class="mt-3">
                            @csrf
                            <input type="hidden" name="universal_locator" value="{{ $searchResult['universal_locator'] ?? '' }}">
                            <input type="hidden" name="air_reservation_locator" value="{{ $searchResult['air_reservation_locator'] ?? '' }}">
                            <button type="submit" class="btn btn-primary btn-sm">Issue ticket (next step)</button>
                        </form>
                    @endif
                </div>
            @elseif(!empty($searchResult['ticket_numbers']))
                <div class="card-modern p-4">
                    <p class="mb-1"><strong>Ticket numbers:</strong></p>
                    <ul class="mb-0">
                        @foreach($searchResult['ticket_numbers'] as $number)
                            <li><code>{{ $number }}</code></li>
                        @endforeach
                    </ul>
                </div>
            @elseif($searchResult['ok'])
                <div class="card-modern p-4">
                    <p class="small text-muted mb-0">Response received. Expand raw output below for full XML.</p>
                </div>
            @endif

            @if(($showDevPanel ?? false) && !empty($searchResult['response_excerpt']))
                <details class="card border-0 shadow-sm mt-3">
                    <summary class="card-body py-2 small text-muted" style="cursor:pointer">Raw API response</summary>
                    <div class="card-body pt-0 border-top">
                        <pre class="small text-break mb-0 bg-light p-2 rounded" style="max-height: 360px; overflow: auto;">{{ $searchResult['response_excerpt'] }}</pre>
                    </div>
                </details>
            @elseif(!empty($searchResult['response_excerpt']))
                <details class="card border-0 shadow-sm mt-3">
                    <summary class="card-body py-2 small text-muted" style="cursor:pointer">Response details</summary>
                    <div class="card-body pt-0 border-top">
                        <pre class="small text-break mb-0 bg-light p-2 rounded" style="max-height: 280px; overflow: auto;">{{ $searchResult['response_excerpt'] }}</pre>
                    </div>
                </details>
            @endif
        </section>
    @endif
</div>
@endsection

@push('scripts')
    @include('flights.partials.scripts-airports')
    @include('flights.partials.scripts-ajax')
@endpush
