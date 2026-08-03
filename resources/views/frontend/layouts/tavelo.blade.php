<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <title>@yield('title', 'Tavelo - Travel Booking')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/logo/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/all-fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/animate.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/magnific-popup.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/nice-select.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/jquery.timepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/home-flights.css') }}?v={{ file_exists(public_path('assets/css/home-flights.css')) ? filemtime(public_path('assets/css/home-flights.css')) : time() }}">
    <link rel="stylesheet" href="{{ asset('assets/css/site-pages.css') }}?v={{ file_exists(public_path('assets/css/site-pages.css')) ? filemtime(public_path('assets/css/site-pages.css')) : time() }}">
    @stack('styles')
</head>
<body>

    <div class="preloader">
        <div class="loader">
            @for ($i = 1; $i <= 20; $i++)
                <span style="--i:{{ $i }};"></span>
            @endfor
            <div class="loader-plane"></div>
        </div>
    </div>

    @include('frontend.layout.header')

    <main class="main">
        @yield('content')
    </main>

    @include('frontend.layout.footer')

    <a href="#" id="scroll-top"><i class="far fa-angle-up"></i></a>

    <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/modernizr.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/imagesloaded.pkgd.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('assets/js/isotope.pkgd.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.appear.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('assets/js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('assets/js/counter-up.js') }}"></script>
    <script src="{{ asset('assets/js/masonry.pkgd.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.nice-select.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.timepicker.min.js') }}"></script>
    <script src="{{ asset('assets/js/wow.min.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="{{ asset('js/airport-picker.js') }}?v={{ file_exists(public_path('js/airport-picker.js')) ? filemtime(public_path('js/airport-picker.js')) : time() }}"></script>
    <script src="{{ asset('js/flight-multicity.js') }}?v={{ file_exists(public_path('js/flight-multicity.js')) ? filemtime(public_path('js/flight-multicity.js')) : time() }}"></script>
    <script>
    (function () {
        window.initHomeFlightSearch?.();
    })();
    </script>
    @stack('scripts')
</body>
</html>
