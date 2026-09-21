@extends('admin.layouts.main')

@section('title', 'Integrations')

@section('content')
<div class="container-fluid panel-page">
    
@include('admin.partials.page-header', [
        'title' => 'Integrations',
        'subtitle' => 'Configure supplier APIs. Credentials are stored encrypted per integration.',
        'icon' => 'fas fa-plug',
    ])

    @include('admin.partials.flash')

    <div class="row g-4">
        @foreach ($items as $item)
            <div class="col-md-6 col-xl-4">
                <div class="card-modern h-100 p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <h2 class="h5 mb-0">{{ $item['name'] }}</h2>
                        <div class="d-flex flex-wrap justify-content-end gap-1">
                            @unless($item['coming_soon'])
                                @if(!($item['configured'] ?? false))
                                    <span class="badge bg-danger">Not configured</span>
                                @else
                                    <span class="badge {{ ($item['environment'] ?? '') === 'live' ? 'bg-success' : 'bg-primary' }}">
                                        {{ $item['environment_label'] ?? 'Sandbox' }}
                                    </span>
                                    <span class="badge {{ $item['is_enabled'] ? 'bg-success' : 'bg-warning text-dark' }}">
                                        {{ $item['is_enabled'] ? 'Enabled' : 'Disabled' }}
                                    </span>
                                @endif
                            @else
                                <span class="badge bg-secondary">Coming soon</span>
                            @endunless
                        </div>
                    </div>
                    <p class="text-muted small flex-grow-1">{{ $item['description'] }}</p>
                    <div class="mt-3">
                        @if($item['coming_soon'])
                            <button type="button" class="btn btn-outline-secondary btn-sm" disabled>Configure</button>
                        @else
                            <a href="{{ route('admin.integrations.edit', $item['slug']) }}" class="btn btn-primary btn-sm">Configure</a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <p class="text-muted small mt-4 mb-0">Encryption uses <code>APP_KEY</code>. Optional <code>TRAVELPORT_*</code> / <code>SUNSPRING_*</code> / <code>XCONNECT_*</code> / <code>DOWNTOWN_TRAVEL_*</code> values in <code>.env</code> still act as defaults until overridden in an integration’s settings.</p>
</div>
@endsection
