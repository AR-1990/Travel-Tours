<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Explore the latest travel insights, destination ideas, and booking tips from Travel Tours.">
    <meta name="keywords" content="travel blog, tours, destinations, travel tips">
    <title>Travel Tours | Our Blog</title>

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
        <div class="site-breadcrumb" style="background: url('{{ asset('assets/img/breadcrumb/01.jpg') }}')">
            <div class="container">
                <h2 class="breadcrumb-title">Our Blog</h2>
                <ul class="breadcrumb-menu">
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li class="active">Our Blog</li>
                </ul>
            </div>
        </div>

        <div class="blog-area py-120">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 mx-auto wow fadeInDown" data-wow-duration="1s" data-wow-delay=".25s">
                        <div class="site-heading text-center">
                            <span class="site-title-tagline"><i class="far fa-plane"></i> Our Blog</span>
                            <h2 class="site-title">Travel Stories, Tips And Booking Insights</h2>
                            <p>Fresh destination ideas, travel planning advice, and curated articles powered directly from your blog database.</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    @forelse ($blogs as $blog)
                        <div class="col-md-6 col-lg-4">
                            <div class="blog-item wow fadeInUp" data-wow-duration="1s" data-wow-delay=".{{ (($loop->index % 3) + 1) * 25 }}s">
                                <span class="blog-date">{{ optional($blog->created_at)->format('F d, Y') }}</span>
                                <div class="blog-item-img">
                                    <a href="{{ route('blogs.show', $blog->slug) }}">
                                        <img src="{{ $blog->image_url }}" alt="{{ $blog->title }}">
                                    </a>
                                </div>
                                <div class="blog-item-info">
                                    <div class="blog-item-meta">
                                        <ul>
                                            <li><a href="{{ route('blogs.show', $blog->slug) }}"><i class="far fa-user-circle"></i> By Travel Tours Team</a></li>
                                            <li><a href="{{ route('blogs.show', $blog->slug) }}"><i class="far fa-folder-open"></i> Travel Insights</a></li>
                                        </ul>
                                    </div>
                                    <h4 class="blog-title">
                                        <a href="{{ route('blogs.show', $blog->slug) }}">{{ $blog->title }}</a>
                                    </h4>
                                    <p>{{ $blog->excerpt }}</p>
                                    <a class="theme-btn mt-3" href="{{ route('blogs.show', $blog->slug) }}">
                                        Read More <i class="fas fa-arrow-circle-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-info text-center">No blogs are available right now. Seed or create blogs to show them here.</div>
                        </div>
                    @endforelse
                </div>

                @if ($blogs->hasPages())
                    <div class="pagination-area">
                        {{ $blogs->links('vendor.pagination.bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
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
</body>
</html>
