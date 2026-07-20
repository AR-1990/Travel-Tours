@extends('admin.layouts.main')

@section('title', 'Search flights')

@push('styles')
    @include('flights.partials.styles')
@endpush

@section('content')
<div class="container-fluid flights-page">
    @include('flights.partials.nav')

    <nav aria-label="breadcrumb" class="mb-2">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route($flightsRoutePrefix . '.flights.index') }}">Flights</a></li>
            <li class="breadcrumb-item active">Search</li>
        </ol>
    </nav>

    <div class="flights-hero">
        <h1><i class="fas fa-plane-departure me-2"></i>Search flights</h1>
        <p class="mb-0">Same guided flow as the public site: Search → Price → Book → Reservation.</p>
    </div>

    @include('flights.partials.workflow-steps', ['workflowStep' => 'search', 'canBookFlights' => $canBookFlights ?? false])

    @if($hasPricingContext)
        <div class="alert alert-info border-0 shadow-sm py-2 small mb-3">
            <i class="fas fa-check-circle me-1"></i> Last search saved —
            <a href="{{ route($flightsRoutePrefix . '.flights.price.show') }}">view priced fare</a>
            @if($canBookFlights ?? false)
                or <a href="{{ route($flightsRoutePrefix . '.flights.book') }}">continue to book</a>.
            @endif
        </div>
    @endif

    @if(session('travelport.flight_booking') || session('travelport.last_booking') || session('travelport.last_reservation_id'))
        <div class="alert alert-success border-0 shadow-sm py-2 small mb-3">
            <i class="fas fa-ticket-alt me-1"></i>
            Active booking in session —
            <a href="{{ route($flightsRoutePrefix . '.flights.confirmation') }}">view reservation</a>
            or <a href="{{ route($flightsRoutePrefix . '.flights.reservations.index') }}">all reservations</a>.
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm">
            <ul class="mb-0 small">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    @include('flights.partials.status')

    @include('flights.partials.search-form')

    @if($searchResult)
        <section class="mb-4" aria-label="Search results">
            @if($searchResult['ok'])
                <span class="status-pill ok mb-3 d-inline-flex"><i class="fas fa-check"></i> {{ $searchResult['message'] }}</span>
            @else
                <span class="status-pill err mb-3"><i class="fas fa-times"></i> {{ $searchResult['message'] }}</span>
            @endif

            @include('flights.partials.result-cards')
        </section>

        @if(($showDevPanel ?? false) && !empty($searchResult['response_excerpt']))
            <details class="card border-0 shadow-sm">
                <summary class="card-body py-2 small text-muted" style="cursor:pointer">Developer: raw API response</summary>
                <div class="card-body pt-0 border-top">
                    @if(!empty($searchResult['trace_id']))
                        <p class="small mb-2">TraceId: <code>{{ $searchResult['trace_id'] }}</code> · HTTP {{ $searchResult['http_status'] ?? '—' }}</p>
                    @endif
                    <pre class="small text-break mb-0 bg-light p-2 rounded" style="max-height: 280px; overflow: auto;">{{ $searchResult['response_excerpt'] }}</pre>
                </div>
            </details>
        @endif
    @elseif($travelportReady)
        <div class="empty-results">
            <i class="fas fa-search fa-2x text-primary mb-3 opacity-50"></i>
            <h3 class="h6">Ready when you are</h3>
            <p class="text-muted small mb-0">Choose From / To with friendly airport names, set dates, then Search Now.</p>
        </div>
    @endif
</div>
@endsection

@push('scripts')
    @include('flights.partials.scripts-airports')
    @include('flights.partials.scripts-ajax')
@endpush
