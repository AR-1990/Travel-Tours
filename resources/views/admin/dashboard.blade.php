@extends('admin.layouts.main')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    @php
        $currentUser = auth()->user();
        $isSuperAdmin = $currentUser && $currentUser->user_type === 'super_admin';
        $isTenantAdmin = $currentUser && $currentUser->user_type === 'tenant_admin';
        $isSubAgent = $currentUser && $currentUser->user_type === 'sub_agent';
    @endphp

    <div class="row g-4">
        @if($isSuperAdmin)
        <div class="col-lg-3 col-md-6">
            <div class="card-modern">
                <h3 class="h4 mb-1 text-gray-700">Agents</h3>
                <p class="h2 mb-0 text-indigo-600">{{ $totalTenants ?? 0 }}</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card-modern">
                <h3 class="h4 mb-1 text-gray-700">Pending Agents</h3>
                <p class="h2 mb-0 text-indigo-600">{{ $pendingTenants ?? 0 }}</p>
            </div>
        </div>
        @endif

        <!-- Stats Cards -->
        <div class="col-lg-3 col-md-6">
            <div class="card-modern">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="h4 mb-1 text-gray-700">Users</h3>
                        <p class="h2 mb-0 text-indigo-600">{{ $totalUsers ?? 0 }}</p>
                    </div>
                    <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-content-center">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card-modern">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="h4 mb-1 text-gray-700">Roles</h3>
                        <p class="h2 mb-0 text-indigo-600">{{ \App\Models\System\Role::query()->when($isSuperAdmin, fn($q) => $q->whereNull('tenant_id')->where('slug', '!=', 'admin'))->when(!$isSuperAdmin, fn($q) => $q->where('tenant_id', auth()->user()->tenant_id))->count() }}</p>
                    </div>
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-content-center">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card-modern">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="h4 mb-1 text-gray-700">Permissions</h3>
                        <p class="h2 mb-0 text-indigo-600">{{ \App\Models\System\Permission::count() }}</p>
                    </div>
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-content-center">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card-modern">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="h4 mb-1 text-gray-700">Sub Agents</h3>
                        <p class="h2 mb-0 text-indigo-600">{{ $subAgentCount ?? 0 }}</p>
                    </div>
                    <div class="w-16 h-16 bg-gradient-to-br from-teal-500 to-green-600 rounded-xl flex items-center justify-content-center">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
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
            <div class="card-modern overflow-hidden p-0">
                <div class="p-4" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: #fff;">
                    <h3 class="h5 mb-2"><i class="fas fa-plane me-2"></i>Flights & GDS</h3>
                    <p class="small mb-0 opacity-90">Search, price, book, and ticket flights — flight APIs only, with clear guidance on each tool.</p>
                </div>
                <div class="p-4">
                    <a href="{{ route($flightsRoute) }}" class="btn btn-primary">Open air APIs</a>
                    <a href="{{ route(str_replace('.index', '.search', $flightsRoute)) }}" class="btn btn-outline-primary ms-2">Low Fare Search</a>
                    @if($isSuperAdmin)
                        <a href="{{ route('admin.integrations.edit', ['slug' => 'travelport']) }}" class="btn btn-outline-secondary ms-2">API settings</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Welcome Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card-modern">
                <h2 class="h3 mb-3 text-gray-800">
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
                <p class="text-gray-600 mb-0">
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
