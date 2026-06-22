@php
    $key = $operationKey ?? '';
    $input = $searchInput ?? [];
@endphp

@if(in_array($key, ['availability_search', 'air_fare_display', 'flight_time_table', 'air_fare_rules', 'low_fare_search_async'], true))
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
            @if(($flightsRoutePrefix ?? 'admin') === 'frontend')
                <a href="{{ route('home') }}">Run a flight search</a> first — Air Price needs a pricing solution from that response.
            @else
                <a href="{{ route($flightsRoutePrefix . '.flights.search') }}">Run Low Fare Search</a> first — Air Price needs a pricing solution from that response.
            @endif
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

@if(in_array($key, ['flight_details', 'flight_information'], true))
    <div class="col-md-2">
        <label class="form-label">Carrier</label>
        <input type="text" name="carrier" class="form-control text-uppercase" maxlength="3" value="{{ $input['carrier'] ?? '' }}" required>
    </div>
    <div class="col-md-2">
        <label class="form-label">Flight #</label>
        <input type="text" name="flight_number" class="form-control" value="{{ $input['flight_number'] ?? '' }}" required>
    </div>
    <div class="col-md-2">
        <label class="form-label">Origin</label>
        <input type="text" name="origin" class="form-control text-uppercase" maxlength="3" value="{{ $input['origin'] ?? '' }}" required>
    </div>
    <div class="col-md-2">
        <label class="form-label">Destination</label>
        <input type="text" name="destination" class="form-control text-uppercase" maxlength="3" value="{{ $input['destination'] ?? '' }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Departure date</label>
        <input type="date" name="departure_date" class="form-control" value="{{ $input['departure_date'] ?? '' }}" required>
    </div>
@endif

@if($key === 'seat_map')
    <div class="row g-3">
        <div class="col-md-2">
            <label class="form-label">Carrier</label>
            <input type="text" name="carrier" class="form-control text-uppercase" maxlength="3" value="{{ $input['carrier'] ?? '' }}" placeholder="auto from last search">
        </div>
        <div class="col-md-2">
            <label class="form-label">Flight #</label>
            <input type="text" name="flight_number" class="form-control" value="{{ $input['flight_number'] ?? '' }}" placeholder="auto from last search">
        </div>
        @include('flights.partials.fields.route', ['input' => $input, 'showReturn' => false, 'showAdults' => false])
        <div class="col-md-4">
            <label class="form-label">Departure (ISO)</label>
            <input type="text" name="departure_time" class="form-control" placeholder="auto from last search or 2026-08-01T10:00:00.000+01:00" value="{{ $input['departure_time'] ?? '' }}">
            <div class="form-text">Leave blank to auto-fill from latest Low Fare Search.</div>
        </div>
    </div>
@endif

@if($key === 'air_create_reservation')
    <div class="col-12">
        <p class="small text-muted mb-2">Uses your last <strong>Air Price</strong> in this session. Enter lead passenger details.</p>
    </div>
    <div class="col-md-2">
        <label class="form-label">Title</label>
        <select name="passenger_prefix" class="form-control">
            @foreach(['Mr', 'Mrs', 'Ms', 'Miss'] as $title)
                <option value="{{ $title }}" @selected(($input['passenger_prefix'] ?? 'Mr') === $title)>{{ $title }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">First name</label>
        <input type="text" name="passenger_first" class="form-control" value="{{ $input['passenger_first'] ?? '' }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Last name</label>
        <input type="text" name="passenger_last" class="form-control" value="{{ $input['passenger_last'] ?? '' }}" required>
    </div>
    <div class="col-md-2">
        <label class="form-label">Gender</label>
        <select name="passenger_gender" class="form-control">
            <option value="M" @selected(($input['passenger_gender'] ?? 'M') === 'M')>Male</option>
            <option value="F" @selected(($input['passenger_gender'] ?? '') === 'F')>Female</option>
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label">Date of birth</label>
        <input type="date" name="passenger_dob" class="form-control" value="{{ $input['passenger_dob'] ?? '' }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Email</label>
        <input type="email" name="passenger_email" class="form-control" value="{{ $input['passenger_email'] ?? '' }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Phone</label>
        <input type="text" name="passenger_phone" class="form-control" value="{{ $input['passenger_phone'] ?? '' }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Form of payment</label>
        <select name="form_of_payment" class="form-control">
            <option value="Cash">Cash</option>
            <option value="Credit">Credit</option>
            <option value="Check">Check</option>
        </select>
    </div>
@endif

@if(in_array($key, ['universal_record_retrieve', 'universal_record_cancel', 'universal_record_modify', 'air_ticketing', 'air_retrieve_document', 'air_cancel', 'air_refund_quote', 'air_refund', 'air_exchange_quote', 'air_exchange', 'air_exchange_ticketing', 'air_reprice', 'air_void_ticket', 'air_pre_pay'], true))
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Universal Record locator</label>
            <input type="text" name="universal_locator" class="form-control text-uppercase" value="{{ $input['universal_locator'] ?? '' }}" placeholder="e.g. 0TU8VK" required>
        </div>
        @if(in_array($key, ['air_ticketing', 'air_retrieve_document', 'air_cancel', 'air_reprice', 'air_refund_quote', 'air_refund', 'air_void_ticket', 'air_exchange_quote', 'air_exchange', 'air_exchange_ticketing', 'air_pre_pay'], true))
        <div class="col-md-6">
            <label class="form-label">Air reservation locator</label>
            <input type="text" name="air_reservation_locator" class="form-control text-uppercase" value="{{ $input['air_reservation_locator'] ?? '' }}" placeholder="From booking response">
        </div>
        @endif
        @if(in_array($key, ['air_retrieve_document', 'air_void_ticket'], true))
        <div class="col-md-4">
            <label class="form-label">Ticket number (optional)</label>
            <input type="text" name="ticket_number" class="form-control" value="{{ $input['ticket_number'] ?? '' }}">
        </div>
        @endif
        @if($key === 'universal_record_cancel' || $key === 'universal_record_modify')
        <div class="col-md-3">
            <label class="form-label">Version</label>
            <input type="text" name="version" class="form-control" value="{{ $input['version'] ?? '0' }}">
        </div>
        @endif
    </div>
@endif

@if($key === 'air_merchandising')
    <div class="col-12">
        <p class="small text-muted mb-0">Uses priced itinerary from your last <strong>Air Price</strong> in this session. No extra fields required.</p>
    </div>
@endif
