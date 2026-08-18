@extends('frontend.layouts.tavelo')

@section('title', 'Hotels — Search')

@section('content')
<div class="hero-section">
    <div class="hero-single" style="background: url({{ asset('assets/img/hero/hero-1.jpg') }})">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-12 mx-auto">
                    <div class="hero-content text-center">
                        <div class="hero-content-wrapper">
                            <h1 class="hero-title">Find your stay</h1>
                            <p>Search hotels via Xconnect wholesale inventory</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('frontend.hotels.partials.search-form', [
    'hotelSearchInput' => $hotelSearchInput ?? [],
    'hotelReady' => $hotelReady ?? false,
    'searchSubmitLabel' => 'Search Hotels',
])
@endsection
