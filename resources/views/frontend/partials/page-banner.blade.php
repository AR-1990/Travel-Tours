@php
    $title = $title ?? 'Page';
    $current = $current ?? $title;
    $text = $text ?? null;
    $kicker = $kicker ?? null;
    $image = $image ?? asset('assets/img/hero/hero-1.jpg');
@endphp
<section class="site-page-banner">
    <div class="site-page-banner__media" style="background-image: url('{{ $image }}');" aria-hidden="true"></div>
    <div class="site-container">
        <div class="site-page-banner__content">
            @if($kicker)
                <span class="site-kicker">{{ $kicker }}</span>
            @endif
            <h1 class="site-page-banner__title">{{ $title }}</h1>
            @if($text)
                <p class="site-page-banner__text">{{ $text }}</p>
            @endif
            <nav class="site-page-crumbs" aria-label="Breadcrumb">
                <a href="{{ route('home') }}">Home</a>
                <span class="site-page-crumbs__sep" aria-hidden="true">/</span>
                <span class="site-page-crumbs__current">{{ $current }}</span>
            </nav>
        </div>
    </div>
</section>
