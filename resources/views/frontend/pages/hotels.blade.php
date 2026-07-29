@extends('frontend.layouts.tavelo')

@section('title', 'Hotels — Tavelo')

@section('content')
<div class="site-page">
    @include('frontend.partials.page-banner', [
        'title' => 'Hotels',
        'current' => 'Hotels',
        'kicker' => 'Stays',
        'text' => 'Explore curated hotels and resorts — request tailored stay options for your trip.',
        'image' => asset('assets/img/hotel/01.jpg'),
    ])

    <section class="site-section">
        <div class="site-container">
            <div class="site-split">
                <div>
                    <span class="site-kicker"><i class="far fa-hotel"></i> Stays</span>
                    <h2 class="site-heading">Stay somewhere that matches your trip style</h2>
                    <p class="site-lead" style="margin-bottom: 1rem;">From boutique city hotels to resort escapes, explore curated stays while our full hotel booking experience expands.</p>
                    <p class="site-text">Browse featured properties below and talk to our team for personalized hotel recommendations paired with your flights.</p>
                    <div style="display:flex; flex-wrap:wrap; gap:.75rem; margin-top:1.25rem;">
                        <a href="{{ route('pages.contact') }}" class="site-btn site-btn--primary">Request hotel options</a>
                        <a href="{{ route('pages.flights') }}" class="site-btn site-btn--outline">Pair with flights</a>
                    </div>
                </div>
                <div class="site-split__media">
                    <img src="{{ asset('assets/img/hotel/01.jpg') }}" alt="Featured hotel">
                </div>
            </div>
        </div>
    </section>

    <section class="site-section site-section--surface">
        <div class="site-container">
            <div class="site-center" style="margin-bottom: 2rem;">
                <span class="site-kicker">Featured stays</span>
                <h2 class="site-heading">Hotels travelers love</h2>
                <p class="site-lead">A modern showcase of destination stays — bookable with expert support today.</p>
            </div>
            <div class="site-grid site-grid--3">
                @foreach([
                    ['img' => '01.jpg', 'name' => 'Harbor View Hotel', 'place' => 'Singapore', 'tag' => 'City'],
                    ['img' => '02.jpg', 'name' => 'Palm Grove Resort', 'place' => 'Maldives', 'tag' => 'Resort'],
                    ['img' => '03.jpg', 'name' => 'Alpine Lodge', 'place' => 'Switzerland', 'tag' => 'Boutique'],
                    ['img' => '04.jpg', 'name' => 'Desert Mirage', 'place' => 'Dubai', 'tag' => 'Luxury'],
                    ['img' => '05.jpg', 'name' => 'Old Town Inn', 'place' => 'Prague', 'tag' => 'Boutique'],
                    ['img' => '06.jpg', 'name' => 'Coastline Suites', 'place' => 'Barcelona', 'tag' => 'Beach'],
                ] as $hotel)
                    <article class="site-card">
                        <div class="site-card__media">
                            <img src="{{ asset('assets/img/hotel/'.$hotel['img']) }}" alt="{{ $hotel['name'] }}">
                        </div>
                        <div class="site-card__body">
                            <div class="site-card__meta">
                                <span class="site-badge">{{ $hotel['tag'] }}</span>
                                <span><i class="far fa-location-dot"></i> {{ $hotel['place'] }}</span>
                            </div>
                            <h3 class="site-card__title">{{ $hotel['name'] }}</h3>
                            <p class="site-card__text">Comfortable rooms, strong location value, and easy pairing with your flight plans.</p>
                            <div class="site-card__footer">
                                <a href="{{ route('pages.contact') }}" class="site-btn site-btn--outline">Ask about availability</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    @include('frontend.partials.site-cta', [
        'title' => 'Want hotel + flight packaged together?',
        'text' => 'Tell us your dates and destination — we’ll recommend strong stay options that fit your itinerary.',
        'primaryLabel' => 'Contact us',
        'primaryUrl' => route('pages.contact'),
        'secondaryLabel' => 'Search flights',
        'secondaryUrl' => route('pages.flights'),
    ])
</div>
@endsection
