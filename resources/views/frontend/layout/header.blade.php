    <header class="header">

        <!-- header-top -->
        <div class="header-top">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-7">
                        <div class="header-top-left">
                            <div class="top-social">
                                <a href="#"><i class="fab fa-facebook-f"></i></a>
                                <a href="#"><i class="fab fa-x-twitter"></i></a>
                                <a href="#"><i class="fab fa-instagram"></i></a>
                                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            </div>
                            <div class="top-contact-info">
                                <ul>
                                    <li><a href="tel:+21234567897"><i class="far fa-phone-arrow-down-left"></i>+2 123
                                            4567 897</a></li>
                                    <li><a href="mailto:info@wisetrust.com"><i
                                                class="far fa-envelopes"></i><span>info@wisetrust.com</span></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="header-top-right">
                            <div class="lang">
                                <select name="lang" class="select">
                                    <option value="1">ENG</option>
                                    <option value="2">RUS</option>
                                    <option value="3">ROM</option>
                                    <option value="4">FRA</option>
                                    <option value="5">ESP</option>
                                    <option value="6">POR</option>
                                </select>
                            </div>
                            <div class="currency">
                                <select name="currency" class="select">
                                    <option value="1">USD</option>
                                    <option value="2">EUR</option>
                                    <option value="3">AUD</option>
                                    <option value="4">BRL</option>
                                    <option value="5">CAD</option>
                                    <option value="6">MXN</option>
                                </select>
                            </div>
                            <div class="account">
                                <a href="{{ route('login.form') }}"><i class="far fa-sign-in"></i>Login</a>
                                <a href="{{ route('register.form') }}"><i class="far fa-user-tie"></i>Sign Up</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- header-top end -->

        <!-- navbar -->
        <div class="main-navigation">
            <nav class="navbar navbar-expand-lg">
                <div class="container">
                    <a class="navbar-brand" href="{{ route('home') }}">
                        <img src="{{ asset('assets/img/logo/logo.png') }}" class="logo-display" alt="logo">
                        <img src="{{ asset('assets/img/logo/logo-dark.png') }}" class="logo-scrolled" alt="logo">
                    </a>
                    <div class="mobile-menu-right">
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                            data-bs-target="#main_nav" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-btn-icon"><i class="far fa-bars"></i></span>
                        </button>
                    </div>
                    <div class="collapse navbar-collapse" id="main_nav">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('pages.flights') || request()->routeIs('frontend.flights.*') ? 'active' : '' }}" href="{{ route('pages.flights') }}">Flight</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('pages.hotels') ? 'active' : '' }}" href="{{ route('pages.hotels') }}">Hotel</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('pages.about') ? 'active' : '' }}" href="{{ route('pages.about') }}">About Us</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('pages.activities') ? 'active' : '' }}" href="{{ route('pages.activities') }}">Activity</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('blogs.*') ? 'active' : '' }}" href="{{ route('blogs.index') }}">Blog</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('pages.contact') ? 'active' : '' }}" href="{{ route('pages.contact') }}">Contact</a>
                            </li>
                        </ul>
                        <div class="header-nav-right">
                            <div class="header-btn">
                                <a href="{{ route('pages.become-expert') }}" class="theme-btn mt-2">Become An Expert</a>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
        </div>
        <!-- navbar end -->

    </header>
