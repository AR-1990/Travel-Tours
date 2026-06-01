@extends('admin.layouts.main')

@section('title', 'Travelport — Integrations')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.integrations.index') }}">Integrations</a></li>
            <li class="breadcrumb-item active" aria-current="page">Travelport</li>
        </ol>
    </nav>

    <div class="mb-4">
        <h1 class="h3 mb-1 text-gray-800">Travelport Universal API</h1>
        <p class="text-muted mb-0">SOAP credentials and endpoints. Step 1: Ping. Later: Air shopping, availability, booking.</p>
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
            @if($travelportHasDbRow)
                <span class="badge bg-success">Saved in <code>integrations</code> table</span>
            @else
                <span class="badge bg-secondary">Using .env defaults only — save below to store in the database</span>
            @endif
        </div>

        <form method="POST" action="{{ route('admin.integrations.update', ['slug' => 'travelport']) }}" class="mb-4">
            @csrf
            @method('PUT')
            <div class="form-check form-switch mb-4">
                <input type="hidden" name="is_enabled" value="0">
                <input class="form-check-input" type="checkbox" name="is_enabled" value="1" id="tp_enabled"
                    @checked(old('is_enabled', $travelportRow?->is_enabled ?? true))>
                <label class="form-check-label" for="tp_enabled">Integration enabled</label>
                <div class="form-text">When off, Travelport settings in the database are ignored (only <code>.env</code> applies) and Ping is blocked.</div>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Region <span class="text-danger">*</span></label>
                    <select name="travelport[region]" class="form-select" required>
                        @foreach (['emea' => 'EMEA', 'americas' => 'Americas', 'apac' => 'APAC'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('travelport.region', $travelport['region'] ?? 'emea') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Environment <span class="text-danger">*</span></label>
                    <select name="travelport[environment]" class="form-select" required>
                        <option value="pp" @selected(old('travelport.environment', $travelport['environment'] ?? 'pp') === 'pp')>Pre-production (pp)</option>
                        <option value="production" @selected(old('travelport.environment', $travelport['environment'] ?? 'pp') === 'production')>Production</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Schema major version <span class="text-danger">*</span></label>
                    <input type="number" name="travelport[schema_major_version]" class="form-control" min="30" max="99" required
                        value="{{ old('travelport.schema_major_version', $travelport['schema_major_version'] ?? 52) }}">
                    <div class="form-text">Ping often uses <code>32</code>; air search auto-tries versions (often <code>52</code>).</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">API username <span class="text-danger">*</span></label>
                    <input type="text" name="travelport[username]" class="form-control" autocomplete="off" required
                        value="{{ old('travelport.username', $travelport['username'] ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">API password @if(!$passwordSet)<span class="text-danger">*</span>@endif</label>
                    <input type="password" name="travelport[password]" class="form-control" autocomplete="new-password"
                        placeholder="{{ $passwordSet ? 'Leave blank to keep current password' : 'Required unless set in .env' }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">GDS</label>
                    <input type="text" name="travelport[gds]" class="form-control" autocomplete="off" placeholder="e.g. 1G"
                        value="{{ old('travelport.gds', $travelport['gds'] ?? '') }}">
                    <div class="form-text">From welcome email (e.g. 1G, 1P, 1A).</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">PCC</label>
                    <input type="text" name="travelport[branch]" class="form-control" autocomplete="off"
                        value="{{ old('travelport.branch', $travelport['branch'] ?? '') }}">
                    <div class="form-text">Pseudo city code from welcome email.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Target branch</label>
                    <input type="text" name="travelport[target_branch]" class="form-control" autocomplete="off"
                        value="{{ old('travelport.target_branch', $travelport['target_branch'] ?? '') }}">
                    <div class="form-text">From welcome email — <strong>not</strong> the PCC. Ping tries without it first; wrong values often cause marshalling faults.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Request timeout (seconds) <span class="text-danger">*</span></label>
                    <input type="number" name="travelport[timeout]" class="form-control" min="5" max="120" required
                        value="{{ old('travelport.timeout', $travelport['timeout'] ?? 30) }}">
                </div>
                                <div class="col-md-6">
                    <label class="form-label">Origin application</label>
                    <input type="text" name="travelport[origin_application]" class="form-control" placeholder="UAPI"
                        value="{{ old('travelport.origin_application', $travelport['origin_application'] ?? 'UAPI') }}">
                    <div class="form-text">Usually <code>UAPI</code> unless Travelport assigned another.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Base URL override</label>
                    <input type="text" name="travelport[base_url_override]" class="form-control" placeholder="https://emea.universal-api.pp.travelport.com"
                        value="{{ old('travelport.base_url_override', $travelport['base_url_override'] ?? '') }}">
                    <div class="form-text"><strong>Host only</strong> — not the full SystemService path. Leave empty for auto.</div>
                </div>
            </div>
            <div class="mt-3 d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('admin.integrations.index') }}" class="btn btn-outline-secondary">Back to all integrations</a>
            </div>
        </form>

        <hr class="my-4">

        <h2 class="h6 mb-2">Test connection (Ping)</h2>
        <p class="text-muted small mb-2">Uses enabled saved or <code>.env</code> credentials.</p>
        <pre class="bg-light p-2 rounded small text-break mb-3">{{ $systemServiceUrl }}</pre>
        <form action="{{ route('admin.integrations.ping', ['slug' => 'travelport']) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-primary"><i class="fas fa-plug me-2"></i>Run Ping test</button>
        </form>

        <details class="mt-4">
            <summary class="small text-muted">Sample Ping XML (SOAP test tool)</summary>
            <p class="small text-muted mt-2 mb-1">Paste into Travelport SOAP/XML test tool. Auth: <code>Universal API/{username}</code> + password. No <code>TargetBranch</code> (official minimal Ping).</p>
            <pre class="bg-light p-2 rounded small text-break mb-0" style="max-height: 280px; overflow: auto;">{{ $samplePingXml }}</pre>
        </details>

        @if(session('travelport_ping'))
            @php $p = session('travelport_ping'); @endphp
            <div class="mt-4 p-3 bg-light rounded">
                <p class="mb-1 small"><strong>HTTP:</strong> {{ $p['http_status'] ?? '—' }}</p>
                @if(!empty($p['response_excerpt']))
                    <pre class="small text-break mb-0" style="max-height: 240px; overflow: auto;">{{ $p['response_excerpt'] }}</pre>
                @endif
            </div>
        @endif

        <hr class="my-4">

        <h2 class="h6 mb-2">Test air search</h2>
        <p class="text-muted small mb-2">Requires <strong>target branch</strong>. Open <a href="{{ route('admin.flights.index') }}">Flights → Flight APIs</a> for shopping and booking tools.</p>
        <form action="{{ route('admin.integrations.test-search', ['slug' => 'travelport']) }}" method="POST" class="row g-2 align-items-end">
            @csrf
            <div class="col-auto">
                <label class="form-label small mb-0">From</label>
                <input type="text" name="origin" class="form-control form-control-sm text-uppercase" maxlength="3" value="LHR">
            </div>
            <div class="col-auto">
                <label class="form-label small mb-0">To</label>
                <input type="text" name="destination" class="form-control form-control-sm text-uppercase" maxlength="3" value="JFK">
            </div>
            <div class="col-auto">
                <label class="form-label small mb-0">Depart</label>
                <input type="date" name="departure_date" class="form-control form-control-sm" value="{{ now()->addDays(21)->format('Y-m-d') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-plane me-1"></i>Quick test</button>
            </div>
        </form>

        @if(session('travelport_lfs'))
            @php $lfs = session('travelport_lfs'); @endphp
            <div class="mt-3 p-3 bg-light rounded">
                <p class="mb-1 small"><strong>HTTP:</strong> {{ $lfs['http_status'] ?? '—' }} · {{ count($lfs['solutions'] ?? []) }} option(s)</p>
                @if(!empty($lfs['response_excerpt']))
                    <pre class="small text-break mb-0" style="max-height: 200px; overflow: auto;">{{ $lfs['response_excerpt'] }}</pre>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
