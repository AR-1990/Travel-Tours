@extends('admin.layouts.main')

@section('title', 'Xconnect — Integrations')

@section('content')
<div class="container-fluid panel-page">
    @include('admin.partials.page-header', [
        'title' => 'Xconnect Hotel API',
        'subtitle' => 'Technoheaven / Rimo wholesale hotels. Countries → Availability → Book → Cancel.',
        'icon' => 'fas fa-hotel',
        'actions' => '<a href="'.e(route('admin.integrations.index')).'" class="btn btn-light btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>',
    ])

    @include('admin.partials.flash')

    <div class="mb-3">
        @include('admin.integrations.partials.environment-badge', [
            'environment' => $xconnect['environment'] ?? 'sandbox',
        ])
        <p class="text-muted small mb-0 mt-2">
            <a href="https://documenter.getpostman.com/view/11578141/TVKBYJAE" target="_blank" rel="noopener">API docs</a>.
            The published Xconnect collection is <strong>hotels only</strong>.
        </p>
    </div>

    <div class="panel-surface">
        <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
            @if($xconnectHasDbRow)
                <span class="badge bg-success">Saved in <code>integrations</code> table</span>
            @else
                <span class="badge bg-secondary">Using .env defaults only — save below to store in the database</span>
            @endif
            <span class="badge bg-light text-dark border">Base: <code>{{ $baseUrl !== '' ? $baseUrl : 'not set' }}</code></span>
        </div>

        <form method="POST" action="{{ route('admin.integrations.update', ['slug' => 'xconnect']) }}" class="mb-4">
            @csrf
            @method('PUT')
            <div class="form-check form-switch mb-4">
                <input type="hidden" name="is_enabled" value="0">
                <input class="form-check-input" type="checkbox" name="is_enabled" value="1" id="xc_enabled"
                    @checked(old('is_enabled', $xconnectRow?->is_enabled ?? true))>
                <label class="form-check-label" for="xc_enabled">Integration enabled</label>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Environment</label>
                    <select name="xconnect[environment]" class="form-select" required>
                        <option value="sandbox" @selected(old('xconnect.environment', $xconnect['environment'] ?? 'sandbox') === 'sandbox')>Sandbox / Test</option>
                        <option value="production" @selected(old('xconnect.environment', $xconnect['environment'] ?? 'sandbox') === 'production')>Production</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Timeout (seconds)</label>
                    <input type="number" name="xconnect[timeout]" class="form-control" min="5" max="180" required
                        value="{{ old('xconnect.timeout', $xconnect['timeout'] ?? 90) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Default currency</label>
                    <input type="text" name="xconnect[default_currency]" class="form-control" maxlength="8"
                        value="{{ old('xconnect.default_currency', $xconnect['default_currency'] ?? 'USD') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Base URL <span class="text-danger">*</span></label>
                    <input type="text" name="xconnect[base_url]" class="form-control" placeholder="https://your-xconnect-host.example"
                        value="{{ old('xconnect.base_url', $xconnect['base_url'] ?? '') }}" required>
                    <div class="form-text">Host only — provided by Technoheaven/Rimo (Postman uses <code>@{{ baseurl }}</code>).</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Base URL override</label>
                    <input type="text" name="xconnect[base_url_override]" class="form-control"
                        value="{{ old('xconnect.base_url_override', $xconnect['base_url_override'] ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">API Token @if(!$tokenSet)<span class="text-danger">*</span>@endif</label>
                    @include('admin.partials.secret-input', [
                        'name' => 'xconnect[token]',
                        'id' => 'xc_token',
                        'value' => old('xconnect.token', $xconnect['token'] ?? ''),
                        'required' => ! $tokenSet,
                        'autocomplete' => 'off',
                    ])
                    <div class="form-text">Whitelisted IP + Token auth. Leave blank on save to keep the stored token.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Default nationality</label>
                    <input type="text" name="xconnect[default_nationality]" class="form-control"
                        value="{{ old('xconnect.default_nationality', $xconnect['default_nationality'] ?? 'india') }}">
                    <div class="form-text">Must match a country name from the Countries API (case as returned).</div>
                </div>
            </div>

            <div class="mt-4 d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary">Save settings</button>
            </div>
        </form>

        <hr>

        <div class="d-flex flex-wrap gap-2 mb-3">
            <form method="POST" action="{{ route('admin.integrations.ping', ['slug' => 'xconnect']) }}">
                @csrf
                <button type="submit" class="btn btn-outline-primary btn-sm" @disabled($baseUrl === '' || ! $tokenSet)>Test Countries (ping)</button>
            </form>
            <form method="POST" action="{{ route('admin.integrations.test-search', ['slug' => 'xconnect']) }}" class="d-flex flex-wrap gap-2 align-items-end">
                @csrf
                <div>
                    <label class="form-label form-label-sm mb-0">City ID</label>
                    <input type="text" name="city_id" class="form-control form-control-sm" value="{{ old('city_id', '') }}" placeholder="from Cities API" required>
                </div>
                <div>
                    <label class="form-label form-label-sm mb-0">Check-in</label>
                    <input type="date" name="check_in" class="form-control form-control-sm" value="{{ now()->addMonths(2)->format('Y-m-d') }}" required>
                </div>
                <div>
                    <label class="form-label form-label-sm mb-0">Check-out</label>
                    <input type="date" name="check_out" class="form-control form-control-sm" value="{{ now()->addMonths(2)->addDay()->format('Y-m-d') }}" required>
                </div>
                <button type="submit" class="btn btn-outline-secondary btn-sm" @disabled($baseUrl === '' || ! $tokenSet)>Test Availability</button>
            </form>
        </div>

        @if(session('xconnect_ping'))
            <pre class="bg-light border rounded p-3 small">{{ json_encode(session('xconnect_ping'), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
        @endif
        @if(session('xconnect_availability'))
            <pre class="bg-light border rounded p-3 small">{{ json_encode(session('xconnect_availability'), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
        @endif
        </div>
    </div>
</div>
@endsection
