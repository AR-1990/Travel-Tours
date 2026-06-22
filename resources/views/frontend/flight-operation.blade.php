@extends('frontend.layouts.tavelo')

@section('title', ($currentOperation['label'] ?? 'Flight') . ' — Tavelo')

@section('content')
    <div class="hero-section">
        <div class="hero-single" style="background: url({{ asset('assets/img/hero/hero-1.jpg') }})">
            <div class="container">
                <div class="hero-content text-center">
                    <h1 class="hero-title">{{ $currentOperation['label'] ?? 'Flight request' }}</h1>
                    <p class="mb-0">{{ $currentOperation['description'] ?? '' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="flight-booking flight-list pt-80 pb-120">
        <div class="container">
            @include('frontend.partials.flight-workflow-steps', ['workflowStep' => $workflowStep ?? 'price'])

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if(!($travelportReady ?? false))
                <div class="alert alert-warning">Flight API is not configured.</div>
            @endif

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('frontend.flights.operation', ['operation' => $operationKey]) }}">
                        @csrf
                        <div class="row g-3 align-items-end">
                            @include('flights.partials.operation-form', [
                                'operationKey' => $operationKey,
                                'searchInput' => $searchInput ?? [],
                                'flightsRoutePrefix' => 'frontend',
                                'hasPricingContext' => $hasPricingContext ?? false,
                                'currentOperation' => $currentOperation ?? [],
                            ])
                        </div>
                        <div class="mt-4 d-flex flex-wrap gap-2">
                            <button type="submit" class="theme-btn" @disabled(!($travelportReady ?? false))>
                                Send request<i class="fas fa-paper-plane"></i>
                            </button>
                            <a href="{{ route('frontend.flights.hub') }}" class="theme-btn theme-btn-outline">All flight APIs</a>
                            <a href="{{ route('frontend.flights.price.show') }}" class="theme-btn theme-btn-outline">Back to price</a>
                        </div>
                    </form>
                </div>
            </div>

            @if(!empty($operationResult))
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        @if($operationResult['ok'] ?? false)
                            <div class="alert alert-success mb-3">{{ $operationResult['message'] ?? 'Success' }}</div>
                            @if(!empty($operationResult['universal_locator']))
                                <p class="mb-2"><strong>Universal Record:</strong> <code>{{ $operationResult['universal_locator'] }}</code></p>
                            @endif
                            @if(!empty($operationResult['air_reservation_locator']))
                                <p class="mb-2"><strong>Air reservation:</strong> <code>{{ $operationResult['air_reservation_locator'] }}</code></p>
                            @endif
                        @else
                            <div class="alert alert-danger mb-3">{{ $operationResult['message'] ?? 'Request failed' }}</div>
                        @endif
                        @if(!empty($operationResult['response_excerpt']))
                            <details>
                                <summary class="small text-muted" style="cursor:pointer">Response details</summary>
                                <pre class="small bg-light p-3 rounded mt-2 mb-0" style="max-height:280px;overflow:auto;">{{ $operationResult['response_excerpt'] }}</pre>
                            </details>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
