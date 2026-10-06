<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Content\PartnerInquiry;
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
        $partnerTypes = PartnerInquiry::partnerTypes();

        return view('frontend.pages.partner-with-us', compact('partnerTypes'));
    }

    public function partnerWithUsSubmit(Request $request)
    {
        $validated = $request->validate([
            'partner_type' => ['required', 'string', 'in:'.implode(',', PartnerInquiry::typeKeys())],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'company' => ['nullable', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        PartnerInquiry::create([
            'partner_type' => $validated['partner_type'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'company' => $validated['company'] ?? null,
            'message' => $validated['message'],
            'status' => PartnerInquiry::STATUS_NEW,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Thank you! Your partnership inquiry has been received. Our team will contact you soon.');
    }
}
