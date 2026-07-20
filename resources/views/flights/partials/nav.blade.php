@php
    $flightsDashboardRoute = match ($flightsRoutePrefix) {
        'admin' => 'admin.dashboard',
        'agent' => 'agent.dashboard',
        default => 'subagent.dashboard',
    };
    $isSearchActive = request()->routeIs($flightsRoutePrefix . '.flights.search');
    $isIndexActive = request()->routeIs($flightsRoutePrefix . '.flights.index');
    $hasBooking = session('travelport.flight_booking') || session('travelport.last_booking') || session('travelport.last_reservation_id');
@endphp
<nav class="flights-nav" aria-label="Flight section">
    <a href="{{ route($flightsDashboardRoute) }}" class="{{ request()->routeIs($flightsDashboardRoute) ? 'active' : '' }}">
        <i class="fas fa-home me-1"></i> Dashboard
    </a>
    <a href="{{ route($flightsRoutePrefix . '.flights.index') }}" class="{{ $isIndexActive ? 'active' : '' }}">
        <i class="fas fa-th-large me-1"></i> Flight APIs
    </a>
    <a href="{{ route($flightsRoutePrefix . '.flights.search') }}" class="{{ $isSearchActive ? 'active' : '' }}">
        <i class="fas fa-search me-1"></i> Low Fare Search
    </a>
    <a href="{{ route($flightsRoutePrefix . '.flights.reservations.index') }}" class="{{ request()->routeIs($flightsRoutePrefix . '.flights.reservations*') ? 'active' : '' }}">
        <i class="fas fa-folder-open me-1"></i> Reservations
    </a>
    @if(session('travelport.flight_price'))
        <a href="{{ route($flightsRoutePrefix . '.flights.price.show') }}" class="{{ request()->routeIs($flightsRoutePrefix . '.flights.price*') ? 'active' : '' }}">
            <i class="fas fa-tag me-1"></i> Price
        </a>
    @endif
    @if(($canBookFlights ?? false) && session('travelport.flight_price'))
        <a href="{{ route($flightsRoutePrefix . '.flights.book') }}" class="{{ request()->routeIs($flightsRoutePrefix . '.flights.book*') ? 'active' : '' }}">
            <i class="fas fa-user me-1"></i> Book
        </a>
    @endif
    @if($hasBooking)
        @php
            $reservationId = session('travelport.last_reservation_id');
            $reservationRoute = $reservationId
                ? route($flightsRoutePrefix . '.flights.reservations.show', ['id' => $reservationId])
                : route($flightsRoutePrefix . '.flights.confirmation');
        @endphp
        <a href="{{ $reservationRoute }}" class="{{ request()->routeIs($flightsRoutePrefix . '.flights.confirmation') || request()->routeIs($flightsRoutePrefix . '.flights.reservations.show') ? 'active' : '' }}">
            <i class="fas fa-file-alt me-1"></i> Current file
        </a>
    @endif
    @if($flightsRoutePrefix === 'admin')
        <a href="{{ route('admin.integrations.edit', ['slug' => 'travelport']) }}" class="{{ request()->routeIs('admin.integrations*') ? 'active' : '' }}">
            <i class="fas fa-cog me-1"></i> API settings
        </a>
    @endif
</nav>

