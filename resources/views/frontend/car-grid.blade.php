<html lang="en"
    class=" js flexbox flexboxlegacy canvas canvastext webgl no-touch geolocation postmessage no-websqldatabase indexeddb hashchange history draganddrop websockets rgba hsla multiplebgs backgroundsize borderimage borderradius boxshadow textshadow opacity cssanimations csscolumns cssgradients cssreflections csstransforms csstransforms3d csstransitions fontface generatedcontent video audio localstorage sessionstorage webworkers no-applicationcache svg inlinesvg smil svgclippaths">

<head>
    <style>
        body {
            transition: opacity ease-in 0.2s;
        }

        body[unresolved] {
            opacity: 0;
            display: block;
            overflow: hidden;
            position: relative;
        }
    </style>
    <!-- meta tags -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="keywords" content="">

    <!-- title -->
    <title>Tavelo - Travel Booking HTML5 Template</title>

    <!-- favicon -->
    <link rel="icon" type="image/x-icon" href="assets/img/logo/favicon.png">

    <!-- css -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/all-fontawesome.min.css">
    <link rel="stylesheet" href="assets/css/animate.min.css">
    <link rel="stylesheet" href="assets/css/magnific-popup.min.css">
    <link rel="stylesheet" href="assets/css/owl.carousel.min.css">
    <link rel="stylesheet" href="assets/css/nice-select.min.css">
    <link rel="stylesheet" href="assets/css/jquery-ui.min.css">
    <link rel="stylesheet" href="assets/css/jquery.timepicker.min.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        .imageye-selected {
            outline: 2px solid black !important;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5) !important;
        }
    </style>
</head>

