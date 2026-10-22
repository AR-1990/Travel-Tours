<?php

namespace App\Services\Finance;

use App\Models\Finance\AirlineFee;
use App\Models\Finance\TenantMarkup;
use App\Support\FlightDisplay;

class MarkupService
{
    /**
     * Resolve the best matching markup rule.
     * Markup is always calculated on base fare only (never taxes/total).
     *
     * @return array{rule: ?TenantMarkup, amount: float, mode: string, value: float}
     */
    public function resolve(?int $tenantId, ?string $provider, ?string $airline, float $baseFare): array
    {
        $provider = strtolower(trim((string) $provider));
        $airline = strtoupper(trim((string) $airline));
        $baseFare = max(0, $baseFare);

        $rules = TenantMarkup::query()
            ->where('is_active', true)
            ->where(function ($q) use ($tenantId) {
                $q->whereNull('tenant_id');
                if ($tenantId) {
                    $q->orWhere('tenant_id', $tenantId);
                }
            })
            ->orderBy('priority')
            ->orderByDesc('id')
            ->get();

        $specificity = [
            TenantMarkup::SCOPE_AIRLINE => 4,
            TenantMarkup::SCOPE_PROVIDER => 3,
            TenantMarkup::SCOPE_AGENT => 2,
            TenantMarkup::SCOPE_GLOBAL => 1,
        ];

        $best = null;
        $bestScore = -1;
        foreach ($rules as $rule) {
            $matches = match ($rule->scope) {
                TenantMarkup::SCOPE_AIRLINE => $airline !== '' && strtoupper((string) $rule->scope_key) === $airline,
                TenantMarkup::SCOPE_PROVIDER => $provider !== '' && strtolower((string) $rule->scope_key) === $provider,
                TenantMarkup::SCOPE_AGENT => $tenantId && (int) $rule->tenant_id === (int) $tenantId,
                TenantMarkup::SCOPE_GLOBAL => true,
                default => false,
            };
            if (! $matches) {
                continue;
            }
            $score = ($specificity[$rule->scope] ?? 0) * 1000 - (int) $rule->priority;
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $rule;
            }
        }

        if (! $best) {
            return ['rule' => null, 'amount' => 0.0, 'mode' => TenantMarkup::MODE_FIXED, 'value' => 0.0];
        }

        $amount = $best->mode === TenantMarkup::MODE_PERCENT
            ? round($baseFare * ((float) $best->value / 100), 2)
            : round((float) $best->value, 2);

