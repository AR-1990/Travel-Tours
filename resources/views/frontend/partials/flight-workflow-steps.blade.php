@php
    $step = $workflowStep ?? 'search';
    $reservationId = session('public.last_reservation_id') ?? session('travelport.last_reservation_id');
    $reservationRoute = $reservationId ? 'frontend.flights.reservations.show' : 'frontend.flights.confirmation';
    $reservationParams = $reservationId ? ['id' => $reservationId] : [];
    $steps = [
        'search' => ['label' => 'Search', 'route' => 'frontend.flights.results'],
        'price' => ['label' => 'Price', 'route' => 'frontend.flights.price.show'],
        'book' => ['label' => 'Book', 'route' => 'frontend.flights.book'],
        'ticket' => ['label' => 'Reservation', 'route' => $reservationRoute, 'params' => $reservationParams],
        'done' => ['label' => 'Done', 'route' => $reservationRoute, 'params' => $reservationParams],
    ];
    $order = ['search', 'price', 'book', 'ticket', 'done'];
    $currentIndex = array_search($step, $order, true);
    if ($currentIndex === false) {
        $currentIndex = 0;
    }
@endphp
<nav class="flight-workflow-nav mb-4" aria-label="Flight booking steps">
    <ol class="flight-workflow-steps">
        @foreach(['search', 'price', 'book', 'ticket'] as $index => $key)
            @php
                $meta = $steps[$key];
                $isDone = $index < $currentIndex || $step === 'done';
                $isCurrent = $key === $step || ($step === 'done' && $key === 'ticket');
                $canLink = $isDone;
                if ($key === 'search') {
                    $canLink = $canLink || session('public.flight_search');
                }
                if ($key === 'price') {
                    $canLink = $canLink || session('public.flight_price');
                }
                if ($key === 'book') {
                    $canLink = $canLink || session('public.flight_booking') || $reservationId;
                }
                if ($key === 'ticket') {
                    $canLink = $canLink || session('public.flight_booking') || $reservationId;
                }
            @endphp
            <li class="flight-workflow-step {{ $isCurrent ? 'is-current' : '' }} {{ $isDone ? 'is-done' : '' }}">
                @if($canLink && ! $isCurrent)
                    <a href="{{ route($meta['route'], $meta['params'] ?? []) }}">{{ $meta['label'] }}</a>
                @else
                    <span>{{ $meta['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
