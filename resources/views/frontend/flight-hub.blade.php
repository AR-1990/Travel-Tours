@extends('frontend.layouts.tavelo')

@section('title', 'Flight APIs — Tavelo')

@section('content')
    <div class="hero-section">
        <div class="hero-single" style="background: url({{ asset('assets/img/hero/hero-1.jpg') }})">
            <div class="container">
                <div class="hero-content text-center">
                    <h1 class="hero-title">Flight booking flow</h1>
                    <p class="mb-0">Search → Price → Book → Ticket</p>
                </div>
            </div>
        </div>
    </div>

    <div class="flight-booking flight-list pt-80 pb-120">
        <div class="container">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <a href="{{ route('home') }}" class="theme-btn">Search flights<i class="fas fa-search"></i></a>
                </div>
                <div class="col-md-6">
                    <a href="{{ route('frontend.flights.results') }}" class="theme-btn theme-btn-outline">View results<i class="fas fa-list"></i></a>
                </div>
            </div>

            @foreach($operationGroups ?? [] as $group)
                <section class="mb-4">
                    <h2 class="h6 text-uppercase text-muted">{{ $group['group_label'] ?? '' }}</h2>
                    <div class="row g-2">
                        @foreach($group['operations'] as $op)
                            @if(($op['key'] ?? '') === 'low_fare_search')
                                @continue
                            @endif
                            <div class="col-md-4 col-sm-6">
                                <a href="{{ route('frontend.flights.operation', ['operation' => $op['key']]) }}" class="card border-0 shadow-sm text-decoration-none h-100 p-3">
                                    <strong class="d-block text-dark">{{ $op['label'] ?? $op['key'] }}</strong>
                                    <small class="text-muted">{{ $op['description'] ?? '' }}</small>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </div>
@endsection
