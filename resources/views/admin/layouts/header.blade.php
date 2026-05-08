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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('admin-assets/css/main.css') }}">
    @vite(['resources/css/app.css'])
</head>
<body>
    <div class="header">
        <div class="container-fluid">
            <div class="header-flex">
                <a href="{{ route('admin.dashboard') }}" class="logo">
                    <i class="fas fa-shield-alt"></i>Admin
                </a>
                <div class="header-actions">
                    <!-- Dark Mode Toggle -->
                    {{-- <button id="darkModeToggle" class="theme-toggle" title="Toggle dark mode">
                        <i class="fas fa-moon"></i>
                    </button>

                    <!-- Sidebar Toggle (Mobile) -->
                    <button id="sidebarToggle" class="sidebar-toggle" title="Toggle sidebar">
                        <i class="fas fa-bars"></i>
                    </button> --}}

                    <!-- User Profile Dropdown -->
                    <div class="profile_dropdown">
                        <div class="dropdown">
                            <a href="#" class="d-flex align-items-center text-light text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" role="button">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->first_name . ' ' . Auth::user()->last_name) }}&background=667eea&color=fff" alt="User Avatar">
                                <span class="ms-2 fw-600">{{ Auth::user()->first_name }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li><a class="dropdown-item" href="{{ route('admin.logout') }}"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

  
