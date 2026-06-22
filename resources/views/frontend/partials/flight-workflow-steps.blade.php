@php
    $step = $workflowStep ?? 'search';
    $steps = [
        'search' => ['label' => 'Search', 'route' => 'frontend.flights.results'],
        'price' => ['label' => 'Price', 'route' => 'frontend.flights.price.show'],
        'book' => ['label' => 'Book', 'route' => 'frontend.flights.operation', 'params' => ['operation' => 'air_create_reservation']],
    ];
    $order = ['search', 'price', 'book'];
    $currentIndex = array_search($step, $order, true);
    if ($currentIndex === false) {
        $currentIndex = 0;
    }
@endphp
<nav class="flight-workflow-nav mb-4" aria-label="Flight booking steps">
    <ol class="flight-workflow-steps">
        @foreach($order as $index => $key)
            @php
                $meta = $steps[$key];
                $isDone = $index < $currentIndex;
                $isCurrent = $index === $currentIndex;
                $canLink = $isDone || ($key === 'search' && session('public.flight_search'));
                if ($key === 'price') {
                    $canLink = $canLink || session('public.flight_price');
                }
            @endphp
            <li class="flight-workflow-step {{ $isCurrent ? 'is-current' : '' }} {{ $isDone ? 'is-done' : '' }}">
                @if($canLink && !$isCurrent)
                    <a href="{{ route($meta['route'], $meta['params'] ?? []) }}">{{ $meta['label'] }}</a>
                @else
                    <span>{{ $meta['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