        return [
            'rule' => $best,
            'amount' => $amount,
            'mode' => (string) $best->mode,
            'value' => (float) $best->value,
        ];
    }

    /**
     * Active airline fees (service / ticketing / other) for carrier.
     *
     * @return array{total: float, items: list<array{id:int,label:string,fee_type:string,amount:float,currency:string}>}
     */
    public function airlineFees(?string $airline, float $baseFare, ?int $tenantId = null): array
    {
        $airline = strtoupper(trim((string) $airline));
        if ($airline === '') {
            return ['total' => 0.0, 'items' => []];
        }

        $fees = AirlineFee::query()
            ->where('is_active', true)
            ->where('airline_code', $airline)
            ->where(function ($q) use ($tenantId) {
                $q->whereNull('tenant_id');
                if ($tenantId) {
                    $q->orWhere('tenant_id', $tenantId);
                }
            })
            ->orderBy('id')
            ->get();

        $items = [];
        $total = 0.0;
        foreach ($fees as $fee) {
            $amount = $fee->mode === 'percent'
                ? round($baseFare * ((float) $fee->amount / 100), 2)
                : round((float) $fee->amount, 2);
            $total += $amount;
            $items[] = [
                'id' => (int) $fee->id,
                'label' => (string) $fee->label,
                'fee_type' => (string) $fee->fee_type,
                'amount' => $amount,
                'currency' => strtoupper((string) $fee->currency),
            ];
        }

        return ['total' => round($total, 2), 'items' => $items];
    }

    /**
     * @param  array<string, mixed>|null  $solution
     * @return array<string, mixed>
     */
    public function breakdown(
        ?array $solution,
        ?int $tenantId,
        ?string $provider,
        float $serviceFee = 0.0,
        ?string $formOfPayment = null,
        ?string $agentCurrency = null
    ): array {
        $fare = $this->money($solution['base_price'] ?? null);
        $taxes = $this->money($solution['taxes'] ?? null);
        $total = $this->money($solution['total_price'] ?? null);
        $currency = $this->currency($solution['total_price'] ?? $solution['base_price'] ?? null) ?: 'USD';

        if ($total <= 0 && ($fare > 0 || $taxes > 0)) {
            $total = round($fare + $taxes, 2);
        }
        if ($fare <= 0 && $total > 0 && $taxes >= 0) {
            $fare = round(max(0, $total - $taxes), 2);
        }

        $airline = strtoupper((string) ($solution['plating_carrier'] ?? data_get($solution, 'segments.0.carrier', '')));
        // Markup ONLY on base fare (0 if base unknown — never on taxes/total).
        $markup = $this->resolve($tenantId, $provider, $airline, $fare);
        $airlineFees = $this->airlineFees($airline, $fare, $tenantId);
        $serviceFee = round($serviceFee, 2);
        $ticketingFee = 0.0;
        foreach ($airlineFees['items'] as $item) {
            if (($item['fee_type'] ?? '') === 'ticketing') {
                $ticketingFee += (float) $item['amount'];
            }
        }
        $otherAirlineFees = round($airlineFees['total'] - $ticketingFee, 2);

        $sellTotal = round($total + $markup['amount'] + $serviceFee + $airlineFees['total'], 2);
        $agentProfit = round($markup['amount'] + $serviceFee, 2);

        $agentCurrency = strtoupper(trim((string) ($agentCurrency ?: '')));
        $currencyRate = 1.0;
        $commissionAmount = $agentProfit;
        if ($agentCurrency !== '' && $agentCurrency !== $currency) {
            $currencyRate = app(CurrencyRateService::class)->rate($currency, $agentCurrency);
            $commissionAmount = round($agentProfit * $currencyRate, 2);
        }

        return [
            'fare' => $fare,
            'taxes' => $taxes,
            'airline_total' => $total,
            'ticketing_fee' => round($ticketingFee, 2),
            'airline_fees_total' => $airlineFees['total'],
            'airline_fees' => $airlineFees['items'],
            'other_airline_fees' => $otherAirlineFees,
            'service_fee' => $serviceFee,
            'markup_amount' => $markup['amount'],
            'sell_total' => $sellTotal,
            'agent_profit' => $agentProfit,
            'commission_amount' => $commissionAmount,
            'currency' => $currency,
            'booking_currency' => $agentCurrency !== '' ? $agentCurrency : $currency,
            'currency_rate' => $currencyRate,
            'currency_rate_base' => $currency,
            'form_of_payment' => $formOfPayment,
            'markup_mode' => $markup['mode'],
            'markup_value' => $markup['value'],
        ];
    }

    public function money(mixed $price): float
    {
        $parsed = FlightDisplay::parsePrice($price);
        if (is_array($parsed) && isset($parsed['amount'])) {
            return round((float) str_replace(',', '', (string) $parsed['amount']), 2);
        }

        if (is_numeric($price)) {
            return round((float) $price, 2);
        }

        if (is_string($price) && preg_match('/([0-9]+(?:\.[0-9]+)?)/', $price, $m)) {
            return round((float) $m[1], 2);
        }

        return 0.0;
    }

    public function currency(mixed $price): ?string
    {
        $parsed = FlightDisplay::parsePrice($price);
        if (is_array($parsed) && ! empty($parsed['currency'])) {
            return strtoupper((string) $parsed['currency']);
        }

        if (is_string($price) && preg_match('/^([A-Z]{3})/i', trim($price), $m)) {
            return strtoupper($m[1]);
        }

        return null;
    }
}
