@extends('admin.layouts.main')

@section('title', 'Dashboard')

@section('content')
@php
    $currentUser = auth()->user();
    $isSuperAdmin = $currentUser && $currentUser->user_type === 'super_admin';
    $isTenantAdmin = $currentUser && $currentUser->user_type === 'tenant_admin';
    $isSubAgent = $currentUser && $currentUser->user_type === 'sub_agent';

    if ($isSuperAdmin) {
        $dashTitle = 'Super Admin Dashboard';
        $dashSubtitle = 'Monitor operations, team activity, and travel modules.';
    } elseif ($isTenantAdmin) {
        $dashTitle = 'Agent Admin Dashboard';
        $dashSubtitle = 'Monitor your agency operations and team activity.';
    } elseif ($isSubAgent) {
        $dashTitle = 'Sub Agent Dashboard';
        $dashSubtitle = 'Access flights, bookings, and modules from one place.';
    } else {
        $dashTitle = 'Admin Dashboard';
        $dashSubtitle = 'Welcome to Tavelo. Monitor your operations from one control center.';
    }
@endphp
<div class="container-fluid panel-page">
    @include('admin.partials.page-header', [
        'title' => $dashTitle,
        'subtitle' => $dashSubtitle,
        'icon' => 'fas fa-tachometer-alt',
    ])

    @include('admin.partials.flash')

    <div class="row g-4">
        @if($isSuperAdmin)
        <div class="col-lg-3 col-md-6">
            <div class="tavelo-stat-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="h6 tavelo-stat-label">Agents</h3>
                        <p class="tavelo-stat-value">{{ $totalTenants ?? 0 }}</p>
                    </div>
                    <span class="tavelo-icon blue"><i class="fas fa-building"></i></span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="tavelo-stat-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="h6 tavelo-stat-label">Pending Agents</h3>
                        <p class="tavelo-stat-value">{{ $pendingTenants ?? 0 }}</p>
                    </div>
                    <span class="tavelo-icon coral"><i class="fas fa-hourglass-half"></i></span>
                </div>
            </div>
        </div>
        @endif

        <div class="col-lg-3 col-md-6">
            <div class="tavelo-stat-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="h6 tavelo-stat-label">Users</h3>
                        <p class="tavelo-stat-value">{{ $totalUsers ?? 0 }}</p>
                    </div>
                    <span class="tavelo-icon cyan"><i class="fas fa-users"></i></span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="tavelo-stat-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="h6 tavelo-stat-label">Roles</h3>
                        <p class="tavelo-stat-value">{{ \App\Models\System\Role::query()->when($isSuperAdmin, fn($q) => $q->whereNull('tenant_id')->where('slug', '!=', 'admin'))->when(!$isSuperAdmin, fn($q) => $q->where('tenant_id', auth()->user()->tenant_id))->count() }}</p>
                    </div>
                    <span class="tavelo-icon navy"><i class="fas fa-user-shield"></i></span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="tavelo-stat-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="h6 tavelo-stat-label">Permissions</h3>
                        <p class="tavelo-stat-value">{{ \App\Models\System\Permission::count() }}</p>
                    </div>
                    <span class="tavelo-icon blue"><i class="fas fa-lock"></i></span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="tavelo-stat-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="h6 tavelo-stat-label">Sub Agents</h3>
                        <p class="tavelo-stat-value">{{ $subAgentCount ?? 0 }}</p>
                    </div>
                    <span class="tavelo-icon coral"><i class="fas fa-user-friends"></i></span>
                </div>
            </div>
        </div>
    </div>

    @php
        $flightsRoute = $isSuperAdmin ? 'admin.flights.index' : ($isTenantAdmin ? 'agent.flights.index' : ($isSubAgent && $currentUser->hasPermission('flights.search') ? 'subagent.flights.index' : null));
    @endphp
    @if($flightsRoute)
    <div class="row mt-2 g-3">
        <div class="col-lg-8">
            <div class="tavelo-surface overflow-hidden p-0">
                <div class="p-4 tavelo-flights-header">
                    <h3 class="h5 mb-2"><i class="fas fa-plane me-2"></i>Flights & GDS</h3>
                    <p class="small mb-0 opacity-90">Search, price, book, and ticket flights — flight APIs only, with clear guidance on each tool.</p>
                </div>
                <div class="p-4">
                    <a href="{{ route($flightsRoute) }}" class="btn tavelo-btn-primary">Open Flights</a>
                    <a href="{{ route(str_replace('.index', '.search', $flightsRoute)) }}" class="btn tavelo-btn-soft ms-2">Search flights</a>
                    @if($isSuperAdmin)
                        <a href="{{ route('admin.integrations.edit', ['slug' => 'travelport']) }}" class="btn tavelo-btn-soft ms-2">API Settings</a>
                    @endif
                    <p class="small text-muted mt-3 mb-0">
                        <strong>Flow:</strong> Search → Price → Book → Reservation. Same steps and airport naming as the public site.
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row mt-4">
        <div class="col-12">
            <div class="tavelo-surface p-4">
                <h2 class="h4 mb-3 tavelo-section-title">
                    @if($isSuperAdmin)
                        Super Admin Dashboard
                    @elseif($isTenantAdmin)
                        Agent Admin Dashboard
                    @elseif($isSubAgent)
                        Sub Agent Dashboard
                    @else
                        Admin Dashboard
                    @endif
                </h2>
                <p class="text-secondary mb-0">
                    @if($isSuperAdmin)
                        Manage agent approvals, agent creation, and system-wide users.
                    @elseif($isTenantAdmin)
                        Manage sub agents, search flights, and configure roles for your agency.
                    @elseif($isSubAgent)
                        Access flights, bookings, and other modules based on your permissions.
                    @else
                        Manage users, roles, permissions, and more from this dashboard.
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
