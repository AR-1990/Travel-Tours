@php
    $index = $index ?? 0;
    $leg = is_array($leg ?? null) ? $leg : [];
    $origin = strtoupper((string) ($leg['origin'] ?? ''));
    $destination = strtoupper((string) ($leg['destination'] ?? ''));
    $departure = (string) ($leg['departure_date'] ?? now()->addDays(14)->format('Y-m-d'));
    $airportSearchUrl = $airportSearchUrl ?? route('api.airports.search');
    $canRemove = (bool) ($canRemove ?? false);
    $originAirport = $origin !== '' ? \App\Support\AirportDirectory::find($origin) : null;
    $destAirport = $destination !== '' ? \App\Support\AirportDirectory::find($destination) : null;
@endphp
<div class="multi-city-leg border rounded p-3 mb-2" data-leg-index="{{ $index }}">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <strong class="small text-uppercase text-muted">Flight <span class="leg-number">{{ is_numeric($index) ? ((int) $index + 1) : '' }}</span></strong>
        @if($canRemove)
            <button type="button" class="btn btn-link btn-sm text-danger p-0 remove-multi-city-leg">Remove</button>
        @endif
    </div>
    <div class="row g-3 align-items-end">
        <div class="col-md-4">
            <div class="airport-picker"
                data-field="legs[{{ $index }}][origin]"
                data-initial-code="{{ $origin }}"
                data-initial-label="{{ $originAirport['label'] ?? '' }}"
                data-search-url="{{ $airportSearchUrl }}">
                <label class="flight-field-label" for="leg_{{ $index }}_origin_display">From</label>
                <div class="airport-picker-input-wrap">
                    <i class="fas fa-plane-departure airport-picker-icon" aria-hidden="true"></i>
                    <input type="text"
                        id="leg_{{ $index }}_origin_display"
                        class="form-control airport-picker-display"
                        placeholder="City or airport"
                        value="{{ $originAirport['label'] ?? '' }}"
                        autocomplete="off"
                        autocorrect="off"
                        spellcheck="false"
                        role="combobox"
                        aria-expanded="false"
                        aria-autocomplete="list"
                        aria-controls="leg_{{ $index }}_origin_list">
                    <input type="hidden" name="legs[{{ $index }}][origin]" id="leg_{{ $index }}_origin" class="airport-picker-code" value="{{ $origin }}">
                </div>
                <ul id="leg_{{ $index }}_origin_list" class="airport-picker-list" role="listbox" hidden></ul>
            </div>
        </div>
        <div class="col-md-4">
            <div class="airport-picker"
                data-field="legs[{{ $index }}][destination]"
                data-initial-code="{{ $destination }}"
                data-initial-label="{{ $destAirport['label'] ?? '' }}"
                data-search-url="{{ $airportSearchUrl }}">
                <label class="flight-field-label" for="leg_{{ $index }}_destination_display">To</label>
                <div class="airport-picker-input-wrap">
                    <i class="fas fa-plane-arrival airport-picker-icon" aria-hidden="true"></i>
                    <input type="text"
                        id="leg_{{ $index }}_destination_display"
                        class="form-control airport-picker-display"
                        placeholder="City or airport"
                        value="{{ $destAirport['label'] ?? '' }}"
                        autocomplete="off"
                        autocorrect="off"
                        spellcheck="false"
                        role="combobox"
                        aria-expanded="false"
                        aria-autocomplete="list"
                        aria-controls="leg_{{ $index }}_destination_list">
                    <input type="hidden" name="legs[{{ $index }}][destination]" id="leg_{{ $index }}_destination" class="airport-picker-code" value="{{ $destination }}">
                </div>
                <ul id="leg_{{ $index }}_destination_list" class="airport-picker-list" role="listbox" hidden></ul>
            </div>
        </div>
        <div class="col-md-4">
            <label class="flight-field-label" for="leg_{{ $index }}_date">Journey Date</label>
            <input type="date"
                name="legs[{{ $index }}][departure_date]"
                id="leg_{{ $index }}_date"
                class="form-control multi-city-date"
                value="{{ $departure }}">
        </div>
    </div>
</div>
