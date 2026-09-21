@extends('admin.layouts.main')

@section('title', 'Hotel Reservations')

@section('content')
<div class="container-fluid panel-page">
    @include('hotels.partials.nav')

    @include('admin.partials.page-header', [
        'title' => 'Hotel reservations',
        'subtitle' => 'Bookings from panel and public hotel search (Downtown Travel / Xconnect).',
        'icon' => 'fas fa-hotel',
        'actions' => '<a href="'.e(route($hotelsRoutePrefix.'.hotels.search')).'" class="btn btn-primary btn-sm"><i class="fas fa-search me-1"></i> Search hotels</a>',
    ])

    @include('admin.partials.flash')

    <form method="GET"
        action="{{ route($hotelsRoutePrefix . '.hotels.reservations.index') }}"
        class="panel-surface mb-3 js-ajax-filter-form"
        data-results="#js-ajax-filter-results">
        <div class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small text-muted mb-1">Search</label>
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control form-control-sm"
                    placeholder="Reference, hotel, guest, city…" autocomplete="off">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="confirmed" @selected(($filters['status'] ?? '') === 'confirmed')>Confirmed</option>
                    <option value="cancelled" @selected(($filters['status'] ?? '') === 'cancelled')>Cancelled</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Provider</label>
                <select name="provider" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach(($providerOptions ?? []) as $option)
                        <option value="{{ $option['id'] }}" @selected(($filters['provider'] ?? '') === $option['id'])>
                            {{ $option['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary btn-sm w-100">Filter</button>
            </div>
        </div>
    </form>

    <div class="panel-surface panel-surface--flush" id="js-ajax-filter-results" data-ajax-filter-results>
        @include('hotels.reservations.partials.results', [
            'reservations' => $reservations,
            'hotelsRoutePrefix' => $hotelsRoutePrefix,
        ])
    </div>
</div>
@endsection

@push('scripts')
    @include('admin.partials.ajax-filters')
@endpush
