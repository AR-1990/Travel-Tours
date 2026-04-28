<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Laravel Boilerplate') }}</title>
    <meta name="description"
        content="{{ config('app.name', 'Laravel Boilerplate') }} - A clean, production-ready Laravel boilerplate with admin panel, user management, roles & permissions.">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ config('app.name', 'Laravel Boilerplate') }}">
    <meta property="og:description"
        content="A clean, production-ready Laravel boilerplate with admin panel, user management, roles & permissions.">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ asset('favicon.ico') }}">
    <meta property="og:site_name" content="{{ config('app.name') }}">

    <!-- fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap"
        rel="stylesheet">

    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

    <link rel="stylesheet" href="{{ asset('assets/css/style.css?v2') }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    {{-- Load built assets directly --}}
    @php
        $manifestPath = public_path('build/manifest.json');
        $appCss = $chatCss = $appJs = null;
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            $appCss = $manifest['resources/css/app.css']['file'] ?? null;
            $chatCss = $manifest['resources/css/chat.css']['file'] ?? null;
            $appJs = $manifest['resources/js/App.jsx']['file'] ?? null;
        }
    @endphp

    @if ($appCss)
        <link rel="stylesheet" href="{{ asset('build/' . $appCss) }}">
    @endif

    @if (!request()->routeIs('inbox') && $chatCss)
        <link rel="stylesheet" href="{{ asset('build/' . $chatCss) }}">
    @endif

    @if ($appJs)
        <script type="module" src="{{ asset('build/' . $appJs) }}"></script>
    @endif

    <style>
        .suggestion-box {
            position: absolute;
            background-color: var(--white);
            border: 1px solid var(--primary);
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            z-index: 100;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-top: 5px;
            display: none;
            border-radius: 7px;
            color: var(--black);
        }

        .suggestion-item {
            padding: 8px;
            cursor: pointer;
        }

        input#header_search {
            font-family: "Inter", sans-serif;
            font-optical-sizing: auto;
            font-style: normal;
            font-weight: 400;
            font-size: 16px;
            color: #273572;
            border-radius: 10px 0 0 10px;
        }

        input#header_search::placeholder {
            font-family: "Inter", sans-serif;
            font-optical-sizing: auto;
            font-style: normal;
            font-weight: 400;
            font-size: 16px;
            color: #273572;
        }

        .custom-tooltip {
            position: absolute;
            background: linear-gradient(180deg, #0e1948, #213bae);
            color: #fff;
            padding: 10px 15px;
            border-radius: 6px;
            font-size: 14px;
            font-family: "Inter", sans-serif;
            font-optical-sizing: auto;
            font-style: normal;
            font-weight: 400;
            pointer-events: none;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 99999999;
            white-space: nowrap;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .custom-tooltip.show {
            opacity: 1;
            visibility: visible;
        }

        .custom-tooltip::before {
            content: '';
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 7px solid transparent;
            border-bottom-color: #0e1948;
        }

        /* Notification Dropdown Styles */
        .notification-dropdown {
            padding: 0;
            min-width: 400px;
        }

        .notification-item {
            padding: 16px 16px;
            border-bottom: 1px solid #e9ecef;
            cursor: pointer;
            transition: .3s;
            position: relative;
        }

        .notification-item-row {
            display: flex;
            align-items: start;
            justify-content: start;
            gap: 16px;
        }

        .notification-item-col {
            max-width: max-content;
            width: 100%;
        }

        .notification-item-col img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }

        .avatar-initials-notification {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #394a93;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 12px;
        }

        .notification-item:hover {
            background-color: #273572;
            transition: .3s;
        }

        .notification-item:hover .notification-title {
            color: #fff;
            transition: .3s;
        }

        .notification-item:hover .notification-message {
            color: #fff;
            transition: .3s;
        }

        .notification-item:hover .notification-time {
            color: #fff;
            transition: .3s;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-item.unread::after {
            content: "";
            position: absolute;
            top: 8px;
            left: 8px;
            width: 8px;
            height: 8px;
            background: #90CDF4;
            border-radius: 50%;
            border: 1px solid #4299E1;
            animation: pulse-unread 2s infinite;
        }

        /* Pulse animation for unread notification */
        @keyframes pulse-unread {

            0%,
            100% {
                box-shadow: 0 0 0 0 #90CDF4;
            }

            50% {
                box-shadow: 0 0 0 4px rgba(34, 197, 94, 0);
            }
        }

        .notification-title {
            color: #1A1F36;
            margin-bottom: 0;
            font-family: "Inter", sans-serif;
            font-weight: 500;
            font-size: 14px;
            line-height: 20px;
        }

        .notification-message {
            font-family: "Inter", sans-serif;
            color: #1A1F36;
            background: #D9D9D957;
            padding: 10px 20px 7px 12px;
            max-width: max-content;
            border-radius: 10px;
            margin: 5px 0 0 0;
            font-weight: 500;
            font-size: 12px;
            line-height: 20px;
        }

        .notification-time {
            font-family: "Inter", sans-serif;
            color: #A5ACB8;
            font-weight: 500;
            font-size: 14px;
            line-height: 20px;
            margin: 8px 0 0 0;
        }

        .notification-empty {
            text-align: center;
            padding: 30px 15px;
            color: #6c757d;
            font-size: 16px;
            font-weight: 400;
            font-family: 'Inter', sans-serif;
        }

        #notificationBadge {
            font-size: 10px;
            padding: 2px 6px;
        }

        /* Ensure dropdown is hidden by default */
        .notification-dropdown {
            display: none !important;
        }

        .notification-dropdown.show {
            display: block !important;
            padding: 0;
            overflow: hidden;
        }

        #notificationList {
            max-height: 400px;
            overflow-y: auto;
            min-height: 100px;
            /* Ensure space for loading spinner */
        }

        #notificationLoader {
            padding: 10px 0;
        }
    </style>

    @yield('styles')
