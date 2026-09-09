@extends('frontend.layouts.tavelo')

@section('title', 'Flight Search Results | Wise Trust Travel & Tourism')

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
            @include('frontend.partials.flight-workflow-steps', ['workflowStep' => 'search'])

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="row">
                <div class="col-lg-12">
                    <div class="booking-sort mb-4">
                        <div>
                            <h5 class="mb-2">
                                @if(!empty($flightSearchResult['solutions']))
                                    {{ count($flightSearchResult['solutions']) }} Results Found
                                @else
                                    0 Results Found
                                @endif
                            </h5>
                            @php
                                $searchSources = $flightSearchResult['sources'] ?? [];
                            @endphp
                        @if(!empty($searchSources))
                            <p class="small text-muted mb-2">
                                @foreach($searchSources as $sourceProvider => $sourceMeta)
                                    <span class="me-2">
                                        @include('flights.partials.provider-badge', ['provider' => $sourceProvider, 'size' => 'sm'])
                                        {{ (int) ($sourceMeta['count'] ?? 0) }}
                                    </span>
                                @endforeach
                            </p>
                        @endif
                        @php
                            $ssCount = (int) data_get($flightSearchResult, 'sources.sunspring.count', 0);
                            $activeRoutes = $sunspringActiveRoutes ?? [];
                        @endphp
                        @if($ssCount === 0 && !empty($activeRoutes) && ($sunspringReady ?? false))
                            <div class="alert alert-info py-2 small mb-2">
                                SunSpring had no fares for this search. Active scheduled routes right now:
                                @foreach($activeRoutes as $route)
                                    <strong class="me-2">{{ $route['origin'] }}→{{ $route['destination'] }}@if(!empty($route['date'])) ({{ $route['date'] }})@endif</strong>
                                @endforeach
                            </div>
                        @endif
                            @if(!empty($flightSearchInput))
                                <p class="mb-0 text-muted">
                                    {{ \App\Support\FlightDisplay::tripSummary(
                                        $flightSearchInput['origin'] ?? null,
                                        $flightSearchInput['destination'] ?? null,
                                        $flightSearchInput['departure_date'] ?? null,
                                        $flightSearchInput['return_date'] ?? null,
                                        (int) ($flightSearchInput['adults'] ?? 1),
                                        $flightSearchInput['legs'] ?? null
                                    ) }}
                                </p>
                            @endif
                        </div>
                        @if(!empty($flightSearchResult['solutions']))
                            <div class="col-md-3 booking-sort-box">
                                <label class="visually-hidden" for="flight-price-sort">Sort by price</label>
                                <select id="flight-price-sort" class="flight-price-sort" aria-label="Sort by price">
                                    <option value="asc" selected>Price: Low to High</option>
                                    <option value="desc">Price: High to Low</option>
                                </select>
                            </div>
                        @endif
                    </div>

                    @if(!empty($flightSearchResult['ok']) && !empty($flightSearchResult['solutions']))
                        <div class="row" id="flight-results-list">
                            @foreach($flightSearchResult['solutions'] as $sol)
                                @include('frontend.partials.flight-result-card', [
                                    'sol' => $sol,
                                    'travelportReady' => $travelportReady ?? false,
                                    'sunspringReady' => $sunspringReady ?? false,
                                    'providerReady' => $providerReady ?? false,
                                    'flightSearchResult' => $flightSearchResult ?? null,
                                ])
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

@push('styles')
<style>
.flight-price-sort {
    width: 100%;
    height: 46px;
    line-height: 44px;
    border-radius: 14px;
    padding: 0 15px;
    font-size: 16px;
    color: var(--color-dark);
    background: #fff;
    border: 1px solid var(--border-info-color);
    cursor: pointer;
    appearance: auto;
}
.flight-price-sort:focus {
    outline: none;
    border-color: var(--theme-color);
}
</style>
@endpush

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

    document.querySelectorAll('.flight-price-form').forEach(function (form) {
        form.addEventListener('submit', function () {
            const btn = form.querySelector('.flight-price-btn');
            if (btn && !btn.disabled) {
                btn.classList.add('is-loading');
                btn.disabled = true;
            }
        });
    });

    function applyFlightPriceSort(order) {
        const list = document.getElementById('flight-results-list');
        if (!list) return;
        const items = Array.from(list.querySelectorAll(':scope > [data-price]'));
        items.sort(function (a, b) {
            const pa = parseFloat(a.getAttribute('data-price') || 'Infinity');
            const pb = parseFloat(b.getAttribute('data-price') || 'Infinity');
            return order === 'desc' ? pb - pa : pa - pb;
        });
        items.forEach(function (el) {
            list.appendChild(el);
        });
    }

    const sortSelect = document.getElementById('flight-price-sort');
    if (sortSelect) {
        const onSort = function () {
            applyFlightPriceSort(sortSelect.value);
        };
        sortSelect.addEventListener('change', onSort);
        sortSelect.addEventListener('input', onSort);
        if (window.jQuery) {
            window.jQuery(sortSelect).on('change', onSort);
            window.jQuery(document).on('click', '.booking-sort-box .nice-select .option', function () {
                const value = this.getAttribute('data-value') || '';
                if (value) {
                    sortSelect.value = value;
                    applyFlightPriceSort(value);
                }
            });
        }
    }
})();
</script>
@endpush
