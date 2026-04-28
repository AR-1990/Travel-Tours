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
        body {
            background: #f8fafc;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        .header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
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
            margin-left: 300px;
            padding: 2rem;
            min-height: calc(100vh - 80px);
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
        <aside id="dashboardSidebar" class="sidebar position-fixed" style="width: 300px; top: 80px; height: calc(100vh - 80px); overflow-y: auto; padding: 1.5rem 0;">
            <ul class="sidebar-menu list-unstyled mb-0">
                @php
                    $user = auth()->user();
                    if ($user) {
                        $user->load(['userPermissions', 'role']);
                    }
                    $isAdmin = $user && $user->role_id == 1;
                @endphp

                <li>
                    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <svg fill="currentColor" viewBox="0 0 24 24">
                            <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                        </svg>
                        <span class="menu-text">Dashboard</span>
                    </a>
                </li>

                @if($isAdmin || ($user && $user->hasPermission('users.view')))
                <li>
                    <a href="{{ route('admin.users') }}" class="{{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                        <svg fill="currentColor" viewBox="0 0 24 24">
                            <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="menu-text">Users</span>
                    </a>
                </li>
                @endif

                @if($isAdmin || ($user && $user->hasPermission('roles.view')))
                <li>
                    <a href="{{ route('admin.roles') }}" class="{{ request()->routeIs('admin.roles*') ? 'active' : '' }}">
                        <svg fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                        <span class="menu-text">Roles</span>
                    </a>
                </li>
                @endif

                @if($isAdmin || ($user && $user->hasPermission('permissions.view')))
                <li>
                    <a href="{{ route('admin.permissions') }}" class="{{ request()->routeIs('admin.permissions*') ? 'active' : '' }}">
                        <svg fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/>
                        </svg>
                        <span class="menu-text">Permissions</span>
                    </a>
                </li>
                @endif

                @if($isAdmin || ($user && $user->hasPermission('managers.view')))
                <li>
                    <a href="{{ route('admin.managers') }}" class="{{ request()->routeIs('admin.managers*') ? 'active' : '' }}">
                        <svg fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                        <span class="menu-text">Managers</span>
                    </a>
                </li>
                @endif
            </ul>
        </aside>
        <main class="main-content flex-grow-1" style="margin-left: 300px; padding: 2rem; min-height: calc(100vh - 80px);">
