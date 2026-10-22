@php
    $reservationModel = $reservation ?? null;
    $afterSales = $afterSaleRequests ?? collect();
    if ($reservationModel && $afterSales->isEmpty() && method_exists($reservationModel, 'relationLoaded')) {
        $afterSales = \App\Models\Finance\AfterSaleRequest::query()
            ->where('flight_reservation_id', $reservationModel->id)
            ->orderByDesc('id')
            ->get();
    }
    $afterSaleStoreRoute = $afterSaleStoreRoute ?? null;
    if (! $afterSaleStoreRoute && $reservationModel && ! empty($flightsRoutePrefix)) {
        try {
            $afterSaleStoreRoute = route($flightsRoutePrefix.'.flights.reservations.after-sale', $reservationModel->id);
        } catch (\Throwable $e) {
            $afterSaleStoreRoute = null;
        }
    }
@endphp
@if($reservationModel && $afterSaleStoreRoute)
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h3 class="h6 mb-3">After sale service / After reservation</h3>
        <form method="POST" action="{{ $afterSaleStoreRoute }}" enctype="multipart/form-data" class="row g-3">
            @csrf
            <div class="col-md-4">
                <label class="form-label">Request type</label>
                <select name="type" class="form-select form-select-sm" id="after-sale-type" required>
                    <option value="void_no_penalty">1) Void with no penalty</option>
                    <option value="cancel_with_penalty">2) Cancelled with penalty</option>
                    <option value="change">3) Change (name, upgrade)</option>
                </select>
            </div>
            <div class="col-md-4 js-change-kind d-none">
                <label class="form-label">Change kind</label>
                <select name="change_kind" class="form-select form-select-sm">
                    <option value="name">Name change</option>
                    <option value="upgrade">Upgrade</option>
                </select>
            </div>
            <div class="col-md-2 js-penalty-fields d-none">
                <label class="form-label">Penalty</label>
                <input type="number" step="0.01" min="0" name="penalty_amount" class="form-control form-control-sm" value="0">
            </div>
            <div class="col-md-2 js-penalty-fields d-none">
                <label class="form-label">Currency</label>
                <input type="text" name="penalty_currency" maxlength="3" class="form-control form-control-sm text-uppercase" value="{{ $reservationModel->currency ?: 'USD' }}">
            </div>
            <div class="col-12">
                <label class="form-label">Note</label>
                <textarea name="note" class="form-control form-control-sm" rows="2" placeholder="After-sale note for this request"></textarea>
            </div>
            <div class="col-md-6 js-new-ticket">
                <label class="form-label">Upload new ticket (if changed)</label>
                <input type="file" name="new_ticket" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                <div class="form-text">Agent uploads replacement ticket to the portal after a change.</div>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-sm">Submit after-sale request</button>
            </div>
        </form>

        @if($afterSales->isNotEmpty())
            <hr class="my-3">
            <h4 class="h6">History</h4>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Penalty</th>
                            <th>Note</th>
                            <th>Ticket</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($afterSales as $as)
                            <tr>
                                <td>{{ $as->typeLabel() }}</td>
                                <td><span class="badge bg-light text-dark">{{ $as->status }}</span></td>
                                <td>
                                    @if($as->penalty_amount !== null)
                                        {{ $as->penalty_currency }} {{ number_format((float)$as->penalty_amount, 2) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="small">{{ $as->note }}</td>
                                <td>
                                    @if($as->new_ticket_path)
                                        <a href="{{ asset('storage/'.$as->new_ticket_path) }}" target="_blank">View</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="small">{{ optional($as->created_at)->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@once
@push('scripts')
<script>
(function () {
    function syncAfterSaleForm() {
        var type = document.getElementById('after-sale-type');
        if (!type) return;
        var change = document.querySelectorAll('.js-change-kind');
        var penalty = document.querySelectorAll('.js-penalty-fields');
        var isChange = type.value === 'change';
        var isPenalty = type.value === 'cancel_with_penalty';
        change.forEach(function (el) { el.classList.toggle('d-none', !isChange); });
        penalty.forEach(function (el) { el.classList.toggle('d-none', !isPenalty); });
    }
    document.addEventListener('DOMContentLoaded', function () {
        var type = document.getElementById('after-sale-type');
        if (!type) return;
        type.addEventListener('change', syncAfterSaleForm);
        syncAfterSaleForm();
    });
})();
</script>
@endpush
@endonce
@endif
