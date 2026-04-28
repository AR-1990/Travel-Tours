@extends('layouts.main')

@section('title', 'Blogs - Travel Tours')

@section('content')
<div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-4xl font-bold text-gray-900">Travel Tours Blogs</h1>
                <p class="text-gray-600 mt-2">Latest updates, travel tips, and platform news.</p>
            </div>
            <a href="{{ route('home') }}" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
                Back to Home
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($blogs as $blog)
                <a href="{{ route('blogs.show', $blog->slug) }}" class="block bg-white rounded-xl shadow border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow blog-card h-full">
                    @if($blog->image)
                        <img src="{{ asset('storage/' . $blog->image) }}" alt="{{ $blog->title }}" class="w-full h-48 object-cover">
                    @else
                        <div class="w-full h-48 bg-gradient-to-r from-indigo-500 to-purple-600"></div>
                    @endif
                    <div class="p-5 blog-card-body">
                        <h2 class="text-xl font-semibold text-gray-900 mb-2 blog-card-title">{{ $blog->title }}</h2>
                        <p class="text-sm text-gray-500 mb-3">{{ $blog->updated_at?->format('M d, Y') }}</p>
                        <p class="text-gray-600 blog-card-excerpt">
                            {{ \Illuminate\Support\Str::limit(strip_tags($blog->description), 140) }}
                        </p>
                    </div>
                </a>
            @empty
                <div class="col-span-3 text-center py-16 bg-white rounded-xl border border-gray-100">
                    <p class="text-gray-500 text-lg">No blogs available yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .blog-card {
        display: flex;
        flex-direction: column;
    }
    .blog-card-body {
        flex: 1;
        min-width: 0;
    }
    .blog-card-title,
    .blog-card-excerpt {
        overflow-wrap: anywhere;
        word-break: break-word;
    }
    .blog-card-excerpt {
        line-clamp: 3;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endsection
