@extends('layouts.frontend-public')

@section('title', 'Our Blog — '.config('app.name'))
@section('meta_description', 'Travel tips, news, and updates from '.config('app.name').'.')

@section('content')
<div class="site-page">
    @include('frontend.partials.page-banner', [
        'title' => 'Our Blog',
        'current' => 'Blog',
        'kicker' => 'Insights',
        'text' => 'Tips, destination ideas, and updates from the Tavelo travel team.',
        'image' => asset('assets/img/hero/hero-4.jpg'),
    ])

    <section class="site-section">
        <div class="site-container">
            <div class="site-center" style="margin-bottom: 2rem;">
                <span class="site-kicker"><i class="far fa-plane"></i> Insights</span>
                <h2 class="site-heading">Latest blog &amp; news</h2>
                <p class="site-lead">Tips, destination ideas, and updates from the Tavelo travel team.</p>
            </div>

            <div class="site-grid site-grid--3">
                @forelse($blogs as $blog)
                    <article class="site-card">
                        <div class="site-card__media">
                            @if($blog->image)
                                <img src="{{ asset('storage/'.$blog->image) }}" alt="{{ $blog->title }}">
                            @else
                                <img src="{{ asset('assets/img/blog/01.jpg') }}" alt="{{ $blog->title }}">
                            @endif
                        </div>
                        <div class="site-card__body">
                            <div class="site-card__meta">
                                <span>{{ $blog->updated_at?->format('M d, Y') }}</span>
                                <span>{{ $blog->updated_at?->diffForHumans() }}</span>
                            </div>
                            <h3 class="site-card__title">
                                <a href="{{ route('blogs.show', $blog->slug) }}">{{ $blog->title }}</a>
                            </h3>
                            <p class="site-card__text">{{ \Illuminate\Support\Str::limit(strip_tags($blog->description), 120) }}</p>
                            <div class="site-card__footer">
                                <a class="site-btn site-btn--outline" href="{{ route('blogs.show', $blog->slug) }}">
                                    Read more <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="site-panel" style="grid-column: 1 / -1; text-align:center;">
                        <p class="site-text mb-0">No blog posts yet. Check back soon.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    @include('frontend.partials.site-cta', [
        'title' => 'Looking for trip inspiration?',
        'text' => 'Search flights or browse destinations while we keep publishing travel insights.',
        'primaryLabel' => 'Search flights',
        'primaryUrl' => route('pages.flights'),
        'secondaryLabel' => 'Contact us',
        'secondaryUrl' => route('pages.contact'),
    ])
</div>
@endsection
