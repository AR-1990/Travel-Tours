<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - {{ config('app.name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    @vite(['resources/css/app.css'])
    <style>
        :root {
            --header-height: 72px;
            --sidebar-width: 300px;
        }
        body {
            background: #f8fafc;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            margin: 0;
            overflow-x: hidden;
        }
        .header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1040;
        }
        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-height: var(--header-height);
            padding: 0.75rem 1.5rem;
        }
        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: #fff;
            text-decoration: none;
        }
        .profile_dropdown img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.3);
        }
        .sidebar {
            background: #fff;
            border-right: 1px solid #e5e7eb;
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
        }
        .sidebar-menu li a {
            color: #4b5563;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            margin: 0.25rem 1rem;
            transition: all 0.2s;
            display: flex;
            align-items: center;
        }
        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: #fff;
        }
        .sidebar-menu svg {
            width: 20px;
            height: 20px;
            margin-right: 0.75rem;
        }
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: calc(100vh - var(--header-height));
            width: calc(100% - var(--sidebar-width));
        }
        .card-modern {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            padding: 1.5rem;
            transition: all 0.3s;
        }
        .card-modern:hover {
            border-color: #6366f1;
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.15);
            transform: translateY(-2px);
        }
        .btn-primary {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 1.25rem;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container-fluid">
            <div class="header-flex">
                <a href="{{ route('admin.dashboard') }}" class="logo">
                    <i class="fas fa-shield-alt me-2"></i>Admin Panel
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
        <aside id="dashboardSidebar" class="sidebar position-fixed" style="width: var(--sidebar-width); top: var(--header-height); height: calc(100vh - var(--header-height)); overflow-y: auto; padding: 1.5rem 0;">
            <ul class="sidebar-menu list-unstyled mb-0">
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

                <li>
                    <a href="{{ route($dashboardRoute) }}" class="{{ request()->routeIs('admin.dashboard') || request()->routeIs('agent.dashboard') || request()->routeIs('subagent.dashboard') ? 'active' : '' }}">
                        <svg fill="currentColor" viewBox="0 0 24 24">
                            <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                        </svg>
                        <span class="menu-text">{{ $isSuperAdmin ? 'Super Admin' : 'Dashboard' }}</span>
                    </a>
                </li>

                @if($isSuperAdmin)
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
