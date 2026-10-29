<?php

namespace App\Support;

use App\Models\Finance\WalletTransaction;

/**
 * Shared account-statement row shape for admin + agency UI/CSV.
 */
class AccountStatementReport
{
    /**
     * @return list<string>
     */
    public static function csvHeaders(bool $includeCustomer = true): array
    {
        $headers = [
            'debit',
            'credit',
            'balance',
            'tx_date',
            'maturity',
            'transaction_type',
            'description',
            'pnr',
            'order_id',
            'seller',
            'carrier',
            'provider',
            'unit',
        ];
        if ($includeCustomer) {
            array_splice($headers, 9, 0, ['customer']);
        }

        return $headers;
    }

    /**
     * @return list<string|float|null>
     */
    public static function csvRow(WalletTransaction $tx, bool $includeCustomer = true): array
    {
        $row = [
            $tx->debitAmount(),
            $tx->creditAmount(),
            (float) $tx->balance_after,
            optional($tx->created_at)?->format('Y-m-d H:i'),
            $tx->maturityLabel(),
            $tx->typeLabel(),
            $tx->descriptionLabel(),
            $tx->pnrLabel(),
            $tx->orderIdLabel(),
            $tx->sellerLabel(),
            $tx->carrierLabel(),
            $tx->providerLabel(),
            $tx->unitLabel(),
        ];
        if ($includeCustomer) {
            array_splice($row, 9, 0, [$tx->customerLabel()]);
        }

        return $row;
    }
}
