<?php

namespace App\Http\Controllers;

use App\Models\Content\Blog;

class BlogController extends Controller
{
    public function index()
    {
        $blogs = Blog::latest()->get();

        return view('pages.blogs.index', compact('blogs'));
    }

    public function show(string $slug)
    {
        $blog = Blog::where('slug', $slug)->firstOrFail();

        return view('pages.blogs.show', compact('blog'));
    }
}
