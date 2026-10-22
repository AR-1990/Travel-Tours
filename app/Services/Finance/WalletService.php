<?php

namespace App\Services\Finance;

use App\Models\Finance\Wallet;
use App\Models\Finance\WalletTransaction;
use App\Models\FlightReservation;
use App\Models\System\Tenant;
use App\Models\Users\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class WalletService
{
    public function ensureWallet(Tenant|int $tenant, ?string $currency = null): Wallet
    {
        $tenantId = $tenant instanceof Tenant ? (int) $tenant->id : (int) $tenant;
        $tenantModel = $tenant instanceof Tenant ? $tenant : Tenant::query()->findOrFail($tenantId);
        $currency = strtoupper((string) ($currency ?: ($tenantModel->currency ?: 'USD')));
        if (strlen($currency) !== 3) {
            $currency = 'USD';
        }

        return Wallet::query()->firstOrCreate(
            ['tenant_id' => $tenantId],
            [
                'currency' => $currency,
                'balance' => 0,
                'is_active' => true,
            ]
        );
    }

    public function balance(Tenant|int $tenant): float
    {
        return (float) $this->ensureWallet($tenant)->balance;
    }

    /**
     * @param  array{description?: string, pnr?: string, order_ref?: string, vendor?: string, meta?: array<string, mixed>}  $meta
     */
    public function topUp(Tenant|int $tenant, float $amount, ?User $actor = null, array $meta = []): WalletTransaction
    {
        return $this->credit($tenant, $amount, WalletTransaction::TYPE_TOPUP, $actor, $meta);
    }

    /**
     * @param  array{description?: string, pnr?: string, order_ref?: string, vendor?: string, meta?: array<string, mixed>}  $meta
     */
    public function refund(Tenant|int $tenant, float $amount, ?User $actor = null, array $meta = []): WalletTransaction
    {
        return $this->credit($tenant, $amount, WalletTransaction::TYPE_REFUND, $actor, $meta);
    }

    /**
     * Debit wallet for a ticket sale. Throws if balance is insufficient.
     *
     * @param  array{description?: string, pnr?: string, order_ref?: string, vendor?: string, meta?: array<string, mixed>}  $meta
     */
    public function debitSale(Tenant|int $tenant, float $amount, ?User $actor = null, array $meta = []): WalletTransaction
    {
        return $this->debit($tenant, $amount, WalletTransaction::TYPE_SALE, $actor, $meta);
    }

    public function reverse(WalletTransaction $original, ?User $actor = null, ?string $description = null): WalletTransaction
    {
        if ($original->type === WalletTransaction::TYPE_REVERSE) {
            throw new InvalidArgumentException('Cannot reverse a reverse transaction.');
        }

        $already = WalletTransaction::query()
            ->where('reverses_transaction_id', $original->id)
            ->exists();
        if ($already) {
            throw new RuntimeException('This transaction was already reversed.');
        }

        $meta = [
            'description' => $description ?: ('Reverse of #'.$original->id.($original->description ? ': '.$original->description : '')),
            'pnr' => $original->pnr,
            'order_ref' => $original->order_ref,
            'vendor' => $original->vendor,
            'flight_reservation_id' => $original->flight_reservation_id,
            'meta' => array_merge(is_array($original->meta) ? $original->meta : [], [
                'reversed_transaction_id' => $original->id,
            ]),
        ];

        if ($original->direction === WalletTransaction::DIR_CREDIT) {
            return $this->debit(
                (int) $original->tenant_id,
                (float) $original->amount,
                WalletTransaction::TYPE_REVERSE,
                $actor,
                $meta,
                (int) $original->id
            );
        }

        return $this->credit(
            (int) $original->tenant_id,
            (float) $original->amount,
            WalletTransaction::TYPE_REVERSE,
            $actor,
            $meta,
            (int) $original->id
        );
    }

    /**
     * Charge wallet for a reservation when payment option is Wallet.
     * Safe no-op when reservation has no tenant or FOP is not Wallet.
     */
    public function chargeReservationIfWallet(FlightReservation $reservation, ?User $actor = null): ?WalletTransaction
    {
        $fop = strtolower((string) ($reservation->form_of_payment ?? ''));
        if ($fop !== 'wallet' || ! $reservation->tenant_id) {
            return null;
        }

        $amount = (float) ($reservation->sell_total ?? 0);
        if ($amount <= 0) {
            $amount = app(MarkupService::class)->money($reservation->total_price);
        }
        if ($amount <= 0) {
            return null;
        }

        // Avoid double-charge: any non-reversed sale for this reservation.
        $saleIds = WalletTransaction::query()
            ->where('flight_reservation_id', $reservation->id)
            ->where('type', WalletTransaction::TYPE_SALE)
            ->pluck('id');
        if ($saleIds->isNotEmpty()) {
            $reversed = WalletTransaction::query()
                ->whereIn('reverses_transaction_id', $saleIds)
                ->pluck('reverses_transaction_id');
            if ($saleIds->diff($reversed)->isNotEmpty()) {
                return null;
            }
        }

        return $this->debitSale((int) $reservation->tenant_id, $amount, $actor, [
            'description' => 'Ticket sale '.$reservation->origin.'-'.$reservation->destination,
            'pnr' => (string) ($reservation->air_reservation_locator ?: $reservation->provider_locator ?: $reservation->universal_locator),
            'order_ref' => (string) ($reservation->universal_locator ?: $reservation->id),
            'vendor' => $reservation->provider(),
            'flight_reservation_id' => $reservation->id,
            'meta' => ['form_of_payment' => $reservation->form_of_payment],
        ]);
    }

    /**
     * @param  array{description?: string, pnr?: string, order_ref?: string, vendor?: string, flight_reservation_id?: int, meta?: array<string, mixed>}  $meta
     */
    protected function credit(
        Tenant|int $tenant,
        float $amount,
        string $type,
        ?User $actor,
        array $meta = [],
        ?int $reversesId = null
    ): WalletTransaction {
        $amount = round($amount, 2);
        if ($amount <= 0) {
            throw new InvalidArgumentException('Amount must be greater than zero.');
        }

        return DB::transaction(function () use ($tenant, $amount, $type, $actor, $meta, $reversesId) {
            $wallet = $this->ensureWallet($tenant);
            $wallet = Wallet::query()->whereKey($wallet->id)->lockForUpdate()->firstOrFail();
            if (! $wallet->is_active) {
                throw new RuntimeException('Wallet is inactive.');
            }

            $wallet->balance = round((float) $wallet->balance + $amount, 2);
            $wallet->save();

            return WalletTransaction::query()->create([
                'wallet_id' => $wallet->id,
                'tenant_id' => $wallet->tenant_id,
                'created_by' => $actor?->id,
                'type' => $type,
                'direction' => WalletTransaction::DIR_CREDIT,
                'amount' => $amount,
                'balance_after' => $wallet->balance,
                'currency' => $wallet->currency,
                'description' => $meta['description'] ?? null,
                'pnr' => $meta['pnr'] ?? null,
                'order_ref' => $meta['order_ref'] ?? null,
                'flight_reservation_id' => $meta['flight_reservation_id'] ?? null,
                'reverses_transaction_id' => $reversesId,
                'vendor' => $meta['vendor'] ?? null,
                'meta' => $meta['meta'] ?? null,
            ]);
        });
    }

    /**
     * @param  array{description?: string, pnr?: string, order_ref?: string, vendor?: string, flight_reservation_id?: int, meta?: array<string, mixed>}  $meta
     */
    protected function debit(
        Tenant|int $tenant,
        float $amount,
        string $type,
        ?User $actor,
        array $meta = [],
        ?int $reversesId = null
    ): WalletTransaction {
        $amount = round($amount, 2);
        if ($amount <= 0) {
            throw new InvalidArgumentException('Amount must be greater than zero.');
        }

        return DB::transaction(function () use ($tenant, $amount, $type, $actor, $meta, $reversesId) {
            $wallet = $this->ensureWallet($tenant);
            $wallet = Wallet::query()->whereKey($wallet->id)->lockForUpdate()->firstOrFail();
            if (! $wallet->is_active) {
                throw new RuntimeException('Wallet is inactive.');
            }
            if ((float) $wallet->balance < $amount) {
                throw new RuntimeException('Insufficient wallet balance.');
            }

            $wallet->balance = round((float) $wallet->balance - $amount, 2);
            $wallet->save();

            return WalletTransaction::query()->create([
                'wallet_id' => $wallet->id,
                'tenant_id' => $wallet->tenant_id,
                'created_by' => $actor?->id,
                'type' => $type,
                'direction' => WalletTransaction::DIR_DEBIT,
                'amount' => $amount,
                'balance_after' => $wallet->balance,
                'currency' => $wallet->currency,
                'description' => $meta['description'] ?? null,
                'pnr' => $meta['pnr'] ?? null,
                'order_ref' => $meta['order_ref'] ?? null,
                'flight_reservation_id' => $meta['flight_reservation_id'] ?? null,
                'reverses_transaction_id' => $reversesId,
                'vendor' => $meta['vendor'] ?? null,
                'meta' => $meta['meta'] ?? null,
            ]);
        });
    }
}
