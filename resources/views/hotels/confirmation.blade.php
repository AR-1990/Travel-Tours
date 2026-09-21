@extends('admin.layouts.main')

@section('title', 'Hotel booking confirmed')

@section('content')
@php $prefix = $hotelsRoutePrefix ?? 'admin'; @endphp
<div class="container-fluid panel-page">
    @include('hotels.partials.nav')
    @include('hotels.partials.workflow-steps', ['workflowStep' => 'confirmation'])
    @include('admin.partials.page-header', [
        'title' => 'Booking confirmed',
        'subtitle' => 'Supplier booking is saved. Open the reservation file for details or cancel.',
        'icon' => 'fas fa-check-circle',
    ])
    @include('admin.partials.flash')

    <div class="panel-surface mb-4">
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

    <div class="d-flex flex-wrap gap-2">
        @if($reservation)
            <a href="{{ route($prefix . '.hotels.reservations.show', $reservation->id) }}" class="btn btn-primary">View booking</a>
        @endif
        <a href="{{ route($prefix . '.hotels.search') }}" class="btn btn-outline-secondary">Search again</a>
        <a href="{{ route($prefix . '.hotels.reservations.index') }}" class="btn btn-outline-secondary">All reservations</a>
    </div>
</div>
@endsection
