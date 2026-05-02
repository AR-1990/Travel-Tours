<?php

namespace App\Http\Controllers;

use App\Services\BlogService;

class BlogController extends Controller
{
    public function __construct(protected BlogService $blogService)
    {
    }

    public function index()
    {
        $blogs = $this->blogService->getPaginatedBlogs();

        return view('pages.blogs.index', compact('blogs'));
    }

    public function show(string $slug)
    {
        return view('pages.blogs.show', $this->blogService->getBlogDetailData($slug));
    }
}
