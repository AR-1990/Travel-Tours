@extends('frontend.layouts.tavelo')

@section('title', 'About Us — Tavelo')

@section('content')
<div class="site-page">
    @include('frontend.partials.page-banner', [
        'title' => 'About Us',
        'current' => 'About Us',
        'kicker' => 'Our story',
        'text' => 'Travel planning made clear, modern, and human — built for travelers and agencies.',
        'image' => asset('assets/img/about/01.jpg'),
    ])

    <section class="site-section">
        <div class="site-container">
            <div class="site-split">
                <div class="site-split__media">
                    <img src="{{ asset('assets/img/about/01.jpg') }}" alt="About Tavelo">
                </div>
                <div>
                    <span class="site-kicker"><i class="far fa-plane"></i> Our story</span>
                    <h2 class="site-heading">Travel planning made clear, modern, and human</h2>
                    <p class="site-lead" style="margin-bottom: 1rem;">Tavelo helps travelers and agencies search flights with confidence while preparing the next generation of hotel and activity experiences.</p>
                    <p class="site-text">We focus on usability, trustworthy processes, and a design system that feels premium without getting in the way of booking decisions.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="site-section site-section--navy">
        <div class="site-container">
            <div class="site-grid site-grid--4">
                <div class="site-stat">
                    <span class="site-stat__value">12k+</span>
                    <span class="site-stat__label">Travelers supported</span>
                </div>
                <div class="site-stat">
                    <span class="site-stat__value">80+</span>
                    <span class="site-stat__label">Destinations covered</span>
                </div>
                <div class="site-stat">
                    <span class="site-stat__value">24/7</span>
                    <span class="site-stat__label">Expert availability</span>
                </div>
                <div class="site-stat">
                    <span class="site-stat__value">98%</span>
                    <span class="site-stat__label">Satisfaction focus</span>
                </div>
            </div>
        </div>
    </section>

    <section class="site-section site-section--surface">
        <div class="site-container">
            <div class="site-center" style="margin-bottom: 2rem;">
                <span class="site-kicker">What we stand for</span>
                <h2 class="site-heading">Built around clarity and service</h2>
            </div>
            <div class="site-grid site-grid--3">
                <div class="site-feature">
                    <span class="site-feature__icon"><i class="fas fa-compass"></i></span>
                    <div>
                        <h3 class="site-feature__title">Purpose-led design</h3>
                        <p class="site-feature__text">Every page and search flow is shaped to help you decide faster with less friction.</p>
                    </div>
                </div>
                <div class="site-feature">
                    <span class="site-feature__icon"><i class="fas fa-handshake"></i></span>
                    <div>
                        <h3 class="site-feature__title">Partner mindset</h3>
                        <p class="site-feature__text">We work with agencies and experts who want to deliver outstanding traveler experiences.</p>
                    </div>
                </div>
                <div class="site-feature">
                    <span class="site-feature__icon"><i class="fas fa-bolt"></i></span>
                    <div>
                        <h3 class="site-feature__title">Continuous improvement</h3>
                        <p class="site-feature__text">Flights first today — hotels and activities expanding with the same modern standard.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="site-section">
        <div class="site-container">
            <div class="site-center" style="margin-bottom: 2rem;">
                <span class="site-kicker">Team</span>
                <h2 class="site-heading">People behind the journey</h2>
            </div>
            <div class="site-grid site-grid--4">
                @foreach([
                    ['img' => '01.jpg', 'name' => 'Ava Mitchell', 'role' => 'Travel Director'],
                    ['img' => '02.jpg', 'name' => 'Noah Carter', 'role' => 'Flight Specialist'],
                    ['img' => '03.jpg', 'name' => 'Mia Brooks', 'role' => 'Experience Lead'],
                    ['img' => '04.jpg', 'name' => 'Liam Ortiz', 'role' => 'Partner Success'],
                ] as $member)
                    <article class="site-card">
                        <div class="site-card__media">
                            <img src="{{ asset('assets/img/team/'.$member['img']) }}" alt="{{ $member['name'] }}">
                        </div>
                        <div class="site-card__body">
                            <h3 class="site-card__title">{{ $member['name'] }}</h3>
                            <p class="site-card__text">{{ $member['role'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    @include('frontend.partials.site-cta', [
        'title' => 'Let’s plan something remarkable',
        'text' => 'Whether you are booking for yourself or growing an agency, we are ready to help.',
        'primaryLabel' => 'Contact us',
        'primaryUrl' => route('pages.contact'),
        'secondaryLabel' => 'Become an expert',
        'secondaryUrl' => route('pages.become-expert'),
    ])
</div>
@endsection
