@extends('frontend.layouts.tavelo')

@section('title', 'Hotel Booking '.$reservation->reference_no)

@section('content')
<div class="pt-80 pb-80">
    <div class="container" style="max-width: 800px;">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <h2 class="mb-3">{{ $reservation->hotel_name }}</h2>
        <div class="border rounded p-4 bg-white mb-3">
            <p><strong>Status:</strong> {{ $reservation->status }}</p>
            <p><strong>Provider:</strong> {{ $reservation->providerLabel() }}</p>
            <p><strong>Reference:</strong> {{ $reservation->reference_no }}</p>
            <p><strong>Internal:</strong> {{ $reservation->internal_reference }}</p>
            <p><strong>Booking ID:</strong> {{ $reservation->booking_id }}</p>
            <p><strong>Dates:</strong> {{ optional($reservation->check_in)->toDateString() }} → {{ optional($reservation->check_out)->toDateString() }}</p>
            <p><strong>Lead:</strong> {{ $reservation->passenger_prefix }} {{ $reservation->passenger_first }} {{ $reservation->passenger_last }}</p>
            <p class="mb-0"><strong>Total:</strong> {{ $reservation->total_price }} {{ $reservation->currency }}</p>
        </div>

        @if($detail)
            <details class="mb-3">
                <summary>Supplier booking detail</summary>
                <pre class="bg-light border rounded p-3 small mt-2">{{ json_encode($detail, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </details>
        @endif

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('frontend.hotels.reservations.index') }}" class="btn btn-outline-secondary">All bookings</a>
            @unless($reservation->isCancelled())
                <form method="POST" action="{{ route('frontend.hotels.reservations.cancel', $reservation->id) }}" onsubmit="return confirm('Cancel this hotel booking?');">
                    @csrf
                    <input type="hidden" name="reason" value="Customer request">
                    <button type="submit" class="btn btn-outline-danger">Cancel booking</button>
                </form>
            @endunless
        </div>
    </div>
</div>
@endsection
