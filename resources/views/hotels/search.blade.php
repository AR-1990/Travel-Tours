@extends('admin.layouts.main')

@section('title', 'Search hotels')

@section('content')
<div class="container-fluid flights-page">
    @include('hotels.partials.nav')

    <nav aria-label="breadcrumb" class="mb-2">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item active">Hotels</li>
            <li class="breadcrumb-item active">Search</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
        <div>
            <h1 class="h3 mb-1 text-gray-800"><i class="fas fa-hotel me-2"></i>Search hotels</h1>
            <p class="text-muted mb-0">Same flow as the website: Search → Select rate → Guest details → Confirmation.</p>
        </div>
    </div>

    @include('hotels.partials.workflow-steps', ['workflowStep' => 'search'])

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm">
            <ul class="mb-0 small">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    @include('hotels.partials.search-form')

    @include('hotels.partials.result-cards', [
        'hotelSearchResult' => $hotelSearchResult ?? null,
        'hotelSearchInput' => $hotelSearchInput ?? [],
    ])
</div>
@endsection
