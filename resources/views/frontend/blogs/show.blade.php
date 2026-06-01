@extends('layouts.frontend-public')

@section('title', ($blog->meta_title ?: $blog->title).' — '.config('app.name'))
@section('meta_description', $blog->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($blog->description), 160))

@push('styles')
<style>
    .blog-details-content img,
    .blog-details-content iframe,
    .blog-details-content video,
    .blog-details-content table {
        max-width: 100%;
        height: auto;
    }
    .blog-details-content table {
        display: block;
        overflow-x: auto;
    }
</style>
@endpush

@section('content')
    <div class="site-breadcrumb" style="background: url({{ asset('assets/img/breadcrumb/01.jpg') }})">
        <div class="container">
            <h2 class="breadcrumb-title">{{ \Illuminate\Support\Str::limit($blog->title, 48) }}</h2>
            <ul class="breadcrumb-menu">
                <li><a href="{{ route('home') }}">Home</a></li>
                <li><a href="{{ route('blogs.index') }}">Our Blog</a></li>
                <li class="active">Article</li>
            </ul>
        </div>
    </div>

    <div class="blog-single-area pt-120 pb-120">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="blog-single-wrapper">
                        <div class="blog-single-content">
                            @if($blog->image)
                                <div class="blog-thumb-img">
                                    <img src="{{ asset('storage/'.$blog->image) }}" alt="{{ $blog->title }}">
                                </div>
                            @endif
                            <div class="blog-info">
                                <div class="blog-meta">
                                    <div class="blog-meta-left">
                                        <ul>
                                            <li><i class="far fa-clock"></i> {{ $blog->updated_at?->format('F d, Y') }}</li>
                                            <li><i class="far fa-folder"></i> <a href="{{ route('blogs.index') }}">Blog</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="blog-details blog-details-content">
                                    <h3 class="blog-details-title mb-20">{{ $blog->title }}</h3>
                                    {!! $blog->description !!}
                                </div>
                                <div class="mt-4">
                                    <a href="{{ route('blogs.index') }}" class="theme-btn"><i class="far fa-arrow-left"></i> Back to blog</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
