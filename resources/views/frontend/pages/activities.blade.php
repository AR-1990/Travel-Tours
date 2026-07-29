@extends('frontend.layouts.tavelo')

@section('title', 'Activities — Tavelo')

@section('content')
<div class="site-page">
    @include('frontend.partials.page-banner', [
        'title' => 'Activities',
        'current' => 'Activities',
        'kicker' => 'Experiences',
        'text' => 'Discover tours, adventures, and local experiences to enrich every destination.',
        'image' => asset('assets/img/activity/01.jpg'),
    ])

    <section class="site-section">
        <div class="site-container">
            <div class="site-center" style="margin-bottom: 2rem;">
                <span class="site-kicker"><i class="far fa-person-biking-mountain"></i> Experiences</span>
                <h2 class="site-heading">Make every destination more memorable</h2>
                <p class="site-lead">Discover curated activities and experiences to enrich your trip — from cultural tours to outdoor adventures.</p>
            </div>
            <div class="site-grid site-grid--3">
                @foreach([
                    ['img' => '01.jpg', 'title' => 'City Walking Tours', 'place' => 'Europe', 'tag' => 'Culture'],
                    ['img' => '02.jpg', 'title' => 'Desert Safari', 'place' => 'UAE', 'tag' => 'Adventure'],
                    ['img' => '03.jpg', 'title' => 'Island Snorkeling', 'place' => 'Asia', 'tag' => 'Water'],
                    ['img' => '04.jpg', 'title' => 'Mountain Hiking', 'place' => 'Alps', 'tag' => 'Outdoor'],
                    ['img' => '05.jpg', 'title' => 'Food & Market Trails', 'place' => 'Global', 'tag' => 'Food'],
                    ['img' => '06.jpg', 'title' => 'Sunset Cruises', 'place' => 'Coastal', 'tag' => 'Leisure'],
                ] as $item)
                    <article class="site-card">
                        <div class="site-card__media">
                            <img src="{{ asset('assets/img/activity/'.$item['img']) }}" alt="{{ $item['title'] }}">
                        </div>
                        <div class="site-card__body">
                            <div class="site-card__meta">
                                <span class="site-badge">{{ $item['tag'] }}</span>
                                <span><i class="far fa-location-dot"></i> {{ $item['place'] }}</span>
                            </div>
                            <h3 class="site-card__title">{{ $item['title'] }}</h3>
                            <p class="site-card__text">Thoughtfully selected experiences designed to fit around your flight schedule.</p>
                            <div class="site-card__footer">
                                <a href="{{ route('pages.contact') }}" class="site-btn site-btn--outline">Plan this activity</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="site-section site-section--surface">
        <div class="site-container">
            <div class="site-grid site-grid--3">
                <div class="site-feature">
                    <span class="site-feature__icon"><i class="fas fa-clock"></i></span>
                    <div>
                        <h3 class="site-feature__title">Flexible timing</h3>
                        <p class="site-feature__text">Choose experiences that sync with arrival and departure windows.</p>
                    </div>
                </div>
                <div class="site-feature">
                    <span class="site-feature__icon"><i class="fas fa-users"></i></span>
                    <div>
                        <h3 class="site-feature__title">Solo or group</h3>
                        <p class="site-feature__text">Private and shared options for couples, families, and teams.</p>
                    </div>
                </div>
                <div class="site-feature">
                    <span class="site-feature__icon"><i class="fas fa-map"></i></span>
                    <div>
                        <h3 class="site-feature__title">Local insight</h3>
                        <p class="site-feature__text">Guided by specialists who know the destination beyond tourist checklists.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('frontend.partials.site-cta', [
        'title' => 'Build a richer itinerary',
        'text' => 'Combine flights with activities and optional hotel stays for a complete trip plan.',
        'primaryLabel' => 'Talk to an expert',
        'primaryUrl' => route('pages.become-expert'),
        'secondaryLabel' => 'Search flights',
        'secondaryUrl' => route('pages.flights'),
    ])
</div>
@endsection
