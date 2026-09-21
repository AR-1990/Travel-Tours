@extends('admin.layouts.main')

@section('title', 'Downtown Travel Hotels — Integrations')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.integrations.index') }}">Integrations</a></li>
            <li class="breadcrumb-item active" aria-current="page">Downtown Travel Hotels</li>
        </ol>
    </nav>

    <div class="mb-4 d-flex flex-wrap align-items-center gap-2">
        <h1 class="h3 mb-0 text-gray-800">Downtown Travel Hotels API</h1>
        @include('admin.integrations.partials.environment-badge', [
            'environment' => $downtown['environment'] ?? 'sandbox',
        ])
        <p class="text-muted mb-0 w-100">
            Hotels API v2 (OAuth2).
            Docs:
            <a href="https://dtt-hotels.readme.io/reference/workflow" target="_blank" rel="noopener">Workflow</a>
            · Credentials from <code>api@downtowntravel.com</code>.
        </p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 small">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card-modern p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
            @if($downtownHasDbRow)
                <span class="badge bg-success">Saved in <code>integrations</code> table</span>
            @else
                <span class="badge bg-secondary">Using .env defaults only — save below to store in the database</span>
            @endif
            <div class="d-flex flex-wrap gap-2">
                <span class="badge bg-light text-dark border">SSO: <code>{{ $ssoBaseUrl }}</code></span>
                <span class="badge bg-light text-dark border">Hotels: <code>{{ $hotelsBaseUrl }}</code></span>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.integrations.update', ['slug' => 'downtown_travel_hotels']) }}" class="mb-4">
            @csrf
            @method('PUT')
            <div class="form-check form-switch mb-4">
                <input type="hidden" name="is_enabled" value="0">
                <input class="form-check-input" type="checkbox" name="is_enabled" value="1" id="dth_enabled"
                    @checked(old('is_enabled', $downtownRow?->is_enabled ?? true))>
                <label class="form-check-label" for="dth_enabled">Integration enabled</label>
                <div class="form-text">When off, database settings are ignored (only <code>.env</code> applies) and token tests are blocked.</div>
            </div>

            <div class="d-flex flex-wrap gap-2 mb-3">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-secret-toggle-all="show">
                    <i class="fas fa-eye me-1"></i> Show all secrets
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-secret-toggle-all="hide">
                    <i class="fas fa-eye-slash me-1"></i> Hide all secrets
                </button>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Environment <span class="text-danger">*</span></label>
                    <select name="downtown[environment]" class="form-select" required>
                        <option value="sandbox" @selected(old('downtown.environment', $downtown['environment'] ?? 'sandbox') === 'sandbox')>Sandbox</option>
                        <option value="production" @selected(old('downtown.environment', $downtown['environment'] ?? 'sandbox') === 'production')>Production</option>
                    </select>
                    <div class="form-text">Sandbox Hotels: <code>hotels.sandbox.thebestagent.pro</code></div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Request timeout (seconds) <span class="text-danger">*</span></label>
                    <input type="number" name="downtown[timeout]" class="form-control" min="5" max="120" required
                        value="{{ old('downtown.timeout', $downtown['timeout'] ?? 90) }}">
                </div>
                <div class="col-md-4"></div>

                <div class="col-md-6">
                    <label class="form-label">SSO base URL override</label>
                    <input type="text" name="downtown[sso_base_url_override]" class="form-control" placeholder="https://sso.sandbox.thebestagent.pro"
                        value="{{ old('downtown.sso_base_url_override', $downtown['sso_base_url_override'] ?? '') }}">
                    <div class="form-text"><strong>Host only</strong> — used for <code>/oauth/token</code>.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Hotels base URL override</label>
                    <input type="text" name="downtown[hotels_base_url_override]" class="form-control" placeholder="https://hotels.sandbox.thebestagent.pro"
                        value="{{ old('downtown.hotels_base_url_override', $downtown['hotels_base_url_override'] ?? '') }}">
                    <div class="form-text"><strong>Host only</strong> — used for <code>/partner/v2/*</code>.</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Client ID <span class="text-danger">*</span></label>
                    @include('admin.partials.secret-input', [
                        'name' => 'downtown[client_id]',
                        'id' => 'dth_client_id',
                        'value' => old('downtown.client_id', $downtown['client_id'] ?? ''),
                        'required' => true,
                        'autocomplete' => 'off',
                    ])
                </div>
                <div class="col-md-6">
                    <label class="form-label">Client secret @if(!$clientSecretSet)<span class="text-danger">*</span>@endif</label>
                    @include('admin.partials.secret-input', [
                        'name' => 'downtown[client_secret]',
                        'id' => 'dth_client_secret',
                        'value' => old('downtown.client_secret', $downtown['client_secret'] ?? ''),
                        'required' => ! $clientSecretSet,
                        'placeholder' => $clientSecretSet ? 'Saved secret shown — edit to change' : 'Required unless set in .env',
                        'autocomplete' => 'new-password',
                    ])
                </div>

                <div class="col-md-6">
                    <label class="form-label">Username <span class="text-danger">*</span></label>
                    @include('admin.partials.secret-input', [
                        'name' => 'downtown[username]',
                        'id' => 'dth_username',
                        'value' => old('downtown.username', $downtown['username'] ?? ''),
                        'required' => true,
                        'autocomplete' => 'off',
                    ])
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password @if(!$passwordSet)<span class="text-danger">*</span>@endif</label>
                    @include('admin.partials.secret-input', [
                        'name' => 'downtown[password]',
                        'id' => 'dth_password',
                        'value' => old('downtown.password', $downtown['password'] ?? ''),
                        'required' => ! $passwordSet,
                        'placeholder' => $passwordSet ? 'Saved password shown — edit to change' : 'Required unless set in .env',
                        'autocomplete' => 'new-password',
                    ])
                </div>
            </div>
            <div class="mt-3 d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.integrations.index') }}" class="btn btn-outline-secondary">Back to all integrations</a>
            </div>
        </form>

        <hr class="my-4">

        <h2 class="h6 mb-2">Test connection (Get Token)</h2>
        <p class="text-muted small mb-2">
            Same OAuth password grant as Air:
            <code>POST /oauth/token</code> with Basic <code>client_id</code>/<code>client_secret</code>.
        </p>
        <pre class="bg-light p-2 rounded small text-break mb-3">{{ $tokenUrl }}</pre>
        <form action="{{ route('admin.integrations.ping', ['slug' => 'downtown_travel_hotels']) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-primary"><i class="fas fa-plug me-2"></i>Get access token</button>
        </form>

        @if(session('downtown_hotels_ping'))
            @php $p = session('downtown_hotels_ping'); @endphp
            <div class="mt-4 p-3 bg-light rounded">
                <p class="mb-1 small"><strong>HTTP:</strong> {{ $p['http_status'] ?? '—' }}
                    @if(!empty($p['token_preview']))
                        · <strong>Token:</strong> {{ $p['token_preview'] }}
                    @endif
                    @if(!empty($p['expires_in']))
                        · <strong>Expires in:</strong> {{ (int) $p['expires_in'] }}s
                    @endif
                </p>
                @if(!empty($p['response_excerpt']))
                    <pre class="small text-break mb-0" style="max-height: 240px; overflow: auto;">{{ $p['response_excerpt'] }}</pre>
                @endif
            </div>
        @endif

        <hr class="my-4">

        <h2 class="h6 mb-2">Test Check Availability</h2>
        <p class="text-muted small mb-2">
            Runs <code>POST /partner/v2/check_availability</code> (London radius sample from docs).
        </p>
        <form action="{{ route('admin.integrations.test-search', ['slug' => 'downtown_travel_hotels']) }}" method="POST" class="row g-2 align-items-end mb-3">
            @csrf
            <div class="col-auto">
                <label class="form-label small mb-0">Check-in</label>
                <input type="date" name="check_in" class="form-control form-control-sm" value="{{ now()->addMonths(2)->format('Y-m-d') }}">
            </div>
            <div class="col-auto">
                <label class="form-label small mb-0">Check-out</label>
                <input type="date" name="check_out" class="form-control form-control-sm" value="{{ now()->addMonths(2)->addDays(2)->format('Y-m-d') }}">
            </div>
            <div class="col-auto">
                <label class="form-label small mb-0">Lat</label>
                <input type="text" name="latitude" class="form-control form-control-sm" value="51.50735" style="width:6.5rem">
            </div>
            <div class="col-auto">
                <label class="form-label small mb-0">Lng</label>
                <input type="text" name="longitude" class="form-control form-control-sm" value="-0.12776" style="width:6.5rem">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-search me-1"></i>Check availability</button>
            </div>
        </form>

        @if(session('downtown_hotels_availability'))
            @php $a = session('downtown_hotels_availability'); @endphp
            <div class="p-3 bg-light rounded mb-3">
                <p class="small mb-1"><strong>{{ ($a['ok'] ?? false) ? 'OK' : 'Failed' }}:</strong> {{ $a['message'] ?? '' }}
                    · {{ (int) ($a['total_found'] ?? 0) }} properties
                    @if(!empty($a['session_id']))
                        · session <code>{{ $a['session_id'] }}</code>
                    @endif
                </p>
                @if(!empty($a['response_excerpt']))
                    <pre class="small text-break mb-0" style="max-height: 240px; overflow: auto;">{{ $a['response_excerpt'] }}</pre>
                @endif
            </div>
        @endif

        <hr class="my-4">

        <h2 class="h6 mb-2">API workflow (from docs)</h2>
        <ol class="small text-muted mb-0">
            <li>Check Availability (radius / rectangle / property IDs)</li>
            <li>Retrieve Property Details (optional)</li>
            <li>Get Offers → Validate Offer Price</li>
            <li>Create Order → Book Order → Get Order Details / Cancel</li>
        </ol>
        <p class="small text-muted mt-2 mb-0">Public Hotels UI wiring can be added after credentials are verified here.</p>
    </div>
</div>
@endsection
