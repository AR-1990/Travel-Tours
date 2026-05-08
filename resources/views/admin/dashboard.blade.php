@extends('admin.layouts.main')

@section('title', 'Dashboard')

@section('content')
<div class="admin-panel">
    <!-- ========== SIDEBAR START ========== -->
    <!-- INCLUDE SIDEBAR HERE: -->
    @include('admin.layouts.sidebar')
    <!-- ========== SIDEBAR END ========== -->

    <main class="main-content flex-grow-1">
        <div class="container-fluid">
    @php
        $currentUser = auth()->user();
        $isSuperAdmin = $currentUser && $currentUser->user_type === 'super_admin';
        $isTenantAdmin = $currentUser && $currentUser->user_type === 'tenant_admin';
        $isSubAgent = $currentUser && $currentUser->user_type === 'sub_agent';
    @endphp

    <!-- Welcome Header -->
    <div class="mb-4">
        <h1 class="dashboard-title">
            @if($isSuperAdmin)
                <span class="title-icon">🛡️</span> Super Admin Dashboard
            @elseif($isTenantAdmin)
                <span class="title-icon">🏢</span> Agent Dashboard
            @elseif($isSubAgent)
                <span class="title-icon">👤</span> Sub Agent Dashboard
            @else
                <span class="title-icon">📊</span> Admin Dashboard
            @endif
        </h1>
        <p class="dashboard-subtitle">
            @if($isSuperAdmin)
                Manage agent approvals, creation, and system-wide operations
            @elseif($isTenantAdmin)
                Manage your agent sub agents and role/permission categories
            @elseif($isSubAgent)
                Access your assigned modules and operations
            @else
                Manage users, roles, permissions, and more
            @endif
        </p>
    </div>

    <!-- Stats Row 1 - Super Admin Cards -->
    @if($isSuperAdmin)
    <div class="row g-4 mb-4">
        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="0">
            <div class="stat-card stat-card-primary">
                <div class="stat-header">
                    <div class="stat-icon icon-indigo">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-label">Agents</h3>
                        <p class="stat-number">{{ $totalTenants ?? 0 }}</p>
                    </div>
                </div>
                <div class="stat-footer">
                    <span class="stat-trend"><i class="fas fa-arrow-up"></i> Active</span>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-card stat-card-warning">
                <div class="stat-header">
                    <div class="stat-icon icon-yellow">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-label">Pending Agents</h3>
                        <p class="stat-number">{{ $pendingTenants ?? 0 }}</p>
                    </div>
                </div>
                <div class="stat-footer">
                    <span class="stat-trend trending-warning"><i class="fas fa-clock"></i> Awaiting Approval</span>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
            <div class="stat-card stat-card-success">
                <div class="stat-header">
                    <div class="stat-icon icon-green">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-label">Total Users</h3>
                        <p class="stat-number">{{ $totalUsers ?? 0 }}</p>
                    </div>
                </div>
                <div class="stat-footer">
                    <span class="stat-trend"><i class="fas fa-arrow-up"></i> All Systems</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Stats Row 2 - Main Stats Cards -->
    <div class="row g-4">
        <!-- Users Card -->
        <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="0">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon icon-indigo">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-label">Users</h3>
                        <p class="stat-number">{{ $totalUsers ?? 0 }}</p>
                    </div>
                </div>
                <div class="stat-footer">
                    <a href="#" class="stat-link">View all <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <!-- Roles Card -->
        <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon icon-blue">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-label">Roles</h3>
                        <p class="stat-number">{{ \App\Models\System\Role::query()->when($isSuperAdmin, fn($q) => $q->whereNull('tenant_id')->where('slug', '!=', 'admin'))->when(!$isSuperAdmin, fn($q) => $q->where('tenant_id', auth()->user()->tenant_id))->count() }}</p>
                    </div>
                </div>
                {{-- <div class="stat-footer">
                    <a href="{{ route($panelPrefix . '.roles') }}" class="stat-link">Manage <i class="fas fa-arrow-right ms-1"></i></a>
                </div> --}}
            </div>
        </div>

        <!-- Permissions Card -->
        <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon icon-purple">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-label">Permissions</h3>
                        <p class="stat-number">{{ \App\Models\System\Permission::count() }}</p>
                    </div>
                </div>
                {{-- <div class="stat-footer">
                    <a href="{{ route($panelPrefix . '.permissions') }}" class="stat-link">Configure <i class="fas fa-arrow-right ms-1"></i></a>
                </div> --}}
            </div>
        </div>

        <!-- Sub Agents Card -->
        <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon icon-teal">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-label">Sub Agents</h3>
                        <p class="stat-number">{{ $subAgentCount ?? 0 }}</p>
                    </div>
                </div>
                {{-- <div class="stat-footer">
                    <a href="{{ route($panelPrefix . '.managers') }}" class="stat-link">Browse <i class="fas fa-arrow-right ms-1"></i></a>
                </div> --}}
            </div>
        </div>
    </div>

    <!-- Welcome Section -->
    <div class="row mt-5">
        <div class="col-12" data-aos="fade-up" data-aos-delay="400">
            <div class="card-modern welcome-card">
                <div class="welcome-content">
                    <div class="welcome-badge">
                        @if($isSuperAdmin)
                            System Administrator
                        @elseif($isTenantAdmin)
                            Agent Administrator
                        @elseif($isSubAgent)
                            Sub Agent
                        @else
                            Administrator
                        @endif
                    </div>
                    <h2 class="welcome-title">
                        Welcome back, <strong>{{ auth()->user()->first_name }}</strong>! 👋
                    </h2>
                    <p class="welcome-text">
                        @if($isSuperAdmin)
                            You have full system access. Manage agent approvals, monitor system-wide activities, and oversee all operations from this centralized dashboard.
                        @elseif($isTenantAdmin)
                            Manage your sub agents efficiently and configure roles and permissions for your organization.
                        @elseif($isSubAgent)
                            Access your assigned modules and perform operations based on your role permissions.
                        @else
                            Manage all administrative functions and keep your system running smoothly.
                        @endif
                    </p>
                    <div class="welcome-actions">
                        <a href="javascript:void(0)" class="btn-welcome btn-welcome-primary">
                            <i class="fas fa-star me-2"></i> Quick Actions
                        </a>
                        <a href="javascript:void(0)" class="btn-welcome btn-welcome-secondary">
                            <i class="fas fa-question-circle me-2"></i> Need Help?
                        </a>
                    </div>
                </div>
                <div class="welcome-decoration">
                    <div class="decoration-shape shape-1"></div>
                    <div class="decoration-shape shape-2"></div>
                    <div class="decoration-shape shape-3"></div>
                </div>
            </div>
        </div>
        </div>
    </main>
</div>

@endsection