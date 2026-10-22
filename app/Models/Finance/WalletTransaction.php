<?php

namespace App\Models\Finance;

use App\Models\FlightReservation;
use App\Models\System\Tenant;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    public const TYPE_TOPUP = 'topup';

    public const TYPE_SALE = 'sale';

    public const TYPE_REFUND = 'refund';

    public const TYPE_REVERSE = 'reverse';

    public const TYPE_ADJUSTMENT = 'adjustment';

    public const DIR_CREDIT = 'credit';

    public const DIR_DEBIT = 'debit';

    protected $fillable = [
        'wallet_id',
        'tenant_id',
        'created_by',
        'type',
        'direction',
        'amount',
        'balance_after',
        'currency',
        'description',
        'pnr',
        'order_ref',
        'flight_reservation_id',
        'reverses_transaction_id',
        'vendor',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'meta' => 'array',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(FlightReservation::class, 'flight_reservation_id');
    }

    public function reversedTransaction(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reverses_transaction_id');
    }
}
