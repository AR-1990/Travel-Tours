<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FrontController extends Controller
{
    public function index() {
        return view('index');
    }

    public function notFound() { return view('404'); }
    public function about() { return view('about'); }

    public function activityAdd() { return view('activity-add'); }
    public function activityBooking() { return view('activity-booking'); }
    public function activityFullWidth() { return view('activity-full-width'); }
    public function activityGrid() { return view('activity-grid'); }
    public function activityList() { return view('activity-list'); }
    public function activitySearchResult() { return view('activity-search-result'); }
    public function activitySingle() { return view('activity-single'); }

    public function becomeExpert() { return view('become-expert'); }
    public function blog() { return view('blog'); }
    public function blogSingle() { return view('blog-single'); }

    public function bookingConfirm() { return view('booking-confirm'); }

    public function carAdd() { return view('car-add'); }
    public function carBooking() { return view('car-booking'); }
    public function carFullWidth() { return view('car-full-width'); }
    public function carGrid() { return view('car-grid'); }
    public function carList() { return view('car-list'); }
    public function carSearchResult() { return view('car-search-result'); }
    public function carSingle() { return view('car-single'); }

    public function career() { return view('career'); }
    public function careerSingle() { return view('career-single'); }

    public function cart() { return view('cart'); }
    public function checkout() { return view('checkout'); }
    public function comingSoon() { return view('coming-soon'); }
    public function contact() { return view('contact'); }

    public function cruiseAdd() { return view('cruise-add'); }
    public function cruiseBooking() { return view('cruise-booking'); }
    public function cruiseFullWidth() { return view('cruise-full-width'); }
    public function cruiseGrid() { return view('cruise-grid'); }
    public function cruiseList() { return view('cruise-list'); }
    public function cruiseSearchResult() { return view('cruise-search-result'); }
    public function cruiseSingle() { return view('cruise-single'); }

    public function dashboard() { return view('dashboard'); }
    public function destination() { return view('destination'); }
    public function faq() { return view('faq'); }

    public function flightAdd() { return view('flight-add'); }
    public function flightBooking() { return view('flight-booking'); }
    public function flightFullWidth() { return view('flight-full-width'); }
    public function flightGrid() { return view('flight-grid'); }
    public function flightList() { return view('flight-list'); }
    public function flightSearchResult() { return view('flight-search-result'); }
    public function flightSingle() { return view('flight-single'); }

    public function forgotPassword() { return view('forgot-password'); }
    public function gallery() { return view('gallery'); }

    public function hotelAdd() { return view('hotel-add'); }
    public function hotelBooking() { return view('hotel-booking'); }
    public function hotelFullWidth() { return view('hotel-full-width'); }
    public function hotelGrid() { return view('hotel-grid'); }
    public function hotelList() { return view('hotel-list'); }
    public function hotelRoomAdd() { return view('hotel-room-add'); }
    public function hotelRoomFullWidth() { return view('hotel-room-full-width'); }
    public function hotelRoomGrid() { return view('hotel-room-grid'); }
    public function hotelRoomList() { return view('hotel-room-list'); }
    public function hotelRoomSearchResult() { return view('hotel-room-search-result'); }
    public function hotelRoomSingle() { return view('hotel-room-single'); }
    public function hotelSearchResult() { return view('hotel-search-result'); }
    public function hotelSingle() { return view('hotel-single'); }

    public function login() { return view('login'); }
    public function pricing() { return view('pricing'); }
    public function privacy() { return view('privacy'); }

    public function profile() { return view('profile'); }
    public function profileBookingHistory() { return view('profile-booking-history'); }
    public function profileBooking() { return view('profile-booking'); }
    public function profileListing() { return view('profile-listing'); }
    public function profileMessage() { return view('profile-message'); }
    public function profileNotification() { return view('profile-notification'); }
    public function profileSetting() { return view('profile-setting'); }
    public function profileWallet() { return view('profile-wallet'); }
    public function profileWishlist() { return view('profile-wishlist'); }

    public function register() { return view('register'); }

    public function service() { return view('service'); }
    public function serviceSingle() { return view('service-single'); }

    public function team() { return view('team'); }
    public function terms() { return view('terms'); }
    public function testimonial() { return view('testimonial'); }

    public function tourAdd() { return view('tour-add'); }
    public function tourBooking() { return view('tour-booking'); }
    public function tourFullWidth() { return view('tour-full-width'); }
    public function tourGrid() { return view('tour-grid'); }
    public function tourList() { return view('tour-list'); }
    public function tourSearchResult() { return view('tour-search-result'); }
    public function tourSingle() { return view('tour-single'); }

    public function welcome() { return view('welcome'); }
}