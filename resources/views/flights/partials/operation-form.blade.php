@php
    $key = $operationKey ?? '';
    $input = $searchInput ?? [];
@endphp

@if(in_array($key, ['availability_search', 'air_fare_display', 'flight_time_table', 'air_fare_rules'], true))
    <div class="col-12"><p class="small text-muted mb-2">Search by city or airport name — pick from the list.</p></div>
    @include('flights.partials.fields.route', [
        'input' => $input,
        'showReturn' => $key !== 'air_fare_display' && $key !== 'flight_time_table',
        'airportSearchUrl' => $airportSearchUrl ?? route('api.airports.search'),
    ])
@endif

@if($key === 'low_fare_search')
    @include('flights.partials.search-form')
@endif

@if(in_array($key, ['air_price'], true))
    <div class="alert alert-light border small mb-3">
        @if($hasPricingContext)
            Uses the first fare option from your last <strong>Low Fare Search</strong> in this browser session.
        @else
            <a href="{{ route($flightsRoutePrefix . '.flights.search') }}">Run Low Fare Search</a> first — Air Price needs a pricing solution from that response.
        @endif
    </div>
    <input type="hidden" name="adults" value="{{ $input['adults'] ?? 1 }}">
@endif

@if($key === 'air_fare_rules')
    <div class="col-md-4">
        <label class="form-label">Fare basis</label>
        <input type="text" name="fare_basis" class="form-control" value="{{ $input['fare_basis'] ?? '' }}" placeholder="e.g. Y26LRCGB">
    </div>
@endif

@if($key === 'seat_map')
    <div class="row g-3">
        <div class="col-md-2">
            <label class="form-label">Carrier</label>
            <input type="text" name="carrier" class="form-control text-uppercase" maxlength="3" value="{{ $input['carrier'] ?? 'BA' }}" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Flight #</label>
            <input type="text" name="flight_number" class="form-control" value="{{ $input['flight_number'] ?? '' }}" required>
        </div>
        @include('flights.partials.fields.route', ['input' => $input, 'showReturn' => false, 'showAdults' => false])
        <div class="col-md-4">
            <label class="form-label">Departure (ISO)</label>
            <input type="text" name="departure_time" class="form-control" placeholder="2026-08-01T10:00:00.000+01:00" value="{{ $input['departure_time'] ?? '' }}" required>
            <div class="form-text">Or use date only: 2026-08-01</div>
        </div>
    </div>
@endif

@if(in_array($key, ['universal_record_retrieve', 'universal_record_cancel', 'air_ticketing', 'air_retrieve_document', 'air_cancel', 'air_refund_quote', 'air_exchange_quote', 'air_exchange_ticketing', 'air_reprice', 'air_void_ticket', 'air_create_reservation', 'air_merchandising'], true))
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Universal Record locator</label>
            <input type="text" name="universal_locator" class="form-control text-uppercase" value="{{ $input['universal_locator'] ?? '' }}" placeholder="e.g. 0TU8VK" required>
        </div>
        @if($key === 'universal_record_cancel')
        <div class="col-md-3">
            <label class="form-label">Version</label>
            <input type="text" name="version" class="form-control" value="{{ $input['version'] ?? '0' }}">
        </div>
        @endif
    </div>
    @if(($currentOperation['status'] ?? '') === 'beta')
        <p class="small text-muted mt-2 mb-0">Sends a minimal {{ $currentOperation['request'] ?? 'request' }} — extend in Travelport samples if your GDS requires more data.</p>
    @endif
@endif
