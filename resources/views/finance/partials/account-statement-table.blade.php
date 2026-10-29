@php
    $includeCustomer = (bool) ($includeCustomer ?? true);
@endphp
<div class="table-responsive">
    <table class="table table-sm table-striped table-hover mb-0 align-middle">
        <thead>
            <tr class="small text-nowrap">
                <th class="text-end">Debit</th>
                <th class="text-end">Credit</th>
                <th class="text-end">Balance</th>
                <th>Tx. date</th>
                <th>Maturity</th>
                <th>Transaction type</th>
                <th>Description</th>
                <th>PNR</th>
                <th>Order ID</th>
                @if($includeCustomer)
                    <th>Customer</th>
                @endif
                <th>Seller</th>
                <th>Carrier</th>
                <th>Provider</th>
                <th>Unit</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $tx)
                <tr class="small text-nowrap">
                    <td class="text-end">{{ number_format($tx->debitAmount(), 2) }}</td>
                    <td class="text-end">{{ number_format($tx->creditAmount(), 2) }}</td>
                    <td class="text-end">{{ number_format((float) $tx->balance_after, 2) }}</td>
                    <td>{{ optional($tx->created_at)->format('Y-m-d H:i') }}</td>
                    <td>{{ $tx->maturityLabel() }}</td>
                    <td><span class="badge text-bg-primary">{{ $tx->typeLabel() }}</span></td>
                    <td class="text-wrap" style="min-width:12rem;max-width:22rem;">{{ $tx->descriptionLabel() }}</td>
                    <td>{{ $tx->pnrLabel() }}</td>
                    <td>{{ $tx->orderIdLabel() }}</td>
                    @if($includeCustomer)
                        <td>{{ $tx->customerLabel() }}</td>
                    @endif
                    <td>{{ $tx->sellerLabel() }}</td>
                    <td>{{ $tx->carrierLabel() }}</td>
                    <td>{{ $tx->providerLabel() }}</td>
                    <td>{{ $tx->unitLabel() }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $includeCustomer ? 14 : 13 }}" class="text-muted text-center py-4">No transactions in this range.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
