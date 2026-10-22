@extends('admin.layouts.main')

@section('title', 'Currency rates')

@section('content')
<div class="container-fluid panel-page">
    @include('admin.partials.page-header', [
        'title' => 'Currency converter (manual)',
        'subtitle' => 'CRUD rates used for agent commission at booking time.',
        'icon' => 'fas fa-exchange-alt',
    ])
    @include('admin.partials.flash')

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h2 class="h6 mb-3">Add / update rate</h2>
            <form method="POST" action="{{ route('admin.finance.currency-rates.store') }}" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-2">
                    <label class="form-label small">Base</label>
                    <input type="text" name="base_currency" value="USD" maxlength="3" class="form-control form-control-sm text-uppercase" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Quote</label>
                    <input type="text" name="quote_currency" value="PKR" maxlength="3" class="form-control form-control-sm text-uppercase" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Rate (1 base = ? quote)</label>
                    <input type="number" step="0.00000001" name="rate" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Notes</label>
                    <input type="text" name="notes" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-check small"><input type="checkbox" name="is_active" value="1" class="form-check-input" checked> Active</label>
                    <button class="btn btn-sm btn-primary d-block mt-1">Save</button>
                </div>
            </form>
        </div>
    </div>

    <div class="panel-surface panel-surface--flush">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Pair</th>
                        <th>Rate</th>
                        <th>Active</th>
                        <th>Updated by</th>
                        <th>Notes</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rates as $rate)
                        <tr>
                            <td><strong>{{ $rate->pairLabel() }}</strong></td>
                            <td>
                                <form method="POST" action="{{ route('admin.finance.currency-rates.update', $rate->id) }}" class="d-inline-flex gap-1">
                                    @csrf @method('PUT')
                                    <input type="number" step="0.00000001" name="rate" value="{{ $rate->rate }}" class="form-control form-control-sm" style="width:8rem">
                                    <input type="hidden" name="is_active" value="{{ $rate->is_active ? 1 : 0 }}">
                                    <button class="btn btn-sm btn-outline-primary">Update</button>
                                </form>
                            </td>
                            <td>{{ $rate->is_active ? 'Yes' : 'No' }}</td>
                            <td class="small">{{ $rate->updater?->email }}</td>
                            <td class="small">{{ $rate->notes }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.finance.currency-rates.destroy', $rate->id) }}" class="d-inline" data-swal-confirm data-swal-title="Delete rate?">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $rates->links() }}</div>
</div>
@endsection