<body>

    <!-- preloader -->
    <div class="preloader" style="display: none;">
        <div class="loader">
            <span style="--i:1;"></span>
            <span style="--i:2;"></span>
            <span style="--i:3;"></span>
            <span style="--i:4;"></span>
            <span style="--i:5;"></span>
            <span style="--i:6;"></span>
            <span style="--i:7;"></span>
            <span style="--i:8;"></span>
            <span style="--i:9;"></span>
            <span style="--i:10;"></span>
            <span style="--i:11;"></span>
            <span style="--i:12;"></span>
            <span style="--i:13;"></span>
            <span style="--i:14;"></span>
            <span style="--i:15;"></span>
            <span style="--i:16;"></span>
            <span style="--i:17;"></span>
            <span style="--i:18;"></span>
            <span style="--i:19;"></span>
            <span style="--i:20;"></span>
            <div class="loader-plane"></div>
        </div>
    </div>
    <!-- preloader end -->


    <!-- header area -->
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
                                <a href="login.html"><i class="far fa-sign-in"></i>Login</a>
                                <a href="register.html"><i class="far fa-user-tie"></i>Sign Up</a>
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
                    <a class="navbar-brand" href="index-2.html">
                        <img src="assets/img/logo/logo.png" class="logo-display" alt="logo">
                        <img src="assets/img/logo/logo-dark.png" class="logo-scrolled" alt="logo">
                    </a>
                    <div class="mobile-menu-right">
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                            data-bs-target="#main_nav" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-btn-icon"><i class="far fa-bars"></i></span>
                        </button>
                    </div>
                    <div class="collapse navbar-collapse" id="main_nav">
                        <ul class="navbar-nav">
                            <!-- <li class="nav-item ">
                                <a class="nav-link" href="index.html" data-bs-toggle="dropdown">Home</a>
                                <ul class="dropdown-menu fade-down">
                                    <li><a class="dropdown-item" href="index-2.html">Home One</a></li>
                                    <li><a class="dropdown-item" href="index-3.html">Home Two</a></li>
                                    <li><a class="dropdown-item" href="index-4.html">Home Three</a></li>
                                    <li><a class="dropdown-item" href="index-5.html">Home Flight</a></li>
                                    <li><a class="dropdown-item" href="index-6.html">Home Hotel</a></li>
                                    <li><a class="dropdown-item" href="index-7.html">Home Activity</a></li>
                                    <li><a class="dropdown-item" href="index-8.html">Home Car</a></li>
                                    <li><a class="dropdown-item" href="index-9.html">Home Cruise</a></li>
                                    <li><a class="dropdown-item" href="index-10.html">Home Tour</a></li>
                                </ul>
                            </li> -->
                            <!-- <li class="nav-item dropdown">
                                <a class="nav-link " href="flight-list.html" data-bs-toggle="dropdown">Flight</a>
                                <ul class="dropdown-menu fade-down">
                                    <li><a class="dropdown-item" href="flight-grid.html">Flight Grid</a></li>
                                    <li><a class="dropdown-item" href="flight-list.html">Flight List</a></li>
                                    <li><a class="dropdown-item" href="flight-full-width.html">Flight Full Width</a>
                                    </li>
                                    <li><a class="dropdown-item" href="flight-single.html">Flight Details</a></li>
                                    <li><a class="dropdown-item" href="flight-booking.html">Flight Booking</a></li>
                                    <li><a class="dropdown-item" href="flight-search-result.html">Flight Search
                                            Result</a></li>
                                    <li><a class="dropdown-item" href="flight-add.html">Add Flight</a></li>
                                </ul>
                            </li> -->
                            <li class="nav-item"><a class="nav-link" href="index.html">Home</a></li>
                            <li class="nav-item"><a class="nav-link" href="flight-list.html">Flight</a></li>
                            <li class="nav-item"><a class="nav-link" href="hotel-grid.html">Hotel</a></li>
                            <li class="nav-item"><a class="nav-link" href="About.html">About Us</a></li>
                            <li class="nav-item"><a class="nav-link" href="activity-grid.html">Activity</a></li>
                            <!-- <li class="nav-item dropdown">
                                <a class="nav-link" href="hotel-grid.html" data-bs-toggle="dropdown">Hotel</a>
                                <ul class="dropdown-menu fade-down">
                                    <li><a class="dropdown-item" href="hotel-grid.html">Hotel Grid</a></li>
                                    <li><a class="dropdown-item" href="hotel-list.html">Hotel List</a></li>
                                    <li><a class="dropdown-item" href="hotel-full-width.html">Hotel Full Width</a></li>
                                    <li class="dropdown-submenu">
                                        <a class="dropdown-item dropdown-toggle" href="#">Hotel Room</a>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="hotel-room-grid.html">Room Grid</a></li>
                                            <li><a class="dropdown-item" href="hotel-room-list.html">Room List</a></li>
                                            <li><a class="dropdown-item" href="hotel-room-full-width.html">Room Full
                                                    Width</a></li>
                                            <li><a class="dropdown-item" href="hotel-room-single.html">Room Details</a>
                                            </li>
                                            <li><a class="dropdown-item" href="hotel-room-search-result.html">Room
                                                    Search Result</a></li>
                                            <li><a class="dropdown-item" href="hotel-room-add.html">Add Room</a></li>
                                        </ul>
                                    </li>
                                    <li><a class="dropdown-item" href="hotel-single.html">Hotel Details</a></li>
                                    <li><a class="dropdown-item" href="hotel-booking.html">Hotel Booking</a></li>
                                    <li><a class="dropdown-item" href="hotel-search-result.html">Hotel Search Result</a>
                                    </li>
                                    <li><a class="dropdown-item" href="hotel-add.html">Add Hotel</a></li>
                                </ul>
                            </li> -->
                            <!-- <li class="nav-item ">
                                <a class="nav-link " href="about.html" data-bs-toggle="dropdown">About Us</a>
                                <div class="dropdown-menu mega-menu fade-down">
                                    <div class="row">
                                        <div class="col-lg-3">
                                            <ul>
                                                <li><a class="dropdown-item" href="destination.html">Destination</a>
                                                </li>
                                                <li><a class="dropdown-item" href="career.html">Career</a></li>
                                                <li><a class="dropdown-item" href="career-single.html">Career
                                                        Details</a></li>
                                                <li><a class="dropdown-item" href="become-expert.html">Become An
                                                        Expert</a></li>
                                                <li><a class="dropdown-item" href="cart.html">My Cart</a></li>
                                                <li><a class="dropdown-item" href="checkout.html">Checkout</a></li>
                                                <li><a class="dropdown-item" href="booking-confirm.html">Booking
                                                        Confirm</a></li>
                                                <li><a class="dropdown-item" href="login.html">Login</a></li>
                                                <li><a class="dropdown-item" href="register.html">Sign Up</a></li>
                                            </ul>
                                        </div>
                                        <div class="col-lg-3">
                                            <ul>
                                                <li><a class="dropdown-item" href="forgot-password.html">Forgot
                                                        Password</a></li>
                                                <li><a class="dropdown-item" href="dashboard.html">Dashboard</a></li>
                                                <li><a class="dropdown-item" href="profile.html">My Profile</a></li>
                                                <li><a class="dropdown-item" href="profile-listing.html">My Listing</a>
                                                </li>
                                                <li><a class="dropdown-item" href="profile-booking.html">My Booking</a>
                                                </li>
                                                <li><a class="dropdown-item" href="profile-booking-history.html">My
                                                        Booking History</a></li>
                                                <li><a class="dropdown-item" href="profile-wishlist.html">My
                                                        Wishlist</a></li>
                                                <li><a class="dropdown-item" href="profile-wallet.html">My Wallet</a>
                                                </li>
                                                <li><a class="dropdown-item" href="profile-message.html">Messages</a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="col-lg-3">
                                            <ul>
                                                <li><a class="dropdown-item"
                                                        href="profile-notification.html">Notifications</a></li>
                                                <li><a class="dropdown-item" href="profile-setting.html">Settings</a>
                                                </li>
                                                <li><a class="dropdown-item" href="about.html">About Us</a></li>
                                                <li><a class="dropdown-item" href="pricing.html">Pricing Plan</a></li>
                                                <li><a class="dropdown-item" href="team.html">Our Team</a></li>
                                                <li><a class="dropdown-item" href="service.html">Services</a></li>
                                                <li><a class="dropdown-item" href="service-single.html">Service
                                                        Single</a></li>
                                                <li><a class="dropdown-item" href="gallery.html">Gallery</a></li>
                                                <li><a class="dropdown-item" href="contact.html">Contact Us</a></li>
                                            </ul>
                                        </div>
                                        <div class="col-lg-3">
                                            <ul>
                                                <li><a class="dropdown-item" href="blog.html">Blog</a></li>
                                                <li><a class="dropdown-item" href="blog-single.html">Blog Single</a>
                                                </li>
                                                <li><a class="dropdown-item" href="faq.html">Faq's</a></li>
                                                <li><a class="dropdown-item" href="testimonial.html">Testimonials</a>
                                                </li>
                                                <li><a class="dropdown-item" href="404.html">404 Error</a></li>
                                                <li><a class="dropdown-item" href="coming-soon.html">Coming Soon</a>
                                                </li>
                                                <li><a class="dropdown-item" href="terms.html">Terms Of Service</a></li>
                                                <li><a class="dropdown-item" href="privacy.html">Privacy Policy</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </li> -->
                            <!-- <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Activity</a>
                                <ul class="dropdown-menu fade-down">
                                    <li><a class="dropdown-item" href="activity-grid.html">Activity Grid</a></li>
                                    <li><a class="dropdown-item" href="activity-list.html">Activity List</a></li>
                                    <li><a class="dropdown-item" href="activity-full-width.html">Activity Full Width</a>
                                    </li>
                                    <li><a class="dropdown-item" href="activity-single.html">Activity Details</a></li>
                                    <li><a class="dropdown-item" href="activity-booking.html">Activity Booking</a></li>
                                    <li><a class="dropdown-item" href="activity-search-result.html">Activity Search
                                            Result</a></li>
                                    <li><a class="dropdown-item" href="activity-add.html">Add Activity</a></li>
                                </ul>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Car</a>
                                <ul class="dropdown-menu fade-down">
                                    <li><a class="dropdown-item" href="car-grid.html">Car Grid</a></li>
                                    <li><a class="dropdown-item" href="car-list.html">Car List</a></li>
                                    <li><a class="dropdown-item" href="car-full-width.html">Car Full Width</a></li>
                                    <li><a class="dropdown-item" href="car-single.html">Car Details</a></li>
                                    <li><a class="dropdown-item" href="car-booking.html">Car Booking</a></li>
                                    <li><a class="dropdown-item" href="car-search-result.html">Car Search Result</a>
                                    </li>
                                    <li><a class="dropdown-item" href="car-add.html">Add Car</a></li>
                                </ul>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Cruise</a>
                                <ul class="dropdown-menu fade-down">
                                    <li><a class="dropdown-item" href="cruise-grid.html">Cruise Grid</a></li>
                                    <li><a class="dropdown-item" href="cruise-list.html">Cruise List</a></li>
                                    <li><a class="dropdown-item" href="cruise-full-width.html">Cruise Full Width</a>
                                    </li>
                                    <li><a class="dropdown-item" href="cruise-single.html">Cruise Details</a></li>
                                    <li><a class="dropdown-item" href="cruise-booking.html">Cruise Booking</a></li>
                                    <li><a class="dropdown-item" href="cruise-search-result.html">Cruise Search
                                            Result</a></li>
                                    <li><a class="dropdown-item" href="cruise-add.html">Add Cruise</a></li>
                                </ul>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Tour</a>
                                <ul class="dropdown-menu fade-down">
                                    <li><a class="dropdown-item" href="tour-grid.html">Tour Grid</a></li>
                                    <li><a class="dropdown-item" href="tour-list.html">Tour List</a></li>
                                    <li><a class="dropdown-item" href="tour-full-width.html">Tour Full Width</a></li>
                                    <li><a class="dropdown-item" href="tour-single.html">Tour Details</a></li>
                                    <li><a class="dropdown-item" href="tour-booking.html">Tour Booking</a></li>
                                    <li><a class="dropdown-item" href="tour-search-result.html">Tour Search Result</a>
                                    </li>
                                    <li><a class="dropdown-item" href="tour-add.html">Add Tour</a></li>
                                </ul>
                            </li> -->
                            <li class="nav-item"><a class="nav-link" href="contact.html">Contact</a></li>
                        </ul>
                        <div class="header-nav-right">
                            <div class="header-btn">
                                <a href="become-expert.html" class="theme-btn mt-2">Become An Expert</a>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
        </div>
        <!-- navbar end -->

    </header>
    <!-- header area end -->



    <main class="main">

        <!-- breadcrumb -->
        <div class="site-breadcrumb" style="background: url(assets/img/breadcrumb/01.jpg)">
            <div class="container">
                <h2 class="breadcrumb-title">Car Grid</h2>
                <ul class="breadcrumb-menu">
                    <li><a href="index.html">Home</a></li>
                    <li class="active">Car Grid</li>
                </ul>
            </div>
        </div>
        <!-- breadcrumb end -->


        <!-- search area -->
        <div class="search-area search-common">
            <div class="container">
                <div class="search-wrapper">
                    <!-- car search -->
                    <div class="search-box car-search">
                        <div class="search-form">
                            <form action="#">
                                <div class="car-search-wrapper">
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label>Picking Up</label>
                                                <div class="form-group-icon">
                                                    <input type="text" name="picking-up" class="form-control"
                                                        value="New York, United States">
                                                    <i class="fal fa-location-dot"></i>
                                                </div>
                                                <p>City, Airport Or Address</p>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <div class="search-form-date">
                                                    <div class="search-form-journey">
                                                        <label>Pick Up date</label>
                                                        <div class="form-group-icon">
                                                            <input type="text" name="pickup-date"
                                                                class="form-control date-picker journey-date hasDatepicker"
                                                                id="dp1771625650003">
                                                            <i class="fal fa-calendar-days"></i>
                                                        </div>
                                                        <p class="journey-day-name">Sunday</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label>Pick Up Time</label>
                                                <div class="form-group-icon">
                                                    <input type="text" name="pick-up-time"
                                                        class="form-control time-picker" value="11:00 PM">
                                                    <i class="fal fa-clock"></i>
                                                </div>
                                                <p>Car Arrival Time</p>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group mt-lg-4">
                                                <label>Drop Off</label>
                                                <div class="form-group-icon">
                                                    <input type="text" name="picking-up" class="form-control"
                                                        value="New York, United States">
                                                    <i class="fal fa-location-dot"></i>
                                                </div>
                                                <p>City, Airport Or Address</p>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group mt-lg-4">
                                                <div class="search-form-date">
                                                    <div class="search-form-journey">
                                                        <label>Drop Off date</label>
                                                        <div class="form-group-icon">
                                                            <input type="text" name="pickup-date"
                                                                class="form-control date-picker journey-date hasDatepicker"
                                                                id="dp1771625650004">
                                                            <i class="fal fa-calendar-days"></i>
                                                        </div>
                                                        <p class="journey-day-name">Sunday</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group mt-lg-4">
                                                <label>Drop Off Time</label>
                                                <div class="form-group-icon">
                                                    <input type="text" name="pick-up-time"
                                                        class="form-control time-picker" value="11:00 PM">
                                                    <i class="fal fa-clock"></i>
                                                </div>
                                                <p>Car Drop Off Time</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="search-btn">
                                        <button type="submit" class="theme-btn"><span
                                                class="far fa-search"></span>Search Now</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- car search end -->
                </div>
            </div>
        </div>
        <!-- search area end -->


        <!-- car grid -->
        <div class="car-grid py-120">
            <div class="container">
                <div class="row">
                    <!-- car booking sidebar -->
                    <div class="col-lg-4 col-xl-3 mb-4">
                        <div class="booking-sidebar">
                            <div class="booking-item">
                                <h4 class="booking-title">Car Category</h4>
                                <div class="car-category">
                                    <div class="form-check">
                                        <input class="form-check-input" name="car-category" type="checkbox" value="1"
                                            id="car-category1">
                                        <label class="form-check-label" for="car-category1">
                                            Convertable <span>(20)</span>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" name="car-category" type="checkbox" value="2"
                                            id="car-category2">
                                        <label class="form-check-label" for="car-category2">
                                            Coupe <span>(15)</span>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" name="car-category" type="checkbox" value="3"
                                            id="car-category3">
                                        <label class="form-check-label" for="car-category3">
                                            Crossover <span>(18)</span>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" name="car-category" type="checkbox" value="4"
                                            id="car-category4">
                                        <label class="form-check-label" for="car-category4">
                                            Muv <span>(35)</span>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" name="car-category" type="checkbox" value="5"
                                            id="car-category5">
                                        <label class="form-check-label" for="car-category5">
                                            SUV <span>(12)</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="booking-item">
                                <h4 class="booking-title">Car Price</h4>
                                <div class="car-price">
                                    <div class="price-range-slider">
                                        <div class="price-range-info">
                                            <label for="priceRange1">Price:</label>
                                            <input type="text" class="priceRange" id="priceRange1" readonly="">
                                        </div>
                                        <div id="price-range1"
                                            class="price-range slider ui-slider ui-corner-all ui-slider-horizontal ui-widget ui-widget-content">
                                            <div class="ui-slider-range ui-corner-all ui-widget-header"
                                                style="left: 15%; width: 35%;"></div><span tabindex="0"
                                                class="ui-slider-handle ui-corner-all ui-state-default"
                                                style="left: 15%;"></span><span tabindex="0"
                                                class="ui-slider-handle ui-corner-all ui-state-default"
                                                style="left: 50%;"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="booking-item">
                                <h4 class="booking-title">Car Condition</h4>
                                <div class="car-condition">
                                    <div class="form-check">
                                        <input class="form-check-input" name="car-condition" type="checkbox" value="1"
                                            id="car-condition1">
                                        <label class="form-check-label" for="car-condition1">
                                            All <span>(20)</span>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" name="car-condition" type="checkbox" value="2"
                                            id="car-condition2">
                                        <label class="form-check-label" for="car-condition2">
                                            New <span>(15)</span>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" name="car-condition" type="checkbox" value="3"
                                            id="car-condition3">
                                        <label class="form-check-label" for="car-condition3">
                                            Used <span>(18)</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="booking-item">
                                <h4 class="booking-title">Listed By</h4>
                                <div class="car-listed">
                                    <div class="form-check">
                                        <input class="form-check-input" name="car-listed" type="checkbox" value="1"
                                            id="car-listed1">
                                        <label class="form-check-label" for="car-listed1">
                                            Dealer <span>(20)</span>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" name="car-listed" type="checkbox" value="2"
                                            id="car-listed2">
                                        <label class="form-check-label" for="car-listed2">
                                            Individual <span>(15)</span>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" name="car-listed" type="checkbox" value="3"
                                            id="car-listed3">
                                        <label class="form-check-label" for="car-listed3">
                                            Reseller <span>(18)</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="booking-item">
                                <h4 class="booking-title">Fuel Type</h4>
                                <div class="fuel-type">
                                    <div class="form-check">
                                        <input class="form-check-input" name="fuel-type" type="checkbox" value="1"
                                            id="fuel-type1">
                                        <label class="form-check-label" for="fuel-type1">
                                            Electric <span>(20)</span>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" name="fuel-type" type="checkbox" value="2"
                                            id="fuel-type2">
                                        <label class="form-check-label" for="fuel-type2">
                                            Diesel <span>(15)</span>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" name="fuel-type" type="checkbox" value="3"
                                            id="fuel-type3">
                                        <label class="form-check-label" for="fuel-type3">
                                            Hybrid <span>(18)</span>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" name="fuel-type" type="checkbox" value="4"
                                            id="fuel-type4">
                                        <label class="form-check-label" for="fuel-type4">
                                            Petrol <span>(18)</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="booking-item">
                                <h4 class="booking-title">Transmission</h4>
                                <div class="transmission">
                                    <div class="form-check">
                                        <input class="form-check-input" name="transmission" type="checkbox" value="1"
                                            id="transmission1">
                                        <label class="form-check-label" for="transmission1">
                                            Automatic <span>(20)</span>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" name="transmission" type="checkbox" value="2"
                                            id="transmission2">
                                        <label class="form-check-label" for="transmission2">
                                            Manual <span>(15)</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- car booking grid -->
                    <div class="col-lg-8 col-xl-9">
                        <div class="col-md-12">
                            <div class="booking-sort">
                                <h5>2,350 Results Found</h5>
                                <div class="booking-sort-list-grid">
                                    <a class="booking-sort-grid active" href="car-grid.html"><i
                                            class="far fa-grid-2"></i></a>
                                    <a class="booking-sort-list" href="car-list.html"><i class="far fa-list-ul"></i></a>
                                </div>
                                <div class="col-md-3 booking-sort-box">
                                    <select class="select" style="display: none;">
                                        <option value="1">Sort By Default</option>
                                        <option value="2">Sort By Popular</option>
                                        <option value="3">Sort By Low Price</option>
                                        <option value="4">Sort By High Price</option>
                                    </select>
                                    <div class="nice-select select" tabindex="0"><span class="current">Sort By
                                            Default</span>
                                        <ul class="list">
                                            <li data-value="1" class="option selected">Sort By Default</li>
                                            <li data-value="2" class="option">Sort By Popular</li>
                                            <li data-value="3" class="option">Sort By Low Price</li>
                                            <li data-value="4" class="option">Sort By High Price</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-lg-4">
                                <div class="car-item">
                                    <div class="car-img">
                                        <span class="badge">Featured</span>
                                        <img src="assets/img/car/01.jpg" alt="">
                                        <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                                    </div>
                                    <div class="car-content">
                                        <h4 class="car-title"><a href="#">Toyota Corolla Car</a></h4>
                                        <p><i class="far fa-location-dot"></i> 25/B Milford Road, New York</p>
                                        <div class="car-rate">
                                            <span class="badge"><i class="far fa-star"></i> 5.0</span>
                                            <span class="car-rate-type">Excellent</span>
                                            <span class="car-rate-review">(2.5k Reviews)</span>
                                        </div>
                                        <ul class="car-info-list">
                                            <li><i class="far fa-car"></i>Model: 2025</li>
                                            <li><i class="far fa-user-tie"></i>4 People</li>
                                            <li><i class="far fa-gas-pump"></i>Hybrid</li>
                                            <li><i class="far fa-steering-wheel"></i>Automatic</li>
                                        </ul>
                                        <div class="car-bottom">
                                            <div class="car-price">
                                                <span class="car-price-amount">$62 <span class="car-price-type">/Per
                                                        Day</span></span>
                                            </div>
                                            <div class="car-text-btn">
                                                <a href="#">See Details <i class="fas fa-arrow-right"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="car-item">
                                    <div class="car-img">
                                        <img src="assets/img/car/02.jpg" alt="">
                                        <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                                    </div>
                                    <div class="car-content">
                                        <h4 class="car-title"><a href="#">Toyota Corolla Car</a></h4>
                                        <p><i class="far fa-location-dot"></i> 25/B Milford Road, New York</p>
                                        <div class="car-rate">
                                            <span class="badge"><i class="far fa-star"></i> 5.0</span>
                                            <span class="car-rate-type">Excellent</span>
                                            <span class="car-rate-review">(2.5k Reviews)</span>
                                        </div>
                                        <ul class="car-info-list">
                                            <li><i class="far fa-car"></i>Model: 2025</li>
                                            <li><i class="far fa-user-tie"></i>4 People</li>
                                            <li><i class="far fa-gas-pump"></i>Hybrid</li>
                                            <li><i class="far fa-steering-wheel"></i>Automatic</li>
                                        </ul>
                                        <div class="car-bottom">
                                            <div class="car-price">
                                                <span class="car-price-amount">$45 <span class="car-price-type">/Per
                                                        Day</span></span>
                                            </div>
                                            <div class="car-text-btn">
                                                <a href="#">See Details <i class="fas fa-arrow-right"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="car-item">
                                    <div class="car-img">
                                        <span class="badge">Popular</span>
                                        <img src="assets/img/car/03.jpg" alt="">
                                        <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                                    </div>
                                    <div class="car-content">
                                        <h4 class="car-title"><a href="#">Toyota Corolla Car</a></h4>
                                        <p><i class="far fa-location-dot"></i> 25/B Milford Road, New York</p>
                                        <div class="car-rate">
                                            <span class="badge"><i class="far fa-star"></i> 5.0</span>
                                            <span class="car-rate-type">Excellent</span>
                                            <span class="car-rate-review">(2.5k Reviews)</span>
                                        </div>
                                        <ul class="car-info-list">
                                            <li><i class="far fa-car"></i>Model: 2025</li>
                                            <li><i class="far fa-user-tie"></i>4 People</li>
                                            <li><i class="far fa-gas-pump"></i>Hybrid</li>
                                            <li><i class="far fa-steering-wheel"></i>Automatic</li>
                                        </ul>
                                        <div class="car-bottom">
                                            <div class="car-price">
                                                <span class="car-price-amount">$45 <span class="car-price-type">/Per
                                                        Day</span></span>
                                            </div>
                                            <div class="car-text-btn">
                                                <a href="#">See Details <i class="fas fa-arrow-right"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="car-item">
                                    <div class="car-img">
                                        <img src="assets/img/car/04.jpg" alt="">
                                        <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                                    </div>
                                    <div class="car-content">
                                        <h4 class="car-title"><a href="#">Toyota Corolla Car</a></h4>
                                        <p><i class="far fa-location-dot"></i> 25/B Milford Road, New York</p>
                                        <div class="car-rate">
                                            <span class="badge"><i class="far fa-star"></i> 5.0</span>
                                            <span class="car-rate-type">Excellent</span>
                                            <span class="car-rate-review">(2.5k Reviews)</span>
                                        </div>
                                        <ul class="car-info-list">
                                            <li><i class="far fa-car"></i>Model: 2025</li>
                                            <li><i class="far fa-user-tie"></i>4 People</li>
                                            <li><i class="far fa-gas-pump"></i>Hybrid</li>
                                            <li><i class="far fa-steering-wheel"></i>Automatic</li>
                                        </ul>
                                        <div class="car-bottom">
                                            <div class="car-price">
                                                <span class="car-price-amount">$45 <span class="car-price-type">/Per
                                                        Day</span></span>
                                            </div>
                                            <div class="car-text-btn">
                                                <a href="#">See Details <i class="fas fa-arrow-right"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="car-item">
                                    <div class="car-img">
                                        <img src="assets/img/car/05.jpg" alt="">
                                        <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                                    </div>
                                    <div class="car-content">
                                        <h4 class="car-title"><a href="#">Toyota Corolla Car</a></h4>
                                        <p><i class="far fa-location-dot"></i> 25/B Milford Road, New York</p>
                                        <div class="car-rate">
                                            <span class="badge"><i class="far fa-star"></i> 5.0</span>
                                            <span class="car-rate-type">Excellent</span>
                                            <span class="car-rate-review">(2.5k Reviews)</span>
                                        </div>
                                        <ul class="car-info-list">
                                            <li><i class="far fa-car"></i>Model: 2025</li>
                                            <li><i class="far fa-user-tie"></i>4 People</li>
                                            <li><i class="far fa-gas-pump"></i>Hybrid</li>
                                            <li><i class="far fa-steering-wheel"></i>Automatic</li>
                                        </ul>
                                        <div class="car-bottom">
                                            <div class="car-price">
                                                <span class="car-price-amount">$45 <span class="car-price-type">/Per
                                                        Day</span></span>
                                            </div>
                                            <div class="car-text-btn">
                                                <a href="#">See Details <i class="fas fa-arrow-right"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="car-item">
                                    <div class="car-img">
                                        <img src="assets/img/car/06.jpg" alt="">
                                        <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                                    </div>
                                    <div class="car-content">
                                        <h4 class="car-title"><a href="#">Toyota Corolla Car</a></h4>
                                        <p><i class="far fa-location-dot"></i> 25/B Milford Road, New York</p>
                                        <div class="car-rate">
                                            <span class="badge"><i class="far fa-star"></i> 5.0</span>
                                            <span class="car-rate-type">Excellent</span>
                                            <span class="car-rate-review">(2.5k Reviews)</span>
                                        </div>
                                        <ul class="car-info-list">
                                            <li><i class="far fa-car"></i>Model: 2025</li>
                                            <li><i class="far fa-user-tie"></i>4 People</li>
                                            <li><i class="far fa-gas-pump"></i>Hybrid</li>
                                            <li><i class="far fa-steering-wheel"></i>Automatic</li>
                                        </ul>
                                        <div class="car-bottom">
                                            <div class="car-price">
                                                <span class="car-price-amount">$45 <span class="car-price-type">/Per
                                                        Day</span></span>
                                            </div>
                                            <div class="car-text-btn">
                                                <a href="#">See Details <i class="fas fa-arrow-right"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="car-item">
                                    <div class="car-img">
                                        <span class="badge badge-discount">25% Save</span>
                                        <img src="assets/img/car/07.jpg" alt="">
                                        <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                                    </div>
                                    <div class="car-content">
                                        <h4 class="car-title"><a href="#">Toyota Corolla Car</a></h4>
                                        <p><i class="far fa-location-dot"></i> 25/B Milford Road, New York</p>
                                        <div class="car-rate">
                                            <span class="badge"><i class="far fa-star"></i> 5.0</span>
                                            <span class="car-rate-type">Excellent</span>
                                            <span class="car-rate-review">(2.5k Reviews)</span>
                                        </div>
                                        <ul class="car-info-list">
                                            <li><i class="far fa-car"></i>Model: 2025</li>
                                            <li><i class="far fa-user-tie"></i>4 People</li>
                                            <li><i class="far fa-gas-pump"></i>Hybrid</li>
                                            <li><i class="far fa-steering-wheel"></i>Automatic</li>
                                        </ul>
                                        <div class="car-bottom">
                                            <div class="car-price">
                                                <span class="car-price-amount">$45 <span class="car-price-type">/Per
                                                        Day</span></span>
                                            </div>
                                            <div class="car-text-btn">
                                                <a href="#">See Details <i class="fas fa-arrow-right"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="car-item">
                                    <div class="car-img">
                                        <img src="assets/img/car/08.jpg" alt="">
                                        <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                                    </div>
                                    <div class="car-content">
                                        <h4 class="car-title"><a href="#">Toyota Corolla Car</a></h4>
                                        <p><i class="far fa-location-dot"></i> 25/B Milford Road, New York</p>
                                        <div class="car-rate">
                                            <span class="badge"><i class="far fa-star"></i> 5.0</span>
                                            <span class="car-rate-type">Excellent</span>
                                            <span class="car-rate-review">(2.5k Reviews)</span>
                                        </div>
                                        <ul class="car-info-list">
                                            <li><i class="far fa-car"></i>Model: 2025</li>
                                            <li><i class="far fa-user-tie"></i>4 People</li>
                                            <li><i class="far fa-gas-pump"></i>Hybrid</li>
                                            <li><i class="far fa-steering-wheel"></i>Automatic</li>
                                        </ul>
                                        <div class="car-bottom">
                                            <div class="car-price">
                                                <span class="car-price-amount">$45 <span class="car-price-type">/Per
                                                        Day</span></span>
                                            </div>
                                            <div class="car-text-btn">
                                                <a href="#">See Details <i class="fas fa-arrow-right"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="car-item">
                                    <div class="car-img">
                                        <img src="assets/img/car/02.jpg" alt="">
                                        <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                                    </div>
                                    <div class="car-content">
                                        <h4 class="car-title"><a href="#">Toyota Corolla Car</a></h4>
                                        <p><i class="far fa-location-dot"></i> 25/B Milford Road, New York</p>
                                        <div class="car-rate">
                                            <span class="badge"><i class="far fa-star"></i> 5.0</span>
                                            <span class="car-rate-type">Excellent</span>
                                            <span class="car-rate-review">(2.5k Reviews)</span>
                                        </div>
                                        <ul class="car-info-list">
                                            <li><i class="far fa-car"></i>Model: 2025</li>
                                            <li><i class="far fa-user-tie"></i>4 People</li>
                                            <li><i class="far fa-gas-pump"></i>Hybrid</li>
                                            <li><i class="far fa-steering-wheel"></i>Automatic</li>
                                        </ul>
                                        <div class="car-bottom">
                                            <div class="car-price">
                                                <span class="car-price-amount">$45 <span class="car-price-type">/Per
                                                        Day</span></span>
                                            </div>
                                            <div class="car-text-btn">
                                                <a href="#">See Details <i class="fas fa-arrow-right"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- pagination -->
                            <div class="pagination-area">
                                <div aria-label="Page navigation example">
                                    <ul class="pagination">
                                        <li class="page-item">
                                            <a class="page-link" href="#" aria-label="Previous">
                                                <span aria-hidden="true"><i class="far fa-angle-double-left"></i></span>
                                            </a>
                                        </li>
                                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                                        <li class="page-item">
                                            <a class="page-link" href="#" aria-label="Next">
                                                <span aria-hidden="true"><i
                                                        class="far fa-angle-double-right"></i></span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="pagination-showing">
                                    <p>Showing 1 - 6 of 24 Cars</p>
                                </div>
                            </div>
                            <!-- pagination end -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- car grid end -->

    </main>


    <!-- footer area -->
    <footer class="footer-area ft-bg">
        <div class="footer-widget">
            <div class="container">
                <div class="row footer-widget-wrapper pt-100 pb-70">
                    <div class="col-md-6 col-lg-3">
                        <div class="footer-widget-box about-us">
                            <a href="#" class="footer-logo">
                                <img src="assets/img/logo/logo.png" alt="">
                            </a>
                            <p class="mb-4">
                                We are many variations of passages available but the majority have suffer alteration
                                in some form by injected.
                            </p>
                            <ul class="footer-contact">
                                <li>
                                    <div class="footer-call">
                                        <div class="footer-call-icon">
                                            <i class="fal fa-headset"></i>
                                        </div>
                                        <div class="footer-call-info">
                                            <h6>24/7 Call Service</h6>
                                            <a href="tel:+21236547898">+2 123 654 7898</a>
                                        </div>
                                    </div>
                                </li>
                                <li><i class="far fa-map-marker-alt"></i>25/B Milford Road, New York</li>
                                <li><a href="mailto:info@example.com"><i
                                            class="far fa-envelopes"></i>info@example.com</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <div class="footer-widget-box list">
                            <h4 class="footer-widget-title">Our Company</h4>
                            <ul class="footer-list">
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> About Us</a></li>
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> Meet Our Team</a></li>
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> Contact Us</a></li>
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> Affiliate Program</a></li>
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> Advertising With Us</a></li>
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> Careers</a></li>
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> Our Blog</a></li>

                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <div class="footer-widget-box list">
                            <h4 class="footer-widget-title">Other Services</h4>
                            <ul class="footer-list">
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> Rewards Program</a></li>
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> Partners</a></li>
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> Community Program</a></li>
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> Investor Relations</a></li>
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> Developer Guide</a></li>
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> Travel API</a></li>
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> PointsPLUS</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <div class="footer-widget-box list">
                            <h4 class="footer-widget-title">Help Center</h4>
                            <ul class="footer-list">
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> Account</a></li>
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> FAQ's</a></li>
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> Legal Notice</a></li>
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> Privacy Policy</a></li>
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> Terms &amp; Conditions</a>
                                </li>
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> Live Chat</a></li>
                                <li><a href="#"><i class="fas fa-angle-double-right"></i> Sitemap</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="footer-widget-box list">
                            <h4 class="footer-widget-title">Newsletter</h4>
                            <div class="footer-newsletter">
                                <p>Subscribe Our Newsletter To Get Latest Update And News</p>
                                <div class="subscribe-form">
                                    <form action="#">
                                        <div class="form-group">
                                            <div class="form-group-icon">
                                                <input type="email" class="form-control" placeholder="Your Email">
                                                <i class="far fa-envelopes"></i>
                                            </div>
                                        </div>
                                        <button class="theme-btn" type="submit">
                                            Subscribe Now <i class="far fa-paper-plane"></i>
                                        </button>
                                        <p><i class="far fa-lock"></i> Your information is safe with us.</p>
                                    </form>
                                </div>
                            </div>
                            <div class="footer-payment-method">
                                <h6>We Accept:</h6>
                                <div class="payment-method-img">
                                    <img src="assets/img/payment/paypal.svg" alt="">
                                    <img src="assets/img/payment/mastercard.svg" alt="">
                                    <img src="assets/img/payment/visa.svg" alt="">
                                    <img src="assets/img/payment/discover.svg" alt="">
                                    <img src="assets/img/payment/american-express.svg" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="copyright">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 align-self-center">
                        <p class="copyright-text">
                            © Copyright <span id="date">2026</span> <a href="#"> Tavelo </a> All Rights Reserved.
                        </p>
                    </div>
                    <div class="col-md-6 align-self-center">
                        <ul class="footer-social">
                            <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                            <li><a href="#"><i class="fab fa-x-twitter"></i></a></li>
                            <li><a href="#"><i class="fab fa-linkedin-in"></i></a></li>
                            <li><a href="#"><i class="fab fa-youtube"></i></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!-- footer area end -->


    <!-- scroll-top -->
    <a href="#" id="scroll-top"><i class="far fa-angle-up"></i></a>
    <!-- scroll-top end -->


    <!-- js -->
    <script src="assets/js/jquery-3.7.1.min.js"></script>
    <script src="assets/js/modernizr.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/imagesloaded.pkgd.min.js"></script>
    <script src="assets/js/jquery.magnific-popup.min.js"></script>
    <script src="assets/js/isotope.pkgd.min.js"></script>
    <script src="assets/js/jquery.appear.min.js"></script>
    <script src="assets/js/jquery.easing.min.js"></script>
    <script src="assets/js/owl.carousel.min.js"></script>
    <script src="assets/js/counter-up.js"></script>
    <script src="assets/js/masonry.pkgd.min.js"></script>
    <script src="assets/js/jquery.nice-select.min.js"></script>
    <script src="assets/js/jquery-ui.min.js"></script>
    <script src="assets/js/jquery.timepicker.min.js"></script>
    <script src="assets/js/wow.min.js"></script>
    <script src="assets/js/main.js"></script>
    <div id="ui-datepicker-div" class="ui-datepicker ui-widget ui-widget-content ui-helper-clearfix ui-corner-all">
    </div>

    <script defer=""
        src="https://static.cloudflareinsights.com/beacon.min.js/vcd15cbe7772f49c399c6a5babf22c1241717689176015"
        integrity="sha512-ZpsOmlRQV6y907TI0dKBHq9Md29nnaEIPlkf84rnaERnq6zvWvPUqr2ft8M1aS28oN72PdrCzSjY4U6VaAw1EQ=="
        data-cf-beacon="{&quot;version&quot;:&quot;2024.11.0&quot;,&quot;token&quot;:&quot;1190e059c5bc497bafd35e121aae37b1&quot;,&quot;r&quot;:1,&quot;server_timing&quot;:{&quot;name&quot;:{&quot;cfCacheStatus&quot;:true,&quot;cfEdge&quot;:true,&quot;cfExtPri&quot;:true,&quot;cfL4&quot;:true,&quot;cfOrigin&quot;:true,&quot;cfSpeedBrain&quot;:true},&quot;location_startswith&quot;:null}}"
        crossorigin="anonymous"></script>


    <div class="ui-timepicker-container ui-timepicker-hidden ui-helper-hidden" style="display: none;">
        <div class="ui-timepicker ui-widget ui-widget-content ui-menu ui-corner-all">
            <ul class="ui-timepicker-viewport"></ul>
        </div>
    </div><veepn-lock-screen>
        <style>
            @font-face {
                font-family: FigtreeVF;
                src: url(chrome-extension://majdfhpaihoncoakbjgbdhglocklcgno/fonts/FigtreeVF.woff2) format("woff2 supports variations"), url(chrome-extension://majdfhpaihoncoakbjgbdhglocklcgno/fonts/FigtreeVF.woff2) format("woff2-variations");
                font-weight: 100 1000;
                font-display: swap
            }
        </style>
    </veepn-lock-screen>
</body>

</html>