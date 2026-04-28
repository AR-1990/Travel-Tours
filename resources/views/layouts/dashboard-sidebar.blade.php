<!-- resources/views/partials/dashboard-sidebar.blade.php -->

<style>
    .sidebar {
        width: 16% !important;
        background-color: #F4F5FB !important;
        border-right: 1px solid #E9EBF0 !important;
        height: -webkit-fill-available !important;
        overflow: visible;
    }

    div#dashboardMenu {
        position: relative;
    }

    div#dashboardMenu .dashboardMenuCollapseBtn {
        position: absolute;
        top: 52px;
        right: -13px;
        outline: none;
        border: none;
        background: #00000000;
        rotate: 0deg;
        padding: 0;
        border-radius: 50%;
    }

    #dashboardSidebar.collapsed .dashboardMenuCollapseBtn {
        rotate: 180deg;
    }

    body {
        overflow-y: unset !important;
        background: #fafbff;
    }

    .header {
        position: fixed !important;
        width: 100%;
    }

    .navbar_d_flex {
        display: flex;
        height: 100vh;
        align-items: unset;
    }

    #dashboardSidebar {
        display: flex;
        width: 100% !important;
        max-width: 277px;
        transition: width 0.3s ease;
        flex-direction: column;
        justify-content: space-between;
        position: fixed;
        left: 0;
        top: 0;
        height: 100% !important;
        z-index: 2;
        margin-top: 109px;
    }

    .main-content {
        width: 100% !important;
        max-width: calc(100% - 277px) !important;
        height: max-content !important;
        overflow-x: hidden !important;
        overflow-y: hidden !important;
        margin-left: auto;
        flex: unset !important;
        margin-top: 109px;
    }

    #dashboardSidebar.collapsed~.main-content {
        width: 100% !important;
        max-width: calc(100% - 90px) !important;
    }

    #dashboardSidebar.collapsed {
        max-width: 90px;
        width: 100% !important;
    }

    #dashboardSidebar.collapsed .menu-text {
        display: inline;
        transition: display 0.3s ease;
    }

    #dashboardSidebar.collapsed .menu-text {
        display: none;
    }

    #dashboardSidebar.collapsed .nav-list .nav-items a {
        padding: 12px 12px;
    }



    .nav-items a {
        transition: all 0.3s ease;
    }

    .appSection {
        padding: 20px;
    }

    .appSection .appSecInner {
        background: #b8c034;
        padding: 30px 20px;
        border-radius: 10px;
        font-family: "Inter";
        color: #333;
    }

    .appSection .appSecInner h3 {
        font-size: 26px;
        font-weight: 600;
    }

    .appSection .appSecInner button {
        width: 100%;
        border: none;
        outline: none;
        border-radius: 8px;
    }

    .appSection .appSecInner button a {
        text-decoration: none;
        color: #333;
        font-size: 18px;
        font-weight: 500;
        line-height: 55px;
    }

    @media (max-width: 1400px) {
        #dashboardSidebar {
            width: 100% !important;
            max-width: 277px;
        }

        body {
            overflow: unset !important;
        }
    }

    @media (max-width: 1080px) {
        #dashboardSidebar {
            left: -100%;
            width: 50% !important;
            transition: left 0.3s ease;
        }

        #dashboardSidebar.open {
            left: 0%;
            transition: left 0.3s ease;
        }

        body {
            overflow: auto !important;
        }

        .main-content {
            width: 100% !important;
            max-width: 100% !important;
            height: 100%;
            padding: 50px 14px 20px 14px !important;
        }

        div#dashboardMenu .dashboardMenuCollapseBtn {
            display: none;
        }

        #dashboardSidebar.collapsed~.main-content {
            width: 100% !important;
            max-width: 100% !important;
        }

    }

    @media (max-width: 768px) {
        #dashboardSidebar {
            width: 80% !important;
        }

        #dashboardSidebar.collapsed {
            width: 14.7% !important;
        }
    }
</style>

<div class="sidebar" id="dashboardSidebar">
    <div id="dashboardMenu">
        <button class="dashboardMenuCollapseBtn"><img src="{{ asset('assets/images/dashboard/sidebarCollapseIcon.svg') }}"
                alt=""></button>
        <ul class="nav-list">
            <li class="nav-items">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">

                    <img src="{{ asset('assets/images/dashboard/sidebarDashboardIcon.svg') }}" alt="">
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>
            <li class="nav-items">
                <a href="{{ route('profile') }}"
                    class="{{ request()->routeIs('profile') ? 'active' : '' }}">

                    <img src="{{ asset('assets/images/dashboard/sidebarMyProfileIcon.svg') }}" alt="">
                    <!-- My Profile -->
                    <span class="menu-text">My Profile</span>
                </a>
            </li>
            <li class="nav-items">
                <a href="{{ route('settings') }}"
                    class="{{ request()->routeIs('settings') ? 'active' : '' }}">

                    <img src="{{ asset('assets/images/dashboard/sidebarProductIcon.svg') }}" alt="">

                    <span class="menu-text">Settings</span>
                </a>
            </li>


        </ul>
    </div>

</div>

<script>
    document.querySelector('.dashboardMenuCollapseBtn')
        .addEventListener('click', function() {
            document.getElementById('dashboardSidebar')
                .classList.toggle('collapsed');
        });
</script>
