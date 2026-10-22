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

    <div class="flight-booking flight-list pt-80 pb-120" id="flight-search-results">
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
                    <div class="booking-sort mb-4" id="flight-results-heading">
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
                                        @include('flights.partials.provider-badge', [
                                            'provider' => $sourceProvider,
                                            'sourceMeta' => $sourceMeta,
                                            'size' => 'sm',
                                        ])
                                        {{ (int) ($sourceMeta['count'] ?? 0) }}
                                    </span>
                                @endforeach
                            </p>
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
                                <select id="flight-price-sort" class="select flight-price-sort" aria-label="Sort by price">
                                    <option value="asc" selected>Price: Low to High</option>
                                    <option value="desc">Price: High to Low</option>
                                </select>
                            </div>
                        @endif
                    </div>

                    @if(!empty($flightSearchResult['ok']) && !empty($flightSearchResult['solutions']))
                        @php
                            // Lowest fare first by default
                            $sortedSolutions = collect($flightSearchResult['solutions'])
                                ->sortBy(fn ($s) => \App\Support\FlightDisplay::priceSortKey($s['total_price'] ?? null))
                                ->values()
                                ->all();
                            $flightSearchResult['solutions'] = $sortedSolutions;
                            $airlineFilters = [];
                            foreach ($flightSearchResult['solutions'] as $filterSol) {
                                $segs = $filterSol['segments'] ?? (($filterSol['journeys'][0]['segments'] ?? []) ?: []);
                                $code = strtoupper((string) ($filterSol['plating_carrier'] ?? ($segs[0]['carrier'] ?? 'XX')));
                                $code = preg_replace('/[^A-Za-z0-9]/', '', $code) ?: 'XX';
                                $name = trim((string) ($filterSol['airline_name'] ?? $segs[0]['airline_name'] ?? \App\Support\FlightDisplay::airlineName($code)));
                                $priceKey = \App\Support\FlightDisplay::priceSortKey($filterSol['total_price'] ?? null);
                                if (! isset($airlineFilters[$code])) {
                                    $airlineFilters[$code] = [
                                        'code' => $code,
                                        'name' => $name !== '' ? $name : $code,
                                        'count' => 0,
                                        'min_price' => $priceKey,
                                        'price_label' => $filterSol['total_price'] ?? null,
                                    ];
                                }
                                $airlineFilters[$code]['count']++;
                                if (is_finite($priceKey) && $priceKey < ($airlineFilters[$code]['min_price'] ?? INF)) {
                                    $airlineFilters[$code]['min_price'] = $priceKey;
                                    $airlineFilters[$code]['price_label'] = $filterSol['total_price'] ?? null;
                                }
                            }
                            uasort($airlineFilters, static fn ($a, $b) => ($a['min_price'] <=> $b['min_price']));
                        @endphp

                        <div class="flight-result-filters mb-4" id="flight-result-filters">
                            <div class="flight-stop-filters mb-3">
                                <span class="small text-muted me-2">Stops:</span>
                                <div class="btn-group flex-wrap" role="group" aria-label="Filter by stops">
                                    <button type="button" class="btn btn-sm btn-outline-secondary js-stop-filter active" data-stops="all">All</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary js-stop-filter" data-stops="0">Non-stop</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary js-stop-filter" data-stops="1">1 stop</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary js-stop-filter" data-stops="2">2+ stops</button>
                                </div>
                            </div>
                            @if(count($airlineFilters) > 1)
                                <div class="flight-airline-filters">
                                    <div class="small text-muted mb-2">Airlines:</div>
                                    <div class="d-flex flex-wrap gap-2" id="flight-airline-filter-row">
                                        @foreach($airlineFilters as $airline)
                                            @php
                                                $parsedAirlinePrice = \App\Support\FlightDisplay::parsePrice($airline['price_label'] ?? null);
                                            @endphp
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-light border js-airline-filter"
                                                data-airline="{{ $airline['code'] }}"
                                                aria-pressed="false"
                                            >
                                                <strong>{{ $airline['code'] }}</strong>
                                                <span class="d-none d-md-inline"> {{ $airline['name'] }}</span>
                                                @if($parsedAirlinePrice)
                                                    <span class="text-muted"> · {{ $parsedAirlinePrice['currency'] }} {{ $parsedAirlinePrice['amount'] }}</span>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                    <button type="button" class="btn btn-link btn-sm px-0 mt-1 js-airline-filter-clear">Clear airline filter</button>
                                </div>
                            @endif
                            <p class="small text-muted mt-2 mb-0" id="flight-filter-empty" hidden>No flights match these filters. Clear filters to see all results.</p>
                        </div>

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
        const items = Array.from(list.querySelectorAll('.js-flight-result'));
        if (items.length < 2) return;

        items.sort(function (a, b) {
            const pa = parseFloat(a.getAttribute('data-price'));
            const pb = parseFloat(b.getAttribute('data-price'));
            const aBad = !Number.isFinite(pa);
            const bBad = !Number.isFinite(pb);
            if (aBad && bBad) return 0;
            if (aBad) return 1;
            if (bBad) return -1;
            return order === 'desc' ? (pb - pa) : (pa - pb);
        });

        const frag = document.createDocumentFragment();
        items.forEach(function (el) {
            frag.appendChild(el);
        });
        list.appendChild(frag);
    }

    function bindPriceSort() {
        const sortSelect = document.getElementById('flight-price-sort');
        if (!sortSelect || sortSelect.dataset.sortBound === '1') return;
        sortSelect.dataset.sortBound = '1';

        const onSort = function () {
            applyFlightPriceSort(sortSelect.value === 'desc' ? 'desc' : 'asc');
        };

        sortSelect.addEventListener('change', onSort);

        if (window.jQuery) {
            const $select = window.jQuery(sortSelect);
            $select.on('change', onSort);

            // Theme nice-select: click option → update value → sort
            window.jQuery(document).on('click', '.booking-sort-box .nice-select .option', function () {
                window.setTimeout(function () {
                    const value = sortSelect.value || this.getAttribute('data-value') || 'asc';
                    applyFlightPriceSort(value === 'desc' ? 'desc' : 'asc');
                }.bind(this), 0);
            });
        }
    }

    bindPriceSort();
    // Default: lowest fare first after search / ticket selection list
    applyFlightPriceSort('asc');
    // main.js may init nice-select slightly later on .select
    window.setTimeout(bindPriceSort, 50);
    window.setTimeout(function () { applyFlightPriceSort('asc'); }, 80);

    (function bindResultFilters() {
        let activeStops = 'all';
        const activeAirlines = new Set();

        function applyFilters() {
            const cards = document.querySelectorAll('#flight-results-list .js-flight-result');
            let visible = 0;
            cards.forEach(function (card) {
                const stops = card.getAttribute('data-stops') || '0';
                const airline = (card.getAttribute('data-airline') || '').toUpperCase();
                const stopOk = activeStops === 'all' || stops === activeStops;
                const airlineOk = activeAirlines.size === 0 || activeAirlines.has(airline);
                const show = stopOk && airlineOk;
                card.classList.toggle('d-none', !show);
                if (show) visible++;
            });
            const empty = document.getElementById('flight-filter-empty');
            if (empty) empty.hidden = visible > 0;
            const heading = document.querySelector('#flight-results-heading h5');
            if (heading && cards.length) {
                heading.textContent = visible + ' Results Found';
            }
        }

        document.querySelectorAll('.js-stop-filter').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.js-stop-filter').forEach(function (b) {
                    b.classList.remove('active');
                });
                btn.classList.add('active');
                activeStops = btn.getAttribute('data-stops') || 'all';
                applyFilters();
            });
        });

        document.querySelectorAll('.js-airline-filter').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const code = (btn.getAttribute('data-airline') || '').toUpperCase();
                if (!code) return;
                if (activeAirlines.has(code)) {
                    activeAirlines.delete(code);
                    btn.classList.remove('active', 'btn-primary');
                    btn.classList.add('btn-light');
                    btn.setAttribute('aria-pressed', 'false');
                } else {
                    activeAirlines.add(code);
                    btn.classList.add('active', 'btn-primary');
                    btn.classList.remove('btn-light');
                    btn.setAttribute('aria-pressed', 'true');
                }
                applyFilters();
            });
        });

        document.querySelector('.js-airline-filter-clear')?.addEventListener('click', function () {
            activeAirlines.clear();
            document.querySelectorAll('.js-airline-filter').forEach(function (btn) {
                btn.classList.remove('active', 'btn-primary');
                btn.classList.add('btn-light');
                btn.setAttribute('aria-pressed', 'false');
            });
            applyFilters();
        });
    })();

    function stickyHeaderOffset() {
        const nav = document.querySelector('.navbar.fixed-top')
            || document.querySelector('.main-navigation')
            || document.querySelector('.navbar');
        const height = nav ? nav.getBoundingClientRect().height : 90;
        // Extra gap so the sticky header does not cover the search UI.
        return Math.ceil(height) + 24;
    }

    function absoluteTop(el) {
        let y = 0;
        while (el) {
            y += el.offsetTop;
            el = el.offsetParent;
        }
        return y;
    }

    function scrollToFlightResults() {
        // Align to the top of the search box (slight scroll), not mid-form / deep results.
        const target = document.getElementById('home-flight-search')
            || document.getElementById('flight-search-results')
            || document.getElementById('flight-results-heading');
        if (!target) return;

        if (window.location.hash) {
            history.replaceState(null, '', window.location.pathname + window.location.search);
        }

        window.scrollTo({
            top: Math.max(0, absoluteTop(target) - stickyHeaderOffset()),
            behavior: 'smooth',
        });
    }

    function scheduleScrollToResults() {
        window.setTimeout(scrollToFlightResults, 200);
    }

    if (document.readyState === 'complete') {
        scheduleScrollToResults();
    } else {
        window.addEventListener('load', scheduleScrollToResults);
    }
})();
</script>
@endpush
