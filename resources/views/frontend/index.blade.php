<!DOCTYPE html>
<html lang="en">


<head>
    <!-- meta tags -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="keywords" content="">

    <!-- title -->
    <title>Tavelo - Travel Booking </title>

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

</head>

<body>

    <!-- preloader -->
    <div class="preloader">
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

    @include('frontend.layout.header')

    <!-- header area end -->



    <main class="main">

        <!-- hero area -->
        <div class="hero-section">
            <div class="hero-single" style="background: url(assets/img/hero/hero-1.jpg)">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-12 mx-auto">
                            <div class="hero-content text-center">
                                <div class="hero-content-wrapper">
                                    <h1 class="hero-title">Explore The World Together</h1>
                                    <p>Find awesome flight, hotel, tour, car and packages</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- hero area end -->


        <!-- search area -->
        <div class="search-area">
            <div class="container">
                <div class="search-wrapper">
                    <!-- search header -->
                    <div class="search-header">
                        <div class="search-nav">
                            <ul class="nav nav-pills" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="pills-tab-1" data-bs-toggle="pill"
                                        data-bs-target="#pills-1" type="button" role="tab" aria-controls="pills-1"
                                        aria-selected="true"><i class="far fa-plane-departure"></i>Flights</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="pills-tab-2" data-bs-toggle="pill"
                                        data-bs-target="#pills-2" type="button" role="tab" aria-controls="pills-2"
                                        aria-selected="false"><i class="far fa-hotel"></i>Hotels</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="pills-tab-3" data-bs-toggle="pill"
                                        data-bs-target="#pills-3" type="button" role="tab" aria-controls="pills-3"
                                        aria-selected="false"><i
                                            class="far fa-person-biking-mountain"></i>Activity</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="pills-tab-4" data-bs-toggle="pill"
                                        data-bs-target="#pills-4" type="button" role="tab" aria-controls="pills-4"
                                        aria-selected="false"><i class="far fa-car-building"></i>Holiday
                                        Package</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="pills-tab-5" data-bs-toggle="pill"
                                        data-bs-target="#pills-5" type="button" role="tab" aria-controls="pills-5"
                                        aria-selected="false"><i class="far fa-car"></i>Cars</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="pills-tab-6" data-bs-toggle="pill"
                                        data-bs-target="#pills-6" type="button" role="tab" aria-controls="pills-6"
                                        aria-selected="false"><i class="far fa-ship"></i>Cruises</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="pills-tab-7" data-bs-toggle="pill"
                                        data-bs-target="#pills-7" type="button" role="tab" aria-controls="pills-7"
                                        aria-selected="false"><i class="far fa-earth-americas"></i>Tours</button>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- search header end -->

                    <!-- tab content -->
                    <div class="tab-content" id="pills-tabContent">

                        <!-- tab 1 -->
                        <div class="tab-pane fade show active" id="pills-1" role="tabpanel"
                            aria-labelledby="pills-tab-1" tabindex="0">
                            <div class="flight-search ft-group">
                                <div class="search-form">
                                    <form action="#">
                                        <!-- flight type -->
                                        <div class="flight-type">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" checked value="one-way"
                                                    name="flight-type" id="flight-type1">
                                                <label class="form-check-label" for="flight-type1">
                                                    One Way
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" value="round-way"
                                                    name="flight-type" id="flight-type2">
                                                <label class="form-check-label" for="flight-type2">
                                                    Round Way
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" value="multi-city"
                                                    name="flight-type" id="flight-type3">
                                                <label class="form-check-label" for="flight-type3">
                                                    Multi City
                                                </label>
                                            </div>
                                        </div>
                                        <!-- flight type end -->

                                        <!-- flight search -->
                                        <div class="flight-search-wrapper">
                                            <div class="flight-search-content">
                                                <!-- flight search content -->
                                                <div class="flight-search-item">
                                                    <div class="row">
                                                        <div class="col-lg-3">
                                                            <div class="form-group">
                                                                <label>From</label>
                                                                <div class="form-group-icon">
                                                                    <input type="text" name="from-destination"
                                                                        class="form-control swap-from" value="New York">
                                                                    <i class="fal fa-plane-departure"></i>
                                                                </div>
                                                                <p>JFK - John F. Kennedy International Airport
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-3">
                                                            <div class="form-group">
                                                                <div class="search-form-swap"><i
                                                                        class="far fa-repeat"></i>
                                                                </div>
                                                                <label>To</label>
                                                                <div class="form-group-icon">
                                                                    <input type="text" name="to-destination"
                                                                        class="form-control swap-to"
                                                                        value="Los Angeles">
                                                                    <i class="fal fa-plane-arrival"></i>
                                                                </div>
                                                                <p>LAX - Los Angeles International Airport</p>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-3">
                                                            <div class="form-group">
                                                                <div class="search-form-date">
                                                                    <div class="search-form-journey">
                                                                        <label>Journey Date</label>
                                                                        <div class="form-group-icon">
                                                                            <input type="text" name="journey-date"
                                                                                class="form-control date-picker journey-date">
                                                                            <i class="fal fa-calendar-days"></i>
                                                                        </div>
                                                                        <p class="journey-day-name"></p>
                                                                    </div>
                                                                    <div class="search-form-return">
                                                                        <label>Return Date</label>
                                                                        <div class="form-group-icon">
                                                                            <input type="text" name="return-date"
                                                                                class="form-control date-picker return-date">
                                                                        </div>
                                                                        <p class="return-day-name"></p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-3">
                                                            <div class="form-group dropdown passenger-box">
                                                                <div class="passenger-class" role="menu"
                                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                                    <label>Passenger, Class</label>
                                                                    <div class="form-group-icon">
                                                                        <div class="passenger-total"><span
                                                                                class="passenger-total-amount">2</span>
                                                                            Passenger
                                                                        </div>
                                                                        <i class="fal fa-user-tie-hair"></i>
                                                                    </div>
                                                                    <p class="passenger-class-name">Business</p>
                                                                </div>
                                                                <div class="dropdown-menu dropdown-menu-end">
                                                                    <div class="dropdown-item">
                                                                        <div class="passenger-item">
                                                                            <div class="passenger-info">
                                                                                <h6>Adults</h6>
                                                                                <p>12+ Years</p>
                                                                            </div>
                                                                            <div class="passenger-qty">
                                                                                <button type="button"
                                                                                    class="minus-btn"><i
                                                                                        class="far fa-minus"></i></button>
                                                                                <input type="text" name="adult"
                                                                                    class="qty-amount passenger-adult"
                                                                                    value="2" readonly>
                                                                                <button type="button"
                                                                                    class="plus-btn"><i
                                                                                        class="far fa-plus"></i></button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="dropdown-item">
                                                                        <div class="passenger-item">
                                                                            <div class="passenger-info">
                                                                                <h6>Children</h6>
                                                                                <p>2-12 Years</p>
                                                                            </div>
                                                                            <div class="passenger-qty">
                                                                                <button type="button"
                                                                                    class="minus-btn"><i
                                                                                        class="far fa-minus"></i></button>
                                                                                <input type="text" name="children"
                                                                                    class="qty-amount passenger-children"
                                                                                    value="0" readonly>
                                                                                <button type="button"
                                                                                    class="plus-btn"><i
                                                                                        class="far fa-plus"></i></button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="dropdown-item">
                                                                        <div class="passenger-item">
                                                                            <div class="passenger-info">
                                                                                <h6>Infant</h6>
                                                                                <p>Below 2 Years</p>
                                                                            </div>
                                                                            <div class="passenger-qty">
                                                                                <button type="button"
                                                                                    class="minus-btn"><i
                                                                                        class="far fa-minus"></i></button>
                                                                                <input type="text" name="infant"
                                                                                    class="qty-amount passenger-infant"
                                                                                    value="0" readonly>
                                                                                <button type="button"
                                                                                    class="plus-btn"><i
                                                                                        class="far fa-plus"></i></button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="dropdown-item">
                                                                        <h6 class="mb-3 mt-2">Cabin Class</h6>
                                                                        <div class="passenger-class-info">
                                                                            <div class="form-check">
                                                                                <input class="form-check-input"
                                                                                    type="radio" value="Economy"
                                                                                    name="cabin-class"
                                                                                    id="cabin-class1">
                                                                                <label class="form-check-label"
                                                                                    for="cabin-class1">
                                                                                    Economy
                                                                                </label>
                                                                            </div>
                                                                            <div class="form-check">
                                                                                <input class="form-check-input" checked
                                                                                    type="radio" value="Business"
                                                                                    name="cabin-class"
                                                                                    id="cabin-class2">
                                                                                <label class="form-check-label"
                                                                                    for="cabin-class2">
                                                                                    Business
                                                                                </label>
                                                                            </div>
                                                                            <div class="form-check">
                                                                                <input class="form-check-input"
                                                                                    type="radio" value="First Class"
                                                                                    name="cabin-class"
                                                                                    id="cabin-class3">
                                                                                <label class="form-check-label"
                                                                                    for="cabin-class3">
                                                                                    First Class
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- flight search content end -->

                                                <!-- flight-multicity-item -->
                                                <div class="flight-search-item flight-multicity-item have-to-clone">
                                                    <div class="row">
                                                        <div class="col-lg-3">
                                                            <div class="form-group">
                                                                <label>From</label>
                                                                <div class="form-group-icon">
                                                                    <input type="text" name="from-destination"
                                                                        class="form-control swap-from" value="New York">
                                                                    <i class="fal fa-plane-departure"></i>
                                                                </div>
                                                                <p>JFK - John F. Kennedy International Airport
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-3">
                                                            <div class="form-group">
                                                                <div class="search-form-swap"><i
                                                                        class="far fa-repeat"></i>
                                                                </div>
                                                                <label>To</label>
                                                                <div class="form-group-icon">
                                                                    <input type="text" name="to-destination"
                                                                        class="form-control swap-to"
                                                                        value="Los Angeles">
                                                                    <i class="fal fa-plane-arrival"></i>
                                                                </div>
                                                                <p>LAX - Los Angeles International Airport</p>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-3">
                                                            <div class="form-group">
                                                                <div class="search-form-date">
                                                                    <div class="search-form-journey">
                                                                        <label>Journey Date</label>
                                                                        <div class="form-group-icon">
                                                                            <input type="text" name="journey-date"
                                                                                class="form-control date-picker journey-date">
                                                                            <i class="fal fa-calendar-days"></i>
                                                                        </div>
                                                                        <p class="journey-day-name"></p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-3">
                                                            <div class="form-group">
                                                                <div class="multicity-btn">
                                                                    <i class="fal fa-plus-circle"></i> Add
                                                                    Another Flight
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- flight multicity end -->
                                            </div>
                                            <div class="search-btn">
                                                <button type="submit" class="theme-btn"><span
                                                        class="far fa-search"></span>Search Now</button>
                                            </div>
                                        </div>
                                        <!-- flight search end -->
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- tab 2 -->
                        <div class="tab-pane fade" id="pills-2" role="tabpanel" aria-labelledby="pills-tab-2"
                            tabindex="0">
                            <div class="hotel-search">
                                <div class="search-form">
                                    <form action="#">
                                        <div class="hotel-search-wrapper">
                                            <div class="row">
                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <label>Destination</label>
                                                        <div class="form-group-icon">
                                                            <input type="text" name="destination" class="form-control"
                                                                value="Reserva Ecologica">
                                                            <i class="fal fa-earth-americas"></i>
                                                        </div>
                                                        <p>Comuna 1, Argentina</p>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <div class="search-form-date">
                                                            <div class="search-form-journey">
                                                                <label>Check In</label>
                                                                <div class="form-group-icon">
                                                                    <input type="text" name="journey-date"
                                                                        class="form-control date-picker journey-date">
                                                                    <i class="fal fa-calendar-days"></i>
                                                                </div>
                                                                <p class="journey-day-name"></p>
                                                            </div>
                                                            <div class="search-form-return">
                                                                <label>Check Out</label>
                                                                <div class="form-group-icon">
                                                                    <input type="text" name="return-date"
                                                                        class="form-control date-picker return-date">
                                                                </div>
                                                                <p class="return-day-name"></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="form-group dropdown passenger-box">
                                                        <div class="passenger-class" role="menu"
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                            <label>Rooms, Guests</label>
                                                            <div class="form-group-icon">
                                                                <div class="passenger-total">
                                                                    <span class="passenger-total-room">2</span> Rooms,
                                                                    <span class="passenger-total-amount">2</span> Guests
                                                                </div>
                                                                <i class="fal fa-user-tie-hair"></i>
                                                            </div>
                                                            <p class="passenger-class-name">Double Room</p>
                                                        </div>
                                                        <div class="dropdown-menu dropdown-menu-end">
                                                            <div class="dropdown-item">
                                                                <div class="passenger-item">
                                                                    <div class="passenger-info">
                                                                        <h6>Adults</h6>
                                                                        <p>12+ Years</p>
                                                                    </div>
                                                                    <div class="passenger-qty">
                                                                        <button type="button" class="minus-btn"><i
                                                                                class="far fa-minus"></i></button>
                                                                        <input type="text" name="adult"
                                                                            class="qty-amount passenger-adult" value="2"
                                                                            readonly>
                                                                        <button type="button" class="plus-btn"><i
                                                                                class="far fa-plus"></i></button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="dropdown-item">
                                                                <div class="passenger-item">
                                                                    <div class="passenger-info">
                                                                        <h6>Children</h6>
                                                                        <p>2-12 Years</p>
                                                                    </div>
                                                                    <div class="passenger-qty">
                                                                        <button type="button" class="minus-btn"><i
                                                                                class="far fa-minus"></i></button>
                                                                        <input type="text" name="children"
                                                                            class="qty-amount passenger-children"
                                                                            value="0" readonly>
                                                                        <button type="button" class="plus-btn"><i
                                                                                class="far fa-plus"></i></button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="dropdown-item">
                                                                <div class="passenger-item">
                                                                    <div class="passenger-info">
                                                                        <h6>Infant</h6>
                                                                        <p>Below 2 Years</p>
                                                                    </div>
                                                                    <div class="passenger-qty">
                                                                        <button type="button" class="minus-btn"><i
                                                                                class="far fa-minus"></i></button>
                                                                        <input type="text" name="infant"
                                                                            class="qty-amount passenger-infant"
                                                                            value="0" readonly>
                                                                        <button type="button" class="plus-btn"><i
                                                                                class="far fa-plus"></i></button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="dropdown-item">
                                                                <div class="passenger-item">
                                                                    <div class="passenger-info">
                                                                        <h6>Rooms</h6>
                                                                    </div>
                                                                    <div class="passenger-qty">
                                                                        <button type="button" class="minus-btn"><i
                                                                                class="far fa-minus"></i></button>
                                                                        <input type="text" name="room"
                                                                            class="qty-amount passenger-room" value="2"
                                                                            readonly>
                                                                        <button type="button" class="plus-btn"><i
                                                                                class="far fa-plus"></i></button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="dropdown-item">
                                                                <h6 class="mb-3 mt-2">Room Type</h6>
                                                                <div class="passenger-class-info">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="radio"
                                                                            value="Single Room" name="room-type"
                                                                            id="room-type1">
                                                                        <label class="form-check-label"
                                                                            for="room-type1">
                                                                            Single Room
                                                                        </label>
                                                                    </div>
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" checked
                                                                            type="radio" value="Double Room"
                                                                            name="room-type" id="room-type2">
                                                                        <label class="form-check-label"
                                                                            for="room-type2">
                                                                            Double Room
                                                                        </label>
                                                                    </div>
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="radio"
                                                                            value="Deluxe Room" name="room-type"
                                                                            id="room-type3">
                                                                        <label class="form-check-label"
                                                                            for="room-type3">
                                                                            Deluxe Room
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
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
                        </div>

                        <!-- tab 3 -->
                        <div class="tab-pane fade" id="pills-3" role="tabpanel" aria-labelledby="pills-tab-3"
                            tabindex="0">
                            <div class="activity-search">
                                <div class="search-form">
                                    <form action="#">
                                        <div class="activity-search-wrapper">
                                            <div class="row">
                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <label>Location</label>
                                                        <div class="form-group-icon">
                                                            <input type="text" name="location" class="form-control"
                                                                value="New York, United States">
                                                            <i class="fal fa-earth-americas"></i>
                                                        </div>
                                                        <p>Where Are You Going?</p>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <div class="search-form-date">
                                                            <div class="search-form-journey">
                                                                <label>Check In</label>
                                                                <div class="form-group-icon">
                                                                    <input type="text" name="journey-date"
                                                                        class="form-control date-picker journey-date">
                                                                    <i class="fal fa-calendar-days"></i>
                                                                </div>
                                                                <p class="journey-day-name"></p>
                                                            </div>
                                                            <div class="search-form-return">
                                                                <label>Check Out</label>
                                                                <div class="form-group-icon">
                                                                    <input type="text" name="return-date"
                                                                        class="form-control date-picker return-date">
                                                                </div>
                                                                <p class="return-day-name"></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="form-group dropdown passenger-box">
                                                        <div class="passenger-class" role="menu"
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                            <label>Rooms, Guests</label>
                                                            <div class="form-group-icon">
                                                                <div class="passenger-total">
                                                                    <span class="passenger-total-room">2</span> Rooms,
                                                                    <span class="passenger-total-amount">2</span> Guests
                                                                </div>
                                                                <i class="fal fa-user-tie-hair"></i>
                                                            </div>
                                                            <p class="passenger-class-name">Double Room</p>
                                                        </div>
                                                        <div class="dropdown-menu dropdown-menu-end">
                                                            <div class="dropdown-item">
                                                                <div class="passenger-item">
                                                                    <div class="passenger-info">
                                                                        <h6>Adults</h6>
                                                                        <p>12+ Years</p>
                                                                    </div>
                                                                    <div class="passenger-qty">
                                                                        <button type="button" class="minus-btn"><i
                                                                                class="far fa-minus"></i></button>
                                                                        <input type="text" name="adult"
                                                                            class="qty-amount passenger-adult" value="2"
                                                                            readonly>
                                                                        <button type="button" class="plus-btn"><i
                                                                                class="far fa-plus"></i></button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="dropdown-item">
                                                                <div class="passenger-item">
                                                                    <div class="passenger-info">
                                                                        <h6>Children</h6>
                                                                        <p>2-12 Years</p>
                                                                    </div>
                                                                    <div class="passenger-qty">
                                                                        <button type="button" class="minus-btn"><i
                                                                                class="far fa-minus"></i></button>
                                                                        <input type="text" name="children"
                                                                            class="qty-amount passenger-children"
                                                                            value="0" readonly>
                                                                        <button type="button" class="plus-btn"><i
                                                                                class="far fa-plus"></i></button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="dropdown-item">
                                                                <div class="passenger-item">
                                                                    <div class="passenger-info">
                                                                        <h6>Infant</h6>
                                                                        <p>Below 2 Years</p>
                                                                    </div>
                                                                    <div class="passenger-qty">
                                                                        <button type="button" class="minus-btn"><i
                                                                                class="far fa-minus"></i></button>
                                                                        <input type="text" name="infant"
                                                                            class="qty-amount passenger-infant"
                                                                            value="0" readonly>
                                                                        <button type="button" class="plus-btn"><i
                                                                                class="far fa-plus"></i></button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="dropdown-item">
                                                                <div class="passenger-item">
                                                                    <div class="passenger-info">
                                                                        <h6>Rooms</h6>
                                                                    </div>
                                                                    <div class="passenger-qty">
                                                                        <button type="button" class="minus-btn"><i
                                                                                class="far fa-minus"></i></button>
                                                                        <input type="text" name="room"
                                                                            class="qty-amount passenger-room" value="2"
                                                                            readonly>
                                                                        <button type="button" class="plus-btn"><i
                                                                                class="far fa-plus"></i></button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="dropdown-item">
                                                                <h6 class="mb-3 mt-2">Room Type</h6>
                                                                <div class="passenger-class-info">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="radio"
                                                                            value="Single Room" name="room-type"
                                                                            id="room-type4">
                                                                        <label class="form-check-label"
                                                                            for="room-type4">
                                                                            Single Room
                                                                        </label>
                                                                    </div>
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" checked
                                                                            type="radio" value="Double Room"
                                                                            name="room-type" id="room-type5">
                                                                        <label class="form-check-label"
                                                                            for="room-type5">
                                                                            Double Room
                                                                        </label>
                                                                    </div>
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="radio"
                                                                            value="Deluxe Room" name="room-type"
                                                                            id="room-type6">
                                                                        <label class="form-check-label"
                                                                            for="room-type6">
                                                                            Deluxe Room
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
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
                        </div>

                        <!-- tab 4 -->
                        <div class="tab-pane fade" id="pills-4" role="tabpanel" aria-labelledby="pills-tab-4"
                            tabindex="0">
                            <div class="holiday-search">
                                <div class="search-form">
                                    <form action="#">
                                        <!-- holiday type -->
                                        <div class="flight-type">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" checked value="holiday1"
                                                    name="holiday-type" id="holiday-type1">
                                                <label class="form-check-label" for="holiday-type1">
                                                    Flight + Hotel + Car
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" value="holiday2"
                                                    name="holiday-type" id="holiday-type2">
                                                <label class="form-check-label" for="holiday-type2">
                                                    Flight + Hotel
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" value="holiday3"
                                                    name="holiday-type" id="holiday-type3">
                                                <label class="form-check-label" for="holiday-type3">
                                                    Flight + Car
                                                </label>
                                            </div>
                                        </div>
                                        <!-- holiday type end -->
                                        <div class="holiday-search-wrapper">
                                            <div class="flight-search-item">
                                                <div class="row">
                                                    <div class="col-lg-3">
                                                        <div class="form-group">
                                                            <label>From</label>
                                                            <div class="form-group-icon">
                                                                <input type="text" name="from-destination"
                                                                    class="form-control swap-from" value="New York">
                                                                <i class="fal fa-plane-departure"></i>
                                                            </div>
                                                            <p>JFK - John F. Kennedy International Airport
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <div class="form-group">
                                                            <div class="search-form-swap"><i class="far fa-repeat"></i>
                                                            </div>
                                                            <label>To</label>
                                                            <div class="form-group-icon">
                                                                <input type="text" name="to-destination"
                                                                    class="form-control swap-to" value="Los Angeles">
                                                                <i class="fal fa-plane-arrival"></i>
                                                            </div>
                                                            <p>LAX - Los Angeles International Airport</p>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <div class="form-group">
                                                            <div class="search-form-date">
                                                                <div class="search-form-journey">
                                                                    <label>Check In</label>
                                                                    <div class="form-group-icon">
                                                                        <input type="text" name="journey-date"
                                                                            class="form-control date-picker journey-date">
                                                                        <i class="fal fa-calendar-days"></i>
                                                                    </div>
                                                                    <p class="journey-day-name"></p>
                                                                </div>
                                                                <div class="search-form-return">
                                                                    <label>Check Out</label>
                                                                    <div class="form-group-icon">
                                                                        <input type="text" name="return-date"
                                                                            class="form-control date-picker return-date">
                                                                    </div>
                                                                    <p class="return-day-name"></p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <div class="form-group dropdown passenger-box">
                                                            <div class="passenger-class" role="menu"
                                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                                <label>Rooms, Guests</label>
                                                                <div class="form-group-icon">
                                                                    <div class="passenger-total">
                                                                        <span class="passenger-total-room">2</span>
                                                                        Rooms,
                                                                        <span class="passenger-total-amount">2</span>
                                                                        Guests
                                                                    </div>
                                                                    <i class="fal fa-user-tie-hair"></i>
                                                                </div>
                                                                <p class="passenger-class-name">Double Room</p>
                                                            </div>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                <div class="dropdown-item">
                                                                    <div class="passenger-item">
                                                                        <div class="passenger-info">
                                                                            <h6>Adults</h6>
                                                                            <p>12+ Years</p>
                                                                        </div>
                                                                        <div class="passenger-qty">
                                                                            <button type="button" class="minus-btn"><i
                                                                                    class="far fa-minus"></i></button>
                                                                            <input type="text" name="adult"
                                                                                class="qty-amount passenger-adult"
                                                                                value="2" readonly>
                                                                            <button type="button" class="plus-btn"><i
                                                                                    class="far fa-plus"></i></button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="dropdown-item">
                                                                    <div class="passenger-item">
                                                                        <div class="passenger-info">
                                                                            <h6>Children</h6>
                                                                            <p>2-12 Years</p>
                                                                        </div>
                                                                        <div class="passenger-qty">
                                                                            <button type="button" class="minus-btn"><i
                                                                                    class="far fa-minus"></i></button>
                                                                            <input type="text" name="children"
                                                                                class="qty-amount passenger-children"
                                                                                value="0" readonly>
                                                                            <button type="button" class="plus-btn"><i
                                                                                    class="far fa-plus"></i></button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="dropdown-item">
                                                                    <div class="passenger-item">
                                                                        <div class="passenger-info">
                                                                            <h6>Infant</h6>
                                                                            <p>Below 2 Years</p>
                                                                        </div>
                                                                        <div class="passenger-qty">
                                                                            <button type="button" class="minus-btn"><i
                                                                                    class="far fa-minus"></i></button>
                                                                            <input type="text" name="infant"
                                                                                class="qty-amount passenger-infant"
                                                                                value="0" readonly>
                                                                            <button type="button" class="plus-btn"><i
                                                                                    class="far fa-plus"></i></button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="dropdown-item">
                                                                    <div class="passenger-item">
                                                                        <div class="passenger-info">
                                                                            <h6>Rooms</h6>
                                                                        </div>
                                                                        <div class="passenger-qty">
                                                                            <button type="button" class="minus-btn"><i
                                                                                    class="far fa-minus"></i></button>
                                                                            <input type="text" name="room"
                                                                                class="qty-amount passenger-room"
                                                                                value="2" readonly>
                                                                            <button type="button" class="plus-btn"><i
                                                                                    class="far fa-plus"></i></button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="dropdown-item">
                                                                    <h6 class="mb-3 mt-2">Room Type</h6>
                                                                    <div class="passenger-class-info">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input" type="radio"
                                                                                value="Single Room" name="room-type"
                                                                                id="room-type7">
                                                                            <label class="form-check-label"
                                                                                for="room-type7">
                                                                                Single Room
                                                                            </label>
                                                                        </div>
                                                                        <div class="form-check">
                                                                            <input class="form-check-input" checked
                                                                                type="radio" value="Double Room"
                                                                                name="room-type" id="room-type8">
                                                                            <label class="form-check-label"
                                                                                for="room-type8">
                                                                                Double Room
                                                                            </label>
                                                                        </div>
                                                                        <div class="form-check">
                                                                            <input class="form-check-input" type="radio"
                                                                                value="Deluxe Room" name="room-type"
                                                                                id="room-type9">
                                                                            <label class="form-check-label"
                                                                                for="room-type9">
                                                                                Deluxe Room
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
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
                        </div>

                        <!-- tab 5 -->
                        <div class="tab-pane fade" id="pills-5" role="tabpanel" aria-labelledby="pills-tab-5"
                            tabindex="0">
                            <div class="car-search">
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
                                                                        class="form-control date-picker journey-date">
                                                                    <i class="fal fa-calendar-days"></i>
                                                                </div>
                                                                <p class="journey-day-name"></p>
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
                                                                        class="form-control date-picker journey-date">
                                                                    <i class="fal fa-calendar-days"></i>
                                                                </div>
                                                                <p class="journey-day-name"></p>
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
                        </div>

                        <!-- tab 6 -->
                        <div class="tab-pane fade" id="pills-6" role="tabpanel" aria-labelledby="pills-tab-6"
                            tabindex="0">
                            <div class="cruise-search">
                                <div class="search-form">
                                    <form action="#">
                                        <div class="cruise-search-wrapper">
                                            <div class="row">
                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <label>Destination</label>
                                                        <div class="form-group-icon">
                                                            <input type="text" name="destination" class="form-control"
                                                                value="New York, United States">
                                                            <i class="fal fa-earth-americas"></i>
                                                        </div>
                                                        <p>Where Are You Going?</p>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <div class="search-form-date">
                                                            <div class="search-form-journey">
                                                                <label>Journey Date</label>
                                                                <div class="form-group-icon">
                                                                    <input type="text" name="journey-date"
                                                                        class="form-control date-picker journey-date">
                                                                    <i class="fal fa-calendar-days"></i>
                                                                </div>
                                                                <p class="journey-day-name"></p>
                                                            </div>
                                                            <div class="search-form-return">
                                                                <label>Return Date</label>
                                                                <div class="form-group-icon">
                                                                    <input type="text" name="return-date"
                                                                        class="form-control date-picker return-date">
                                                                </div>
                                                                <p class="return-day-name"></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="form-group dropdown passenger-box">
                                                        <div class="passenger-class" role="menu"
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                            <label>Travelers</label>
                                                            <div class="form-group-icon">
                                                                <div class="passenger-total">
                                                                    <span class="passenger-total-amount">2</span>
                                                                    Travelers
                                                                </div>
                                                                <i class="fal fa-user-tie-hair"></i>
                                                            </div>
                                                            <p class="passenger-class-name">In Cabin</p>
                                                        </div>
                                                        <div class="dropdown-menu dropdown-menu-end">
                                                            <div class="dropdown-item">
                                                                <div class="passenger-item">
                                                                    <div class="passenger-info">
                                                                        <h6>Adults</h6>
                                                                        <p>12+ Years</p>
                                                                    </div>
                                                                    <div class="passenger-qty">
                                                                        <button type="button" class="minus-btn"><i
                                                                                class="far fa-minus"></i></button>
                                                                        <input type="text" name="adult"
                                                                            class="qty-amount passenger-adult" value="2"
                                                                            readonly>
                                                                        <button type="button" class="plus-btn"><i
                                                                                class="far fa-plus"></i></button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="dropdown-item">
                                                                <div class="passenger-item">
                                                                    <div class="passenger-info">
                                                                        <h6>Children</h6>
                                                                        <p>2-12 Years</p>
                                                                    </div>
                                                                    <div class="passenger-qty">
                                                                        <button type="button" class="minus-btn"><i
                                                                                class="far fa-minus"></i></button>
                                                                        <input type="text" name="children"
                                                                            class="qty-amount passenger-children"
                                                                            value="0" readonly>
                                                                        <button type="button" class="plus-btn"><i
                                                                                class="far fa-plus"></i></button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="dropdown-item">
                                                                <div class="passenger-item">
                                                                    <div class="passenger-info">
                                                                        <h6>Infant</h6>
                                                                        <p>Below 2 Years</p>
                                                                    </div>
                                                                    <div class="passenger-qty">
                                                                        <button type="button" class="minus-btn"><i
                                                                                class="far fa-minus"></i></button>
                                                                        <input type="text" name="infant"
                                                                            class="qty-amount passenger-infant"
                                                                            value="0" readonly>
                                                                        <button type="button" class="plus-btn"><i
                                                                                class="far fa-plus"></i></button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="dropdown-item">
                                                                <h6 class="mb-3 mt-2">Cruise Class</h6>
                                                                <div class="passenger-class-info">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="radio"
                                                                            value="In Cabin" name="cruise-class"
                                                                            id="cruise-class1">
                                                                        <label class="form-check-label"
                                                                            for="cruise-class1">
                                                                            In Cabin
                                                                        </label>
                                                                    </div>
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" checked
                                                                            type="radio" value="In Chair"
                                                                            name="cruise-class" id="cruise-class2">
                                                                        <label class="form-check-label"
                                                                            for="cruise-class2">
                                                                            In Chair
                                                                        </label>
                                                                    </div>
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="radio"
                                                                            value="In First Class" name="cruise-class"
                                                                            id="cruise-class3">
                                                                        <label class="form-check-label"
                                                                            for="cruise-class3">
                                                                            In First Class
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
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
                        </div>

                        <!-- tab 7 -->
                        <div class="tab-pane fade" id="pills-7" role="tabpanel" aria-labelledby="pills-tab-7"
                            tabindex="0">
                            <div class="tour-search">
                                <div class="search-form">
                                    <form action="#">
                                        <div class="tour-search-wrapper">
                                            <div class="row">
                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <label>Destination</label>
                                                        <div class="form-group-icon">
                                                            <input type="text" name="destination" class="form-control"
                                                                value="New York, United States">
                                                            <i class="fal fa-earth-americas"></i>
                                                        </div>
                                                        <p>Where Are You Going?</p>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <div class="search-form-date">
                                                            <div class="search-form-journey">
                                                                <label>Journey Date</label>
                                                                <div class="form-group-icon">
                                                                    <input type="text" name="journey-date"
                                                                        class="form-control date-picker journey-date">
                                                                    <i class="fal fa-calendar-days"></i>
                                                                </div>
                                                                <p class="journey-day-name"></p>
                                                            </div>
                                                            <div class="search-form-return">
                                                                <label>Return Date</label>
                                                                <div class="form-group-icon">
                                                                    <input type="text" name="return-date"
                                                                        class="form-control date-picker return-date">
                                                                </div>
                                                                <p class="return-day-name"></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="form-group dropdown passenger-box">
                                                        <div class="passenger-class" role="menu"
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                            <label>Rooms, Guests</label>
                                                            <div class="form-group-icon">
                                                                <div class="passenger-total">
                                                                    <span class="passenger-total-room">2</span> Rooms,
                                                                    <span class="passenger-total-amount">2</span> Guests
                                                                </div>
                                                                <i class="fal fa-user-tie-hair"></i>
                                                            </div>
                                                            <p class="passenger-class-name">Double Room</p>
                                                        </div>
                                                        <div class="dropdown-menu dropdown-menu-end">
                                                            <div class="dropdown-item">
                                                                <div class="passenger-item">
                                                                    <div class="passenger-info">
                                                                        <h6>Adults</h6>
                                                                        <p>12+ Years</p>
                                                                    </div>
                                                                    <div class="passenger-qty">
                                                                        <button type="button" class="minus-btn"><i
                                                                                class="far fa-minus"></i></button>
                                                                        <input type="text" name="adult"
                                                                            class="qty-amount passenger-adult" value="2"
                                                                            readonly>
                                                                        <button type="button" class="plus-btn"><i
                                                                                class="far fa-plus"></i></button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="dropdown-item">
                                                                <div class="passenger-item">
                                                                    <div class="passenger-info">
                                                                        <h6>Children</h6>
                                                                        <p>2-12 Years</p>
                                                                    </div>
                                                                    <div class="passenger-qty">
                                                                        <button type="button" class="minus-btn"><i
                                                                                class="far fa-minus"></i></button>
                                                                        <input type="text" name="children"
                                                                            class="qty-amount passenger-children"
                                                                            value="0" readonly>
                                                                        <button type="button" class="plus-btn"><i
                                                                                class="far fa-plus"></i></button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="dropdown-item">
                                                                <div class="passenger-item">
                                                                    <div class="passenger-info">
                                                                        <h6>Infant</h6>
                                                                        <p>Below 2 Years</p>
                                                                    </div>
                                                                    <div class="passenger-qty">
                                                                        <button type="button" class="minus-btn"><i
                                                                                class="far fa-minus"></i></button>
                                                                        <input type="text" name="infant"
                                                                            class="qty-amount passenger-infant"
                                                                            value="0" readonly>
                                                                        <button type="button" class="plus-btn"><i
                                                                                class="far fa-plus"></i></button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="dropdown-item">
                                                                <div class="passenger-item">
                                                                    <div class="passenger-info">
                                                                        <h6>Rooms</h6>
                                                                    </div>
                                                                    <div class="passenger-qty">
                                                                        <button type="button" class="minus-btn"><i
                                                                                class="far fa-minus"></i></button>
                                                                        <input type="text" name="room"
                                                                            class="qty-amount passenger-room" value="2"
                                                                            readonly>
                                                                        <button type="button" class="plus-btn"><i
                                                                                class="far fa-plus"></i></button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="dropdown-item">
                                                                <h6 class="mb-3 mt-2">Room Type</h6>
                                                                <div class="passenger-class-info">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="radio"
                                                                            value="Single Room" name="room-type"
                                                                            id="room-type10">
                                                                        <label class="form-check-label"
                                                                            for="room-type10">
                                                                            Single Room
                                                                        </label>
                                                                    </div>
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" checked
                                                                            type="radio" value="Double Room"
                                                                            name="room-type" id="room-type11">
                                                                        <label class="form-check-label"
                                                                            for="room-type11">
                                                                            Double Room
                                                                        </label>
                                                                    </div>
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="radio"
                                                                            value="Deluxe Room" name="room-type"
                                                                            id="room-type12">
                                                                        <label class="form-check-label"
                                                                            for="room-type12">
                                                                            Deluxe Room
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
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
                        </div>
                    </div>
                    <!-- tab content end -->
                </div>
            </div>
        </div>
        <!-- search area end -->


        <!-- about-area -->
        <div class="about-area py-120">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="about-left wow fadeInLeft" data-wow-delay=".25s">
                            <div class="about-img">
                                <div class="row">
                                    <div class="col-6">
                                        <img class="img-1" src="assets/img/about/01.jpg" alt="">
                                    </div>
                                    <div class="col-6">
                                        <img class="img-2" src="assets/img/about/02.jpg" alt="">
                                    </div>
                                </div>
                            </div>
                            <div class="about-experience">
                                <h5>30<span>+</span></h5>
                                <p>Years Of Experience</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="about-right wow fadeInUp" data-wow-delay=".25s">
                            <div class="site-heading mb-3">
                                <span class="site-title-tagline"><i class="far fa-plane"></i> About Us</span>
                                <h2 class="site-title">We Are The World <span>Best Travel Booking</span> Agency Company
                                </h2>
                            </div>
                            <p class="about-text">There are many variations of passages of Lorem Ipsum available,
                                but the majority have suffered alteration in some form by injected humour or randomised
                                printer took a galley of type and scrambled it to make a type specimen book.
                                words which don't look even have suffered alteration in some form by injected.</p>
                            <div class="about-content">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="about-item">
                                            <div class="icon">
                                                <img src="assets/img/icon/deal.svg" alt="">
                                            </div>
                                            <div class="content">
                                                <h6>Get Your Best Deals</h6>
                                                <p>Take a look at our up of the round shows</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="about-item">
                                            <div class="icon">
                                                <img src="assets/img/icon/booking.svg" alt="">
                                            </div>
                                            <div class="content">
                                                <h6>Easy To Booking</h6>
                                                <p>Take a look at our up of the round shows</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <a href="about" class="theme-btn">Discover More <i
                                    class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- about-area end -->


        <!-- feature area -->
        <div class="feature-area pb-120">
            <div class="container">
                <div class="feature-wrapper">
                    <div class="row g-4">
                        <div class="col-lg-6 col-xl-4">
                            <div class="wow fadeInLeft" data-wow-delay=".25s">
                                <div class="site-heading mb-3">
                                    <span class="site-title-tagline"><i class="far fa-plane"></i> Features</span>
                                    <h2 class="site-title">Let's Check Our <span>Awesome</span> Features</h2>
                                </div>
                                <p>
                                    There are many variations of passages orem psum available but the majority have
                                    suffered alteration in some form, by
                                    if you are going to use a passage orem psum you need to be sure there isn't anything
                                    embarrassing hidden injected humour, or randomised words which don't look even
                                    slightly believable.
                                </p>
                                <a href="contact" class="theme-btn mt-30">Learn More <i
                                        class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        <div class="col-lg-6 col-xl-4">
                            <div class="feature-img wow fadeInUp" data-wow-delay=".25s">
                                <img src="assets/img/feature/01.jpg" alt="">
                            </div>
                        </div>
                        <div class="col-lg-6 col-xl-4">
                            <div class="wow fadeInRight" data-wow-delay=".25s">
                                <div class="feature-item">
                                    <div class="feature-icon">
                                        <img src="assets/img/icon/world.svg" alt="">
                                    </div>
                                    <div class="feature-content">
                                        <h4 class="feature-title">Worldwide Coverage</h4>
                                        <p>It is a long established fact that reader will of page when looking at its
                                            layout.</p>
                                    </div>
                                </div>
                                <div class="feature-item mt-20">
                                    <div class="feature-icon">
                                        <img src="assets/img/icon/quality.svg" alt="">
                                    </div>
                                    <div class="feature-content">
                                        <h4 class="feature-title">Best Quality Services</h4>
                                        <p>It is a long established fact that reader will of page when looking at its
                                            layout.</p>
                                    </div>
                                </div>
                                <div class="feature-item mt-20">
                                    <div class="feature-icon">
                                        <img src="assets/img/icon/support.svg" alt="">
                                    </div>
                                    <div class="feature-content">
                                        <h4 class="feature-title">24/7 Customer Service</h4>
                                        <p>It is a long established fact that reader will of page when looking at its
                                            layout.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- feature area end -->


        <!-- destination area -->
        <div class="destination-area bg pt-80 pb-120">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 mx-auto wow fadeInDown" data-wow-duration="1s" data-wow-delay=".25s">
                        <div class="site-heading text-center">
                            <span class="site-title-tagline"><i class="far fa-plane"></i> Destination</span>
                            <h2 class="site-title">Our Most Popular Destinations</h2>
                        </div>
                    </div>
                </div>
                <div class="destination-slider owl-carousel owl-theme wow fadeInUp" data-wow-duration="1s"
                    data-wow-delay=".25s">
                    <div class="destination-item">
                        <div class="destination-img">
                            <img src="assets/img/destination/01.jpg" alt="">
                            <a href="tour-grid" class="destination-btn"><i class="far fa-arrow-right"></i></a>
                        </div>
                        <div class="destination-info">
                            <h4><a href="#">New York City</a></h4>
                            <div class="destination-rate">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fal fa-star"></i>
                                <span>(2.5k Reviews)</span>
                            </div>
                            <div class="destination-more-info">
                                <div class="destination-more-info">
                                    <span><i class="far fa-backpack"></i> Start $50</span>
                                    <span><i class="far fa-earth-americas"></i> 35 Tour</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="destination-item">
                        <div class="destination-img">
                            <img src="assets/img/destination/02.jpg" alt="">
                            <a href="tour-grid" class="destination-btn"><i class="far fa-arrow-right"></i></a>
                        </div>
                        <div class="destination-info">
                            <h4><a href="#">San Francisco</a></h4>
                            <div class="destination-rate">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fal fa-star"></i>
                                <span>(2.5k Reviews)</span>
                            </div>
                            <div class="destination-more-info">
                                <span><i class="far fa-backpack"></i> Start $70</span>
                                <span><i class="far fa-earth-americas"></i> 25 Tour</span>
                            </div>
                        </div>
                    </div>
                    <div class="destination-item">
                        <div class="destination-img">
                            <img src="assets/img/destination/03.jpg" alt="">
                            <a href="tour-grid" class="destination-btn"><i class="far fa-arrow-right"></i></a>
                        </div>
                        <div class="destination-info">
                            <h4><a href="#">New Orleans</a></h4>
                            <div class="destination-rate">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fal fa-star"></i>
                                <span>(2.5k Reviews)</span>
                            </div>
                            <div class="destination-more-info">
                                <span><i class="far fa-backpack"></i> Start $47</span>
                                <span><i class="far fa-earth-americas"></i> 28 Tour</span>
                            </div>
                        </div>
                    </div>
                    <div class="destination-item">
                        <div class="destination-img">
                            <img src="assets/img/destination/04.jpg" alt="">
                            <a href="tour-grid" class="destination-btn"><i class="far fa-arrow-right"></i></a>
                        </div>
                        <div class="destination-info">
                            <h4><a href="#">Los Angeles</a></h4>
                            <div class="destination-rate">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fal fa-star"></i>
                                <span>(2.5k Reviews)</span>
                            </div>
                            <div class="destination-more-info">
                                <span><i class="far fa-backpack"></i> Start $60</span>
                                <span><i class="far fa-earth-americas"></i> 33 Tour</span>
                            </div>
                        </div>
                    </div>
                    <div class="destination-item">
                        <div class="destination-img">
                            <img src="assets/img/destination/05.jpg" alt="">
                            <a href="tour-grid" class="destination-btn"><i class="far fa-arrow-right"></i></a>
                        </div>
                        <div class="destination-info">
                            <h4><a href="#">Sydney</a></h4>
                            <div class="destination-rate">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fal fa-star"></i>
                                <span>(2.5k Reviews)</span>
                            </div>
                            <div class="destination-more-info">
                                <span><i class="far fa-backpack"></i> Start $60</span>
                                <span><i class="far fa-earth-americas"></i> 33 Tour</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- destination area end -->


        <!-- counter area -->
        <div class="counter-area counter-negative">
            <div class="col-lg-11 col-xl-9">
                <div class="counter-wrap">
                    <div class="row">
                        <div class="col-lg-3 col-sm-6">
                            <div class="counter-box">
                                <div class="icon">
                                    <img src="assets/img/icon/booking-confirm.svg" alt="">
                                </div>
                                <div class="counter-content">
                                    <div class="counter-number">
                                        <span class="counter" data-count="+" data-to="120" data-speed="3000">120</span>
                                        <span class="counter-sign">k</span>
                                    </div>
                                    <h6 class="title">Booking Done</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="counter-box">
                                <div class="icon">
                                    <img src="assets/img/icon/destination.svg" alt="">
                                </div>
                                <div class="counter-content">
                                    <div class="counter-number">
                                        <span class="counter" data-count="+" data-to="200" data-speed="3000">200</span>
                                        <span class="counter-sign">+</span>
                                    </div>
                                    <h6 class="title">Our Destination</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="counter-box">
                                <div class="icon">
                                    <img src="assets/img/icon/rating.svg" alt="">
                                </div>
                                <div class="counter-content">
                                    <div class="counter-number">
                                        <span class="counter" data-count="+" data-to="40" data-speed="3000">40</span>
                                        <span class="counter-sign">k</span>
                                    </div>
                                    <h6 class="title">Happy Clients</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="counter-box">
                                <div class="icon">
                                    <img src="assets/img/icon/partner.svg" alt="">
                                </div>
                                <div class="counter-content">
                                    <div class="counter-number">
                                        <span class="counter" data-count="+" data-to="180" data-speed="3000">180</span>
                                        <span class="counter-sign">+</span>
                                    </div>
                                    <h6 class="title">Our Partners</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- counter area end -->


        <!-- flight area -->
        <div class="flight-area py-120">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 mx-auto wow fadeInDown" data-wow-duration="1s" data-wow-delay=".25s">
                        <div class="site-heading text-center">
                            <span class="site-title-tagline"><i class="far fa-plane"></i> Flight</span>
                            <h2 class="site-title">Our Most Popular Flights</h2>
                        </div>
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md-6 col-lg-4 col-xl-3">
                        <div class="flight-item wow fadeInUp" data-wow-duration="1s" data-wow-delay=".25s">
                            <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                            <div class="flight-img">
                                <img src="assets/img/flight/01.jpg" alt="">
                            </div>
                            <div class="flight-content">
                                <div class="flight-title">
                                    <div class="flight-title-info">
                                        <img src="assets/img/flight/airline-1.png" alt="">
                                        <h4><a href="#">New York <i class="far fa-exchange"></i> Los Angeles</a></h4>
                                    </div>
                                    <p class="flight-date"><i class="far fa-calendar-days"></i> Aug 01, 2025 - Aug 30,
                                        2025</p>
                                </div>
                                <div class="flight-bottom">
                                    <div class="flight-price">
                                        From <span>$300</span>
                                    </div>
                                    <div class="flight-text-btn">
                                        <a href="flight-single">See Details <i class="fas fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 col-xl-3">
                        <div class="flight-item wow fadeInUp" data-wow-duration="1s" data-wow-delay=".50s">
                            <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                            <span class="badge">Featured</span>
                            <div class="flight-img">
                                <img src="assets/img/flight/02.jpg" alt="">
                            </div>
                            <div class="flight-content">
                                <div class="flight-title">
                                    <div class="flight-title-info">
                                        <img src="assets/img/flight/airline-2.png" alt="">
                                        <h4><a href="#">New York <i class="far fa-exchange"></i> Los Angeles</a></h4>
                                    </div>
                                    <p class="flight-date"><i class="far fa-calendar-days"></i> Aug 01, 2025 - Aug 30,
                                        2025</p>
                                </div>
                                <div class="flight-bottom">
                                    <div class="flight-price">
                                        From <span>$450</span>
                                    </div>
                                    <div class="flight-text-btn">
                                        <a href="flight-single">See Details <i class="fas fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 col-xl-3">
                        <div class="flight-item wow fadeInUp" data-wow-duration="1s" data-wow-delay=".75s">
                            <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                            <div class="flight-img">
                                <img src="assets/img/flight/03.jpg" alt="">
                            </div>
                            <div class="flight-content">
                                <div class="flight-title">
                                    <div class="flight-title-info">
                                        <img src="assets/img/flight/airline-3.png" alt="">
                                        <h4><a href="#">New York <i class="far fa-exchange"></i> Los Angeles</a></h4>
                                    </div>
                                    <p class="flight-date"><i class="far fa-calendar-days"></i> Aug 01, 2025 - Aug 30,
                                        2025</p>
                                </div>
                                <div class="flight-bottom">
                                    <div class="flight-price">
                                        From <span>$520</span>
                                    </div>
                                    <div class="flight-text-btn">
                                        <a href="flight-single">See Details <i class="fas fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 col-xl-3">
                        <div class="flight-item wow fadeInUp" data-wow-duration="1s" data-wow-delay="1s">
                            <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                            <div class="flight-img">
                                <img src="assets/img/flight/04.jpg" alt="">
                            </div>
                            <div class="flight-content">
                                <div class="flight-title">
                                    <div class="flight-title-info">
                                        <img src="assets/img/flight/airline-4.png" alt="">
                                        <h4><a href="#">New York <i class="far fa-exchange"></i> Los Angeles</a></h4>
                                    </div>
                                    <p class="flight-date"><i class="far fa-calendar-days"></i> Aug 01, 2025 - Aug 30,
                                        2025</p>
                                </div>
                                <div class="flight-bottom">
                                    <div class="flight-price">
                                        From <span>$630</span>
                                    </div>
                                    <div class="flight-text-btn">
                                        <a href="flight-single">See Details <i class="fas fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 col-xl-3">
                        <div class="flight-item wow fadeInUp" data-wow-duration="1s" data-wow-delay=".25s">
                            <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                            <div class="flight-img">
                                <img src="assets/img/flight/05.jpg" alt="">
                            </div>
                            <div class="flight-content">
                                <div class="flight-title">
                                    <div class="flight-title-info">
                                        <img src="assets/img/flight/airline-5.png" alt="">
                                        <h4><a href="#">New York <i class="far fa-exchange"></i> Los Angeles</a></h4>
                                    </div>
                                    <p class="flight-date"><i class="far fa-calendar-days"></i> Aug 01, 2025 - Aug 30,
                                        2025</p>
                                </div>
                                <div class="flight-bottom">
                                    <div class="flight-price">
                                        From <span>$780</span>
                                    </div>
                                    <div class="flight-text-btn">
                                        <a href="flight-single">See Details <i class="fas fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 col-xl-3">
                        <div class="flight-item wow fadeInUp" data-wow-duration="1s" data-wow-delay=".50s">
                            <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                            <span class="badge badge-discount">25% Save</span>
                            <div class="flight-img">
                                <img src="assets/img/flight/06.jpg" alt="">
                            </div>
                            <div class="flight-content">
                                <div class="flight-title">
                                    <div class="flight-title-info">
                                        <img src="assets/img/flight/airline-6.png" alt="">
                                        <h4><a href="#">New York <i class="far fa-exchange"></i> Los Angeles</a></h4>
                                    </div>
                                    <p class="flight-date"><i class="far fa-calendar-days"></i> Aug 01, 2025 - Aug 30,
                                        2025</p>
                                </div>
                                <div class="flight-bottom">
                                    <div class="flight-price">
                                        From <span>$680</span>
                                    </div>
                                    <div class="flight-text-btn">
                                        <a href="flight-single">See Details <i class="fas fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 col-xl-3">
                        <div class="flight-item wow fadeInUp" data-wow-duration="1s" data-wow-delay=".75s">
                            <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                            <div class="flight-img">
                                <img src="assets/img/flight/07.jpg" alt="">
                            </div>
                            <div class="flight-content">
                                <div class="flight-title">
                                    <div class="flight-title-info">
                                        <img src="assets/img/flight/airline-1.png" alt="">
                                        <h4><a href="#">New York <i class="far fa-exchange"></i> Los Angeles</a></h4>
                                    </div>
                                    <p class="flight-date"><i class="far fa-calendar-days"></i> Aug 01, 2025 - Aug 30,
                                        2025</p>
                                </div>
                                <div class="flight-bottom">
                                    <div class="flight-price">
                                        From <span>$580</span>
                                    </div>
                                    <div class="flight-text-btn">
                                        <a href="flight-single">See Details <i class="fas fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 col-xl-3">
                        <div class="flight-item wow fadeInUp" data-wow-duration="1s" data-wow-delay="1s">
                            <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                            <div class="flight-img">
                                <img src="assets/img/flight/08.jpg" alt="">
                            </div>
                            <div class="flight-content">
                                <div class="flight-title">
                                    <div class="flight-title-info">
                                        <img src="assets/img/flight/airline-2.png" alt="">
                                        <h4><a href="#">New York <i class="far fa-exchange"></i> Los Angeles</a></h4>
                                    </div>
                                    <p class="flight-date"><i class="far fa-calendar-days"></i> Aug 01, 2025 - Aug 30,
                                        2025</p>
                                </div>
                                <div class="flight-bottom">
                                    <div class="flight-price">
                                        From <span>$490</span>
                                    </div>
                                    <div class="flight-text-btn">
                                        <a href="flight-single">See Details <i class="fas fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-3 wow fadeInUp" data-wow-duration="1s" data-wow-delay=".25s">
                        <a href="flight-grid" class="theme-btn">Discover More<i
                                class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <!-- flight area end -->


        <!-- hotel area -->
        <div class="hotel-area bg pt-80 pb-80">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 mx-auto wow fadeInDown" data-wow-duration="1s" data-wow-delay=".25s">
                        <div class="site-heading text-center">
                            <span class="site-title-tagline"><i class="far fa-plane"></i> Hotel</span>
                            <h2 class="site-title">Our Most Popular Hotels</h2>
                        </div>
                    </div>
                </div>
                <div class="hotel-slider owl-carousel owl-theme">
                    <div class="hotel-item">
                        <div class="hotel-img">
                            <span class="badge">Featured</span>
                            <img src="assets/img/hotel/01.jpg" alt="">
                            <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                        </div>
                        <div class="hotel-content">
                            <h4 class="hotel-title"><a href="hotel-single">Western Grant Park Hotel</a></h4>
                            <p><i class="far fa-location-dot"></i> 25/B Milford Road, New York</p>
                            <div class="hotel-rate">
                                <span class="badge"><i class="far fa-star"></i> 5.0</span>
                                <span class="hotel-rate-type">Excellent</span>
                                <span class="hotel-rate-review">(2.5k Reviews)</span>
                            </div>
                            <div class="hotel-bottom">
                                <div class="hotel-price">
                                    <span class="hotel-price-amount">$300 <span class="hotel-price-type">/Per
                                            Night</span></span>
                                </div>
                                <div class="hotel-text-btn">
                                    <a href="hotel-single">See Details <i class="fas fa-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="hotel-item">
                        <div class="hotel-img">
                            <img src="assets/img/hotel/02.jpg" alt="">
                            <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                        </div>
                        <div class="hotel-content">
                            <h4 class="hotel-title"><a href="hotel-single">Western Grant Park Hotel</a></h4>
                            <p><i class="far fa-location-dot"></i> 25/B Milford Road, New York</p>
                            <div class="hotel-rate">
                                <span class="badge"><i class="far fa-star"></i> 5.0</span>
                                <span class="hotel-rate-type">Excellent</span>
                                <span class="hotel-rate-review">(2.5k Reviews)</span>
                            </div>
                            <div class="hotel-bottom">
                                <div class="hotel-price">
                                    <span class="hotel-price-amount">$300 <span class="hotel-price-type">/Per
                                            Night</span></span>
                                </div>
                                <div class="hotel-text-btn">
                                    <a href="hotel-single">See Details <i class="fas fa-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="hotel-item">
                        <div class="hotel-img">
                            <span class="badge">Popular</span>
                            <img src="assets/img/hotel/03.jpg" alt="">
                            <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                        </div>
                        <div class="hotel-content">
                            <h4 class="hotel-title"><a href="hotel-single">Western Grant Park Hotel</a></h4>
                            <p><i class="far fa-location-dot"></i> 25/B Milford Road, New York</p>
                            <div class="hotel-rate">
                                <span class="badge"><i class="far fa-star"></i> 5.0</span>
                                <span class="hotel-rate-type">Excellent</span>
                                <span class="hotel-rate-review">(2.5k Reviews)</span>
                            </div>
                            <div class="hotel-bottom">
                                <div class="hotel-price">
                                    <span class="hotel-price-amount">$300 <span class="hotel-price-type">/Per
                                            Night</span></span>
                                </div>
                                <div class="hotel-text-btn">
                                    <a href="hotel-single">See Details <i class="fas fa-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="hotel-item">
                        <div class="hotel-img">
                            <span class="badge badge-discount">25% Save</span>
                            <img src="assets/img/hotel/04.jpg" alt="">
                            <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                        </div>
                        <div class="hotel-content">
                            <h4 class="hotel-title"><a href="hotel-single">Western Grant Park Hotel</a></h4>
                            <p><i class="far fa-location-dot"></i> 25/B Milford Road, New York</p>
                            <div class="hotel-rate">
                                <span class="badge"><i class="far fa-star"></i> 5.0</span>
                                <span class="hotel-rate-type">Excellent</span>
                                <span class="hotel-rate-review">(2.5k Reviews)</span>
                            </div>
                            <div class="hotel-bottom">
                                <div class="hotel-price">
                                    <span class="hotel-price-amount">$300 <span class="hotel-price-type">/Per
                                            Night</span></span>
                                </div>
                                <div class="hotel-text-btn">
                                    <a href="hotel-single">See Details <i class="fas fa-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="hotel-item">
                        <div class="hotel-img">
                            <img src="assets/img/hotel/05.jpg" alt="">
                            <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                        </div>
                        <div class="hotel-content">
                            <h4 class="hotel-title"><a href="hotel-single">Western Grant Park Hotel</a></h4>
                            <p><i class="far fa-location-dot"></i> 25/B Milford Road, New York</p>
                            <div class="hotel-rate">
                                <span class="badge"><i class="far fa-star"></i> 5.0</span>
                                <span class="hotel-rate-type">Excellent</span>
                                <span class="hotel-rate-review">(2.5k Reviews)</span>
                            </div>
                            <div class="hotel-bottom">
                                <div class="hotel-price">
                                    <span class="hotel-price-amount">$300 <span class="hotel-price-type">/Per
                                            Night</span></span>
                                </div>
                                <div class="hotel-text-btn">
                                    <a href="hotel-single">See Details <i class="fas fa-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="hotel-item">
                        <div class="hotel-img">
                            <img src="assets/img/hotel/06.jpg" alt="">
                            <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                        </div>
                        <div class="hotel-content">
                            <h4 class="hotel-title"><a href="hotel-single">Western Grant Park Hotel</a></h4>
                            <p><i class="far fa-location-dot"></i> 25/B Milford Road, New York</p>
                            <div class="hotel-rate">
                                <span class="badge"><i class="far fa-star"></i> 5.0</span>
                                <span class="hotel-rate-type">Excellent</span>
                                <span class="hotel-rate-review">(2.5k Reviews)</span>
                            </div>
                            <div class="hotel-bottom">
                                <div class="hotel-price">
                                    <span class="hotel-price-amount">$300 <span class="hotel-price-type">/Per
                                            Night</span></span>
                                </div>
                                <div class="hotel-text-btn">
                                    <a href="hotel-single">See Details <i class="fas fa-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- hotel area end -->


        <!-- video-area -->
        <div class="video-area py-120">
            <div class="container-fluid pe-0 p-lg-0">
                <div class="col-lg-10 ms-lg-auto">
                    <div class="row g-4 align-items-center">
                        <div class="col-md-8 col-lg-4 wow fadeInLeft" data-wow-delay=".25s">
                            <div class="site-heading mb-3">
                                <span class="site-title-tagline"><i class="far fa-plane"></i> Our Video</span>
                                <h2 class="site-title">
                                    Let's Check Our <span>Latest Update</span> And Video
                                </h2>
                            </div>
                            <p class="about-text">
                                There are many variations of passages available but the majority have suffered
                                alteration in some form injected humour if you are going to use passage you need sure
                                there
                                isn't anything look even slightly believable.
                            </p>
                            <a href="about" class="theme-btn mt-30">Learn More<i
                                    class="fas fa-arrow-circle-right"></i></a>
                        </div>
                        <div class="col-lg-8 wow fadeInRight" data-wow-delay=".25s">
                            <div class="video-content" style="background-image: url(assets/img/video/01.jpg);">
                                <div class="row align-items-center">
                                    <div class="col-lg-12">
                                        <div class="video-wrapper">
                                            <a class="play-btn popup-youtube"
                                                href="https://www.youtube.com/watch?v=jLS3DrTJrpI">
                                                <i class="fas fa-play"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- video-area end -->


        <!-- banner area -->
        <div class="banner-area bg pt-50 pb-50">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 mx-auto wow fadeInDown" data-wow-duration="1s" data-wow-delay=".25s">
                        <div class="site-heading text-center">
                            <span class="site-title-tagline"><i class="far fa-plane"></i> Offers</span>
                            <h2 class="site-title">Let's Check Exclusive Offers</h2>
                        </div>
                    </div>
                </div>
                <div class="banner-slider owl-carousel owl-theme">
                    <div class="banner-item">
                        <div class="banner-img">
                            <img src="assets/img/banner/01.jpg" alt="">
                        </div>
                        <div class="banner-content">
                            <h6>Get Upto <span>70%</span> Discount!</h6>
                            <p>It is a long established fact that reader distracted.</p>
                            <a href="contact" class="theme-btn">Learn More<i
                                    class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="banner-item">
                        <div class="banner-img">
                            <img src="assets/img/banner/02.jpg" alt="">
                        </div>
                        <div class="banner-content">
                            <h6>Get Upto <span>70%</span> Discount!</h6>
                            <p>It is a long established fact that reader distracted.</p>
                            <a href="contact" class="theme-btn">Learn More<i
                                    class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="banner-item">
                        <div class="banner-img">
                            <img src="assets/img/banner/03.jpg" alt="">
                        </div>
                        <div class="banner-content">
                            <h6>Get Upto <span>70%</span> Discount!</h6>
                            <p>It is a long established fact that reader distracted.</p>
                            <a href="contact" class="theme-btn">Learn More<i
                                    class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="banner-item">
                        <div class="banner-img">
                            <img src="assets/img/banner/04.jpg" alt="">
                        </div>
                        <div class="banner-content">
                            <h6>Get Upto <span>70%</span> Discount!</h6>
                            <p>It is a long established fact that reader distracted.</p>
                            <a href="contact" class="theme-btn">Learn More<i
                                    class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- banner area end -->


        <!-- tour area -->
        <div class="tour-area py-120">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12 mx-auto wow fadeInDown" data-wow-duration="1s" data-wow-delay=".25s">
                        <div class="site-heading-inline mb-50">
                            <div>
                                <span class="site-title-tagline"><i class="far fa-plane"></i> Tours</span>
                                <h2 class="site-title">Our Most Popular Tours</h2>
                            </div>
                            <div class="filter-controls">
                                <ul class="filter-btns">
                                    <li class="active" data-filter="*">All Tour</li>
                                    <li data-filter=".cat1">Historical</li>
                                    <li data-filter=".cat2">Weekend Trip</li>
                                    <li data-filter=".cat3">Special Tour</li>
                                    <li data-filter=".cat4">Holiday Tour</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row filter-box">
                    <div class="col-md-6 col-lg-4 col-xl-3 filter-item cat1">
                        <div class="tour-item">
                            <div class="tour-img">
                                <span class="badge badge-discount">25% Save</span>
                                <img src="assets/img/tour/01.jpg" alt="">
                                <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                            </div>
                            <div class="tour-content">
                                <div class="tour-top">
                                    <a href="#" class="tour-category"><i class="far fa-tag"></i> Historical</a>
                                    <span class="tour-place"><i class="far fa-earth-americas"></i> 10 Places</span>
                                </div>
                                <h4 class="tour-title"><a href="tour-single">Canada Historical Tour</a></h4>
                                <p><i class="far fa-location-dot"></i> 25/B Milford Road, New York</p>
                                <div class="hotel-rate">
                                    <span class="badge"><i class="far fa-star"></i> 5.0</span>
                                    <span class="hotel-rate-type">Excellent</span>
                                    <span class="hotel-rate-review">(2.5k Reviews)</span>
                                </div>
                                <div class="tour-duration"><i class="far fa-clock"></i> 5 Days 4 Nights Trip</div>
                                <div class="tour-bottom">
                                    <div class="tour-price">
                                        From <span>$500</span>
                                    </div>
                                    <div class="tour-text-btn">
                                        <a href="tour-single">See More <i class="fas fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 col-xl-3 filter-item cat2">
                        <div class="tour-item">
                            <div class="tour-img">
                                <img src="assets/img/tour/02.jpg" alt="">
                                <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                            </div>
                            <div class="tour-content">
                                <div class="tour-top">
                                    <a href="#" class="tour-category"><i class="far fa-tag"></i> Weekend</a>
                                    <span class="tour-place"><i class="far fa-earth-americas"></i> 10 Places</span>
                                </div>
                                <h4 class="tour-title"><a href="tour-single">Canada Weekend Tour</a></h4>
                                <p><i class="far fa-location-dot"></i> 25/B Milford Road, New York</p>
                                <div class="hotel-rate">
                                    <span class="badge"><i class="far fa-star"></i> 5.0</span>
                                    <span class="hotel-rate-type">Excellent</span>
                                    <span class="hotel-rate-review">(2.5k Reviews)</span>
                                </div>
                                <div class="tour-duration"><i class="far fa-clock"></i> 5 Days 4 Nights Trip</div>
                                <div class="tour-bottom">
                                    <div class="tour-price">
                                        From <span>$500</span>
                                    </div>
                                    <div class="tour-text-btn">
                                        <a href="tour-single">See More <i class="fas fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 col-xl-3 filter-item cat3">
                        <div class="tour-item">
                            <div class="tour-img">
                                <img src="assets/img/tour/03.jpg" alt="">
                                <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                            </div>
                            <div class="tour-content">
                                <div class="tour-top">
                                    <a href="#" class="tour-category"><i class="far fa-tag"></i> Special</a>
                                    <span class="tour-place"><i class="far fa-earth-americas"></i> 10 Places</span>
                                </div>
                                <h4 class="tour-title"><a href="tour-single">Canada Special Tour</a></h4>
                                <p><i class="far fa-location-dot"></i> 25/B Milford Road, New York</p>
                                <div class="hotel-rate">
                                    <span class="badge"><i class="far fa-star"></i> 5.0</span>
                                    <span class="hotel-rate-type">Excellent</span>
                                    <span class="hotel-rate-review">(2.5k Reviews)</span>
                                </div>
                                <div class="tour-duration"><i class="far fa-clock"></i> 5 Days 4 Nights Trip</div>
                                <div class="tour-bottom">
                                    <div class="tour-price">
                                        From <span>$500</span>
                                    </div>
                                    <div class="tour-text-btn">
                                        <a href="tour-single">See More <i class="fas fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 col-xl-3 filter-item cat4">
                        <div class="tour-item">
                            <div class="tour-img">
                                <img src="assets/img/tour/04.jpg" alt="">
                                <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                            </div>
                            <div class="tour-content">
                                <div class="tour-top">
                                    <a href="#" class="tour-category"><i class="far fa-tag"></i> Holiday</a>
                                    <span class="tour-place"><i class="far fa-earth-americas"></i> 10 Places</span>
                                </div>
                                <h4 class="tour-title"><a href="tour-single">Canada Holiday Tour</a></h4>
                                <p><i class="far fa-location-dot"></i> 25/B Milford Road, New York</p>
                                <div class="hotel-rate">
                                    <span class="badge"><i class="far fa-star"></i> 5.0</span>
                                    <span class="hotel-rate-type">Excellent</span>
                                    <span class="hotel-rate-review">(2.5k Reviews)</span>
                                </div>
                                <div class="tour-duration"><i class="far fa-clock"></i> 5 Days 4 Nights Trip</div>
                                <div class="tour-bottom">
                                    <div class="tour-price">
                                        From <span>$500</span>
                                    </div>
                                    <div class="tour-text-btn">
                                        <a href="tour-single">See More <i class="fas fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 col-xl-3 filter-item cat2">
                        <div class="tour-item">
                            <div class="tour-img">
                                <span class="badge">Featured</span>
                                <img src="assets/img/tour/05.jpg" alt="">
                                <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                            </div>
                            <div class="tour-content">
                                <div class="tour-top">
                                    <a href="#" class="tour-category"><i class="far fa-tag"></i> Weekend</a>
                                    <span class="tour-place"><i class="far fa-earth-americas"></i> 10 Places</span>
                                </div>
                                <h4 class="tour-title"><a href="tour-single">Canada Weekend Tour</a></h4>
                                <p><i class="far fa-location-dot"></i> 25/B Milford Road, New York</p>
                                <div class="hotel-rate">
                                    <span class="badge"><i class="far fa-star"></i> 5.0</span>
                                    <span class="hotel-rate-type">Excellent</span>
                                    <span class="hotel-rate-review">(2.5k Reviews)</span>
                                </div>
                                <div class="tour-duration"><i class="far fa-clock"></i> 5 Days 4 Nights Trip</div>
                                <div class="tour-bottom">
                                    <div class="tour-price">
                                        From <span>$500</span>
                                    </div>
                                    <div class="tour-text-btn">
                                        <a href="tour-single">See More <i class="fas fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 col-xl-3 filter-item cat4">
                        <div class="tour-item">
                            <div class="tour-img">
                                <img src="assets/img/tour/06.jpg" alt="">
                                <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                            </div>
                            <div class="tour-content">
                                <div class="tour-top">
                                    <a href="#" class="tour-category"><i class="far fa-tag"></i> Holiday</a>
                                    <span class="tour-place"><i class="far fa-earth-americas"></i> 10 Places</span>
                                </div>
                                <h4 class="tour-title"><a href="tour-single">Canada Holiday Tour</a></h4>
                                <p><i class="far fa-location-dot"></i> 25/B Milford Road, New York</p>
                                <div class="hotel-rate">
                                    <span class="badge"><i class="far fa-star"></i> 5.0</span>
                                    <span class="hotel-rate-type">Excellent</span>
                                    <span class="hotel-rate-review">(2.5k Reviews)</span>
                                </div>
                                <div class="tour-duration"><i class="far fa-clock"></i> 5 Days 4 Nights Trip</div>
                                <div class="tour-bottom">
                                    <div class="tour-price">
                                        From <span>$500</span>
                                    </div>
                                    <div class="tour-text-btn">
                                        <a href="tour-single">See More <i class="fas fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 col-xl-3 filter-item cat3">
                        <div class="tour-item">
                            <div class="tour-img">
                                <img src="assets/img/tour/07.jpg" alt="">
                                <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                            </div>
                            <div class="tour-content">
                                <div class="tour-top">
                                    <a href="#" class="tour-category"><i class="far fa-tag"></i> Special</a>
                                    <span class="tour-place"><i class="far fa-earth-americas"></i> 10 Places</span>
                                </div>
                                <h4 class="tour-title"><a href="tour-single">Canada Special Tour</a></h4>
                                <p><i class="far fa-location-dot"></i> 25/B Milford Road, New York</p>
                                <div class="hotel-rate">
                                    <span class="badge"><i class="far fa-star"></i> 5.0</span>
                                    <span class="hotel-rate-type">Excellent</span>
                                    <span class="hotel-rate-review">(2.5k Reviews)</span>
                                </div>
                                <div class="tour-duration"><i class="far fa-clock"></i> 5 Days 4 Nights Trip</div>
                                <div class="tour-bottom">
                                    <div class="tour-price">
                                        From <span>$500</span>
                                    </div>
                                    <div class="tour-text-btn">
                                        <a href="tour-single">See More <i class="fas fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 col-xl-3 filter-item cat1">
                        <div class="tour-item">
                            <div class="tour-img">
                                <span class="badge badge-discount">25% Save</span>
                                <img src="assets/img/tour/08.jpg" alt="">
                                <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                            </div>
                            <div class="tour-content">
                                <div class="tour-top">
                                    <a href="#" class="tour-category"><i class="far fa-tag"></i> Historical</a>
                                    <span class="tour-place"><i class="far fa-earth-americas"></i> 10 Places</span>
                                </div>
                                <h4 class="tour-title"><a href="tour-single">Canada Historical Tour</a></h4>
                                <p><i class="far fa-location-dot"></i> 25/B Milford Road, New York</p>
                                <div class="hotel-rate">
                                    <span class="badge"><i class="far fa-star"></i> 5.0</span>
                                    <span class="hotel-rate-type">Excellent</span>
                                    <span class="hotel-rate-review">(2.5k Reviews)</span>
                                </div>
                                <div class="tour-duration"><i class="far fa-clock"></i> 5 Days 4 Nights Trip</div>
                                <div class="tour-bottom">
                                    <div class="tour-price">
                                        From <span>$500</span>
                                    </div>
                                    <div class="tour-text-btn">
                                        <a href="tour-single">See More <i class="fas fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- tour area end -->


        <!-- cta-area -->
        <div class="cta-area">
            <div class="container">
                <div class="cta-wrapper">
                    <div class="col-md-10 col-lg-8 col-xl-6 mx-auto">
                        <div class="cta-content">
                            <div class="cta-text">
                                <h1>First Booking <span>Get 70%</span> Discount!</h1>
                                <p>It is a long established fact that a reader will be distracted by the readable
                                    content web page editors now use of a page when looking at its layout.</p>
                            </div>
                            <a href="contact" class="theme-btn mt-20">Book Now <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="cta-img">
                        <img class="w-100" src="assets/img/cta/01.jpg" alt="">
                    </div>
                </div>
            </div>
        </div>
        <!-- cta-area end -->


        <!-- choose area -->
        <div class="choose-area py-120">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 mx-auto wow fadeInDown" data-wow-duration="1s" data-wow-delay=".25s">
                        <div class="site-heading text-center">
                            <span class="site-title-tagline"><i class="far fa-plane"></i> Why Choose Us</span>
                            <h2 class="site-title">Discover Beautiful Place With Us</h2>
                        </div>
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-lg-6 wow fadeInLeft" data-wow-duration="1s" data-wow-delay=".25s">
                        <div class="choose-item">
                            <span class="count">01</span>
                            <div class="icon">
                                <img src="assets/img/icon/safety.svg" alt="">
                            </div>
                            <div class="content">
                                <h4>Safety And Trust</h4>
                                <p>It is a long established fact that a reader will be distracted by the readable
                                    content of a page when looking at its layout.</p>
                            </div>
                        </div>
                        <div class="choose-item">
                            <span class="count">02</span>
                            <div class="icon">
                                <img src="assets/img/icon/price.svg" alt="">
                            </div>
                            <div class="content">
                                <h4>100% Price Transparency</h4>
                                <p>It is a long established fact that a reader will be distracted by the readable
                                    content of a page when looking at its layout.</p>
                            </div>
                        </div>
                        <div class="choose-item">
                            <span class="count">03</span>
                            <div class="icon">
                                <img src="assets/img/icon/booking-confirm.svg" alt="">
                            </div>
                            <div class="content">
                                <h4>Travel With More Confidence</h4>
                                <p>It is a long established fact that a reader will be distracted by the readable
                                    content of a page when looking at its layout.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 wow fadeInRight" data-wow-duration="1s" data-wow-delay=".25s">
                        <div class="choose-img">
                            <img class="shape" src="assets/img/shape/04.png" alt="">
                            <img class="img-1" src="assets/img/choose/01.jpg" alt="">
                            <img class="img-2" src="assets/img/choose/02.jpg" alt="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- choose area end -->


        <!-- car area -->
        <div class="car-area bg pt-80 pb-80">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 mx-auto wow fadeInDown" data-wow-duration="1s" data-wow-delay=".25s">
                        <div class="site-heading text-center">
                            <span class="site-title-tagline"><i class="far fa-plane"></i> Car Rental</span>
                            <h2 class="site-title">Our Most Popular Car Rentals</h2>
                        </div>
                    </div>
                </div>
                <div class="car-slider owl-carousel owl-theme">
                    <div class="car-item">
                        <div class="car-img">
                            <span class="badge">Featured</span>
                            <img src="assets/img/car/01.jpg" alt="">
                            <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                        </div>
                        <div class="car-content">
                            <h4 class="car-title"><a href="car-single">Toyota Corolla Car</a></h4>
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
                                    <a href="car-single">See Details <i class="fas fa-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="car-item">
                        <div class="car-img">
                            <img src="assets/img/car/02.jpg" alt="">
                            <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                        </div>
                        <div class="car-content">
                            <h4 class="car-title"><a href="car-single">Toyota Corolla Car</a></h4>
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
                                    <a href="car-single">See Details <i class="fas fa-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="car-item">
                        <div class="car-img">
                            <span class="badge">Popular</span>
                            <img src="assets/img/car/03.jpg" alt="">
                            <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                        </div>
                        <div class="car-content">
                            <h4 class="car-title"><a href="car-single">Toyota Corolla Car</a></h4>
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
                                    <span class="car-price-amount">$50 <span class="car-price-type">/Per
                                            Day</span></span>
                                </div>
                                <div class="car-text-btn">
                                    <a href="car-single">See Details <i class="fas fa-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="car-item">
                        <div class="car-img">
                            <span class="badge badge-discount">25% Save</span>
                            <img src="assets/img/car/04.jpg" alt="">
                            <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                        </div>
                        <div class="car-content">
                            <h4 class="car-title"><a href="car-single">Toyota Corolla Car</a></h4>
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
                                    <span class="car-price-amount">$70 <span class="car-price-type">/Per
                                            Day</span></span>
                                </div>
                                <div class="car-text-btn">
                                    <a href="car-single">See Details <i class="fas fa-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="car-item">
                        <div class="car-img">
                            <img src="assets/img/car/05.jpg" alt="">
                            <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                        </div>
                        <div class="car-content">
                            <h4 class="car-title"><a href="car-single">Toyota Corolla Car</a></h4>
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
                                    <span class="car-price-amount">$65 <span class="car-price-type">/Per
                                            Day</span></span>
                                </div>
                                <div class="car-text-btn">
                                    <a href="car-single">See Details <i class="fas fa-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="car-item">
                        <div class="car-img">
                            <img src="assets/img/car/06.jpg" alt="">
                            <a href="#" class="add-wishlist"><i class="far fa-heart"></i></a>
                        </div>
                        <div class="car-content">
                            <h4 class="car-title"><a href="car-single">Toyota Corolla Car</a></h4>
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
                                    <span class="car-price-amount">$58 <span class="car-price-type">/Per
                                            Day</span></span>
                                </div>
                                <div class="car-text-btn">
                                    <a href="car-single">See Details <i class="fas fa-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- car area end -->


        <!-- team-area -->
        <div class="team-area py-120">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 mx-auto wow fadeInDown" data-wow-duration="1s" data-wow-delay=".25s">
                        <div class="site-heading text-center">
                            <span class="site-title-tagline"><i class="far fa-plane"></i> Our Team</span>
                            <h2 class="site-title">Meet With Our Experts Team</h2>
                        </div>
                    </div>
                </div>
                <div class="row g-5">
                    <div class="col-md-6 col-lg-4 col-xl-3">
                        <div class="team-item wow fadeInUp" data-wow-duration="1s" data-wow-delay=".25s">
                            <div class="team-img">
                                <img src="assets/img/team/01.jpg" alt="thumb">
                            </div>
                            <div class="team-content">
                                <div class="team-bio">
                                    <h5><a href="#">Edna Craig</a></h5>
                                    <span>Head of Design</span>
                                </div>
                                <div class="team-social">
                                    <ul class="team-social-btn">
                                        <li><span><i class="far fa-share-alt"></i></span></li>
                                        <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                                        <li><a href="#"><i class="fab fa-x-twitter"></i></a></li>
                                        <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                                        <li><a href="#"><i class="fab fa-linkedin-in"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 col-xl-3">
                        <div class="team-item wow fadeInUp" data-wow-duration="1s" data-wow-delay=".50s">
                            <div class="team-img">
                                <img src="assets/img/team/02.jpg" alt="thumb">
                            </div>
                            <div class="team-content">
                                <div class="team-bio">
                                    <h5><a href="#">Jeffrey Cox</a></h5>
                                    <span>Founder & Director</span>
                                </div>
                                <div class="team-social">
                                    <ul class="team-social-btn">
                                        <li><span><i class="far fa-share-alt"></i></span></li>
                                        <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                                        <li><a href="#"><i class="fab fa-x-twitter"></i></a></li>
                                        <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                                        <li><a href="#"><i class="fab fa-linkedin-in"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 col-xl-3">
                        <div class="team-item wow fadeInUp" data-wow-duration="1s" data-wow-delay=".75s">
                            <div class="team-img">
                                <img src="assets/img/team/03.jpg" alt="thumb">
                            </div>
                            <div class="team-content">
                                <div class="team-bio">
                                    <h5><a href="#">Audrey Gadis</a></h5>
                                    <span>Sales Support</span>
                                </div>
                                <div class="team-social">
                                    <ul class="team-social-btn">
                                        <li><span><i class="far fa-share-alt"></i></span></li>
                                        <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                                        <li><a href="#"><i class="fab fa-x-twitter"></i></a></li>
                                        <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                                        <li><a href="#"><i class="fab fa-linkedin-in"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 col-xl-3">
                        <div class="team-item wow fadeInUp" data-wow-duration="1s" data-wow-delay="1s">
                            <div class="team-img">
                                <img src="assets/img/team/04.jpg" alt="thumb">
                            </div>
                            <div class="team-content">
                                <div class="team-bio">
                                    <h5><a href="#">Rodger Garza</a></h5>
                                    <span>Account Manager</span>
                                </div>
                                <div class="team-social">
                                    <ul class="team-social-btn">
                                        <li><span><i class="far fa-share-alt"></i></span></li>
                                        <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                                        <li><a href="#"><i class="fab fa-x-twitter"></i></a></li>
                                        <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                                        <li><a href="#"><i class="fab fa-linkedin-in"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- team-area end -->


        <!-- download area -->
        <div class="download-area pb-120">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="download-img wow fadeInUp" data-wow-duration="1s" data-wow-delay=".25s">
                            <img src="assets/img/download/01.png" alt="">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="download-content wow fadeInDown" data-wow-duration="1s" data-wow-delay=".25s">
                            <div class="site-heading mb-0">
                                <span class="site-title-tagline"><i class="far fa-plane"></i> Download</span>
                                <h2 class="site-title">Tavelo Android and IOS App is Available! Download Now</h2>
                                <p>There are many variations of passages contrary to popular belief available the but
                                    the majority have suffered alteration in some form by injected humour.</p>
                                <ul class="download-feature">
                                    <li><i class="far fa-check"></i> At vero accusamus iusto odio ducimus blanditii</li>
                                    <li><i class="far fa-check"></i> Sed perspiciatis unde omnis iste natu sit
                                        voluptatem</li>
                                    <li><i class="far fa-check"></i> Nor again is anyone who loves pursues desires</li>
                                </ul>
                                <div class="download-link">
                                    <a href="#"><img src="assets/img/download/google-play.png" alt=""></a>
                                    <a href="#"><img src="assets/img/download/app-store.png" alt=""></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- download area end -->


        <!-- testimonial area -->
        <div class="testimonial-area ts-bg py-120">
            <div class="shadow-text">tavelo</div>
            <div class="container pb-30">
                <div class="row">
                    <div class="col-lg-6 mx-auto wow fadeInDown" data-wow-duration="1s" data-wow-delay=".25s">
                        <div class="site-heading text-center mb-4">
                            <span class="site-title-tagline"><i class="far fa-plane"></i> Testimonials</span>
                            <h2 class="site-title text-white">What Our Customers Are Saying About Us?</h2>
                        </div>
                    </div>
                </div>
                <div class="testimonial-slider owl-carousel owl-theme wow fadeInUp" data-wow-duration="1s"
                    data-wow-delay=".25s">
                    <div class="testimonial-single">
                        <div class="testimonial-content">
                            <div class="testimonial-author-img">
                                <img src="assets/img/testimonial/01.jpg" alt="">
                            </div>
                        </div>
                        <div class="testimonial-quote">
                            <span class="count">01</span>
                            <div class="testimonial-author-info">
                                <h4>Diana Carter</h4>
                                <p>Our Clients</p>
                            </div>
                            <p>
                                There are many variations passages of available but to the majority have
                                suffered for the alteration in some form injected humour words which look even slig
                                believable.
                            </p>
                            <div class="testimonial-quote-icon">
                                <img src="assets/img/icon/quote.svg" alt="">
                            </div>
                            <div class="testimonial-rate">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-single">
                        <div class="testimonial-content">
                            <div class="testimonial-author-img">
                                <img src="assets/img/testimonial/02.jpg" alt="">
                            </div>
                        </div>
                        <div class="testimonial-quote">
                            <span class="count">02</span>
                            <div class="testimonial-author-info">
                                <h4>Brandon Wigfall</h4>
                                <p>Our Clients</p>
                            </div>
                            <p>
                                There are many variations passages of available but to the majority have
                                suffered for the alteration in some form injected humour words which look even slig
                                believable.
                            </p>
                            <div class="testimonial-quote-icon">
                                <img src="assets/img/icon/quote.svg" alt="">
                            </div>
                            <div class="testimonial-rate">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-single">
                        <div class="testimonial-content">
                            <div class="testimonial-author-img">
                                <img src="assets/img/testimonial/03.jpg" alt="">
                            </div>
                        </div>
                        <div class="testimonial-quote">
                            <span class="count">03</span>
                            <div class="testimonial-author-info">
                                <h4>Sylvia Green</h4>
                                <p>Our Clients</p>
                            </div>
                            <p>
                                There are many variations passages of available but to the majority have
                                suffered for the alteration in some form injected humour words which look even slig
                                believable.
                            </p>
                            <div class="testimonial-quote-icon">
                                <img src="assets/img/icon/quote.svg" alt="">
                            </div>
                            <div class="testimonial-rate">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-single">
                        <div class="testimonial-content">
                            <div class="testimonial-author-img">
                                <img src="assets/img/testimonial/04.jpg" alt="">
                            </div>
                        </div>
                        <div class="testimonial-quote">
                            <span class="count">04</span>
                            <div class="testimonial-author-info">
                                <h4>Miguel Woodworth</h4>
                                <p>Our Clients</p>
                            </div>
                            <p>
                                There are many variations passages of available but to the majority have
                                suffered for the alteration in some form injected humour words which look even slig
                                believable.
                            </p>
                            <div class="testimonial-quote-icon">
                                <img src="assets/img/icon/quote.svg" alt="">
                            </div>
                            <div class="testimonial-rate">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- testimonial area end -->


        <!-- blog area -->
        <div class="blog-area pt-120 pb-80">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 mx-auto wow fadeInDown" data-wow-duration="1s" data-wow-delay=".25s">
                        <div class="site-heading text-center">
                            <span class="site-title-tagline"><i class="far fa-plane"></i> Our Blog</span>
                            <h2 class="site-title">Our Latest Blog & News</h2>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 col-lg-4">
                        <div class="blog-item wow fadeInUp" data-wow-duration="1s" data-wow-delay=".25s">
                            <span class="blog-date">August 25, 2025</span>
                            <div class="blog-item-img">
                                <img src="assets/img/blog/01.jpg" alt="Thumb">
                            </div>
                            <div class="blog-item-info">
                                <div class="blog-item-meta">
                                    <ul>
                                        <li><a href="#"><i class="far fa-user-circle"></i> By Alicia Davis</a></li>
                                        <li><a href="#"><i class="far fa-comments"></i> 25.5k Comments</a></li>
                                    </ul>
                                </div>
                                <h4 class="blog-title">
                                    <a href="blog-single">There are many variations available For majority in some injected
                                        humour</a>
                                </h4>
                                <a class="theme-btn mt-3" href="blog-single">Read More <i
                                        class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="blog-item wow fadeInUp" data-wow-duration="1s" data-wow-delay=".50s">
                            <span class="blog-date">August 27, 2025</span>
                            <div class="blog-item-img">
                                <img src="assets/img/blog/02.jpg" alt="Thumb">
                            </div>
                            <div class="blog-item-info">
                                <div class="blog-item-meta">
                                    <ul>
                                        <li><a href="#"><i class="far fa-user-circle"></i> By Alicia Davis</a></li>
                                        <li><a href="#"><i class="far fa-comments"></i> 25.5k Comments</a></li>
                                    </ul>
                                </div>
                                <h4 class="blog-title">
                                    <a href="blog-single">Established fact that reader distract will by the readable content</a>
                                </h4>
                                <a class="theme-btn mt-3" href="blog-single">Read More <i
                                        class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="blog-item wow fadeInUp" data-wow-duration="1s" data-wow-delay=".75s">
                            <span class="blog-date">August 30, 2025</span>
                            <div class="blog-item-img">
                                <img src="assets/img/blog/03.jpg" alt="Thumb">
                            </div>
                            <div class="blog-item-info">
                                <div class="blog-item-meta">
                                    <ul>
                                        <li><a href="#"><i class="far fa-user-circle"></i> By Alicia Davis</a></li>
                                        <li><a href="#"><i class="far fa-comments"></i> 25.5k Comments</a></li>
                                    </ul>
                                </div>
                                <h4 class="blog-title">
                                    <a href="blog-single">Contrary to popular belief simply text random roots piece of
                                        classical</a>
                                </h4>
                                <a class="theme-btn mt-3" href="blog-single">Read More <i
                                        class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- blog area end -->


        <!-- partner area -->
        <div class="partner-area">
            <div class="col-lg-8">
                <div class="partner-wrap partner-negative">
                    <div class="col-lg-11 mx-auto">
                        <div class="partner-slider owl-carousel owl-theme">
                            <img src="assets/img/partner/01.png" alt="thumb">
                            <img src="assets/img/partner/02.png" alt="thumb">
                            <img src="assets/img/partner/03.png" alt="thumb">
                            <img src="assets/img/partner/04.png" alt="thumb">
                            <img src="assets/img/partner/01.png" alt="thumb">
                            <img src="assets/img/partner/02.png" alt="thumb">
                            <img src="assets/img/partner/03.png" alt="thumb">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- partner area end -->

    </main>


    <!-- footer area -->

    @include('frontend.layout.footer')

    <!-- footer area end -->


    <!-- scroll-top -->
    <a href="#" id="scroll-top"><i class="far fa-angle-up"></i></a>
    <!-- scroll-top end -->


    <!-- js -->
    <script data-cfasync="false" src="../cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script><script src="assets/js/jquery-3.7.1.min.js"></script>
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

<script defer src="https://static.cloudflareinsights.com/beacon.min.js/vcd15cbe7772f49c399c6a5babf22c1241717689176015" integrity="sha512-ZpsOmlRQV6y907TI0dKBHq9Md29nnaEIPlkf84rnaERnq6zvWvPUqr2ft8M1aS28oN72PdrCzSjY4U6VaAw1EQ==" data-cf-beacon='{"version":"2024.11.0","token":"1190e059c5bc497bafd35e121aae37b1","r":1,"server_timing":{"name":{"cfCacheStatus":true,"cfEdge":true,"cfExtPri":true,"cfL4":true,"cfOrigin":true,"cfSpeedBrain":true},"location_startswith":null}}' crossorigin="anonymous"></script>
</body>


</html>