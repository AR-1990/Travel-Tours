@php
    $flightsDashboardRoute = match ($flightsRoutePrefix) {
        'admin' => 'admin.dashboard',
        'agent' => 'agent.dashboard',
        default => 'subagent.dashboard',
    };
    $isSearchActive = request()->routeIs($flightsRoutePrefix . '.flights.search');
    $isIndexActive = request()->routeIs($flightsRoutePrefix . '.flights.index');
@endphp
<nav class="flights-nav" aria-label="Flight section">
    <a href="{{ route($flightsDashboardRoute) }}" class="{{ request()->routeIs($flightsDashboardRoute) ? 'active' : '' }}">
        <i class="fas fa-home me-1"></i> Dashboard
    </a>
    <a href="{{ route($flightsRoutePrefix . '.flights.index') }}" class="{{ $isIndexActive ? 'active' : '' }}">
        <i class="fas fa-th-large me-1"></i> All APIs
    </a>
    <a href="{{ route($flightsRoutePrefix . '.flights.search') }}" class="{{ $isSearchActive ? 'active' : '' }}">
        <i class="fas fa-search me-1"></i> Low Fare Search
    </a>
    @if($flightsRoutePrefix === 'admin')
        <a href="{{ route('admin.integrations.edit', ['slug' => 'travelport']) }}" class="{{ request()->routeIs('admin.integrations*') ? 'active' : '' }}">
            <i class="fas fa-cog me-1"></i> API settings
        </a>
    @endif
</nav>
