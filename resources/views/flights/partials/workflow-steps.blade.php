@php
    $prefix = $flightsRoutePrefix ?? 'admin';
    $step = $workflowStep ?? 'search';
    $reservationId = session('travelport.last_reservation_id') ?? session('public.last_reservation_id');
    $reservationShowRoute = $reservationId
        ? $prefix.'.flights.reservations.show'
        : $prefix.'.flights.confirmation';
    $reservationParams = $reservationId ? ['id' => $reservationId] : [];
    $steps = [
        'search' => ['label' => 'Search', 'route' => $prefix.'.flights.search'],
        'price' => ['label' => 'Price', 'route' => $prefix.'.flights.price.show'],
        'book' => ['label' => 'Book', 'route' => $prefix.'.flights.book'],
        'ticket' => ['label' => 'Reservation', 'route' => $reservationShowRoute, 'params' => $reservationParams],
        'done' => ['label' => 'Done', 'route' => $reservationShowRoute, 'params' => $reservationParams],
    ];
    $order = ['search', 'price', 'book', 'ticket', 'done'];
    $currentIndex = array_search($step, $order, true);
    if ($currentIndex === false) {
        $currentIndex = 0;
    }
    $searchSession = session('travelport.flight_search');
    $priceSession = session('travelport.flight_price');
    $bookingSession = session('travelport.flight_booking') ?? session('travelport.last_booking');
@endphp
<nav class="flight-workflow-nav mb-4" aria-label="Flight booking steps">
    <ol class="flight-workflow-steps list-unstyled d-flex flex-wrap gap-2 mb-0">
        @foreach(['search', 'price', 'book', 'ticket'] as $index => $key)
            @php
                $meta = $steps[$key];
                $isDone = $index < $currentIndex || $step === 'done';
                $isCurrent = $key === $step || ($step === 'done' && $key === 'ticket');
                $canLink = $isDone;
                if ($key === 'search') {
                    $canLink = $canLink || ! empty($searchSession);
                }
                if ($key === 'price') {
                    $canLink = $canLink || ! empty($priceSession);
                }
                if (in_array($key, ['book', 'ticket'], true)) {
                    $canLink = $canLink || ! empty($bookingSession) || ! empty($reservationId);
                }
                if ($key === 'book' && empty($canBookFlights ?? false)) {
                    $canLink = false;
                }
            @endphp
            <li class="badge rounded-pill {{ $isCurrent ? 'bg-primary' : ($isDone ? 'bg-success' : 'bg-light text-muted') }} px-3 py-2">
                @if($canLink && ! $isCurrent)
                    <a href="{{ route($meta['route'], $meta['params'] ?? []) }}" class="text-decoration-none {{ $isDone ? 'text-white' : 'text-dark' }}">{{ $meta['label'] }}</a>
                @else
                    <span>{{ $meta['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
