@extends('admin.layouts.main')

@section('title', 'Hotel booking confirmed')

@section('content')
@php $prefix = $hotelsRoutePrefix ?? 'admin'; @endphp
<div class="container-fluid flights-page">
    @include('hotels.partials.nav')
    @include('hotels.partials.workflow-steps', ['workflowStep' => 'confirmation'])

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <h1 class="h3 mb-3 text-gray-800"><i class="fas fa-check-circle me-2 text-success"></i>Booking confirmed</h1>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <p><strong>Hotel:</strong> {{ $booking['hotel_name'] ?? $reservation?->hotel_name }}</p>
            <p><strong>Reference:</strong> {{ $booking['reference_no'] ?? $reservation?->reference_no ?? '—' }}</p>
            <p><strong>Internal ref:</strong> {{ $booking['internal_reference'] ?? $reservation?->internal_reference ?? '—' }}</p>
            <p><strong>Provider:</strong> {{ $reservation?->providerLabel() ?? \App\Support\HotelProvider::label($booking['provider'] ?? null) }}</p>
            <p><strong>Dates:</strong>
                {{ $booking['check_in'] ?? optional($reservation?->check_in)->toDateString() }}
                →
                {{ $booking['check_out'] ?? optional($reservation?->check_out)->toDateString() }}
            </p>
            <p class="mb-0"><strong>Total:</strong> {{ $booking['total_price'] ?? $reservation?->total_price }} {{ $booking['currency'] ?? $reservation?->currency }}</p>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2">
        @if($reservation)
            <a href="{{ route($prefix . '.hotels.reservations.show', $reservation->id) }}" class="btn btn-primary">View booking</a>
        @endif
        <a href="{{ route($prefix . '.hotels.search') }}" class="btn btn-outline-secondary">Search again</a>
        <a href="{{ route($prefix . '.hotels.reservations.index') }}" class="btn btn-outline-secondary">All reservations</a>
    </div>
</div>
@endsection
