@php
    $input = $hotelSearchInput ?? [];
    $checkIn = old('check_in', $input['check_in'] ?? now()->addMonths(2)->format('Y-m-d'));
    $checkOut = old('check_out', $input['check_out'] ?? now()->addMonths(2)->addDay()->format('Y-m-d'));
    $selectedProvider = old('provider', $input['provider'] ?? ($hotelProvider ?? \App\Support\HotelProvider::current()));
    $providers = $providerOptions ?? \App\Support\HotelProvider::options();
    $destinations = $downtownDestinations ?? \App\Services\DowntownTravel\DowntownTravelHotelService::destinationOptions();
    $isDowntown = $selectedProvider === \App\Support\HotelProvider::DOWNTOWN_TRAVEL_HOTELS;
@endphp
<div class="search-area pt-40 pb-40">
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="search-wrapper p-4 border rounded bg-white">
            @unless($hotelReady ?? false)
                <div class="alert alert-warning">
                    Hotel API is not ready. Configure <strong>Admin → Integrations</strong>
                    (Downtown Travel Hotels and/or Xconnect).
                </div>
            @endunless

            <form method="POST" action="{{ route('frontend.hotels.search') }}" id="hotelSearchForm">
                @csrf
                <div class="row g-3 mb-2">
                    <div class="col-12">
                        <label class="form-label">Search via</label>
                        <div class="d-flex flex-wrap gap-3">
                            @foreach($providers as $option)
                                <label class="d-inline-flex align-items-center gap-2">
                                    <input type="radio" name="provider" value="{{ $option['id'] }}"
                                        class="js-hotel-provider"
                                        @checked($selectedProvider === $option['id'])
                                        @disabled(empty($option['ready']))>
                                    <span>{{ $option['label'] }}@if(empty($option['ready'])) (off)@endif</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-3 js-downtown-fields" style="{{ $isDowntown ? '' : 'display:none' }}">
                        <label class="form-label">City <span class="text-danger">*</span></label>
                        <select name="destination" class="form-select" @disabled(! $isDowntown)>
                            <option value="">Select city</option>
                            @foreach($destinations as $dest)
                                <option value="{{ $dest['id'] }}"
                                    @selected(old('destination', $input['destination'] ?? '') === $dest['id'])>
                                    {{ $dest['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 js-xconnect-fields" style="{{ $isDowntown ? 'display:none' : '' }}">
                        <label class="form-label">City ID <span class="text-danger">*</span></label>
                        <input type="text" name="city_id" class="form-control"
                            value="{{ old('city_id', $input['city_id'] ?? '') }}"
                            placeholder="From Xconnect Cities API"
                            @disabled($isDowntown)>
                        <div class="form-text">Use Cities endpoint after picking a country.</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Check-in</label>
                        <input type="date" name="check_in" class="form-control" required value="{{ $checkIn }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Check-out</label>
                        <input type="date" name="check_out" class="form-control" required value="{{ $checkOut }}">
                    </div>
                    <div class="col-md-3 js-xconnect-fields" style="{{ $isDowntown ? 'display:none' : '' }}">
                        <label class="form-label">Nationality</label>
                        <input type="text" name="nationality" class="form-control"
                            value="{{ old('nationality', $input['nationality'] ?? config('xconnect.default_nationality')) }}"
                            @disabled($isDowntown)>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Adults</label>
                        <input type="number" name="adults" class="form-control" min="1" max="8"
                            value="{{ old('adults', $input['adults'] ?? 2) }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Children</label>
                        <input type="number" name="children" class="form-control" min="0" max="6"
                            value="{{ old('children', $input['children'] ?? 0) }}">
                    </div>
                    <div class="col-md-2 js-xconnect-fields" style="{{ $isDowntown ? 'display:none' : '' }}">
                        <label class="form-label">Currency</label>
                        <input type="text" name="currency" class="form-control" maxlength="8"
                            value="{{ old('currency', $input['currency'] ?? config('xconnect.default_currency')) }}"
                            @disabled($isDowntown)>
                    </div>
                    <div class="col-md-6 d-flex align-items-end gap-2">
                        <button type="submit" class="theme-btn" @disabled(!($hotelReady ?? false))>
                            {{ $searchSubmitLabel ?? 'Search Hotels' }}
                        </button>
                        <a href="{{ route('frontend.hotels.reservations.index') }}" class="btn btn-outline-secondary">My hotel bookings</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@once
<script>
(function () {
    function syncHotelProvider() {
        var selected = document.querySelector('.js-hotel-provider:checked');
        var isDowntown = selected && selected.value === 'downtown_travel_hotels';
        document.querySelectorAll('.js-downtown-fields').forEach(function (el) {
            el.style.display = isDowntown ? '' : 'none';
            el.querySelectorAll('select,input').forEach(function (field) {
                field.disabled = !isDowntown;
            });
        });
        document.querySelectorAll('.js-xconnect-fields').forEach(function (el) {
            el.style.display = isDowntown ? 'none' : '';
            el.querySelectorAll('select,input').forEach(function (field) {
                field.disabled = !!isDowntown;
            });
        });
    }
    document.querySelectorAll('.js-hotel-provider').forEach(function (el) {
        el.addEventListener('change', syncHotelProvider);
    });
    syncHotelProvider();
})();
</script>
@endonce
