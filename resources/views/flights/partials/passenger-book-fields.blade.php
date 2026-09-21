@php
    $pricedProvider = \App\Support\FlightProvider::fromResult($flightPriceResult ?? null);
    $providerId = strtolower((string) ($flightProvider ?? $pricedProvider ?: \App\Support\FlightProvider::current()));
    if (! in_array($providerId, \App\Support\FlightProvider::all(), true)) {
        $providerId = \App\Support\FlightProvider::TRAVELPORT;
    }
    $stamped = strtolower((string) data_get($flightPriceResult ?? [], 'provider', ''));
    if (in_array($stamped, \App\Support\FlightProvider::all(), true)) {
        $providerId = $stamped;
    } elseif (in_array($pricedProvider, [\App\Support\FlightProvider::SUNSPRING, \App\Support\FlightProvider::DOWNTOWN_TRAVEL], true)
        && \App\Support\FlightProvider::usesPassengerArray($pricedProvider)) {
        $providerId = $pricedProvider;
    }

    $usesPassengerArray = \App\Support\FlightProvider::usesPassengerArray($providerId);
    $spec = \App\Support\FlightProvider::bookFieldSpecs($providerId);
    $defaultNationality = \App\Support\FlightProvider::defaultNationality($providerId);
    $defaultCountryCode = \App\Support\FlightProvider::defaultCountryCode($providerId);
    $slots = $passengerSlots ?? [];
    if ($slots === []) {
        $slots = [[
            'type' => 'ADT',
            'label' => 'Adult 1',
            'prefix' => 'Mr',
            'gender' => 'M',
        ]];
    }
    $oldPassengers = old('passengers', []);
    $compact = $compact ?? false;
    $control = $compact ? 'form-control form-control-sm' : 'form-control';
    $select = $compact ? 'form-select form-select-sm' : 'form-control';
    $today = now()->subDay()->format('Y-m-d');
    $passportMin = now()->addDay()->format('Y-m-d');
@endphp

<input type="hidden" name="provider" value="{{ $providerId }}">

