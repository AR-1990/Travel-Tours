@php
    $isSunSpring = ($flightProvider ?? '') === 'sunspring'
        || \App\Support\FlightProvider::fromResult($flightPriceResult ?? null) === 'sunspring';
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
@endphp

@if($isSunSpring)
    <p class="small text-muted mb-3">
        Enter every traveler from your search. SunSpring needs a valid national ID and passport (e.g. <code>A12345678</code>).
    </p>
    @foreach($slots as $index => $slot)
        @php
            $old = is_array($oldPassengers[$index] ?? null) ? $oldPassengers[$index] : [];
            $type = (string) ($slot['type'] ?? 'ADT');
            $defaultPrefix = $type === 'CHD' ? 'Miss' : ($type === 'INF' ? 'Mstr' : (($slot['prefix'] ?? 'Mr')));
            $defaultGender = $type === 'CHD' ? 'F' : (string) ($slot['gender'] ?? 'M');
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
                    <input type="text" name="passengers[{{ $index }}][first]" class="{{ $control }}" value="{{ $old['first'] ?? '' }}" required maxlength="80">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Last name</label>
                    <input type="text" name="passengers[{{ $index }}][last]" class="{{ $control }}" value="{{ $old['last'] ?? '' }}" required maxlength="80">
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
                    <input type="date" name="passengers[{{ $index }}][dob]" class="{{ $control }}" value="{{ $old['dob'] ?? '' }}" required>
                </div>
                @if($index === 0)
                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="passengers[{{ $index }}][email]" class="{{ $control }}" value="{{ $old['email'] ?? old('passenger_email', $bookInput['passenger_email'] ?? '') }}" required maxlength="120">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Phone</label>
                        <input type="text" name="passengers[{{ $index }}][phone]" class="{{ $control }}" value="{{ $old['phone'] ?? old('passenger_phone', $bookInput['passenger_phone'] ?? '') }}" required maxlength="30">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Country code</label>
                        <input type="text" name="country_code" class="{{ $control }}" value="{{ old('country_code', '+98') }}" maxlength="8">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nationality</label>
                        <input type="text" name="passengers[{{ $index }}][nationality]" class="{{ $control }}" value="{{ $old['nationality'] ?? 'IRN' }}" maxlength="8" required>
                    </div>
                @else
                    <input type="hidden" name="passengers[{{ $index }}][email]" value="{{ $old['email'] ?? old('passenger_email', 'cert.test@example.com') }}">
                    <input type="hidden" name="passengers[{{ $index }}][phone]" value="{{ $old['phone'] ?? old('passenger_phone', '9151112233') }}">
                    <input type="hidden" name="passengers[{{ $index }}][nationality]" value="{{ $old['nationality'] ?? 'IRN' }}">
                @endif
                <div class="col-md-4">
                    <label class="form-label">National ID</label>
                    <input type="text" name="passengers[{{ $index }}][national_id]" class="{{ $control }}" value="{{ $old['national_id'] ?? '' }}" maxlength="32" required placeholder="10-digit national ID">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Passport number</label>
                    <input type="text" name="passengers[{{ $index }}][passport_number]" class="{{ $control }}" value="{{ $old['passport_number'] ?? '' }}" maxlength="32" required placeholder="A12345678">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Passport expiry</label>
                    <input type="date" name="passengers[{{ $index }}][passport_expire]" class="{{ $control }}" value="{{ $old['passport_expire'] ?? '2030-12-31' }}" required>
                </div>
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
            <input type="text" name="passenger_first" class="{{ $control }}" value="{{ old('passenger_first', $bookInput['passenger_first'] ?? '') }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Last name</label>
            <input type="text" name="passenger_last" class="{{ $control }}" value="{{ old('passenger_last', $bookInput['passenger_last'] ?? '') }}" required>
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
            <input type="date" name="passenger_dob" class="{{ $control }}" value="{{ old('passenger_dob', $bookInput['passenger_dob'] ?? '') }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Email</label>
            <input type="email" name="passenger_email" class="{{ $control }}" value="{{ old('passenger_email', $bookInput['passenger_email'] ?? '') }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Phone</label>
            <input type="text" name="passenger_phone" class="{{ $control }}" value="{{ old('passenger_phone', $bookInput['passenger_phone'] ?? '') }}" required>
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
