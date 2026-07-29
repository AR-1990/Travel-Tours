@php
    $index = $index ?? 0;
    $leg = is_array($leg ?? null) ? $leg : [];
    $origin = strtoupper((string) ($leg['origin'] ?? ''));
    $destination = strtoupper((string) ($leg['destination'] ?? ''));
    $departureRaw = (string) ($leg['departure_date'] ?? now()->addDays(14)->format('Y-m-d'));
    try {
        $departureDisplay = \Carbon\Carbon::parse($departureRaw)->format('m/d/Y');
    } catch (\Throwable) {
        $departureDisplay = $departureRaw;
    }
    $airportSearchUrl = $airportSearchUrl ?? route('api.airports.search');
    $canRemove = (bool) ($canRemove ?? false);
    $originAirport = $origin !== '' ? \App\Support\AirportDirectory::find($origin) : null;
    $destAirport = $destination !== '' ? \App\Support\AirportDirectory::find($destination) : null;
    $legNumber = is_numeric($index) ? ((int) $index + 1) : '';
@endphp
<div class="flight-search-item home-multicity-leg" data-leg-index="{{ $index }}">
    <div class="home-multicity-leg-header">
        <span class="home-multicity-leg-badge">
            <i class="fas fa-plane" aria-hidden="true"></i>
            Flight <span class="leg-number">{{ $legNumber }}</span>
        </span>
        @if($canRemove)
            <button type="button"
                class="home-multicity-remove remove-home-multi-city-leg"
                title="Remove flight"
                aria-label="Remove flight {{ $legNumber }}">
                <i class="fal fa-trash-can" aria-hidden="true"></i>
                <span>Remove</span>
            </button>
        @endif
    </div>

    <div class="row g-3 align-items-stretch home-flight-fields home-multicity-leg-fields">
        <div class="col-lg-4 col-md-6">
            <div class="form-group home-airport-field home-field-card">
                <div class="airport-picker home-airport-picker"
                    data-field="legs[{{ $index }}][origin]"
                    data-initial-code="{{ $origin }}"
                    data-initial-label="{{ $originAirport['label'] ?? '' }}"
                    data-search-url="{{ $airportSearchUrl }}">
                    <label class="flight-field-label" for="home_leg_{{ $index }}_origin_display">From</label>
                    <div class="airport-picker-input-wrap">
                        <i class="fas fa-plane-departure airport-picker-icon" aria-hidden="true"></i>
                        <input type="text"
                            id="home_leg_{{ $index }}_origin_display"
                            class="form-control airport-picker-display"
                            placeholder="City or airport"
                            value="{{ $originAirport['label'] ?? '' }}"
                            autocomplete="off"
                            autocorrect="off"
                            spellcheck="false"
                            role="combobox"
                            aria-expanded="false"
                            aria-autocomplete="list"
                            aria-controls="home_leg_{{ $index }}_origin_list">
                        <input type="hidden" name="legs[{{ $index }}][origin]" id="home_leg_{{ $index }}_origin" class="airport-picker-code" value="{{ $origin }}">
                    </div>
                    <ul id="home_leg_{{ $index }}_origin_list" class="airport-picker-list" role="listbox" hidden></ul>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="form-group home-airport-field home-airport-field-to home-field-card">
                <button type="button" class="search-form-swap home-swap-airports home-multicity-swap" title="Swap airports" aria-label="Swap from and to">
                    <i class="far fa-repeat"></i>
                </button>
                <div class="airport-picker home-airport-picker"
                    data-field="legs[{{ $index }}][destination]"
                    data-initial-code="{{ $destination }}"
                    data-initial-label="{{ $destAirport['label'] ?? '' }}"
                    data-search-url="{{ $airportSearchUrl }}">
                    <label class="flight-field-label" for="home_leg_{{ $index }}_destination_display">To</label>
                    <div class="airport-picker-input-wrap">
                        <i class="fas fa-plane-arrival airport-picker-icon" aria-hidden="true"></i>
                        <input type="text"
                            id="home_leg_{{ $index }}_destination_display"
                            class="form-control airport-picker-display"
                            placeholder="City or airport"
                            value="{{ $destAirport['label'] ?? '' }}"
                            autocomplete="off"
                            autocorrect="off"
                            spellcheck="false"
                            role="combobox"
                            aria-expanded="false"
                            aria-autocomplete="list"
                            aria-controls="home_leg_{{ $index }}_destination_list">
                        <input type="hidden" name="legs[{{ $index }}][destination]" id="home_leg_{{ $index }}_destination" class="airport-picker-code" value="{{ $destination }}">
                    </div>
                    <ul id="home_leg_{{ $index }}_destination_list" class="airport-picker-list" role="listbox" hidden></ul>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-12">
            <div class="form-group home-field-card">
                <div class="search-form-date">
                    <div class="search-form-journey">
                        <label>Journey Date</label>
                        <div class="form-group-icon">
                            <input type="text"
                                name="legs[{{ $index }}][departure_date]"
                                id="home_leg_{{ $index }}_date"
                                class="form-control date-picker journey-date multi-city-date"
                                value="{{ $departureDisplay }}">
                            <i class="fal fa-calendar-days"></i>
                        </div>
                        <p class="journey-day-name"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
