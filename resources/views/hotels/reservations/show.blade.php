@extends('admin.layouts.main')

@section('title', 'Hotel Reservation '.$reservation->referenceLabel())

@section('content')
<div class="container-fluid panel-page">
    @include('hotels.partials.nav')

    <div class="panel-breadcrumb">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item">
                    <a href="{{ route($hotelsRoutePrefix . '.hotels.reservations.index') }}">Hotel reservations</a>
                </li>
                <li class="breadcrumb-item active">{{ $reservation->referenceLabel() }}</li>
            </ol>
        </nav>
    </div>

    @include('admin.partials.page-header', [
        'title' => $reservation->hotel_name ?: 'Hotel booking',
        'subtitle' => trim(($reservation->city_id ? $reservation->city_id.' · ' : '').$reservation->datesLabel()),
        'icon' => 'fas fa-hotel',
        'actions' => '<span class="badge '.$reservation->statusBadgeClass().' fs-6">'.e($reservation->statusLabel()).'</span>',
    ])

    @include('admin.partials.flash')

    <div class="panel-surface mb-3">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="small text-muted">Provider</div>
                <div class="fw-semibold">{{ $reservation->providerLabel() }}</div>
            </div>
            <div class="col-md-6">
                <div class="small text-muted">Channel</div>
                <div class="fw-semibold">{{ ucfirst((string) $reservation->channel) }}</div>
            </div>
            <div class="col-md-6">
                <div class="small text-muted">Reference</div>
                <div><code>{{ $reservation->reference_no ?: '—' }}</code></div>
            </div>
            <div class="col-md-6">
                <div class="small text-muted">Internal / Booking ID</div>
                <div>
                    <code>{{ $reservation->internal_reference ?: '—' }}</code>
                    @if($reservation->booking_id)
                        · <code>{{ $reservation->booking_id }}</code>
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <div class="small text-muted">Stay</div>
                <div class="fw-semibold">{{ $reservation->datesLabel() }}</div>
                @if($reservation->nights)
                    <div class="small text-muted">{{ $reservation->nights }} night(s) · {{ $reservation->rooms_count ?: 1 }} room(s)</div>
                @endif
            </div>
            <div class="col-md-6">
                <div class="small text-muted">Total</div>
                <div class="fw-semibold">{{ $reservation->total_price }} {{ $reservation->currency }}</div>
            </div>
            <div class="col-md-6">
                <div class="small text-muted">Lead guest</div>
                <div class="fw-semibold">{{ $reservation->passengerName() ?: '—' }}</div>
                <div class="small text-muted">{{ $reservation->passenger_email }} {{ $reservation->passenger_phone }}</div>
            </div>
            <div class="col-md-6">
                <div class="small text-muted">Booked</div>
                <div>{{ optional($reservation->booked_at)->format('d M Y H:i') ?? '—' }}</div>
                @if($reservation->cancelled_at)
                    <div class="small text-danger">Cancelled {{ $reservation->cancelled_at->format('d M Y H:i') }}</div>
                @endif
            </div>
        </div>
    </div>

    @if(is_array($reservation->guests) && $reservation->guests !== [])
        <div class="panel-surface mb-3">
            <h2 class="h6 mb-2">Guests</h2>
            <ul class="mb-0 small">
                @foreach($reservation->guests as $guest)
                    <li>
                        {{ $guest['prefix'] ?? '' }} {{ $guest['first_name'] ?? '' }} {{ $guest['last_name'] ?? '' }}
                        @if(!empty($guest['is_lead'])) <span class="badge bg-light text-dark border">Lead</span> @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($detail || $reservation->provider_snapshot)
        <details class="panel-surface mb-3">
            <summary class="fw-semibold" style="cursor:pointer">Supplier snapshot</summary>
            <pre class="bg-light border rounded p-3 small mb-0 mt-3">{{ json_encode($detail ?: $reservation->provider_snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
        </details>
    @endif

    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route($hotelsRoutePrefix . '.hotels.reservations.index') }}" class="btn btn-outline-secondary">Back to list</a>
        @unless($reservation->isCancelled())
            <form method="POST" action="{{ $cancelActionRoute }}" onsubmit="return confirm('Cancel this hotel booking?');">
                @csrf
                <input type="hidden" name="reason" value="Admin cancel">
                <button type="submit" class="btn btn-outline-danger">Cancel booking</button>
            </form>
        @endunless
    </div>
</div>
@endsection
