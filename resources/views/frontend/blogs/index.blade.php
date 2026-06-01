@extends('layouts.frontend-public')

@section('title', 'Our Blog — '.config('app.name'))
@section('meta_description', 'Travel tips, news, and updates from '.config('app.name').'.')

@section('content')
    <div class="site-breadcrumb" style="background: url({{ asset('assets/img/breadcrumb/01.jpg') }})">
        <div class="container">
            <h2 class="breadcrumb-title">Our Blog</h2>
            <ul class="breadcrumb-menu">
                <li><a href="{{ route('home') }}">Home</a></li>
                <li class="active">Our Blog</li>
            </ul>
        </div>
    </div>

    <div class="blog-area py-120">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 mx-auto wow fadeInDown" data-wow-duration="1s" data-wow-delay=".25s">
                    <div class="site-heading text-center">
                        <span class="site-title-tagline"><i class="far fa-plane"></i> Our Blog</span>
                        <h2 class="site-title">Latest Blog &amp; News</h2>
                    </div>
                </div>
            </div>
            <div class="row">
                @forelse($blogs as $blog)
                    <div class="col-md-6 col-lg-4">
                        <div class="blog-item wow fadeInUp" data-wow-duration="1s" data-wow-delay=".25s">
                            <span class="blog-date">{{ $blog->updated_at?->format('F d, Y') }}</span>
                            <div class="blog-item-img">
                                @if($blog->image)
                                    <img src="{{ asset('storage/'.$blog->image) }}" alt="{{ $blog->title }}">
                                @else
                                    <img src="{{ asset('assets/img/blog/01.jpg') }}" alt="{{ $blog->title }}">
                                @endif
                            </div>
                            <div class="blog-item-info">
                                <div class="blog-item-meta">
                                    <ul>
                                        <li><a href="{{ route('blogs.show', $blog->slug) }}"><i class="far fa-user-circle"></i> Read article</a></li>
                                        <li><i class="far fa-clock"></i> {{ $blog->updated_at?->diffForHumans() }}</li>
                                    </ul>
                                </div>
                                <h4 class="blog-title">
                                    <a href="{{ route('blogs.show', $blog->slug) }}">{{ $blog->title }}</a>
                                </h4>
                                <p class="text-muted small mb-0">{{ \Illuminate\Support\Str::limit(strip_tags($blog->description), 120) }}</p>
                                <a class="theme-btn mt-3" href="{{ route('blogs.show', $blog->slug) }}">Read More <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <p class="text-muted mb-0">No blog posts yet. Check back soon.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
