@extends('admin.layouts.main')

@section('title', 'Search hotels')

@section('content')
<div class="container-fluid panel-page">
    @include('hotels.partials.nav')

    <div class="panel-breadcrumb">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item active">Hotels</li>
                <li class="breadcrumb-item active">Search</li>
            </ol>
        </nav>
    </div>

    @include('admin.partials.page-header', [
        'title' => 'Search hotels',
        'subtitle' => 'Same flow as the website: Search → Select rate → Guest details → Confirmation.',
        'icon' => 'fas fa-hotel',
    ])

    @include('hotels.partials.workflow-steps', ['workflowStep' => 'search'])
    @include('admin.partials.flash')
    @include('hotels.partials.search-form')
    @include('hotels.partials.result-cards', [
        'hotelSearchResult' => $hotelSearchResult ?? null,
        'hotelSearchInput' => $hotelSearchInput ?? [],
    ])
</div>
@endsection
