<?php

namespace App\Services\Finance;

use App\Models\Finance\CurrencyRate;

class CurrencyRateService
{
    /**
     * Manual rate: how many quote units for 1 base unit.
     * Returns 1.0 when currencies match or no rate is configured.
     */
    public function rate(string $base, string $quote): float
    {
        $base = strtoupper(trim($base));
        $quote = strtoupper(trim($quote));
        if ($base === '' || $quote === '' || $base === $quote) {
            return 1.0;
        }

        $direct = CurrencyRate::query()
            ->where('is_active', true)
            ->where('base_currency', $base)
            ->where('quote_currency', $quote)
            ->value('rate');
        if ($direct !== null && (float) $direct > 0) {
            return (float) $direct;
        }

        $inverse = CurrencyRate::query()
            ->where('is_active', true)
            ->where('base_currency', $quote)
            ->where('quote_currency', $base)
            ->value('rate');
        if ($inverse !== null && (float) $inverse > 0) {
            return round(1 / (float) $inverse, 8);
        }

        return 1.0;
    }

    public function convert(float $amount, string $from, string $to): float
    {
        return round($amount * $this->rate($from, $to), 2);
    }
}
