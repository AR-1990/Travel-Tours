@extends('frontend.layouts.tavelo')

@section('title', 'Flights — Tavelo')

@section('content')
<div class="site-page">
    @include('frontend.partials.page-hero', [
        'kicker' => 'Flights',
        'title' => 'Find flexible fares for every journey',
        'text' => 'Compare routes, choose one-way, round trip, or multi-destination, and book with confidence.',
        'image' => asset('assets/img/hero/hero-1.jpg'),
        'primaryLabel' => 'Start searching',
        'primaryUrl' => '#home-flight-search',
        'secondaryLabel' => 'Talk to us',
        'secondaryUrl' => route('pages.contact'),
        'current' => 'Flights',
    ])

    @include('frontend.partials.flight-search-box', [
        'flightSearchInput' => $flightSearchInput ?? [],
        'searchSubmitLabel' => 'Search Flights',
    ])

    <section class="site-section">
        <div class="site-container">
            <div class="site-center" style="margin-bottom: 2rem;">
                <span class="site-kicker"><i class="far fa-plane"></i> Why fly with Tavelo</span>
                <h2 class="site-heading">Smarter flight search, clearer choices</h2>
                <p class="site-lead">Built for travelers who want speed, clarity, and trustworthy booking support.</p>
            </div>
            <div class="site-grid site-grid--3">
                <div class="site-feature">
                    <span class="site-feature__icon"><i class="fas fa-route"></i></span>
                    <div>
                        <h3 class="site-feature__title">Flexible trip types</h3>
                        <p class="site-feature__text">One-way, round trip, and multi-destination itineraries in one clean search experience.</p>
                    </div>
                </div>
                <div class="site-feature">
                    <span class="site-feature__icon"><i class="fas fa-shield-alt"></i></span>
                    <div>
                        <h3 class="site-feature__title">Trusted process</h3>
                        <p class="site-feature__text">From fare search to confirmation, every step is designed to stay transparent and reliable.</p>
                    </div>
                </div>
                <div class="site-feature">
                    <span class="site-feature__icon"><i class="fas fa-headset"></i></span>
                    <div>
                        <h3 class="site-feature__title">Expert support</h3>
                        <p class="site-feature__text">Need help refining a route? Our travel specialists are ready when you are.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="site-section site-section--surface">
        <div class="site-container">
            <div class="site-center" style="margin-bottom: 2rem;">
                <span class="site-kicker">Popular routes</span>
                <h2 class="site-heading">Inspiration for your next takeoff</h2>
            </div>
            <div class="site-grid site-grid--3">
                @foreach([
                    ['img' => '01.jpg', 'from' => 'New York', 'to' => 'Los Angeles', 'note' => 'Coast-to-coast classics'],
                    ['img' => '02.jpg', 'from' => 'London', 'to' => 'Dubai', 'note' => 'City to desert skyline'],
                    ['img' => '03.jpg', 'from' => 'Paris', 'to' => 'Rome', 'note' => 'Weekend European hops'],
                ] as $route)
                    <article class="site-card">
                        <div class="site-card__media">
                            <img src="{{ asset('assets/img/flight/'.$route['img']) }}" alt="{{ $route['from'] }} to {{ $route['to'] }}">
                        </div>
                        <div class="site-card__body">
                            <span class="site-badge">{{ $route['note'] }}</span>
                            <h3 class="site-card__title">{{ $route['from'] }} → {{ $route['to'] }}</h3>
                            <p class="site-card__text">Search live fares and cabin options for this route.</p>
                            <div class="site-card__footer">
                                <a href="#home-flight-search" class="site-btn site-btn--outline">Search this route</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    @include('frontend.partials.site-cta', [
        'title' => 'Need a complex multi-city itinerary?',
        'text' => 'Use Multi Destination search or contact our experts for a tailored flight plan.',
        'primaryLabel' => 'Contact experts',
        'primaryUrl' => route('pages.contact'),
        'secondaryLabel' => 'Become an expert',
        'secondaryUrl' => route('pages.become-expert'),
    ])
</div>
@endsection