</head>

<body>

    <div class="header position-relative">
        <div class="header_flex">
            <!-- Mobile Toggle Button -->
            <div class="mobile_toggle">
                <i class="fas fa-bars" id="toggleDrawerBtn"></i>
            </div>
            <div class="header_left">
                <div class="logo">
                    <a href="{{ route('home') }}">
                        <img src="{{ asset('assets/images/greenAndWhiteLogo.png') }}" alt="" class="img-fluid">
                    </a>
                </div>

                <div class="header-mid mobile_hide">
                    <form method="GET" action="{{ route('search') }}" class="mb-0" id="search_form">
                        <div class="search_area">
                            <div class="suggestion_search">
                                <input type="text" id="header_search" autocomplete="off"
                                    placeholder="Search People, Product, Service or Industry" class="form-control">
                                <div id="suggestion_box" class="suggestion-box" style="display: none;"></div>
                            </div>

                            <input type="hidden" name="name" id="first_name1">
                            <input type="hidden" name="product" id="product1">
                            <input type="hidden" name="service" id="service1">
                            <input type="hidden" name="company_industry" id="company_industry1">

                            <button type="submit" class="btn btn-primary search_btn">
                                <img src="{{ asset('assets/images/fe_search.svg') }}" alt="Search">
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="header_right">
                <div class="top_header_links mobile_hide">
                    <ul>
                        <li>
                            <a href="{{ route('news-feed') }}" class="btn btn-primary" data-toggle="tooltip"
                                data-placement="bottom" title="News Feed">
                                <i class="fa-solid fa-newspaper"></i>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('smart.suggestion') }}" class="btn btn-primary" data-toggle="tooltip"
                                data-placement="bottom" title="Smart Suggestions">
                                <img src="{{ asset('assets/images/suggestion.svg') }}" alt="Suggestions">
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('our.community') }}" class="btn btn-primary" data-toggle="tooltip"
                                data-placement="bottom" title="Our Community">
                                <img src="{{ asset('assets/images/Vector.svg') }}" alt="community">
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('inbox') }}" class="btn btn-primary position-relative"
                                data-toggle="tooltip" data-placement="bottom" title="Inbox">
                                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25"
                                    viewBox="0 0 24 24" style="fill: rgba(255, 255, 255, 1);transform: ;msFilter:;">
                                    <circle cx="9.5" cy="9.5" r="1.5"></circle>
                                    <circle cx="14.5" cy="9.5" r="1.5"></circle>
                                    <path
                                        d="M12 2C6.486 2 2 5.589 2 10c0 2.908 1.897 5.515 5 6.934V22l5.34-4.004C17.697 17.852 22 14.32 22 10c0-4.411-4.486-8-10-8zm0 14h-.333L9 18v-2.417l-.641-.247C5.671 14.301 4 12.256 4 10c0-3.309 3.589-6 8-6s8 2.691 8 6-3.589 6-8 6z">
                                    </path>
                                </svg>
                                <div id="inbox-unread-badge"></div>
                            </a>
                        </li>
                        <li>
                            <div class="dropdown position-relative">
                                <button class="btn btn-primary position-relative" type="button"
                                    id="notificationDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false"
                                    data-toggle="tooltip" data-placement="bottom" title="Notifications">
                                    <i class="fas fa-bell"></i>
                                    <span
                                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                        id="notificationBadge" style="display: none;">
                                        0
                                    </span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end notification-dropdown"
                                    id="notificationDropdown">
                                    <li>
                                        <h6 class="dropdown-header">
                                            <span>Notifications</span>
                                            <button class="btn btn-sm btn-link text-decoration-none"
                                                id="markAllReadBtn">
                                                Mark all read <i class="fa-regular fa-circle-check"></i>
                                            </button>
                                        </h6>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <div id="notificationList">
                                            <!-- Initial loading state -->
                                            <div class="text-center py-3">
                                                <div class="spinner-border spinner-border-sm text-primary"
                                                    role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li id="notificationLoader" style="display: none;">
                                        <div class="text-center py-2">
                                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="profile">
                    @php
                        $currentUser = Auth::user();
                        $photoPath = $currentUser->photo ?? null;
                        $hasPhoto = false;
                        if ($photoPath) {
                            if (str_starts_with($photoPath, 'http')) {
                                $hasPhoto = true;
                            } else {
                                $hasPhoto = \Illuminate\Support\Facades\Storage::disk('public')->exists($photoPath);
                            }
                        }
                        $initials = strtoupper(
                            substr($currentUser->first_name ?? '', 0, 1) . substr($currentUser->last_name ?? '', 0, 1),
                        );
                    @endphp

                    @if ($hasPhoto && $photoPath)
                        <img src="{{ getImageUrl($photoPath) }}" alt="{{ $currentUser->first_name }}">
                    @else
                        <div class="avatar-initials-header">
                            {{ $initials }}
                        </div>
                    @endif
                    <div class="dropdown">
                        <a href="javascript:void(0);" class="profile_name_dd dropdown-toggle"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="user_profile_name_h"> {{ Auth::user()->first_name }} </span>
                            <img id="userProfileDropdown" src="{{ asset('assets/images/whiteChevron.svg') }}"
                                alt="DropDown">
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li><a class="dropdown-item logoutBtn" href="{{ route('logout') }}">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Side Drawer -->
        <div class="side_drawer" id="sideDrawer">
            <div class="drawer_header">
                <i class="fas fa-times" id="closeDrawerBtn"></i>
            </div>
            <div class="drawer_content">
                <form method="GET" action="{{ route('search') }}" id="mobile_search_form">
                    <div class="search_area flex-column">
                        <div class="suggestion_search w-100">
                            <input type="text" id="mobile_header_search" autocomplete="off"
                                placeholder="Search People, Product, Service or Industry" class="form-control">
                        </div>

                        <input type="hidden" name="name" id="mobile_first_name1">
                        <input type="hidden" name="product" id="mobile_product1">
                        <input type="hidden" name="service" id="mobile_service1">
                        <input type="hidden" name="company_industry" id="mobile_company_industry1">

                        <button type="submit" class="btn btn-primary search_btn mt-2">Search</button>
                    </div>
                </form>

                <a href="{{ route('news-feed') }}" class="btn btn-primary mt-2 w-100">News Feed</a>
                <a href="{{ route('smart.suggestion') }}" class="btn btn-primary mt-2 w-100">Smart Suggestion</a>
                <a href="{{ route('our.community') }}" class="btn btn-primary mt-4 w-100">Our Community</a>
                <a href="{{ route('inbox') }}" class="btn btn-primary mt-2 w-100">Inbox</a>
            </div>
        </div>
    </div>

    <script>
        // ===== TOOLTIP JS =====
        document.addEventListener('DOMContentLoaded', function() {
            const triggers = document.querySelectorAll('[data-toggle="tooltip"]');
            let activeTooltip = null;

            triggers.forEach(function(trigger) {
                trigger.addEventListener('mouseenter', function() {
                    const text = trigger.getAttribute('title') || trigger.getAttribute(
                        'data-original-title');
                    if (!text) return;

                    if (!trigger.getAttribute('data-original-title')) {
                        trigger.setAttribute('data-original-title', text);
                        trigger.removeAttribute('title');
                    }

                    if (activeTooltip) {
                        activeTooltip.remove();
                    }

                    activeTooltip = document.createElement('div');
                    activeTooltip.className = 'custom-tooltip';
                    activeTooltip.textContent = text;
                    document.body.appendChild(activeTooltip);

                    const triggerRect = trigger.getBoundingClientRect();
                    const tooltipRect = activeTooltip.getBoundingClientRect();
                    const left = triggerRect.left + (triggerRect.width / 2) - (tooltipRect.width /
                        2) + window.scrollX;
                    const top = triggerRect.bottom + 10 + window.scrollY;

                    activeTooltip.style.left = left + 'px';
                    activeTooltip.style.top = top + 'px';

                    setTimeout(function() {
                        if (activeTooltip) {
                            activeTooltip.classList.add('show');
                        }
                    }, 10);
                });

                trigger.addEventListener('mouseleave', function() {
                    if (activeTooltip) {
                        activeTooltip.classList.remove('show');
                        const tooltipToRemove = activeTooltip;
                        setTimeout(function() {
                            tooltipToRemove.remove();
                        }, 300);
                        activeTooltip = null;
                    }
                });
            });
        });
    </script>

    <script>
        // Notification Dropdown Functionality
        (function() {
            let notifications = [];
            let unreadCount = 0;
            let currentPage = 1;
            let lastPage = 1;
            let isLoading = false;
            let hasLoadedOnce = false;

            // ========== DEBUGGING UTILITIES ==========
            const DEBUG = true; // Set to false to disable logs in production
            const timers = {};

            function debugLog(message, data = null) {
                if (!DEBUG) return;
                const timestamp = new Date().toISOString().split('T')[1];
                if (data) {
                    console.log(`[NOTIF ${timestamp}] ${message}`, data);
                } else {
                    console.log(`[NOTIF ${timestamp}] ${message}`);
                }
            }

            function startTimer(name) {
                if (!DEBUG) return;
                timers[name] = performance.now();
                debugLog(`⏱️ START: ${name}`);
            }

            function endTimer(name) {
                if (!DEBUG) return;
                if (timers[name]) {
                    const elapsed = (performance.now() - timers[name]).toFixed(2);
                    debugLog(`⏱️ END: ${name} - took ${elapsed}ms`);
                    delete timers[name];
                }
            }

            // ========== FETCH NOTIFICATIONS ==========
            function fetchNotifications(page = 1, append = false) {
                startTimer('fetchNotifications');
                debugLog(`📥 fetchNotifications called`, {
                    page,
                    append,
                    isLoading,
                    hasLoadedOnce
                });

                if (isLoading) {
                    debugLog('⚠️ Already loading, aborting');
                    endTimer('fetchNotifications');
                    return;
                }

                const token = localStorage.getItem('sanctum-token');
                if (!token) {
                    debugLog('❌ No token found');
                    $('#notificationList').html(
                        '<div class="notification-empty">Please login to see notifications</div>');
                    endTimer('fetchNotifications');
                    return;
                }

                isLoading = true;
                debugLog('🔄 isLoading set to true');

                // Show loading state
                if (!append) {
                    if (!hasLoadedOnce) {
                        startTimer('showLoadingSpinner');
                        $('#notificationList').html(
                            '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>'
                        );
                        endTimer('showLoadingSpinner');
                        debugLog('🔵 Loading spinner shown');
                    }
                } else {
                    $('#notificationLoader').show();
                    debugLog('🔵 Loader shown for append');
                }

                startTimer('ajaxRequest');
                debugLog('🌐 Starting AJAX request');

                $.ajax({
                    url: `/notifications?per_page=5&page=${page}&unread_only=false`,
                    method: 'GET',
                    headers: {
                        'Authorization': token,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    dataType: 'json',
                    cache: false,
                    beforeSend: function() {
                        debugLog('📤 AJAX beforeSend fired');
                    },
                    success: function(response) {
                        endTimer('ajaxRequest');
                        debugLog('✅ AJAX success', {
                            notificationCount: response.notifications?.data?.length,
                            unreadCount: response.unread_count
                        });

                        isLoading = false;
                        hasLoadedOnce = true;
                        $('#notificationLoader').hide();

                        if (response.status && response.notifications) {
                            startTimer('processResponse');

                            const newNotifications = response.notifications.data || [];
                            currentPage = response.notifications.current_page;
                            lastPage = response.notifications.last_page;
                            unreadCount = response.unread_count || 0;

                            debugLog('📊 Response processed', {
                                newCount: newNotifications.length,
                                currentPage,
                                lastPage,
                                unreadCount
                            });

                            if (append) {
                                notifications = notifications.concat(newNotifications);
                                debugLog('➕ Appended to existing notifications');
                            } else {
                                notifications = newNotifications;
                                debugLog('🔄 Replaced notifications');
                            }

                            endTimer('processResponse');

                            updateNotificationBadge();
                            renderNotifications(append);
                        }

                        endTimer('fetchNotifications');
                    },
                    error: function(xhr, status, error) {
                        endTimer('ajaxRequest');
                        debugLog('❌ AJAX error', {
                            status: xhr.status,
                            error,
                            statusText: xhr.statusText
                        });

                        isLoading = false;
                        hasLoadedOnce = true;
                        $('#notificationLoader').hide();

                        if (!append) {
                            $('#notificationList').html(
                                '<div class="notification-empty">Failed to load notifications</div>');
                        }

                        endTimer('fetchNotifications');
                    }
                });
            }

            // ========== UPDATE BADGE ==========
            function updateNotificationBadge() {
                startTimer('updateBadge');
                const badge = $('#notificationBadge');
                if (unreadCount > 0) {
                    badge.text(unreadCount > 99 ? '99+' : unreadCount).show();
                    debugLog('🔔 Badge updated', {
                        count: unreadCount
                    });
                } else {
                    badge.hide();
                    debugLog('🔕 Badge hidden');
                }
                endTimer('updateBadge');
            }

            // ========== RENDER NOTIFICATIONS ==========
            function renderNotifications(append = false) {
                startTimer('renderNotifications');
                debugLog('🎨 renderNotifications called', {
                    count: notifications.length,
                    append
                });

                const list = $('#notificationList');

                if (notifications.length === 0) {
                    list.html('<div class="notification-empty">No notifications yet</div>');
                    debugLog('📭 No notifications to render');
                    endTimer('renderNotifications');
                    return;
                }

                startTimer('buildHTML');
                let html = '';
                notifications.forEach(function(notif) {
                    const isUnread = !notif.read_at;
                    const timeAgo = getTimeAgo(notif.created_at);
                    const notifDataString = JSON.stringify(notif).replace(/'/g, '&apos;').replace(/"/g,
                        '&quot;');

                    let avatarHtml = '';
                    if (notif.user_has_photo && notif.user_photo) {
                        avatarHtml = `<img src="${notif.user_photo}" alt="" class="img-fluid">`;
                    } else if (notif.user_initials) {
                        avatarHtml = `<div class="avatar-initials-notification">${notif.user_initials}</div>`;
                    } else {
                        avatarHtml = `<div class="avatar-initials-notification">?</div>`;
                    }

                    html += `
                <div class="notification-item ${isUnread ? 'unread' : ''}"
                     data-id="${notif.id}"
                     data-type="${notif.type || ''}"
                     data-notification='${notifDataString}'>
                     <div class="notification-item-row">
                        <div class="notification-item-col">
                            ${avatarHtml}
                        </div>
                        <div class="notification-item-col">
                            <div class="notification-title">${escapeHtml(notif.title || 'Notification')}</div>
                            <div class="notification-message">${escapeHtml(notif.message || '')}</div>
                            <div class="notification-time">${timeAgo}</div>
                        </div>
                     </div>
                </div>`;
                });
                endTimer('buildHTML');
                debugLog('🏗️ HTML built for notifications');

                startTimer('injectHTML');
                if (append) {
                    list.append(html);
                    debugLog('➕ HTML appended');
                } else {
                    list.html(html);
                    debugLog('🔄 HTML replaced');
                }
                endTimer('injectHTML');

                bindNotificationClicks();
                endTimer('renderNotifications');
            }

            // ========== BIND CLICKS ==========
            function bindNotificationClicks() {
                startTimer('bindClicks');
                debugLog('🖱️ Binding click events');

                $('.notification-item').off('click').on('click', function(e) {
                    debugLog('👆 Notification clicked');
                    e.preventDefault();
                    e.stopPropagation();

                    const $item = $(this);
                    const notificationId = $item.data('id');

                    let notification;
                    try {
                        const notifData = $item.attr('data-notification');
                        if (notifData) {
                            notification = JSON.parse(notifData);
                            debugLog('✅ Notification parsed', {
                                id: notificationId,
                                type: notification.type
                            });
                        }
                    } catch (e) {
                        debugLog('⚠️ Parse failed, searching in array', {
                            error: e.message
                        });
                        notification = notifications.find(n => n.id === notificationId);
                    }

                    if (notification) {
                        if (!notification.read_at) {
                            debugLog('📧 Marking as read', {
                                id: notificationId
                            });
                            markAsRead(notificationId);
                        }

                        const dropdownBtn = document.getElementById('notificationDropdownBtn');
                        const dropdownInstance = bootstrap.Dropdown.getInstance(dropdownBtn);
                        if (dropdownInstance) {
                            dropdownInstance.hide();
                            debugLog('🔽 Dropdown hidden');
                        }

                        const redirectUrl = getNotificationRedirectUrl(notification);
                        debugLog('🔗 Redirecting to', {
                            url: redirectUrl
                        });

                        setTimeout(function() {
                            window.location.href = redirectUrl;
                        }, 100);
                    } else {
                        debugLog('❌ Notification not found', {
                            id: notificationId
                        });
                    }
                });

                endTimer('bindClicks');
                debugLog(`✅ Click events bound to ${$('.notification-item').length} items`);
            }

            // ========== INFINITE SCROLL ==========
            function setupInfiniteScroll() {
                startTimer('setupInfiniteScroll');
                debugLog('📜 Setting up infinite scroll');

                $('#notificationList').off('scroll').on('scroll', function() {
                    const scrollTop = $(this).scrollTop();
                    const scrollHeight = $(this)[0].scrollHeight;
                    const clientHeight = $(this).height();

                    if (scrollTop + clientHeight >= scrollHeight - 50) {
                        if (currentPage < lastPage && !isLoading) {
                            debugLog('📜 Scroll triggered load more', {
                                currentPage,
                                lastPage
                            });
                            fetchNotifications(currentPage + 1, true);
                        }
                    }
                });

                endTimer('setupInfiniteScroll');
                debugLog('✅ Infinite scroll ready');
            }

            // ========== HELPER FUNCTIONS ==========
            function getTimeAgo(dateString) {
                const date = new Date(dateString);
                const now = new Date();
                const diffInSeconds = Math.floor((now - date) / 1000);

                if (diffInSeconds < 60) return 'Just now';
                if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + ' minutes ago';
                if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + ' hours ago';
                if (diffInSeconds < 604800) return Math.floor(diffInSeconds / 86400) + ' days ago';
                return date.toLocaleDateString();
            }

            function escapeHtml(text) {
                const map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return text.replace(/[&<>"']/g, m => map[m]);
            }

            function getNotificationRedirectUrl(notification) {
                const data = notification.data || {};

                switch (notification.type) {
                    case 'new_message':
                    case 'message_reaction':
                        if (data.conversation_id) {
                            return `/inbox?conversation=${data.conversation_id}`;
                        }
                        return '/inbox';

                    case 'post_reaction':
                    case 'post_share':
                        if (data.post_slug) {
                            return `/news-feed/posts/${data.post_slug}`;
                        }
                        return '/news-feed';

                    case 'post_comment':
                        if (data.post_slug) {
                            let url = `/news-feed/posts/${data.post_slug}`;
                            if (data.comment_id) {
                                url += `#comment-${data.comment_id}`;
                            }
                            return url;
                        }
                        return '/news-feed';

                    case 'comment_reply':
                        if (data.post_slug) {
                            let url = `/news-feed/posts/${data.post_slug}`;
                            if (data.parent_comment_id) {
                                url += `#comment-${data.parent_comment_id}`;
                            } else if (data.comment_id) {
                                url += `#comment-${data.comment_id}`;
                            }
                            return url;
                        }
                        return '/news-feed';

                    case 'new_service':
                        if (data.service_id) {
                            return `/services#service-${data.service_id}`;
                        }
                        return '/services';

                    case 'new_product':
                        if (data.product_id) {
                            return `/products#product-${data.product_id}`;
                        }
                        return '/products';

                    case 'profile_view':
                        if (data.viewer_id) {
                            return `/user/profile/${data.viewer_slug || data.viewer_id}`;
                        }
                        return '/news-feed';

                    case 'new_follower':
                        if (data.follower_id) {
                            return `/user/profile/${data.follower_slug || data.follower_id}`;
                        }
                        return '/news-feed';

                    default:
                        return '/news-feed';
                }
            }

            // ========== MARK AS READ ==========
            function markAsRead(notificationId) {
                const token = localStorage.getItem('sanctum-token');
                if (!token) return;

                debugLog('📧 Marking notification as read', {
                    id: notificationId
                });

                $.ajax({
                    url: `/notifications/${notificationId}/read`,
                    method: 'POST',
                    headers: {
                        'Authorization': token,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function() {
                        const notif = notifications.find(n => n.id === notificationId);
                        if (notif) {
                            notif.read_at = new Date().toISOString();
                            unreadCount = Math.max(0, unreadCount - 1);
                            updateNotificationBadge();
                            renderNotifications(false);
                            debugLog('✅ Marked as read successfully', {
                                id: notificationId
                            });
                        }
                    }
                });
            }

            // ========== MARK ALL AS READ ==========
            function markAllAsRead() {
                const token = localStorage.getItem('sanctum-token');
                if (!token) return;

                debugLog('📧 Marking all notifications as read');

                $.ajax({
                    url: '/notifications/read-all',
                    method: 'POST',
                    headers: {
                        'Authorization': token,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function() {
                        debugLog('✅ All marked as read');
                        fetchNotifications(1, false);
                    }
                });
            }

            // ========== INITIALIZATION - RUNS IMMEDIATELY ==========
            (function initializeDropdown() {
                debugLog('🚀 Notification system initializing (immediate execution)...');

                const dropdownElement = document.getElementById('notificationDropdown');
                const dropdownBtn = document.getElementById('notificationDropdownBtn');

                if (!dropdownElement || !dropdownBtn) {
                    debugLog('⚠️ Dropdown elements not found yet, waiting for DOM...');
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', initializeDropdown);
                    } else {
                        setTimeout(initializeDropdown, 50);
                    }
                    return;
                }

                debugLog('✅ Dropdown elements found');

                // Method 1: Watch for class changes using MutationObserver (most reliable)
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.attributeName === 'class') {
                            const isOpen = dropdownElement.classList.contains('show');

                            if (isOpen && !isLoading && !hasLoadedOnce) {
                                debugLog(
                                    '🔔 DROPDOWN OPENED (MutationObserver detected class change)'
                                    );
                                startTimer('dropdownOpen');
                                hasLoadedOnce = false;
                                currentPage = 1;
                                fetchNotifications(1, false);
                                setupInfiniteScroll();
                            }
                        }
                    });
                });

                observer.observe(dropdownElement, {
                    attributes: true,
                    attributeFilter: ['class']
                });

                debugLog('✅ MutationObserver attached');

                // Method 2: Bootstrap dropdown events (backup)
                dropdownElement.addEventListener('show.bs.dropdown', function(e) {
                    debugLog('🔔 show.bs.dropdown event fired (Bootstrap event)');
                    if (!isLoading) {
                        hasLoadedOnce = false;
                        currentPage = 1;
                        fetchNotifications(1, false);
                    }
                });

                dropdownElement.addEventListener('shown.bs.dropdown', function() {
                    debugLog('🔔 shown.bs.dropdown event fired');
                    setupInfiniteScroll();
                });

                debugLog('✅ Bootstrap dropdown events attached');

                // Method 3: Direct button click listener (triple backup)
                dropdownBtn.addEventListener('click', function(e) {
                    debugLog('🖱️ BELL BUTTON CLICKED');

                    // Check if dropdown is currently closed
                    const isCurrentlyClosed = !dropdownElement.classList.contains('show');

                    if (isCurrentlyClosed) {
                        debugLog('🔔 Dropdown is closed, will open - preloading data');

                        // Give Bootstrap 10ms to start opening the dropdown
                        setTimeout(function() {
                            if (!isLoading && !hasLoadedOnce) {
                                debugLog('🔔 Triggering fetch from button click');
                                hasLoadedOnce = false;
                                currentPage = 1;
                                fetchNotifications(1, false);
                            }
                        }, 10);
                    }
                });

                debugLog('✅ Button click listener attached');
                debugLog('✅ All initialization complete - triple redundancy active');
            })();

            // ========== MARK ALL READ BUTTON ==========
            $(document).ready(function() {
                $('#markAllReadBtn').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    debugLog('🖱️ Mark all as read button clicked');
                    markAllAsRead();
                });
            });

            // ========== INITIAL BADGE LOAD ==========
            const token = localStorage.getItem('sanctum-token');
            if (token) {
                debugLog('🔔 Loading initial badge count');

                $.ajax({
                    url: '/notifications?per_page=1&unread_only=true',
                    method: 'GET',
                    headers: {
                        'Authorization': token,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        if (response.status) {
                            unreadCount = response.unread_count || 0;
                            updateNotificationBadge();
                            debugLog('✅ Initial badge loaded', {
                                count: unreadCount
                            });
                        }
                    }
                });
            }

            // ========== AUTO REFRESH ==========
            setInterval(function() {
                const dropdown = document.getElementById('notificationDropdown');
                if (!dropdown || !dropdown.classList.contains('show')) {
                    // Dropdown is closed - just update badge
                    const token = localStorage.getItem('sanctum-token');
                    if (token) {
                        $.ajax({
                            url: '/notifications?per_page=1&unread_only=true',
                            method: 'GET',
                            headers: {
                                'Authorization': token,
                                'Accept': 'application/json'
                            },
                            success: function(response) {
                                if (response.status) {
                                    unreadCount = response.unread_count || 0;
                                    updateNotificationBadge();
                                }
                            }
                        });
                    }
                } else {
                    // Dropdown is open - refresh full list
                    debugLog('🔄 Auto-refresh (dropdown is open)');
                    fetchNotifications(1, false);
                }
            }, 30000);

            debugLog('✅ All systems ready - notification system fully initialized');
        })();
    </script>


</body>

</html>
