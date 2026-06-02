<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - Tavelo</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo/favicon.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="{{ asset('assets/css/admin-theme.css') }}">
    @stack('styles')
</head>
<body>
    @php
        $user = auth()->user();
        if ($user) {
            $user->load(['userPermissions', 'role']);
        }
        $isSuperAdmin = $user && $user->user_type === 'super_admin';
        $dashboardRoute = 'admin.dashboard';
        $panelPrefix = 'admin';
        if ($user && $user->user_type === 'tenant_admin') {
            $dashboardRoute = 'agent.dashboard';
            $panelPrefix = 'agent';
        } elseif ($user && $user->user_type === 'sub_agent') {
            $dashboardRoute = 'subagent.dashboard';
            $panelPrefix = 'subagent';
        }
    @endphp
    <div class="header">
        <div class="container-fluid">
            <div class="header-flex">
                <a href="{{ route($dashboardRoute ?? 'admin.dashboard') }}" class="logo">
                    <img src="{{ asset('assets/img/logo/logo.png') }}" alt="Logo">
                </a>
                <div class="profile_dropdown">
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-light text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->first_name . ' ' . Auth::user()->last_name) }}&background=6366f1&color=fff" alt="">
                            <span class="ms-2">{{ Auth::user()->first_name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="{{ route('admin.logout') }}"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="admin-panel d-flex">
        <aside id="dashboardSidebar" class="sidebar admin-sidebar position-fixed">
            <ul class="sidebar-menu list-unstyled mb-0">
                <li>
                    <a href="{{ route($dashboardRoute) }}" class="{{ request()->routeIs('admin.dashboard') || request()->routeIs('agent.dashboard') || request()->routeIs('subagent.dashboard') ? 'active' : '' }}">
                        <svg fill="currentColor" viewBox="0 0 24 24">
                            <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                        </svg>
                        <span class="menu-text">Dashboard</span>
                    </a>
                </li>

                @if($isSuperAdmin || ($user && ($user->user_type === 'tenant_admin' || $user->hasPermission('flights.search'))))
                <li class="sidebar-section-label">Travel</li>
                <li>
                    <a href="{{ route($isSuperAdmin ? 'admin.flights.search' : $panelPrefix . '.flights.search') }}" class="{{ request()->routeIs('admin.flights*') || request()->routeIs('agent.flights*') || request()->routeIs('subagent.flights*') ? 'active' : '' }}">
                        <svg fill="currentColor" viewBox="0 0 24 24"><path d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/></svg>
                        <span class="menu-text">Flights</span>
                    </a>
                </li>
                @endif

                @if($isSuperAdmin)
                <li class="sidebar-section-label">Platform</li>
                <li>
                    <a href="{{ route('admin.tenants.index') }}" class="{{ request()->routeIs('admin.tenants*') ? 'active' : '' }}">
                        <svg fill="currentColor" viewBox="0 0 24 24">
                            <path d="M3 7h18v2H3V7zm2 4h14v10H5V11zm3 2v6h2v-6H8zm6 0v6h2v-6h-2z"/>
                        </svg>
                        <span class="menu-text">Agents</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.blogs.index') }}" class="{{ request()->routeIs('admin.blogs*') ? 'active' : '' }}">
                        <svg fill="currentColor" viewBox="0 0 24 24">
                            <path d="M19 2H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM8 0h11c2.21 0 4 1.79 4 4v14c0 2.21-1.79 4-4 4H8c-2.21 0-4-1.79-4-4V4c0-2.21 1.79-4 4-4zm3 7h8v2h-8V7zm0 4h8v2h-8v-2zm0 4h5v2h-5v-2zM7 7h2v2H7V7zm0 4h2v2H7v-2zm0 4h2v2H7v-2z"/>
                        </svg>
                        <span class="menu-text">Blogs</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.users') }}" class="{{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                        <svg fill="currentColor" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                        <span class="menu-text">Users</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.debtor-types.index') }}" class="{{ request()->routeIs('admin.debtor-types*') ? 'active' : '' }}">
                        <svg fill="currentColor" viewBox="0 0 24 24"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/></svg>
                        <span class="menu-text">Debtor types</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.integrations.index') }}" class="{{ request()->routeIs('admin.integrations*') ? 'active' : '' }}">
                        <svg fill="currentColor" viewBox="0 0 24 24"><path d="M21 16v-2l-8-5V3.5c0-.83-.67-1.5-1.5-1.5S10 2.67 10 3.5V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/></svg>
                        <span class="menu-text">Integrations</span>
                    </a>
                </li>

                @endif

                @if(!$isSuperAdmin)
                <li class="sidebar-section-label">Team</li>
                @endif

                @if($isSuperAdmin || ($user && $user->hasPermission('roles.view')))
                <li>
                    <a href="{{ route($panelPrefix . '.roles') }}" class="{{ request()->routeIs('admin.roles*') || request()->routeIs('agent.roles*') || request()->routeIs('subagent.roles*') ? 'active' : '' }}">
                        <svg fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                        <span class="menu-text">Roles</span>
                    </a>
                </li>
                @endif

                @if($isSuperAdmin || ($user && $user->hasPermission('permissions.view')))
                <li>
                    <a href="{{ route($panelPrefix . '.permissions') }}" class="{{ request()->routeIs('admin.permissions*') || request()->routeIs('agent.permissions*') || request()->routeIs('subagent.permissions*') ? 'active' : '' }}">
                        <svg fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/>
                        </svg>
                        <span class="menu-text">Permissions</span>
                    </a>
                </li>
                @endif

                @if(!$isSuperAdmin && ($user && $user->hasPermission('managers.view')))
                <li>
                    <a href="{{ route($panelPrefix . '.managers') }}" class="{{ request()->routeIs('admin.managers*') || request()->routeIs('agent.managers*') || request()->routeIs('subagent.managers*') ? 'active' : '' }}">
                        <svg fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                        <span class="menu-text">Sub Agents</span>
                    </a>
                </li>
                @endif

                
            </ul>
        </aside>
        <main class="main-content flex-grow-1">
