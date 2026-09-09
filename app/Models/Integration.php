<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Integration extends Model
{
    public const SLUG_TRAVELPORT = 'travelport';

    public const SLUG_SUNSPRING = 'sunspring';

    public const SLUG_XCONNECT = 'xconnect';

    public const SLUG_DOWNTOWN_TRAVEL = 'downtown_travel';

    public const SLUG_DOWNTOWN_TRAVEL_HOTELS = 'downtown_travel_hotels';

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
