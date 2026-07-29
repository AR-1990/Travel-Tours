@php
    $title = $title ?? 'Explore with Tavelo';
    $text = $text ?? '';
    $kicker = $kicker ?? null;
    $image = $image ?? asset('assets/img/hero/hero-1.jpg');
    $primaryLabel = $primaryLabel ?? null;
    $primaryUrl = $primaryUrl ?? null;
    $secondaryLabel = $secondaryLabel ?? null;
    $secondaryUrl = $secondaryUrl ?? null;
    $current = $current ?? null;
@endphp
<section class="site-hero">
    <div class="site-hero__media" style="background-image: url('{{ $image }}');" aria-hidden="true"></div>
    <div class="site-container">
        <div class="site-hero__content">
            @if($kicker)
                <span class="site-kicker">{{ $kicker }}</span>
            @endif
            <h1 class="site-hero__title">{{ $title }}</h1>
            @if($text)
                <p class="site-hero__text">{{ $text }}</p>
            @endif
            @if($primaryLabel || $secondaryLabel)
                <div class="site-hero__actions">
                    @if($primaryLabel && $primaryUrl)
                        <a href="{{ $primaryUrl }}" class="site-btn site-btn--primary">{{ $primaryLabel }}</a>
                    @endif
                    @if($secondaryLabel && $secondaryUrl)
                        <a href="{{ $secondaryUrl }}" class="site-btn site-btn--ghost">{{ $secondaryLabel }}</a>
                    @endif
                </div>
            @endif
            @if($current)
                <nav class="site-page-crumbs" aria-label="Breadcrumb" style="margin-top: 1.25rem;">
                    <a href="{{ route('home') }}">Home</a>
                    <span class="site-page-crumbs__sep" aria-hidden="true">/</span>
                    <span class="site-page-crumbs__current">{{ $current }}</span>
                </nav>
            @endif
        </div>
    </div>
</section>
