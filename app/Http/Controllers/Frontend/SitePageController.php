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

    public function partnerWithUs(): View
    {
        $partnerTypes = $this->partnerTypes();

        return view('frontend.pages.partner-with-us', compact('partnerTypes'));
    }

    public function partnerWithUsSubmit(Request $request)
    {
        $request->validate([
            'partner_type' => ['required', 'string', 'in:'.implode(',', array_keys($this->partnerTypes()))],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'company' => ['nullable', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        return back()->with('success', 'Thank you! Your partnership inquiry has been received. Our team will contact you soon.');
    }

    /** @return array<string, array{number: int, title: string, text: string, icon: string, tone: string, features: list<string>}> */
    private function partnerTypes(): array
    {
        return [
            'b2b' => [
                'number' => 1,
                'title' => 'B2B Partner',
                'text' => 'For travel agencies & businesses who want to sell travel services.',
                'icon' => 'fas fa-user-friends',
                'tone' => 'blue',
                'features' => [
                    'Agent Portal Access',
                    'Competitive Rates',
                    'Credit Facility',
                    'Dedicated Support',
                ],
            ],
            'b2c' => [
                'number' => 2,
                'title' => 'B2C Partner',
                'text' => 'For businesses who want to sell travel services directly to customers.',
                'icon' => 'fas fa-user',
                'tone' => 'green',
                'features' => [
                    'Retail Booking System',
                    'Best Customer Prices',
                    'Multiple Payment Options',
                    'Marketing Support',
                ],
            ],
            'api' => [
                'number' => 3,
                'title' => 'API Partner',
                'text' => 'Integrate our powerful travel API into your platform or system.',
                'icon' => 'fas fa-code',
                'tone' => 'purple',
                'features' => [
                    'Real-time Inventory',
                    'Seamless Integration',
                    'Global Content',
                    'Technical Support',
                ],
            ],
            'whitelabel' => [
                'number' => 4,
                'title' => 'Whitelable Partner',
                'text' => 'Launch your own travel brand with our white-label solution.',
                'icon' => 'fas fa-desktop',
                'tone' => 'orange',
                'features' => [
                    'Your Own Brand',
                    'Custom Domain',
                    'Full System Control',
                    'End-to-End Support',
                ],
            ],
            'supplier' => [
                'number' => 5,
                'title' => 'Become Supplier',
                'text' => 'For hotels, airlines, transfer services & other suppliers to connect with us.',
                'icon' => 'fas fa-briefcase',
                'tone' => 'teal',
                'features' => [
                    'Global Visibility',
                    'Increase Bookings',
                    'Secure Payments',
                    'Long Term Partnership',
                ],
            ],
        ];
    }
}
