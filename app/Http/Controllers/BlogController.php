<?php

namespace App\Http\Controllers;

use App\Models\Content\Blog;

class BlogController extends Controller
{
    public function index()
    {
        $blogs = Blog::latest()->get();

        return view('frontend.blogs.index', compact('blogs'));
    }

    public function show(string $slug)
    {
        $blog = Blog::where('slug', $slug)->firstOrFail();

        return view('frontend.blogs.show', compact('blog'));
    }
}
