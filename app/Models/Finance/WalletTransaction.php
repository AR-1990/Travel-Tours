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

    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_SALE => 'T. Sale',
            self::TYPE_TOPUP => 'Payment',
            self::TYPE_REFUND => 'Refund',
            self::TYPE_REVERSE => 'Reverse',
            self::TYPE_ADJUSTMENT => 'Adjustment',
            default => ucfirst((string) $this->type),
        };
    }

    public function debitAmount(): float
    {
        return $this->direction === self::DIR_DEBIT ? (float) $this->amount : 0.0;
    }

    public function creditAmount(): float
    {
        return $this->direction === self::DIR_CREDIT ? (float) $this->amount : 0.0;
    }

    public function customerLabel(): string
    {
        return trim((string) ($this->tenant?->name ?? '')) ?: '—';
    }

    public function sellerLabel(): string
    {
        $user = $this->creator;
        if (! $user) {
            return '—';
        }
        $name = trim((string) (($user->first_name ?? '').' '.($user->last_name ?? '')));
        if ($name !== '') {
            return $name;
        }

        return trim((string) ($user->username ?: $user->email)) ?: '—';
    }

    public function pnrLabel(): string
    {
        $pnr = trim((string) ($this->pnr ?? ''));
        if ($pnr !== '') {
            return $pnr;
        }
        $fromRes = $this->reservation?->pnrLabel();

        return ($fromRes && $fromRes !== '—') ? $fromRes : '—';
    }

    public function orderIdLabel(): string
    {
        if ($this->flight_reservation_id) {
            return (string) $this->flight_reservation_id;
        }
        $ref = trim((string) ($this->order_ref ?? ''));
        if ($ref !== '') {
            return $ref;
        }

        return '—';
    }

    public function carrierLabel(): string
    {
        $fromMeta = trim((string) data_get($this->meta, 'carrier', ''));
        if ($fromMeta !== '') {
            return strtoupper($fromMeta);
        }
        $fromRes = strtoupper(trim((string) ($this->reservation?->carrier ?? '')));
        if ($fromRes !== '') {
            return $fromRes;
        }
        $vendor = trim((string) ($this->vendor ?? ''));

        return $vendor !== '' ? $vendor : '—';
    }

    public function providerLabel(): string
    {
        $fromMeta = trim((string) data_get($this->meta, 'provider', ''));
        if ($fromMeta !== '') {
            return \App\Support\FlightProvider::label($fromMeta);
        }
        if ($this->reservation) {
            return $this->reservation->providerLabel();
        }
        $vendor = trim((string) ($this->vendor ?? ''));

        return $vendor !== '' ? \App\Support\FlightProvider::label($vendor) : '—';
    }

    public function unitLabel(): string
    {
        $cur = strtoupper(trim((string) ($this->currency ?? '')));
        if ($cur !== '') {
            return $cur;
        }
        $fromMeta = strtoupper(trim((string) data_get($this->meta, 'currency', '')));

        return $fromMeta !== '' ? $fromMeta : '—';
    }

    public function maturityLabel(): string
    {
        $fromMeta = trim((string) data_get($this->meta, 'maturity', ''));
        if ($fromMeta !== '') {
            return $fromMeta;
        }
        if ($this->reservation?->departure_date) {
            return $this->reservation->departure_date->format('Y-m-d');
        }

        return optional($this->created_at)?->format('Y-m-d') ?: '—';
    }

    public function descriptionLabel(): string
    {
        $desc = trim((string) ($this->description ?? ''));

        return $desc !== '' ? $desc : '—';
    }
}
