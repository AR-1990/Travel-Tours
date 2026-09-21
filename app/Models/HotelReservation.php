<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotelReservation extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'channel',
        'provider',
        'status',
        'booking_id',
        'reference_no',
        'internal_reference',
        'hotel_id',
        'hotel_name',
        'city_id',
        'nationality',
        'check_in',
        'check_out',
        'nights',
        'rooms_count',
        'passenger_prefix',
        'passenger_first',
        'passenger_last',
        'passenger_email',
        'passenger_phone',
        'total_price',
        'currency',
        'guests',
        'price_snapshot',
        'raw_result',
        'provider_snapshot',
        'booked_at',
        'cancelled_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'check_in' => 'date',
            'check_out' => 'date',
            'guests' => 'array',
            'price_snapshot' => 'array',
            'raw_result' => 'array',
            'provider_snapshot' => 'array',
            'booked_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Users\User::class);
    }

    public function isXconnect(): bool
    {
        return strtolower((string) $this->provider) === 'xconnect';
    }

    public function providerLabel(): string
    {
        return \App\Support\HotelProvider::label((string) $this->provider);
    }

    public function isDowntownTravel(): bool
    {
        return strtolower((string) $this->provider) === \App\Support\HotelProvider::DOWNTOWN_TRAVEL_HOTELS;
    }

    public function isCancelled(): bool
    {
        return $this->cancelled_at !== null
            || strtolower((string) $this->status) === 'cancelled';
    }
}
