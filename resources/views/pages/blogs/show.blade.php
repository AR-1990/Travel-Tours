@extends('layouts.main')

@section('title', ($blog->meta_title ?: $blog->title) . ' - Travel Tours')
@section('meta_description', $blog->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($blog->description), 160))

@section('content')
<div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <a href="{{ route('blogs.index') }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-700 mb-6">
            <span class="mr-2">←</span> Back to Blogs
        </a>

        <article class="bg-white rounded-xl shadow border border-gray-100 overflow-hidden">
            @if($blog->image)
                <img src="{{ asset('storage/' . $blog->image) }}" alt="{{ $blog->title }}" class="w-full h-72 object-cover">
            @endif

            <div class="p-8">
                <p class="text-sm text-gray-500 mb-3">{{ $blog->updated_at?->format('M d, Y') }}</p>
                <h1 class="text-4xl font-bold text-gray-900 mb-6">{{ $blog->title }}</h1>

                <div class="prose max-w-none text-gray-700 blog-detail-content">
                    {!! $blog->description !!}
                </div>
            </div>
        </article>
    </div>
</div>
@endsection

@section('styles')
<style>
    .blog-detail-content,
    .blog-detail-content * {
        overflow-wrap: anywhere;
        word-break: break-word;
    }
    .blog-detail-content img,
    .blog-detail-content iframe,
    .blog-detail-content video,
    .blog-detail-content table {
        max-width: 100%;
        height: auto;
    }
    .blog-detail-content table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
</style>
@endsection
