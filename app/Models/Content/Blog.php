<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'meta_title',
        'meta_description',
        'image',
    ];

    public function getImageUrlAttribute(): string
    {
        if (!$this->image) {
            return asset('assets/img/blog/01.jpg');
        }

        if (Str::startsWith($this->image, ['http://', 'https://'])) {
            return $this->image;
        }

        if (Str::startsWith($this->image, ['assets/', 'storage/'])) {
            return asset($this->image);
        }

        return asset('storage/' . ltrim($this->image, '/'));
    }

    public function getExcerptAttribute(): string
    {
        return Str::limit(trim(strip_tags((string) $this->description)), 140);
    }
}
