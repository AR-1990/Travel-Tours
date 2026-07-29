@php
    $title = $title ?? 'Ready to plan your next trip?';
    $text = $text ?? 'Search flights, discover stays, and book memorable experiences with Tavelo.';
    $primaryLabel = $primaryLabel ?? 'Search Flights';
    $primaryUrl = $primaryUrl ?? route('pages.flights');
    $secondaryLabel = $secondaryLabel ?? null;
    $secondaryUrl = $secondaryUrl ?? null;
@endphp
<section class="site-section site-section--tight">
    <div class="site-container">
        <div class="site-cta">
            <div class="site-cta__content">
                <h2 class="site-cta__title">{{ $title }}</h2>
                <p class="site-cta__text">{{ $text }}</p>
            </div>
            <div class="site-cta__actions">
                <a href="{{ $primaryUrl }}" class="site-btn site-btn--primary">{{ $primaryLabel }}</a>
                @if($secondaryLabel && $secondaryUrl)
                    <a href="{{ $secondaryUrl }}" class="site-btn site-btn--ghost">{{ $secondaryLabel }}</a>
                @endif
            </div>
        </div>
    </div>
</section>
