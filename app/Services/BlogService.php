<?php

namespace App\Services;

use App\Models\Content\Blog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BlogService
{
    public function getPaginatedBlogs(int $perPage = 6): LengthAwarePaginator
    {
        return Blog::latest()->paginate($perPage);
    }

    public function getBlogBySlug(string $slug): Blog
    {
        return Blog::where('slug', $slug)->firstOrFail();
    }

    public function getRecentBlogs(Blog $blog, int $limit = 3)
    {
        return Blog::where('id', '!=', $blog->id)
            ->latest()
            ->take($limit)
            ->get();
    }

    public function getBlogDetailData(string $slug): array
    {
        $blog = $this->getBlogBySlug($slug);

        return [
            'blog' => $blog,
            'recentBlogs' => $this->getRecentBlogs($blog),
        ];
    }
}
