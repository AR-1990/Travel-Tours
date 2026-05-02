<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $blog->meta_description ?: $blog->excerpt }}">
    <meta name="keywords" content="travel blog, tours, travel guide">
    <title>{{ $blog->meta_title ?: $blog->title }} | Travel Tours</title>

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
                <h2 class="breadcrumb-title">{{ $blog->title }}</h2>
                <ul class="breadcrumb-menu">
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li><a href="{{ route('blogs.index') }}">Blog</a></li>
                    <li class="active">Blog Detail</li>
                </ul>
            </div>
        </div>

        <div class="blog-single-area pt-120 pb-120">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="blog-single-wrapper">
                            <div class="blog-single-content">
                                <div class="blog-thumb-img">
                                    <img src="{{ $blog->image_url }}" alt="{{ $blog->title }}">
                                </div>
                                <div class="blog-info">
                                    <div class="blog-meta">
                                        <div class="blog-meta-left">
                                            <ul>
                                                <li><i class="far fa-user"></i><a href="{{ route('blogs.index') }}">Travel Tours Team</a></li>
                                                <li><i class="far fa-calendar-alt"></i>{{ optional($blog->created_at)->format('F d, Y') }}</li>
                                                <li><i class="far fa-folder-open"></i>Travel Insights</li>
                                            </ul>
                                        </div>
                                        <div class="blog-meta-right">
                                            <a href="{{ route('blogs.index') }}" class="share-link"><i class="far fa-arrow-left"></i>Back To Blog</a>
                                        </div>
                                    </div>

                                    <div class="blog-details">
                                        <h3 class="blog-details-title mb-20">{{ $blog->title }}</h3>
                                        {!! $blog->description !!}
                                        <hr>
                                        <div class="blog-details-tags pb-20">
                                            <h5>Highlights :</h5>
                                            <ul>
                                                <li><a href="{{ route('blogs.index') }}">Travel Planning</a></li>
                                                <li><a href="{{ route('blogs.index') }}">Destination Ideas</a></li>
                                                <li><a href="{{ route('blogs.index') }}">Booking Tips</a></li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="blog-author">
                                        <div class="author-info">
                                            <h6>Author</h6>
                                            <h3 class="author-name">Travel Tours Editorial Team</h3>
                                            <p>We create practical travel content that helps customers discover destinations, compare ideas, and book with more confidence.</p>
                                            <div class="author-social">
                                                <a href="#"><i class="fab fa-facebook-f"></i></a>
                                                <a href="#"><i class="fab fa-x-twitter"></i></a>
                                                <a href="#"><i class="fab fa-instagram"></i></a>
                                                <a href="#"><i class="fab fa-whatsapp"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <aside class="sidebar">
                            <div class="widget">
                                <h5 class="widget-title">About This Article</h5>
                                <p>{{ $blog->excerpt }}</p>
                                <a href="{{ route('blogs.index') }}" class="theme-btn mt-3">View All Blogs <i class="fas fa-arrow-circle-right"></i></a>
                            </div>

                            <div class="widget category">
                                <h5 class="widget-title">Explore More</h5>
                                <div class="category-list">
                                    <a href="{{ url('/destination') }}"><i class="far fa-arrow-right"></i>Destination Ideas</a>
                                    <a href="{{ url('/tour-grid') }}"><i class="far fa-arrow-right"></i>Tour Packages</a>
                                    <a href="{{ url('/flight-list') }}"><i class="far fa-arrow-right"></i>Flight Booking</a>
                                    <a href="{{ url('/hotel-grid') }}"><i class="far fa-arrow-right"></i>Hotel Stays</a>
                                    <a href="{{ url('/contact') }}"><i class="far fa-arrow-right"></i>Talk To An Expert</a>
                                </div>
                            </div>

                            @if ($recentBlogs->isNotEmpty())
                                <div class="widget recent-post">
                                    <h5 class="widget-title">Recent Post</h5>
                                    @foreach ($recentBlogs as $recentBlog)
                                        <div class="recent-post-single">
                                            <div class="recent-post-img">
                                                <a href="{{ route('blogs.show', $recentBlog->slug) }}">
                                                    <img src="{{ $recentBlog->image_url }}" alt="{{ $recentBlog->title }}">
                                                </a>
                                            </div>
                                            <div class="recent-post-bio">
                                                <h6><a href="{{ route('blogs.show', $recentBlog->slug) }}">{{ $recentBlog->title }}</a></h6>
                                                <span><i class="far fa-clock"></i>{{ optional($recentBlog->created_at)->format('d F, Y') }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="widget sidebar-tag">
                                <h5 class="widget-title">Popular Tags</h5>
                                <div class="tag-list">
                                    <a href="{{ route('blogs.index') }}">Travel</a>
                                    <a href="{{ route('blogs.index') }}">Tours</a>
                                    <a href="{{ route('blogs.index') }}">Flights</a>
                                    <a href="{{ route('blogs.index') }}">Hotels</a>
                                    <a href="{{ route('blogs.index') }}">Packages</a>
                                    <a href="{{ route('blogs.index') }}">Guides</a>
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>
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
