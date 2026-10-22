<?php

namespace App\Models\Finance;

use App\Models\FlightReservation;
use App\Models\System\Tenant;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AfterSaleRequest extends Model
{
    public const TYPE_VOID_NO_PENALTY = 'void_no_penalty';

    public const TYPE_CANCEL_WITH_PENALTY = 'cancel_with_penalty';

    public const TYPE_CHANGE = 'change';

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'flight_reservation_id',
        'tenant_id',
        'requested_by',
        'type',
        'status',
        'change_kind',
        'penalty_amount',
        'penalty_currency',
        'note',
        'new_ticket_path',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'penalty_amount' => 'decimal:2',
            'meta' => 'array',
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(FlightReservation::class, 'flight_reservation_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_VOID_NO_PENALTY => 'Void (no penalty)',
            self::TYPE_CANCEL_WITH_PENALTY => 'Cancel (with penalty)',
            self::TYPE_CHANGE => 'Change ('.($this->change_kind ?: 'name/upgrade').')',
            default => $this->type,
        };
    }
}