@if($usesPassengerArray)
    <p class="small text-muted mb-3">{{ $spec['hint'] }}</p>
    @foreach($slots as $index => $slot)
        @php
            $old = is_array($oldPassengers[$index] ?? null) ? $oldPassengers[$index] : [];
            $type = (string) ($slot['type'] ?? 'ADT');
            $defaultPrefix = $type === 'CHD' ? 'Miss' : ($type === 'INF' ? 'Mstr' : (($slot['prefix'] ?? 'Mr')));
            $defaultGender = $type === 'CHD' ? 'F' : (string) ($slot['gender'] ?? 'M');
            $maxDob = $type === 'INF'
                ? now()->subDay()->format('Y-m-d')
                : ($type === 'CHD' ? now()->subYears(2)->format('Y-m-d') : now()->subYears(12)->format('Y-m-d'));
            $minDob = $type === 'INF'
                ? now()->subYears(2)->format('Y-m-d')
                : ($type === 'CHD' ? now()->subYears(12)->format('Y-m-d') : now()->subYears(100)->format('Y-m-d'));
        @endphp
        <div class="border rounded p-3 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <strong>{{ $slot['label'] ?? ('Passenger '.($index + 1)) }}</strong>
                <span class="badge bg-light text-dark">{{ $type }}</span>
            </div>
            <input type="hidden" name="passengers[{{ $index }}][type]" value="{{ $type }}">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Title</label>
                    <select name="passengers[{{ $index }}][prefix]" class="{{ $select }}">
                        @foreach(['Mr', 'Mrs', 'Ms', 'Miss', 'Mstr'] as $title)
                            <option value="{{ $title }}" @selected(($old['prefix'] ?? $defaultPrefix) === $title)>{{ $title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">First name</label>
                    <input type="text" name="passengers[{{ $index }}][first]" class="{{ $control }}"
                        value="{{ $old['first'] ?? '' }}"
                        required
                        minlength="{{ $spec['name_min'] }}"
                        maxlength="{{ $spec['name_max'] }}"
                        pattern="{{ $spec['name_pattern'] }}"
                        title="{{ $spec['name_title'] }}"
                        autocomplete="given-name">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Last name</label>
                    <input type="text" name="passengers[{{ $index }}][last]" class="{{ $control }}"
                        value="{{ $old['last'] ?? '' }}"
                        required
                        minlength="{{ $spec['name_min'] }}"
                        maxlength="{{ $spec['name_max'] }}"
                        pattern="{{ $spec['name_pattern'] }}"
                        title="{{ $spec['name_title'] }}"
                        autocomplete="family-name">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Gender</label>
                    <select name="passengers[{{ $index }}][gender]" class="{{ $select }}" required>
                        <option value="M" @selected(($old['gender'] ?? $defaultGender) === 'M')>Male</option>
                        <option value="F" @selected(($old['gender'] ?? $defaultGender) === 'F')>Female</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date of birth</label>
                    <input type="date" name="passengers[{{ $index }}][dob]" class="{{ $control }}"
                        value="{{ $old['dob'] ?? '' }}"
                        required
                        min="{{ $minDob }}"
                        max="{{ $maxDob }}"
                        title="Date of birth must match {{ $type }} age band">
                </div>
                @if($index === 0)
                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="passengers[{{ $index }}][email]" class="{{ $control }}"
                            value="{{ $old['email'] ?? old('passenger_email', $bookInput['passenger_email'] ?? '') }}"
                            required maxlength="120" autocomplete="email">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="passengers[{{ $index }}][phone]" class="{{ $control }}"
                            value="{{ $old['phone'] ?? old('passenger_phone', $bookInput['passenger_phone'] ?? '') }}"
                            required
                            minlength="{{ $spec['phone_min'] }}"
                            maxlength="{{ $spec['phone_max'] }}"
                            pattern="{{ $spec['phone_pattern'] }}"
                            title="{{ $spec['phone_title'] }}"
                            autocomplete="tel"
                            placeholder="{{ $providerId === 'downtown_travel' ? '+14057787503' : '9151112233' }}">
                    </div>
                    @if($spec['show_country_code'])
                        <div class="col-md-4">
                            <label class="form-label">Country code</label>
                            <input type="text" name="country_code" class="{{ $control }}"
                                value="{{ old('country_code', $defaultCountryCode) }}"
                                maxlength="{{ $spec['country_code_max'] }}"
                                pattern="{{ $spec['country_code_pattern'] }}"
                                title="{{ $spec['country_code_title'] }}"
                                placeholder="{{ $defaultCountryCode }}"
                                inputmode="tel">
                        </div>
                    @endif
                    @if($spec['show_nationality'])
                        <div class="col-md-4">
                            <label class="form-label">Nationality</label>
                            <input type="text" name="passengers[{{ $index }}][nationality]" class="{{ $control }} text-uppercase"
                                value="{{ $old['nationality'] ?? $defaultNationality }}"
                                required
                                minlength="{{ $spec['nationality_min'] }}"
                                maxlength="{{ $spec['nationality_max'] }}"
                                pattern="{{ $spec['nationality_pattern'] }}"
                                title="{{ $spec['nationality_title'] }}"
                                placeholder="{{ $spec['nationality_placeholder'] }}"
                                style="text-transform:uppercase">
                        </div>
                    @endif
                @else
                    <input type="hidden" name="passengers[{{ $index }}][email]" value="{{ $old['email'] ?? old('passenger_email', 'cert.test@example.com') }}">
                    <input type="hidden" name="passengers[{{ $index }}][phone]" value="{{ $old['phone'] ?? old('passenger_phone', '9151112233') }}">
                    <input type="hidden" name="passengers[{{ $index }}][nationality]" value="{{ $old['nationality'] ?? $defaultNationality }}">
                @endif
                @if($spec['show_docs'] && $spec['docs_required'])
                    <div class="col-md-4">
                        <label class="form-label">National ID</label>
                        <input type="text" name="passengers[{{ $index }}][national_id]" class="{{ $control }}"
                            value="{{ $old['national_id'] ?? '' }}"
                            required
                            minlength="{{ $spec['national_id_min'] }}"
                            maxlength="{{ $spec['national_id_max'] }}"
                            pattern="{{ $spec['national_id_pattern'] }}"
                            title="{{ $spec['national_id_title'] }}"
                            placeholder="{{ $spec['national_id_title'] }}"
                            inputmode="numeric">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Passport number</label>
                        <input type="text" name="passengers[{{ $index }}][passport_number]" class="{{ $control }}"
                            value="{{ $old['passport_number'] ?? '' }}"
                            required
                            minlength="{{ $spec['passport_min'] }}"
                            maxlength="{{ $spec['passport_max'] }}"
                            pattern="{{ $spec['passport_pattern'] }}"
                            title="{{ $spec['passport_title'] }}"
                            placeholder="A12345678">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Passport expiry</label>
                        <input type="date" name="passengers[{{ $index }}][passport_expire]" class="{{ $control }}"
                            value="{{ $old['passport_expire'] ?? '2030-12-31' }}"
                            required
                            min="{{ $passportMin }}">
                    </div>
                @elseif($spec['show_docs'] && $spec['passport_optional'])
                    <div class="col-md-4">
                        <label class="form-label">Passport number <span class="text-muted">(optional)</span></label>
                        <input type="text" name="passengers[{{ $index }}][passport_number]" class="{{ $control }}"
                            value="{{ $old['passport_number'] ?? '' }}"
                            minlength="{{ $spec['passport_min'] }}"
                            maxlength="{{ $spec['passport_max'] }}"
                            pattern="{{ $spec['passport_pattern'] }}"
                            title="{{ $spec['passport_title'] }}"
                            placeholder="Leave blank if not required">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Passport expiry</label>
                        <input type="date" name="passengers[{{ $index }}][passport_expire]" class="{{ $control }}"
                            value="{{ $old['passport_expire'] ?? '' }}"
                            min="{{ $passportMin }}">
                    </div>
                @endif
            </div>
        </div>
    @endforeach
    <div class="row g-3 mb-2">
        <div class="col-md-4">
            <label class="form-label">Form of payment</label>
            <select name="form_of_payment" class="{{ $select }}">
                <option value="Cash">Cash</option>
                <option value="Credit">Credit</option>
                <option value="Check">Check</option>
            </select>
        </div>
    </div>
@else
    <p class="small text-muted mb-3">{{ $spec['hint'] }}</p>
    <div class="row g-3">
        <div class="col-md-2">
            <label class="form-label">Title</label>
            <select name="passenger_prefix" class="{{ $select }}">
                @foreach(['Mr', 'Mrs', 'Ms', 'Miss'] as $title)
                    <option value="{{ $title }}" @selected(old('passenger_prefix', $bookInput['passenger_prefix'] ?? 'Mr') === $title)>{{ $title }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">First name</label>
            <input type="text" name="passenger_first" class="{{ $control }}"
                value="{{ old('passenger_first', $bookInput['passenger_first'] ?? '') }}"
                required
                minlength="{{ $spec['name_min'] }}"
                maxlength="{{ $spec['name_max'] }}"
                pattern="{{ $spec['name_pattern'] }}"
                title="{{ $spec['name_title'] }}"
                autocomplete="given-name">
        </div>
        <div class="col-md-4">
            <label class="form-label">Last name</label>
            <input type="text" name="passenger_last" class="{{ $control }}"
                value="{{ old('passenger_last', $bookInput['passenger_last'] ?? '') }}"
                required
                minlength="{{ $spec['name_min'] }}"
                maxlength="{{ $spec['name_max'] }}"
                pattern="{{ $spec['name_pattern'] }}"
                title="{{ $spec['name_title'] }}"
                autocomplete="family-name">
        </div>
        <div class="col-md-2">
            <label class="form-label">Gender</label>
            <select name="passenger_gender" class="{{ $select }}" required>
                <option value="M" @selected(old('passenger_gender', $bookInput['passenger_gender'] ?? 'M') === 'M')>Male</option>
                <option value="F" @selected(old('passenger_gender', $bookInput['passenger_gender'] ?? '') === 'F')>Female</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Date of birth</label>
            <input type="date" name="passenger_dob" class="{{ $control }}"
                value="{{ old('passenger_dob', $bookInput['passenger_dob'] ?? '') }}"
                required
                max="{{ $today }}"
                min="{{ now()->subYears(100)->format('Y-m-d') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Email</label>
            <input type="email" name="passenger_email" class="{{ $control }}"
                value="{{ old('passenger_email', $bookInput['passenger_email'] ?? '') }}"
                required maxlength="120" autocomplete="email">
        </div>
        <div class="col-md-4">
            <label class="form-label">Phone</label>
            <input type="tel" name="passenger_phone" class="{{ $control }}"
                value="{{ old('passenger_phone', $bookInput['passenger_phone'] ?? '') }}"
                required
                minlength="{{ $spec['phone_min'] }}"
                maxlength="{{ $spec['phone_max'] }}"
                pattern="{{ $spec['phone_pattern'] }}"
                title="{{ $spec['phone_title'] }}"
                autocomplete="tel">
        </div>
        <div class="col-md-4">
            <label class="form-label">Form of payment</label>
            <select name="form_of_payment" class="{{ $select }}">
                <option value="Cash">Cash</option>
                <option value="Credit">Credit</option>
                <option value="Check">Check</option>
            </select>
        </div>
    </div>
@endif
