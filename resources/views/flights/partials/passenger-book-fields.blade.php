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
    $req = '<span class="text-danger">*</span>';
@endphp

<input type="hidden" name="provider" value="{{ $providerId }}">

@php
    $pricedSolution = data_get($flightPriceResult ?? [], 'solutions.0');
    $paymentInfo = app(\App\Services\Finance\MarkupService::class)->breakdown(
        is_array($pricedSolution) ? $pricedSolution : null,
        auth()->user()?->tenant_id ? (int) auth()->user()->tenant_id : null,
        $providerId,
        (float) old('service_fee', 0),
        (string) old('form_of_payment', '')
    );
@endphp
@include('flights.partials.payment-information', ['paymentInfo' => $paymentInfo])

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
                    <label class="form-label">First name {!! $req !!}</label>
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
                    <label class="form-label">Last name {!! $req !!}</label>
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
                    <label class="form-label">Gender {!! $req !!}</label>
                    <select name="passengers[{{ $index }}][gender]" class="{{ $select }}" required>
                        <option value="M" @selected(($old['gender'] ?? $defaultGender) === 'M')>Male</option>
                        <option value="F" @selected(($old['gender'] ?? $defaultGender) === 'F')>Female</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date of birth {!! $req !!}</label>
                    <input type="date" name="passengers[{{ $index }}][dob]" class="{{ $control }}"
                        value="{{ $old['dob'] ?? '' }}"
                        required
                        min="{{ $minDob }}"
                        max="{{ $maxDob }}"
                        title="Date of birth must match {{ $type }} age band">
                </div>
                @if($index === 0)
                    @php
                        $oldPhone = (string) ($old['phone'] ?? old('passenger_phone', $bookInput['passenger_phone'] ?? ''));
                        $oldCountry = (string) old('country_code', $defaultCountryCode);
                        if ($oldPhone !== '' && str_starts_with($oldPhone, '+') && ! old('country_code')) {
                            if (preg_match('/^(\+\d{1,4})(.*)$/', $oldPhone, $m)) {
                                $oldCountry = $m[1];
                                $oldPhone = trim($m[2]);
                            }
                        }
                    @endphp
                    <div class="col-md-4">
                        <label class="form-label">Email {!! $req !!}</label>
                        <input type="email" name="passengers[{{ $index }}][email]" class="{{ $control }}"
                            value="{{ $old['email'] ?? old('passenger_email', $bookInput['passenger_email'] ?? '') }}"
                            required maxlength="120" autocomplete="email">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Country code {!! $req !!}</label>
                        @php $dialingOptions = \App\Support\FlightProvider::dialingCodeOptions(); @endphp
                        <select name="country_code" class="{{ $select }}" required aria-label="Country code">
                            @foreach($dialingOptions as $code => $label)
                                <option value="{{ $code }}" @selected(($oldCountry ?: $defaultCountryCode) === $code)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Phone {!! $req !!}</label>
                        <input type="tel" name="passengers[{{ $index }}][phone]" class="{{ $control }} js-merged-phone"
                            value="{{ $oldPhone }}"
                            required
                            minlength="{{ $spec['phone_min'] }}"
                            maxlength="{{ $spec['phone_max'] }}"
                            pattern="[0-9() \\-]{7,20}"
                            title="Local phone number without country code"
                            autocomplete="tel"
                            inputmode="tel"
                            placeholder="{{ $providerId === 'downtown_travel' ? '4057787503' : '3330248947' }}">
                        <div class="form-text">Side by side with country code; validated dialing code list.</div>
                    </div>
                @else
                    <input type="hidden" name="passengers[{{ $index }}][email]" value="{{ $old['email'] ?? old('passenger_email', 'cert.test@example.com') }}">
                    <input type="hidden" name="passengers[{{ $index }}][phone]" value="{{ $old['phone'] ?? old('passenger_phone', '9151112233') }}">
                @endif
                @if($spec['show_nationality'])
                    <div class="col-md-4">
                        <label class="form-label">Nationality {!! $req !!}</label>
                        @php
                            $nationalityOptions = \App\Support\FlightProvider::nationalityOptions($providerId);
                            $selectedNationality = strtoupper((string) ($old['nationality'] ?? $defaultNationality));
                        @endphp
                        <select name="passengers[{{ $index }}][nationality]" class="{{ $select }} js-passenger-nationality" required
                            data-passenger-index="{{ $index }}">
                            @foreach($nationalityOptions as $code => $label)
                                <option value="{{ $code }}" @selected($selectedNationality === $code)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                @if($spec['show_docs'] && $spec['docs_required'])
                    @if(! empty($spec['national_id_pattern']))
                        <div class="col-md-4">
                            <label class="form-label">
                                National ID
                                <span class="text-danger js-national-id-required-mark @if(strtoupper((string) ($old['nationality'] ?? $defaultNationality)) !== 'IRN') d-none @endif">*</span>
                                <span class="text-muted small js-national-id-optional-mark @if(strtoupper((string) ($old['nationality'] ?? $defaultNationality)) === 'IRN') d-none @endif">(Iran only)</span>
                            </label>
                            <input type="text" name="passengers[{{ $index }}][national_id]" class="{{ $control }} js-sunspring-national-id"
                                value="{{ $old['national_id'] ?? '' }}"
                                minlength="{{ $spec['national_id_min'] }}"
                                maxlength="{{ $spec['national_id_max'] }}"
                                pattern="{{ $spec['national_id_pattern'] }}"
                                title="{{ $spec['national_id_title'] }}"
                                placeholder="IRN only — e.g. 0013542419"
                                inputmode="numeric"
                                autocomplete="off"
                                data-national-id-check="1"
                                data-passenger-index="{{ $index }}">
                            <div class="form-text">Required only for Iranian (IRN) nationality · 10-digit کد ملی</div>
                        </div>
                    @endif
                    <div class="col-md-4">
                        <label class="form-label">Passport number {!! $req !!}</label>
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
                        <label class="form-label">Passport expiry {!! $req !!}</label>
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
    @php
        $isAgencyUser = auth()->check() && in_array(auth()->user()->user_type ?? '', ['tenant_admin', 'sub_agent', 'super_admin'], true);
        $paymentOptions = $isAgencyUser
            ? [
                'Wallet' => 'Pay by wallet',
                'Reserve' => 'Reserve',
                'Credit' => 'Credit card',
                'Debit' => 'Debit card',
                'BankTransfer' => 'Bank transfer',
            ]
            : [
                'Credit' => 'Credit card',
                'Debit' => 'Debit card',
                'BankTransfer' => 'Bank transfer',
            ];
        $selectedPayment = old('form_of_payment', $isAgencyUser ? 'Wallet' : 'Credit');
    @endphp
    <div class="row g-3 mb-2">
        <div class="col-md-6">
            <label class="form-label">Payment option {!! $req !!}</label>
            <select name="form_of_payment" class="{{ $select }}" required>
                @foreach($paymentOptions as $value => $label)
                    <option value="{{ $value }}" @selected($selectedPayment === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <div class="form-text">
                {{ $isAgencyUser ? 'B2B options: wallet, reserve, cards, or bank transfer.' : 'B2C options: credit/debit card or bank transfer.' }}
            </div>
        </div>
        @if($isAgencyUser)
            <div class="col-md-6">
                <label class="form-label">Ticket print</label>
                <select name="print_with_price" class="{{ $select }}">
                    <option value="1" @selected(old('print_with_price', '1') == '1')>With price</option>
                    <option value="0" @selected(old('print_with_price') === '0' || old('print_with_price') === 0)>Without price</option>
                </select>
                <div class="form-text">Agency can print e-ticket with or without fare amount.</div>
            </div>
        @endif
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
            <label class="form-label">First name {!! $req !!}</label>
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
            <label class="form-label">Last name {!! $req !!}</label>
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
            <label class="form-label">Gender {!! $req !!}</label>
            <select name="passenger_gender" class="{{ $select }}" required>
                <option value="M" @selected(old('passenger_gender', $bookInput['passenger_gender'] ?? 'M') === 'M')>Male</option>
                <option value="F" @selected(old('passenger_gender', $bookInput['passenger_gender'] ?? '') === 'F')>Female</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Date of birth {!! $req !!}</label>
            <input type="date" name="passenger_dob" class="{{ $control }}"
                value="{{ old('passenger_dob', $bookInput['passenger_dob'] ?? '') }}"
                required
                max="{{ $today }}"
                min="{{ now()->subYears(100)->format('Y-m-d') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Email {!! $req !!}</label>
            <input type="email" name="passenger_email" class="{{ $control }}"
                value="{{ old('passenger_email', $bookInput['passenger_email'] ?? '') }}"
                required maxlength="120" autocomplete="email">
        </div>
        @php
            $tpPhone = (string) old('passenger_phone', $bookInput['passenger_phone'] ?? '');
            $tpCountry = (string) old('country_code', $defaultCountryCode);
            if ($tpPhone !== '' && str_starts_with($tpPhone, '+') && ! old('country_code')) {
                if (preg_match('/^(\+\d{1,4})(.*)$/', $tpPhone, $m)) {
                    $tpCountry = $m[1];
                    $tpPhone = trim($m[2]);
                }
            }
            $isAgencyUserFlat = auth()->check() && in_array(auth()->user()->user_type ?? '', ['tenant_admin', 'sub_agent', 'super_admin'], true);
            $paymentOptionsFlat = $isAgencyUserFlat
                ? [
                    'Wallet' => 'Pay by wallet',
                    'Reserve' => 'Reserve',
                    'Credit' => 'Credit card',
                    'Debit' => 'Debit card',
                    'BankTransfer' => 'Bank transfer',
                ]
                : [
                    'Credit' => 'Credit card',
                    'Debit' => 'Debit card',
                    'BankTransfer' => 'Bank transfer',
                ];
            $selectedPaymentFlat = old('form_of_payment', $isAgencyUserFlat ? 'Wallet' : 'Credit');
        @endphp
        <div class="col-md-4">
            <label class="form-label">Country code {!! $req !!}</label>
            @php $dialingOptionsFlat = \App\Support\FlightProvider::dialingCodeOptions(); @endphp
            <select name="country_code" class="{{ $select }}" required aria-label="Country code">
                @foreach($dialingOptionsFlat as $code => $label)
                    <option value="{{ $code }}" @selected(($tpCountry ?: $defaultCountryCode) === $code)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Phone {!! $req !!}</label>
            <input type="tel" name="passenger_phone" class="{{ $control }}"
                value="{{ $tpPhone }}"
                required
                minlength="{{ $spec['phone_min'] }}"
                maxlength="{{ $spec['phone_max'] }}"
                pattern="[0-9() \\-]{7,20}"
                title="Local phone number without country code"
                autocomplete="tel"
                inputmode="tel">
        </div>
        <div class="col-md-4">
            <label class="form-label">Payment option {!! $req !!}</label>
            <select name="form_of_payment" class="{{ $select }}" required>
                @foreach($paymentOptionsFlat as $value => $label)
                    <option value="{{ $value }}" @selected($selectedPaymentFlat === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        @if($isAgencyUserFlat)
            <div class="col-md-4">
                <label class="form-label">Ticket print</label>
                <select name="print_with_price" class="{{ $select }}">
                    <option value="1" @selected(old('print_with_price', '1') == '1')>With price</option>
                    <option value="0" @selected(old('print_with_price') === '0' || old('print_with_price') === 0)>Without price</option>
                </select>
            </div>
        @endif
    </div>
@endif

@once
@push('scripts')
<script>
(function () {
    function isValidIranianNationalId(value) {
        var nid = String(value || '').replace(/\D+/g, '');
        if (!/^\d{10}$/.test(nid)) return false;
        if (/^(\d)\1{9}$/.test(nid)) return false;
        var sum = 0;
        for (var i = 0; i < 9; i++) sum += parseInt(nid.charAt(i), 10) * (10 - i);
        var rem = sum % 11;
        var check = rem < 2 ? rem : 11 - rem;
        return check === parseInt(nid.charAt(9), 10);
    }

    function passengerRow(el) {
        return el.closest('.border.rounded') || el.closest('.row') || document;
    }

    function syncNationalIdField(input) {
        var row = passengerRow(input);
        var nationality = row.querySelector('.js-passenger-nationality');
        var code = nationality ? String(nationality.value || '').toUpperCase() : '';
        var iranian = code === 'IRN';
        var reqMark = row.querySelector('.js-national-id-required-mark');
        var optMark = row.querySelector('.js-national-id-optional-mark');
        if (reqMark) reqMark.classList.toggle('d-none', !iranian);
        if (optMark) optMark.classList.toggle('d-none', iranian);
        input.required = iranian;
        if (!iranian) {
            input.setCustomValidity('');
            return;
        }
        var ok = isValidIranianNationalId(input.value);
        input.setCustomValidity(ok ? '' : 'Iranian passengers need a valid 10-digit national ID (کد ملی).');
    }

    function bindNationalIdChecks(root) {
        (root || document).querySelectorAll('[data-national-id-check="1"]').forEach(function (input) {
            if (input.dataset.nidBound === '1') return;
            input.dataset.nidBound = '1';
            var row = passengerRow(input);
            var nationality = row.querySelector('.js-passenger-nationality');
            input.addEventListener('input', function () { syncNationalIdField(input); });
            input.addEventListener('blur', function () { syncNationalIdField(input); });
            if (nationality) {
                nationality.addEventListener('change', function () { syncNationalIdField(input); });
            }
            syncNationalIdField(input);
        });
    }

    function mergePhoneOnSubmit(form) {
        var country = form.querySelector('input[name="country_code"]');
        if (!country) return;
        var cc = String(country.value || '').trim();
        if (!cc) return;
        var phones = form.querySelectorAll('input[name="passengers[0][phone]"], input[name="passenger_phone"]');
        phones.forEach(function (input) {
            var val = String(input.value || '').trim();
            if (!val || val.charAt(0) === '+') return;
            var ccDigits = cc.replace(/\D+/g, '');
            var phoneDigits = val.replace(/\D+/g, '');
            if (!ccDigits || !phoneDigits) return;
            if (phoneDigits.indexOf(ccDigits) === 0 && phoneDigits.length >= ccDigits.length + 7) {
                input.value = '+' + phoneDigits;
            } else {
                input.value = '+' + ccDigits + phoneDigits;
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        bindNationalIdChecks(document);
        document.querySelectorAll('form').forEach(function (form) {
            if (!form.querySelector('input[name="country_code"]')) return;
            form.addEventListener('submit', function () {
                mergePhoneOnSubmit(form);
            });
        });
    });
})();
</script>
@endpush
@endonce
