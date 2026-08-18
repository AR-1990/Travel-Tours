@extends('frontend.layouts.tavelo')

@section('title', 'Hotel Booking Confirmed')

@section('content')
<div class="pt-80 pb-80">
    <div class="container" style="max-width: 720px;">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <h2 class="mb-3">Booking confirmed</h2>
        <div class="border rounded p-4 bg-white">
            <p><strong>Hotel:</strong> {{ $booking['hotel_name'] ?? $reservation?->hotel_name }}</p>
            <p><strong>Reference:</strong> {{ $booking['reference_no'] ?? $reservation?->reference_no ?? '—' }}</p>
            <p><strong>Internal ref:</strong> {{ $booking['internal_reference'] ?? $reservation?->internal_reference ?? '—' }}</p>
            <p><strong>Dates:</strong> {{ $booking['check_in'] ?? $reservation?->check_in?->toDateString() }} → {{ $booking['check_out'] ?? $reservation?->check_out?->toDateString() }}</p>
            <p class="mb-0"><strong>Total:</strong> {{ $booking['total_price'] ?? $reservation?->total_price }} {{ $booking['currency'] ?? $reservation?->currency }}</p>
        </div>

        <div class="mt-4 d-flex gap-2">
            @if($reservation)
                <a href="{{ route('frontend.hotels.reservations.show', $reservation->id) }}" class="theme-btn">View booking</a>
            @endif
            <a href="{{ route('frontend.hotels.hub') }}" class="btn btn-outline-secondary">Search again</a>
        </div>
    </div>
</div>
@endsection
