@extends('admin.layouts.main')

@section('title', 'Flights — Flight APIs')

@push('styles')
    @include('flights.partials.styles')
@endpush

@section('content')
<div class="container-fluid flights-page">
    @include('flights.partials.nav')

    <div class="flights-hero">
        <h1><i class="fas fa-plane me-2"></i>Flight APIs</h1>
        <p class="mb-0">Search, price, book, ticket, and manage air travel through Travelport — flight services only.</p>
    </div>

    @include('flights.partials.status')

    @if($hasPricingContext)
        <div class="alert alert-info border-0 shadow-sm py-2 small">
            <i class="fas fa-link me-1"></i> Your last <strong>Low Fare Search</strong> is saved — continue with <strong>Air Price</strong> or booking steps.
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card-modern h-100 border-primary border-opacity-25">
                <span class="badge bg-primary mb-2">Start here</span>
                <h2 class="h6">Low Fare Search</h2>
                <p class="text-muted small mb-2">Compare live bookable fares for your route and dates — the usual first step for selling a flight.</p>
                <a href="{{ route($flightsRoutePrefix . '.flights.search') }}" class="btn btn-primary btn-sm">Search fares</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-modern h-100">
                <h2 class="h6">Typical booking flow</h2>
                <ol class="small text-muted mb-0 ps-3">
                    <li><strong>Search</strong> — Low Fare Search or Availability</li>
                    <li><strong>Price</strong> — Air Price, then Fare Rules if needed</li>
                    <li><strong>Book</strong> — Create Reservation (PNR)</li>
                    <li><strong>Ticket</strong> — Issue Ticket and retrieve documents</li>
                </ol>
            </div>
        </div>
        @if($flightsRoutePrefix === 'admin')
        <div class="col-md-4">
            <div class="card-modern h-100">
                <h2 class="h6">API connection</h2>
                <p class="text-muted small mb-2">Username, password, target branch, and GDS are set by your administrator.</p>
                <a href="{{ route('admin.integrations.edit', ['slug' => 'travelport']) }}" class="btn btn-outline-primary btn-sm">Travelport settings</a>
            </div>
        </div>
        @endif
    </div>

    @foreach($operationGroups as $group)
        <section class="mb-4">
            <h2 class="h6 text-uppercase text-muted mb-1">
                <i class="fas {{ $group['group_icon'] }} me-2"></i>{{ $group['group_label'] }}
            </h2>
            @if(!empty($group['group_description']))
                <p class="small text-muted mb-3">{{ $group['group_description'] }}</p>
            @endif
            <div class="row g-3">
                @foreach($group['operations'] as $op)
                    @php
                        $status = $op['status'] ?? 'beta';
                        $badge = match($status) {
                            'ready' => 'success',
                            'beta' => 'warning',
                            default => 'secondary',
                        };
                        $badgeLabel = match($status) {
                            'ready' => 'Ready',
                            'beta' => 'Preview',
                            default => ucfirst($status),
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
                                    <span class="badge bg-{{ $badge }}">{{ $badgeLabel }}</span>
                                </div>
                                <p class="text-muted small mb-0">{{ $op['description'] ?? '' }}</p>
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
