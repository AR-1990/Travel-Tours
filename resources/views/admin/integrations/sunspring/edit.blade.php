@extends('admin.layouts.main')

@section('title', 'SunSpring — Integrations')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.integrations.index') }}">Integrations</a></li>
            <li class="breadcrumb-item active" aria-current="page">SunSpring</li>
        </ol>
    </nav>

    <div class="mb-4">
        <h1 class="h3 mb-1 text-gray-800">SunSpring Airline API</h1>
        <p class="text-muted mb-0">REST credentials and endpoints. Step 1: Authorize token. Later: FlightSearch, AirPrice, Book, Ticket.</p>
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
            @if($sunspringHasDbRow)
                <span class="badge bg-success">Saved in <code>integrations</code> table</span>
            @else
                <span class="badge bg-secondary">Using .env defaults only — save below to store in the database</span>
            @endif
            <span class="badge bg-light text-dark border">Base: <code>{{ $baseUrl }}</code></span>
        </div>

        <form method="POST" action="{{ route('admin.integrations.update', ['slug' => 'sunspring']) }}" class="mb-4">
            @csrf
            @method('PUT')
            <div class="form-check form-switch mb-4">
                <input type="hidden" name="is_enabled" value="0">
                <input class="form-check-input" type="checkbox" name="is_enabled" value="1" id="ss_enabled"
                    @checked(old('is_enabled', $sunspringRow?->is_enabled ?? true))>
                <label class="form-check-label" for="ss_enabled">Integration enabled</label>
                <div class="form-text">When off, SunSpring settings in the database are ignored (only <code>.env</code> applies) and token tests are blocked.</div>
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
                    <select name="sunspring[environment]" class="form-select" required>
                        <option value="sandbox" @selected(old('sunspring.environment', $sunspring['environment'] ?? 'sandbox') === 'sandbox')>Sandbox</option>
                        <option value="production" @selected(old('sunspring.environment', $sunspring['environment'] ?? 'sandbox') === 'production')>Production</option>
                    </select>
                    <div class="form-text">Sandbox uses <code>https://sandbox.sunspring.ae</code> unless overridden.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Request timeout (seconds) <span class="text-danger">*</span></label>
                    <input type="number" name="sunspring[timeout]" class="form-control" min="5" max="120" required
                        value="{{ old('sunspring.timeout', $sunspring['timeout'] ?? 60) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Base URL override</label>
                    <input type="text" name="sunspring[base_url_override]" class="form-control" placeholder="https://sandbox.sunspring.ae"
                        value="{{ old('sunspring.base_url_override', $sunspring['base_url_override'] ?? '') }}">
                    <div class="form-text"><strong>Host only</strong> — no trailing path. Leave empty for auto.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">API username <span class="text-danger">*</span></label>
                    @include('admin.partials.secret-input', [
                        'name' => 'sunspring[username]',
                        'id' => 'ss_username',
                        'value' => old('sunspring.username', $sunspring['username'] ?? ''),
                        'required' => true,
                        'autocomplete' => 'off',
                    ])
                </div>
                <div class="col-md-6">
                    <label class="form-label">API password @if(!$passwordSet)<span class="text-danger">*</span>@endif</label>
                    @include('admin.partials.secret-input', [
                        'name' => 'sunspring[password]',
                        'id' => 'ss_password',
                        'value' => old('sunspring.password', $sunspring['password'] ?? ''),
                        'required' => ! $passwordSet,
                        'placeholder' => $passwordSet ? 'Saved password shown — edit to change' : 'Required unless set in .env',
                        'autocomplete' => 'new-password',
                    ])
                    <div class="form-text">Stored encrypted in the database. Leave unchanged and save to keep the current password.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Agency code</label>
                    <input type="text" name="sunspring[agency_code]" class="form-control" autocomplete="off"
                        value="{{ old('sunspring.agency_code', $sunspring['agency_code'] ?? '') }}">
                    <div class="form-text">Optional — only if SunSpring assigned one.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Office ID</label>
                    <input type="text" name="sunspring[office_id]" class="form-control" autocomplete="off"
                        value="{{ old('sunspring.office_id', $sunspring['office_id'] ?? '') }}">
                    <div class="form-text">Optional — only if SunSpring assigned one.</div>
                </div>
            </div>
            <div class="mt-3 d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.integrations.index') }}" class="btn btn-outline-secondary">Back to all integrations</a>
            </div>
        </form>

        <hr class="my-4">

        <h2 class="h6 mb-2">Test connection (Authorize token)</h2>
        <p class="text-muted small mb-2">
            Calls <code>POST /api/v2/accounting/getAuthorizeToken</code> with headers
            <code>api-username</code> / <code>api-password</code>.
            The returned <code>data.authorization</code> JWT is sent as <code>Authorization: Bearer {token}</code> on all other methods.
        </p>
        <pre class="bg-light p-2 rounded small text-break mb-3">{{ $authorizeUrl }}</pre>
        <form action="{{ route('admin.integrations.ping', ['slug' => 'sunspring']) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-primary"><i class="fas fa-plug me-2"></i>Get authorize token</button>
        </form>

        @if(session('sunspring_ping'))
            @php $p = session('sunspring_ping'); @endphp
            <div class="mt-4 p-3 bg-light rounded">
                <p class="mb-1 small"><strong>HTTP:</strong> {{ $p['http_status'] ?? '—' }}
                    @if(!empty($p['token_preview']))
                        · <strong>Token:</strong> {{ $p['token_preview'] }}
                    @endif
                    @if(!empty($p['expired_at']))
                        · <strong>Expires:</strong> {{ \Carbon\Carbon::createFromTimestamp((int) $p['expired_at'])->toDateTimeString() }}
                    @endif
                </p>
                @if(!empty($p['response_excerpt']))
                    <pre class="small text-break mb-0" style="max-height: 240px; overflow: auto;">{{ $p['response_excerpt'] }}</pre>
                @endif
            </div>
        @endif

        <hr class="my-4">

        <h2 class="h6 mb-2">Test FlightSearch</h2>
        <p class="text-muted small mb-2">
            Runs a live sandbox search (default Sepehran route <code>THR → MHD</code>). Empty inventory is common in sandbox.
        </p>
        <form action="{{ route('admin.integrations.test-search', ['slug' => 'sunspring']) }}" method="POST" class="row g-2 align-items-end mb-3">
            @csrf
            <div class="col-auto">
                <label class="form-label small mb-0">Origin</label>
                <input type="text" name="origin" class="form-control form-control-sm" value="THR" maxlength="3" style="width:5rem">
            </div>
            <div class="col-auto">
                <label class="form-label small mb-0">Destination</label>
                <input type="text" name="destination" class="form-control form-control-sm" value="MHD" maxlength="3" style="width:5rem">
            </div>
            <div class="col-auto">
                <label class="form-label small mb-0">Date</label>
                <input type="date" name="departure_date" class="form-control form-control-sm" value="{{ now()->addDays(14)->format('Y-m-d') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-search me-1"></i>Search</button>
            </div>
        </form>

        @if(session('sunspring_lfs'))
            @php $lfs = session('sunspring_lfs'); @endphp
            <div class="p-3 bg-light rounded mb-3">
                <p class="small mb-1"><strong>{{ ($lfs['ok'] ?? false) ? 'OK' : 'Failed' }}:</strong> {{ $lfs['message'] ?? '' }}
                    · {{ (int) ($lfs['total_found'] ?? 0) }} option(s)
                </p>
                @if(!empty($lfs['response_excerpt']))
                    <pre class="small text-break mb-0" style="max-height: 240px; overflow: auto;">{{ $lfs['response_excerpt'] }}</pre>
                @endif
            </div>
        @endif

        <hr class="my-4">

        <h2 class="h6 mb-2">Full journey (wired in Flights)</h2>
        <ul class="small text-muted mb-0">
            <li>Admin / public search → choose <strong>SunSpring</strong> provider</li>
            <li><code>FlightSearch</code> → <code>AirPrice</code> → <code>Book</code> → <code>Confirm</code> → <code>AirDemandTicket</code></li>
            <li>Reservations: ticket / retrieve (TicketInfo) / cancel</li>
        </ul>
    </div>
</div>
@endsection
