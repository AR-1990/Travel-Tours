@extends('admin.layouts.main')

@section('title', 'Reservations')

@push('styles')
    @include('flights.partials.styles')
@endpush

@section('content')
<div class="container-fluid flights-page">
    @include('flights.partials.nav')

    <nav aria-label="breadcrumb" class="mb-2">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route($flightsRoutePrefix . '.flights.index') }}">Flights</a></li>
            <li class="breadcrumb-item active">Reservations</li>
        </ol>
    </nav>

    <div class="flights-hero d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h1><i class="fas fa-folder-open me-2"></i>Reservations</h1>
            <p class="mb-0">Bookings created from Search → Price → Book. Open a file for passenger, itinerary, and ticketing.</p>
        </div>
        <a href="{{ route($flightsRoutePrefix . '.flights.search') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-search me-1"></i> New search
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <form method="GET"
        action="{{ route($flightsRoutePrefix . '.flights.reservations.index') }}"
        class="card border-0 shadow-sm mb-3 js-ajax-filter-form"
        data-results="#js-ajax-filter-results">
        <div class="card-body py-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Search</label>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control form-control-sm" placeholder="Locator, passenger, route…" autocomplete="off">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="reserved" @selected(($filters['status'] ?? '') === 'reserved')>Reserved</option>
                        <option value="ticketed" @selected(($filters['status'] ?? '') === 'ticketed')>Ticketed</option>
                        <option value="cancelled" @selected(($filters['status'] ?? '') === 'cancelled')>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Provider</label>
                    <select name="provider" class="form-select form-select-sm">
                        <option value="">All providers</option>
                        @foreach(($flightProviders ?? \App\Support\FlightProvider::options()) as $option)
                            <option value="{{ $option['id'] }}" @selected(($filters['provider'] ?? '') === $option['id'])>
                                {{ $option['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-primary btn-sm w-100">Filter</button>
                </div>
            </div>
        </div>
    </form>

    <div class="card border-0 shadow-sm" id="js-ajax-filter-results" data-ajax-filter-results>
        @include('flights.reservations.partials.results', [
            'reservations' => $reservations,
            'flightsRoutePrefix' => $flightsRoutePrefix,
        ])
    </div>
</div>
@endsection

@push('scripts')
    @include('admin.partials.ajax-filters')
@endpush
