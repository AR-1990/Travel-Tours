<?php

namespace App\Models;

use App\Models\System\Tenant;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotelReservation extends Model
{
    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_CANCELLED = 'cancelled';

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
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
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
            || strtolower((string) $this->status) === self::STATUS_CANCELLED;
    }

    public function passengerName(): string
    {
        return trim(($this->passenger_prefix ? $this->passenger_prefix.' ' : '').($this->passenger_first ?? '').' '.($this->passenger_last ?? ''));
    }

    public function stayLabel(): string
    {
        $hotel = trim((string) ($this->hotel_name ?: 'Hotel'));
        $city = trim((string) ($this->city_id ?? ''));

        return $city !== '' ? $hotel.' · '.$city : $hotel;
    }

    public function datesLabel(): string
    {
        $in = optional($this->check_in)->format('d M Y');
        $out = optional($this->check_out)->format('d M Y');

        if ($in && $out) {
            return $in.' → '.$out;
        }

        return $in ?: ($out ?: '—');
    }

    public function statusLabel(): string
    {
        return match (strtolower((string) $this->status)) {
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_CONFIRMED => 'Confirmed',
            default => ucfirst((string) ($this->status ?: 'unknown')),
        };
    }

    public function statusBadgeClass(): string
    {
        return match (strtolower((string) $this->status)) {
            self::STATUS_CANCELLED => 'bg-danger',
            self::STATUS_CONFIRMED => 'bg-success',
            default => 'bg-secondary',
        };
    }

    public function referenceLabel(): string
    {
        return (string) ($this->reference_no ?: $this->internal_reference ?: $this->booking_id ?: '—');
    }
}
