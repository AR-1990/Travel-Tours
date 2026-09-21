@extends('admin.layouts.main')

@section('title', 'Downtown Travel — Integrations')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.integrations.index') }}">Integrations</a></li>
            <li class="breadcrumb-item active" aria-current="page">Downtown Travel</li>
        </ol>
    </nav>

    <div class="mb-4 d-flex flex-wrap align-items-center gap-2">
        <h1 class="h3 mb-0 text-gray-800">Downtown Travel Air API</h1>
        @include('admin.integrations.partials.environment-badge', [
            'environment' => $downtown['environment'] ?? 'sandbox',
        ])
        <p class="text-muted mb-0 w-100">
            OAuth2 + REST Air APIs.
            Docs:
            <a href="https://documenter.getpostman.com/view/28465574/2s9Xy2PrwH" target="_blank" rel="noopener">Postman collection</a>.
            Credentials from <code>api@downtowntravel.com</code>.
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
                <span class="badge bg-light text-dark border">Air: <code>{{ $airBaseUrl }}</code></span>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.integrations.update', ['slug' => 'downtown_travel']) }}" class="mb-4">
            @csrf
            @method('PUT')
            <div class="form-check form-switch mb-4">
                <input type="hidden" name="is_enabled" value="0">
                <input class="form-check-input" type="checkbox" name="is_enabled" value="1" id="dt_enabled"
                    @checked(old('is_enabled', $downtownRow?->is_enabled ?? true))>
                <label class="form-check-label" for="dt_enabled">Integration enabled</label>
                <div class="form-text">When off, Downtown Travel settings in the database are ignored (only <code>.env</code> applies) and token tests are blocked.</div>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-secret-toggle-all="show">
                        <i class="fas fa-eye me-1"></i> Show all secrets
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-secret-toggle-all="hide">
                        <i class="fas fa-eye-slash me-1"></i> Hide all secrets
                    </button>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Environment <span class="text-danger">*</span></label>
                    <select name="downtown[environment]" class="form-select" required>
                        <option value="sandbox" @selected(old('downtown.environment', $downtown['environment'] ?? 'sandbox') === 'sandbox')>Sandbox</option>
                        <option value="production" @selected(old('downtown.environment', $downtown['environment'] ?? 'sandbox') === 'production')>Production</option>
                    </select>
                    <div class="form-text">Sandbox SSO: <code>sso.sandbox.thebestagent.pro</code> · Air: <code>air.sandbox.thebestagent.pro</code></div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Request timeout (seconds) <span class="text-danger">*</span></label>
                    <input type="number" name="downtown[timeout]" class="form-control" min="5" max="120" required
                        value="{{ old('downtown.timeout', $downtown['timeout'] ?? 60) }}">
                </div>
                <div class="col-md-4"></div>

                <div class="col-md-6">
                    <label class="form-label">SSO base URL override</label>
                    <input type="text" name="downtown[sso_base_url_override]" class="form-control" placeholder="https://sso.sandbox.thebestagent.pro"
                        value="{{ old('downtown.sso_base_url_override', $downtown['sso_base_url_override'] ?? '') }}">
                    <div class="form-text"><strong>Host only</strong> — used for <code>/oauth/token</code>.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Air base URL override</label>
                    <input type="text" name="downtown[air_base_url_override]" class="form-control" placeholder="https://air.sandbox.thebestagent.pro"
                        value="{{ old('downtown.air_base_url_override', $downtown['air_base_url_override'] ?? '') }}">
                    <div class="form-text"><strong>Host only</strong> — used for <code>/api/public/v2/*</code>.</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Client ID <span class="text-danger">*</span></label>
                    @include('admin.partials.secret-input', [
                        'name' => 'downtown[client_id]',
                        'id' => 'dt_client_id',
                        'value' => old('downtown.client_id', $downtown['client_id'] ?? ''),
                        'required' => true,
                        'autocomplete' => 'off',
                    ])
                    <div class="form-text">OAuth Basic auth username for Get Token.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Client secret @if(!$clientSecretSet)<span class="text-danger">*</span>@endif</label>
                    @include('admin.partials.secret-input', [
                        'name' => 'downtown[client_secret]',
                        'id' => 'dt_client_secret',
                        'value' => old('downtown.client_secret', $downtown['client_secret'] ?? ''),
                        'required' => ! $clientSecretSet,
                        'placeholder' => $clientSecretSet ? 'Saved secret shown — edit to change' : 'Required unless set in .env',
                        'autocomplete' => 'new-password',
                    ])
                    <div class="form-text">OAuth Basic auth password for Get Token.</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Username <span class="text-danger">*</span></label>
                    @include('admin.partials.secret-input', [
                        'name' => 'downtown[username]',
                        'id' => 'dt_username',
                        'value' => old('downtown.username', $downtown['username'] ?? ''),
                        'required' => true,
                        'autocomplete' => 'off',
                    ])
                    <div class="form-text">Password-grant resource owner username.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password @if(!$passwordSet)<span class="text-danger">*</span>@endif</label>
                    @include('admin.partials.secret-input', [
                        'name' => 'downtown[password]',
                        'id' => 'dt_password',
                        'value' => old('downtown.password', $downtown['password'] ?? ''),
                        'required' => ! $passwordSet,
                        'placeholder' => $passwordSet ? 'Saved password shown — edit to change' : 'Required unless set in .env',
                        'autocomplete' => 'new-password',
                    ])
                    <div class="form-text">Stored encrypted in the database. Leave unchanged and save to keep the current password.</div>
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
            Calls <code>POST /oauth/token</code> with Basic <code>client_id</code>/<code>client_secret</code>
            and body <code>grant_type=password</code> + username/password.
            Returns <code>access_token</code> / <code>refresh_token</code> for Air API calls.
        </p>
        <pre class="bg-light p-2 rounded small text-break mb-3">{{ $tokenUrl }}</pre>
        <form action="{{ route('admin.integrations.ping', ['slug' => 'downtown_travel']) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-primary"><i class="fas fa-plug me-2"></i>Get access token</button>
        </form>

        @if(session('downtown_ping'))
            @php $p = session('downtown_ping'); @endphp
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

        <h2 class="h6 mb-2">Test Search</h2>
        <p class="text-muted small mb-2">
            Runs <code>POST /api/public/v2/search</code> (default sample route <code>NYC → ZRH</code> from the Postman docs).
        </p>
        <form action="{{ route('admin.integrations.test-search', ['slug' => 'downtown_travel']) }}" method="POST" class="row g-2 align-items-end mb-3">
            @csrf
            <div class="col-auto">
                <label class="form-label small mb-0">Origin</label>
                <input type="text" name="origin" class="form-control form-control-sm" value="NYC" maxlength="3" style="width:5rem">
            </div>
            <div class="col-auto">
                <label class="form-label small mb-0">Destination</label>
                <input type="text" name="destination" class="form-control form-control-sm" value="ZRH" maxlength="3" style="width:5rem">
            </div>
            <div class="col-auto">
                <label class="form-label small mb-0">Date</label>
                <input type="date" name="departure_date" class="form-control form-control-sm" value="{{ now()->addDays(21)->format('Y-m-d') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-search me-1"></i>Search</button>
            </div>
        </form>

        @if(session('downtown_search'))
            @php $s = session('downtown_search'); @endphp
            <div class="p-3 bg-light rounded mb-3">
                <p class="small mb-1"><strong>{{ ($s['ok'] ?? false) ? 'OK' : 'Failed' }}:</strong> {{ $s['message'] ?? '' }}
                    · {{ (int) ($s['total_found'] ?? 0) }} result group(s)
                </p>
                @if(!empty($s['response_excerpt']))
                    <pre class="small text-break mb-0" style="max-height: 240px; overflow: auto;">{{ $s['response_excerpt'] }}</pre>
                @endif
            </div>
        @endif

        <hr class="my-4">

        <h2 class="h6 mb-2">API surface (from docs)</h2>
        <ul class="small text-muted mb-0">
            <li>Get Token / Refresh Token</li>
            <li>Search → Preliminary Booking → Book → Confirm price → Issue tickets</li>
            <li>Cancel / Void / Refund Offer / Refund / Order details</li>
        </ul>
        <p class="small text-muted mt-2 mb-0">Full Flights UI wiring (provider switch) can be added next once credentials are verified here.</p>
    </div>
</div>
@endsection
