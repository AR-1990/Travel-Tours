@php
    $pricedProvider = \App\Support\FlightProvider::fromResult($flightPriceResult ?? null);
    $providerId = strtolower((string) ($flightProvider ?? $pricedProvider ?: \App\Support\FlightProvider::current()));
    if (! in_array($providerId, \App\Support\FlightProvider::all(), true)) {
        $providerId = \App\Support\FlightProvider::TRAVELPORT;
    }
    $stamped = strtolower((string) data_get($flightPriceResult ?? [], 'provider', ''));
    if (in_array($stamped, \App\Support\FlightProvider::all(), true)) {
        $providerId = $stamped;
    }

    $search = $searchInput ?? session('travelport.flight_search.input', session('public.flight_search.input', []));
    $slots = $passengerSlots ?? [];
    $passengers = count($slots) > 0
        ? count($slots)
        : max(1, (int) ($search['adults'] ?? 1) + (int) ($search['children'] ?? 0) + (int) ($search['infants'] ?? 0));

    $displayCurrency = \App\Support\DisplayCurrency::resolve(auth()->user());
    $oldFee = old('service_fee');
    $pricedSolution = data_get($flightPriceResult ?? [], 'solutions.0');
    $paymentInfo = app(\App\Services\Finance\MarkupService::class)->breakdown(
        is_array($pricedSolution) ? $pricedSolution : null,
        auth()->user()?->tenant_id ? (int) auth()->user()->tenant_id : null,
        $providerId,
        $oldFee !== null ? (float) $oldFee : 0.0,
        (string) old('form_of_payment', ''),
        $displayCurrency,
        $passengers,
        $oldFee !== null ? (float) $oldFee : null
    );

    $compact = $compact ?? false;
    $select = $compact ? 'form-select form-select-sm' : 'form-control';
    $req = '<span class="text-danger">*</span>';
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

<div class="book-section book-section-payment mb-3">
    @include('flights.partials.payment-information', [
        'paymentInfo' => $paymentInfo,
        'paymentHeading' => '4. Payment information',
        'passengers' => $passengers,
    ])

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="row g-3">
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
            </div>
        </div>
    </div>
</div>
