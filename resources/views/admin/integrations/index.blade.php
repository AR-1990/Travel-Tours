@extends('admin.layouts.main')

@section('title', 'Integrations')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h1 class="h3 mb-1 text-gray-800">Integrations</h1>
        <p class="text-muted mb-0">Each integration has its own record in the <code>integrations</code> table (encrypted credentials). Add new providers in <code>config/integrations.php</code>, then wire their admin screens and persistence.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="row g-4">
        @foreach ($items as $item)
            <div class="col-md-6 col-xl-4">
                <div class="card-modern h-100 p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <h2 class="h5 mb-0">{{ $item['name'] }}</h2>
                        @if($item['coming_soon'])
                            <span class="badge bg-secondary">Coming soon</span>
                        @elseif($item['configured'])
                            <span class="badge {{ $item['is_enabled'] ? 'bg-success' : 'bg-warning text-dark' }}">
                                {{ $item['is_enabled'] ? 'Enabled' : 'Disabled' }}
                            </span>
                        @else
                            <span class="badge bg-light text-dark border">Not configured</span>
                        @endif
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

    <p class="text-muted small mt-4 mb-0">Encryption uses <code>APP_KEY</code>. Optional <code>TRAVELPORT_*</code> values in <code>.env</code> still act as defaults until overridden in an integration’s settings.</p>
</div>
@endsection
