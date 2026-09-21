@extends('admin.layouts.main')

@section('title', 'Hotel Reservations')

@section('content')
<div class="container-fluid">
    @include('hotels.partials.nav')

    <nav aria-label="breadcrumb" class="mb-2">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item active">Hotel reservations</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800"><i class="fas fa-hotel me-2"></i>Hotel reservations</h1>
            <p class="text-muted mb-0">Bookings from panel and public hotel search (Downtown Travel / Xconnect).</p>
        </div>
        <a href="{{ route($hotelsRoutePrefix . '.hotels.search') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-search me-1"></i> Search hotels
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <form method="GET"
        action="{{ route($hotelsRoutePrefix . '.hotels.reservations.index') }}"
        class="card border-0 shadow-sm mb-3 js-ajax-filter-form"
        data-results="#js-ajax-filter-results">
        <div class="card-body py-3">
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
        </div>
    </form>

    <div class="card border-0 shadow-sm" id="js-ajax-filter-results" data-ajax-filter-results>
        @include('hotels.reservations.partials.results', [
            'reservations' => $reservations,
            'hotelsRoutePrefix' => $hotelsRoutePrefix,
        ])
    </div>
</div>
<style>
.provider-badge{display:inline-flex;align-items:center;gap:.35rem;padding:.3rem .7rem;border-radius:999px;font-size:.75rem;font-weight:600;border:1px solid transparent;white-space:nowrap}
.provider-badge--sm{font-size:.68rem;padding:.2rem .55rem}
.provider-badge--downtown{background:#fff7ed;color:#9a3412;border-color:#fed7aa}
.provider-badge--xconnect{background:#eff6ff;color:#1e40af;border-color:#bfdbfe}
</style>
@endsection

@push('scripts')
    @include('admin.partials.ajax-filters')
@endpush
