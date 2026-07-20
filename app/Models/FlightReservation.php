<?php

namespace App\Models;

use App\Models\System\Tenant;
use App\Models\Users\User;
use App\Support\FlightDisplay;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlightReservation extends Model
{
    public const STATUS_RESERVED = 'reserved';

    public const STATUS_TICKETED = 'ticketed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'channel',
        'status',
        'universal_locator',
        'air_reservation_locator',
        'provider_locator',
        'origin',
        'destination',
        'departure_date',
        'return_date',
        'adults',
        'carrier',
        'passenger_prefix',
        'passenger_first',
        'passenger_last',
        'passenger_email',
        'passenger_phone',
        'passenger_dob',
        'passenger_gender',
        'total_price',
        'base_price',
        'taxes',
        'fare_basis',
        'itinerary',
        'price_snapshot',
        'ticket_numbers',
        'raw_result',
        'booked_at',
        'ticketed_at',
    ];

    protected function casts(): array
    {
        return [
            'departure_date' => 'date',
            'return_date' => 'date',
            'passenger_dob' => 'date',
            'itinerary' => 'array',
            'price_snapshot' => 'array',
            'ticket_numbers' => 'array',
            'raw_result' => 'array',
            'booked_at' => 'datetime',
            'ticketed_at' => 'datetime',
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

    public function passengerName(): string
    {
        return trim(($this->passenger_prefix ? $this->passenger_prefix.' ' : '').($this->passenger_first ?? '').' '.($this->passenger_last ?? ''));
    }

    public function routeLabel(): string
    {
        return FlightDisplay::airportCity($this->origin).' → '.FlightDisplay::airportCity($this->destination);
    }

    public function airlineLabel(): string
    {
        return FlightDisplay::airlineName($this->carrier);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_TICKETED => 'Ticketed',
            self::STATUS_CANCELLED => 'Cancelled',
            default => 'Reserved',
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_TICKETED => 'bg-success',
            self::STATUS_CANCELLED => 'bg-secondary',
            default => 'bg-warning text-dark',
        };
    }

    /**
     * Shape used by reservation-detail blade (session-compatible).
     *
     * @return array<string, mixed>
     */
    public function toWorkflowBookingArray(): array
    {
        return [
            'id' => $this->id,
            'universal_locator' => $this->universal_locator,
            'air_reservation_locator' => $this->air_reservation_locator,
            'provider_locator' => $this->provider_locator,
            'booked_at' => optional($this->booked_at)?->toIso8601String(),
            'input' => [
                'passenger_prefix' => $this->passenger_prefix,
                'passenger_first' => $this->passenger_first,
                'passenger_last' => $this->passenger_last,
                'passenger_email' => $this->passenger_email,
                'passenger_phone' => $this->passenger_phone,
                'passenger_dob' => optional($this->passenger_dob)?->format('Y-m-d'),
                'passenger_gender' => $this->passenger_gender,
                'passengers' => [[
                    'prefix' => $this->passenger_prefix,
                    'first' => $this->passenger_first,
                    'last' => $this->passenger_last,
                    'email' => $this->passenger_email,
                    'phone' => $this->passenger_phone,
                    'dob' => optional($this->passenger_dob)?->format('Y-m-d'),
                    'gender' => $this->passenger_gender,
                ]],
            ],
            'result' => $this->raw_result ?? [],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function toPriceResultArray(): ?array
    {
        if (is_array($this->price_snapshot) && $this->price_snapshot !== []) {
            return $this->price_snapshot;
        }

        if (! is_array($this->itinerary) || $this->itinerary === []) {
            return null;
        }

        return [
            'ok' => true,
            'solutions' => [[
                'plating_carrier' => $this->carrier,
                'fare_basis' => $this->fare_basis,
                'total_price' => $this->total_price,
                'base_price' => $this->base_price,
                'taxes' => $this->taxes,
                'journeys' => $this->itinerary['journeys'] ?? [],
                'segments' => $this->itinerary['segments'] ?? [],
            ]],
        ];
    }
}
