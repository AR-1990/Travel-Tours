<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Integration extends Model
{
    public const SLUG_TRAVELPORT = 'travelport';

    protected $fillable = [
        'slug',
        'name',
        'is_enabled',
        'payload',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'encrypted:array',
            'is_enabled' => 'boolean',
        ];
    }
}
