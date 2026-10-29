@php
    $filterSolutions = is_array($filterSolutions ?? null)
        ? $filterSolutions
        : (is_array($searchResult['solutions'] ?? null) ? $searchResult['solutions'] : []);
    $displayCurrency = $displayCurrency
        ?? \App\Support\DisplayCurrency::resolve(auth()->user());
    $airlineFilters = [];
    foreach ($filterSolutions as $filterSol) {
        if (! is_array($filterSol)) {
            continue;
        }
        $priceKey = \App\Support\FlightDisplay::priceSortKey($filterSol['total_price'] ?? null);
        $stopBucket = \App\Support\FlightDisplay::stopBucket($filterSol);
        $codes = \App\Support\FlightDisplay::airlineCodes($filterSol);
        if ($codes === []) {
            continue;
        }
        $primaryName = trim((string) ($filterSol['airline_name'] ?? ''));
        foreach ($codes as $codeIndex => $code) {
            $name = $codeIndex === 0 && $primaryName !== ''
                ? $primaryName
                : \App\Support\FlightDisplay::airlineName($code);
            if (! isset($airlineFilters[$code])) {
                $airlineFilters[$code] = [
                    'code' => $code,
                    'name' => $name !== '' ? $name : $code,
                    'count' => 0,
                    'min_price' => $priceKey,
                    'price_label' => $filterSol['total_price'] ?? null,
                    'stops' => [],
                ];
            }
            $airlineFilters[$code]['count']++;
            $airlineFilters[$code]['stops'][$stopBucket] = true;
            if (is_finite($priceKey) && $priceKey < ($airlineFilters[$code]['min_price'] ?? INF)) {
                $airlineFilters[$code]['min_price'] = $priceKey;
                $airlineFilters[$code]['price_label'] = $filterSol['total_price'] ?? null;
            }
        }
    }
    uasort($airlineFilters, static fn ($a, $b) => ($a['min_price'] <=> $b['min_price']));
    $resultsListId = $resultsListId ?? 'flight-results-list';
@endphp

@if(count($filterSolutions) > 0)
<div class="flight-result-filters mb-4" id="flight-result-filters" data-results-list="{{ $resultsListId }}">
    <div class="flight-stop-filters mb-3">
        <span class="small text-muted me-2">Stops:</span>
        <div class="btn-group flex-wrap" role="group" aria-label="Filter by stops">
            <button type="button" class="btn btn-sm btn-outline-secondary js-stop-filter active" data-stops="all">All</button>
            <button type="button" class="btn btn-sm btn-outline-secondary js-stop-filter" data-stops="0">Non-stop</button>
            <button type="button" class="btn btn-sm btn-outline-secondary js-stop-filter" data-stops="1">1 stop</button>
            <button type="button" class="btn btn-sm btn-outline-secondary js-stop-filter" data-stops="2">2+ stops</button>
        </div>
    </div>
    @if(count($airlineFilters) > 0)
        <div class="flight-airline-filters">
            <div class="small text-muted mb-2">Airlines:</div>
            <div class="d-flex flex-wrap gap-2" id="flight-airline-filter-row">
                @foreach($airlineFilters as $airline)
                    @php
                        $parsedAirlinePrice = \App\Support\FlightDisplay::forDisplayCurrency(
                            $airline['price_label'] ?? null,
                            $displayCurrency
                        );
                    @endphp
                    <button
                        type="button"
                        class="btn btn-sm btn-light border js-airline-filter"
                        data-airline="{{ $airline['code'] }}"
                        data-stops-available="{{ implode(',', array_keys($airline['stops'] ?? ['0'=>true,'1'=>true,'2'=>true])) }}"
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

@once
@push('scripts')
<script>
(function () {
    window.initFlightResultFilters = function initFlightResultFilters() {
        const root = document.getElementById('flight-result-filters');
        if (!root) return;

        if (root._filterAbort) {
            root._filterAbort.abort();
        }
        const ac = new AbortController();
        root._filterAbort = ac;
        const { signal } = ac;

        let activeStops = 'all';
        const activeAirlines = new Set();
        const listId = root.getAttribute('data-results-list') || 'flight-results-list';
        const list = document.getElementById(listId);
        if (!list) return;

        const cards = Array.from(list.querySelectorAll('.js-flight-result')).map(function (card) {
            const airlinesAttr = (card.getAttribute('data-airlines') || card.getAttribute('data-airline') || '').toUpperCase();
            return {
                el: card,
                stops: card.getAttribute('data-stops') || '0',
                airlines: airlinesAttr.split(',').map(function (c) { return c.trim(); }).filter(Boolean),
            };
        });
        const airlineButtons = Array.from(root.querySelectorAll('.js-airline-filter'));
        let frame = 0;

        function cardMatchesAirline(cardAirlines) {
            if (activeAirlines.size === 0) return true;
            for (let i = 0; i < cardAirlines.length; i++) {
                if (activeAirlines.has(cardAirlines[i])) return true;
            }
            return false;
        }

        function syncAirlineAvailability() {
            airlineButtons.forEach(function (btn) {
                const available = (btn.getAttribute('data-stops-available') || '0,1,2').split(',');
                const ok = activeStops === 'all' || available.indexOf(activeStops) !== -1;
                btn.classList.toggle('is-unavailable', !ok);
                if (!ok) {
                    const code = (btn.getAttribute('data-airline') || '').toUpperCase();
                    if (activeAirlines.has(code)) {
                        activeAirlines.delete(code);
                        btn.classList.remove('active', 'btn-primary');
                        btn.classList.add('btn-light');
                        btn.setAttribute('aria-pressed', 'false');
                    }
                }
            });
        }

        function applyFiltersNow() {
            let visible = 0;
            for (let i = 0; i < cards.length; i++) {
                const card = cards[i];
                const stopOk = activeStops === 'all' || card.stops === activeStops;
                const show = stopOk && cardMatchesAirline(card.airlines);
                card.el.hidden = !show;
                if (show) visible++;
            }
            const empty = document.getElementById('flight-filter-empty');
            if (empty) empty.hidden = visible > 0;
            const heading = document.querySelector('#flight-results-heading h5');
            if (heading && cards.length) {
                heading.textContent = visible + ' Results Found';
            }
        }

        function applyFilters() {
            syncAirlineAvailability();
            if (frame) cancelAnimationFrame(frame);
            frame = requestAnimationFrame(applyFiltersNow);
        }

        root.querySelectorAll('.js-stop-filter').forEach(function (btn) {
            btn.addEventListener('click', function () {
                root.querySelectorAll('.js-stop-filter').forEach(function (b) {
                    b.classList.remove('active');
                });
                btn.classList.add('active');
                activeStops = btn.getAttribute('data-stops') || 'all';
                applyFilters();
            }, { signal });
        });

        airlineButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (btn.classList.contains('is-unavailable')) return;
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
            }, { signal });
        });

        root.querySelector('.js-airline-filter-clear')?.addEventListener('click', function () {
            activeAirlines.clear();
            airlineButtons.forEach(function (btn) {
                btn.classList.remove('active', 'btn-primary');
                btn.classList.add('btn-light');
                btn.setAttribute('aria-pressed', 'false');
            });
            applyFilters();
        }, { signal });

        applyFilters();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            window.initFlightResultFilters?.();
        });
    } else {
        window.initFlightResultFilters?.();
    }
})();
</script>
@endpush

@push('styles')
<style>
.js-airline-filter.is-unavailable { opacity: 0.45; }
.js-airline-filter.is-unavailable:not(.active) { pointer-events: none; }
.js-flight-result[hidden] { display: none !important; }
</style>
@endpush
@endonce
@endif
