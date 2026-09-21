@php
    $prefix = $hotelsRoutePrefix ?? 'admin';
    $dashboardRoute = match ($prefix) {
        'admin' => 'admin.dashboard',
        'agent' => 'agent.dashboard',
        default => 'subagent.dashboard',
    };
    $hasPrebook = session('panel.hotel_prebook.'.$prefix);
    $hasBooking = session('panel.hotel_booking.'.$prefix);
@endphp
<nav class="flights-nav mb-3" aria-label="Hotel section">
    <a href="{{ route($dashboardRoute) }}" class="{{ request()->routeIs($dashboardRoute) ? 'active' : '' }}">
        <i class="fas fa-home me-1"></i> Dashboard
    </a>
    <a href="{{ route($prefix . '.hotels.search') }}" class="{{ request()->routeIs($prefix . '.hotels.search') ? 'active' : '' }}">
        <i class="fas fa-search me-1"></i> Search
    </a>
    @if($hasPrebook)
        <a href="{{ route($prefix . '.hotels.book') }}" class="{{ request()->routeIs($prefix . '.hotels.book*') ? 'active' : '' }}">
            <i class="fas fa-user me-1"></i> Book
        </a>
    @endif
    @if($hasBooking)
        <a href="{{ route($prefix . '.hotels.confirmation') }}" class="{{ request()->routeIs($prefix . '.hotels.confirmation') ? 'active' : '' }}">
            <i class="fas fa-file-alt me-1"></i> Current booking
        </a>
    @endif
    <a href="{{ route($prefix . '.hotels.reservations.index') }}" class="{{ request()->routeIs($prefix . '.hotels.reservations*') ? 'active' : '' }}">
        <i class="fas fa-folder-open me-1"></i> Reservations
    </a>
    @if($prefix === 'admin')
        <a href="{{ route('admin.integrations.edit', ['slug' => 'downtown_travel_hotels']) }}">
            <i class="fas fa-cog me-1"></i> API settings
        </a>
    @endif
</nav>
