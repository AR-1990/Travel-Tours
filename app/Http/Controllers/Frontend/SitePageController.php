<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SitePageController extends Controller
{
    public function flights(): View
    {
        $stored = session('public.flight_search');
        $flightSearchInput = is_array($stored) ? ($stored['input'] ?? []) : [];

        return view('frontend.pages.flights', compact('flightSearchInput'));
    }

    public function hotels(): View
    {
        return view('frontend.pages.hotels');
    }

    public function activities(): View
    {
        return view('frontend.pages.activities');
    }

    public function about(): View
    {
        return view('frontend.pages.about');
    }

    public function contact(): View
    {
        return view('frontend.pages.contact');
    }

    public function contactSubmit(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'subject' => ['required', 'string', 'max:180'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        return back()->with('success', 'Thank you! Your message has been received. We will get back to you soon.');
    }

    public function becomeExpert(): View
    {
        return view('frontend.pages.become-expert');
    }

    public function becomeExpertSubmit(Request $request)
    {
        $request->validate([
            'full_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'expertise' => ['required', 'string', 'max:120'],
            'experience' => ['nullable', 'string', 'max:80'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        return back()->with('success', 'Thanks for applying! Our partnerships team will review your profile shortly.');
    }
}
