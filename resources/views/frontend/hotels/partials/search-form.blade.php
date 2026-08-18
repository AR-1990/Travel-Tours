@php
    $input = $hotelSearchInput ?? [];
    $checkIn = old('check_in', $input['check_in'] ?? now()->addMonths(2)->format('Y-m-d'));
    $checkOut = old('check_out', $input['check_out'] ?? now()->addMonths(2)->addDay()->format('Y-m-d'));
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
                    Hotel API is not ready. Configure <strong>Admin → Integrations → Xconnect</strong> (Token + Base URL).
                    Requests also require a whitelisted server IP with Technoheaven/Rimo.
                </div>
            @endunless

            <form method="POST" action="{{ route('frontend.hotels.search') }}">
                @csrf
                <input type="hidden" name="provider" value="xconnect">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">City ID <span class="text-danger">*</span></label>
                        <input type="text" name="city_id" class="form-control" required
                            value="{{ old('city_id', $input['city_id'] ?? '') }}"
                            placeholder="From Xconnect Cities API">
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
                    <div class="col-md-3">
                        <label class="form-label">Nationality</label>
                        <input type="text" name="nationality" class="form-control"
                            value="{{ old('nationality', $input['nationality'] ?? config('xconnect.default_nationality')) }}">
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
                    <div class="col-md-2">
                        <label class="form-label">Currency</label>
                        <input type="text" name="currency" class="form-control" maxlength="8"
                            value="{{ old('currency', $input['currency'] ?? config('xconnect.default_currency')) }}">
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
