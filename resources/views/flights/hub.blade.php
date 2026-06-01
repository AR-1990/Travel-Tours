@extends('admin.layouts.main')

@section('title', 'Flights — Travelport APIs')

@push('styles')
    @include('flights.partials.styles')
@endpush

@section('content')
<div class="container-fluid flights-page">
    @include('flights.partials.nav')

    <div class="flights-hero">
        <h1><i class="fas fa-plane me-2"></i>Air APIs</h1>
        <p>Full Travelport Universal API workflow — shop, price, book, ticket, and manage.</p>
    </div>

    @include('flights.partials.status')

    @if($hasPricingContext)
        <div class="alert alert-info border-0 shadow-sm py-2 small">
            <i class="fas fa-link me-1"></i> A priced itinerary from your last <strong>Low Fare Search</strong> is in session — you can run <strong>Air Price</strong> next.
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card-modern h-100 border-primary border-opacity-25">
                <span class="badge bg-primary mb-2">Start here</span>
                <h2 class="h6">Low Fare Search</h2>
                <p class="text-muted small">Main shopping — compare fares and select an option.</p>
                <a href="{{ route($flightsRoutePrefix . '.flights.search') }}" class="btn btn-primary btn-sm">Search fares</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-modern h-100">
                <h2 class="h6">Typical flow</h2>
                <ol class="small text-muted mb-3 ps-3">
                    <li>Low Fare Search or Availability</li>
                    <li>Air Price → Fare Rules</li>
                    <li>Create Reservation (PNR)</li>
                    <li>Ticketing & documents</li>
                </ol>
            </div>
        </div>
        @if($flightsRoutePrefix === 'admin')
        <div class="col-md-4">
            <div class="card-modern h-100">
                <h2 class="h6">Configuration</h2>
                <p class="text-muted small">Credentials, target branch, GDS, schema version.</p>
                <a href="{{ route('admin.integrations.edit', ['slug' => 'travelport']) }}" class="btn btn-outline-primary btn-sm">Travelport settings</a>
            </div>
        </div>
        @endif
    </div>

    @foreach($operationGroups as $group)
        <section class="mb-4">
            <h2 class="h6 text-uppercase text-muted mb-3">
                <i class="fas {{ $group['group_icon'] }} me-2"></i>{{ $group['group_label'] }}
            </h2>
            <div class="row g-3">
                @foreach($group['operations'] as $op)
                    @php
                        $status = $op['status'] ?? 'beta';
                        $badge = match($status) {
                            'ready' => 'success',
                            default => 'secondary',
                        };
                        $route = ($op['route'] ?? null) === 'search'
                            ? route($flightsRoutePrefix . '.flights.search')
                            : route($flightsRoutePrefix . '.flights.operation', ['operation' => $op['key']]);
                    @endphp
                    <div class="col-md-6 col-lg-4">
                        <a href="{{ $route }}" class="text-decoration-none text-dark">
                            <div class="card-modern h-100 flight-op-card">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <strong class="small">{{ $op['label'] }}</strong>
                                    <span class="badge bg-{{ $badge }}">{{ ucfirst($status) }}</span>
                                </div>
                                <p class="text-muted small mb-2">{{ $op['description'] ?? '' }}</p>
                                <code class="small text-muted">{{ $op['request'] ?? '' }}</code>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </section>
    @endforeach
</div>
@endsection

@push('styles')
<style>.flight-op-card:hover { border-color: #6366f1 !important; }</style>
@endpush
