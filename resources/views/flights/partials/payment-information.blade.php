@php
    $paymentInfo = $paymentInfo ?? null;
    if (! is_array($paymentInfo)) {
        $paymentInfo = [];
    }
    $currency = $paymentInfo['currency'] ?? 'USD';
    $isAgency = auth()->check() && in_array(auth()->user()->user_type ?? '', ['tenant_admin', 'sub_agent', 'super_admin'], true);
@endphp
@if(!empty($paymentInfo))
<div class="card border-0 shadow-sm mb-3 payment-information-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 class="h6 mb-0"><i class="fas fa-plane text-success me-1"></i> Payment information</h2>
            <span class="badge bg-light text-dark">Published fare</span>
        </div>
        <div class="row small">
            <div class="col-6">Fare</div>
            <div class="col-6 text-end">{{ $currency }} {{ number_format((float)($paymentInfo['fare'] ?? 0), 2) }}</div>
            <div class="col-6">Taxes</div>
            <div class="col-6 text-end">{{ $currency }} {{ number_format((float)($paymentInfo['taxes'] ?? 0), 2) }}</div>
            <div class="col-6 fw-semibold">Airline total</div>
            <div class="col-6 text-end fw-semibold">{{ $currency }} {{ number_format((float)($paymentInfo['airline_total'] ?? 0), 2) }}</div>
            <div class="col-6">Ticketing fee</div>
            <div class="col-6 text-end">{{ $currency }} {{ number_format((float)($paymentInfo['ticketing_fee'] ?? 0), 2) }}</div>
            @if(($paymentInfo['airline_fees_total'] ?? 0) > 0)
                <div class="col-6">Airline fees</div>
                <div class="col-6 text-end">{{ $currency }} {{ number_format((float)$paymentInfo['airline_fees_total'], 2) }}</div>
                @foreach(($paymentInfo['airline_fees'] ?? []) as $feeRow)
                    <div class="col-6 text-muted ps-3">{{ $feeRow['label'] ?? 'Fee' }}</div>
                    <div class="col-6 text-end text-muted">{{ number_format((float)($feeRow['amount'] ?? 0), 2) }}</div>
                @endforeach
            @endif
            @if(($paymentInfo['markup_amount'] ?? 0) != 0)
                <div class="col-6">Agency markup <span class="text-muted">(base fare only)</span></div>
                <div class="col-6 text-end">{{ $currency }} {{ number_format((float)$paymentInfo['markup_amount'], 2) }}</div>
            @endif
            @if(!empty($paymentInfo['booking_currency']) && ($paymentInfo['booking_currency'] ?? '') !== $currency)
                <div class="col-6">Commission ({{ $paymentInfo['booking_currency'] }} @ {{ number_format((float)($paymentInfo['currency_rate'] ?? 1), 4) }})</div>
                <div class="col-6 text-end">{{ $paymentInfo['booking_currency'] }} {{ number_format((float)($paymentInfo['commission_amount'] ?? 0), 2) }}</div>
            @endif
        </div>
        @if($isAgency)
            <hr class="my-2">
            <div class="small mb-1">Your profit counts</div>
            <div class="input-group input-group-sm mb-2" style="max-width: 16rem;">
                <span class="input-group-text">{{ $currency }}</span>
                <input type="number" step="0.01" name="service_fee" class="form-control js-service-fee"
                    value="{{ old('service_fee', $paymentInfo['service_fee'] ?? 0) }}"
                    @if($readonly ?? false) readonly @endif
                    aria-label="Agent extra service fee / discount">
            </div>
            <div class="small">Agent extra service fee / discount</div>
            <div class="fw-semibold mt-1 js-agent-profit">Your profit {{ $currency }} {{ number_format((float)($paymentInfo['agent_profit'] ?? 0), 2) }}</div>
        @else
            <input type="hidden" name="service_fee" value="{{ old('service_fee', $paymentInfo['service_fee'] ?? 0) }}">
        @endif
        <hr class="my-2">
        <div class="d-flex justify-content-between">
            <strong>Sell total</strong>
            <strong class="js-sell-total">{{ $currency }} {{ number_format((float)($paymentInfo['sell_total'] ?? 0), 2) }}</strong>
        </div>
        @if(!empty($paymentInfo['form_of_payment']))
            <div class="small text-muted mt-1">Payment option: {{ $paymentInfo['form_of_payment'] }}</div>
        @endif
    </div>
</div>
@if($isAgency && empty($readonly))
@once
@push('scripts')
<script>
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var fee = document.querySelector('.js-service-fee');
        if (!fee) return;
        var markup = {{ (float) ($paymentInfo['markup_amount'] ?? 0) }};
        var airline = {{ (float) ($paymentInfo['airline_total'] ?? 0) }};
        var ticketFee = {{ (float) ($paymentInfo['ticketing_fee'] ?? 0) }};
        var currency = @json($currency);
        function sync() {
            var service = parseFloat(fee.value || '0') || 0;
            var profit = markup + service;
            var sell = airline + markup + service + ticketFee;
            var profitEl = document.querySelector('.js-agent-profit');
            var sellEl = document.querySelector('.js-sell-total');
            if (profitEl) profitEl.textContent = 'Your profit ' + currency + ' ' + profit.toFixed(2);
            if (sellEl) sellEl.textContent = currency + ' ' + sell.toFixed(2);
        }
        fee.addEventListener('input', sync);
        sync();
    });
})();
</script>
@endpush
@endonce
@endif
@endif
