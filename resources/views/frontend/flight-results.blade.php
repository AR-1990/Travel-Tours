@extends('frontend.layouts.tavelo')

@section('title', 'Flight Search Results — Tavelo')

@section('content')
    <div class="hero-section">
        <div class="hero-single" style="background: url({{ asset('assets/img/hero/hero-1.jpg') }})">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-12 mx-auto">
                        <div class="hero-content text-center">
                            <div class="hero-content-wrapper">
                                <h1 class="hero-title">Explore The World Together</h1>
                                <p>Find awesome flight, hotel, tour, car and packages</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('frontend.partials.flight-search-box', [
        'searchSubmitLabel' => 'Update Search',
    ])

    <div class="flight-booking flight-list pt-80 pb-120">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="booking-sort mb-4">
                        <h5>
                            @if(!empty($flightSearchResult['solutions']))
                                {{ count($flightSearchResult['solutions']) }} Results Found
                            @else
                                0 Results Found
                            @endif
                        </h5>
                        @if(!empty($flightSearchInput))
                            <p class="mb-0 text-muted">
                                {{ \App\Support\FlightDisplay::tripSummary(
                                    $flightSearchInput['origin'] ?? null,
                                    $flightSearchInput['destination'] ?? null,
                                    $flightSearchInput['departure_date'] ?? null,
                                    $flightSearchInput['return_date'] ?? null,
                                    (int) ($flightSearchInput['adults'] ?? 1)
                                ) }}
                            </p>
                        @endif
                    </div>

                    @if(!empty($flightSearchResult['ok']) && !empty($flightSearchResult['solutions']))
                        <div class="row">
                            @foreach($flightSearchResult['solutions'] as $sol)
                                @include('frontend.partials.flight-result-card', ['sol' => $sol])
                            @endforeach
                        </div>
                    @elseif(!empty($flightSearchResult['ok']))
                        <div class="alert alert-info">No fares found for this route and date. Try different airports or dates.</div>
                    @else
                        <div class="alert alert-danger">{{ $flightSearchResult['message'] ?? 'Flight search failed.' }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const airportLabels = @json($airportOptions ?? []);
    const origin = document.querySelector('select[name="origin"]');
    const destination = document.querySelector('select[name="destination"]');
    const originHint = document.getElementById('home-origin-hint');
    const destinationHint = document.getElementById('home-destination-hint');

    function syncHint(select, hint) {
        if (!select || !hint) return;
        hint.textContent = airportLabels[select.value] || select.value;
    }

    origin?.addEventListener('change', () => syncHint(origin, originHint));
    destination?.addEventListener('change', () => syncHint(destination, destinationHint));
    syncHint(origin, originHint);
    syncHint(destination, destinationHint);

    if (document.getElementById('flight-type2')?.checked) {
        $('.flight-search .search-form-return').show();
    }
})();
</script>
@endpush
