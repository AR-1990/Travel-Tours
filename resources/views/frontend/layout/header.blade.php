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
                                    <li><a href="https://live.themewild.com/cdn-cgi/l/email-protection#462f28202906233e272b362a236825292b"><i
                                                class="far fa-envelopes"></i><span class="__cf_email__" data-cfemail="ea83848c85aa8f928b879a868fc4898587">[email&#160;protected]</span></a></li>
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
                            <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Home</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/flight-list') }}">Flight</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/hotel-grid') }}">Hotel</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/about') }}">About Us</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/activity-grid') }}">Activity</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('blogs.index') }}">Blog</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ url('/contact') }}">Contact</a></li>
                        </ul>
                        <div class="header-nav-right">
                            <div class="header-btn">
                                <a href="{{ url('/become-expert') }}" class="theme-btn mt-2">Become An Expert</a>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
        </div>
        <!-- navbar end -->

    </header>
