@php
    $input = $input ?? [];
    $showReturn = $showReturn ?? true;
    $showAdults = $showAdults ?? true;
    $airportSearchUrl = $airportSearchUrl ?? route('api.airports.search');
@endphp
<div class="col-lg-3 col-md-6">
    @include('flights.partials.airport-picker', [
        'name' => 'origin',
        'id' => 'op_origin',
        'value' => $input['origin'] ?? 'LHR',
        'label' => 'From',
        'searchUrl' => $airportSearchUrl,
    ])
</div>
<div class="col-lg-3 col-md-6">
    @include('flights.partials.airport-picker', [
        'name' => 'destination',
        'id' => 'op_destination',
        'value' => $input['destination'] ?? 'JFK',
        'label' => 'To',
        'searchUrl' => $airportSearchUrl,
    ])
</div>
<div class="col-md-6 col-lg-3">
    <label class="form-label">Depart</label>
    <input type="date" name="departure_date" class="form-control" required
        value="{{ $input['departure_date'] ?? now()->addDays(14)->format('Y-m-d') }}">
</div>
@if($showReturn)
<div class="col-md-6 col-lg-3">
    <label class="form-label">Return</label>
    <input type="date" name="return_date" class="form-control" value="{{ $input['return_date'] ?? '' }}">
</div>
@endif
@if($showAdults)
<div class="col-md-6 col-lg-2">
    <label class="form-label">Adults</label>
    <select name="adults" class="form-select">
        @for ($i = 1; $i <= 9; $i++)
            <option value="{{ $i }}" @selected((int)($input['adults'] ?? 1) === $i)>{{ $i }}</option>
        @endfor
    </select>
</div>
@endif
