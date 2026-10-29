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
        'form_of_payment',
        'service_fee',
        'airline_fees_total',
        'airline_fees_snapshot',
        'markup_amount',
        'sell_total',
        'agent_profit',
        'commission_amount',
        'currency',
        'booking_currency',
        'currency_rate',
        'currency_rate_base',
        'print_with_price',
        'itinerary',
        'price_snapshot',
        'ticket_numbers',
        'raw_result',
        'gds_snapshot',
        'gds_version',
        'booked_at',
        'ticketed_at',
        'cancelled_at',
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
            'gds_snapshot' => 'array',
            'service_fee' => 'decimal:2',
            'airline_fees_total' => 'decimal:2',
            'airline_fees_snapshot' => 'array',
            'markup_amount' => 'decimal:2',
            'sell_total' => 'decimal:2',
            'agent_profit' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'currency_rate' => 'decimal:8',
            'print_with_price' => 'boolean',
            'booked_at' => 'datetime',
            'ticketed_at' => 'datetime',
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

    public function provider(): string
    {
        $fromRaw = strtolower((string) data_get($this->raw_result, 'provider', ''));
        if (in_array($fromRaw, \App\Support\FlightProvider::all(), true)) {
            return $fromRaw;
        }

        $fromSnapshot = strtolower((string) data_get($this->price_snapshot, 'provider', ''));
        if (in_array($fromSnapshot, \App\Support\FlightProvider::all(), true)) {
            return $fromSnapshot;
        }

        return 'travelport';
    }

    /**
     * Filter by effective provider (raw_result / price_snapshot / default Travelport).
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\FlightReservation>  $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\FlightReservation>
     */
    public function scopeWhereProvider($query, ?string $provider)
    {
        $provider = strtolower(trim((string) $provider));
        if ($provider === '' || ! in_array($provider, \App\Support\FlightProvider::all(), true)) {
            return $query;
        }

        // Match FlightReservation::provider() resolution order.
        return $query->whereRaw(
            "LOWER(COALESCE(
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(raw_result, '$.provider')), ''),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(price_snapshot, '$.provider')), ''),
                'travelport'
            )) = ?",
            [$provider]
        );
    }

    public function isSunSpring(): bool
    {
        return $this->provider() === 'sunspring';
    }

    public function isDowntownTravel(): bool
    {
        return $this->provider() === 'downtown_travel';
    }

    public function isPublicChannel(): bool
    {
        return strtolower(trim((string) $this->channel)) === 'public';
    }

    /**
     * B2C customers must not run Downtown agent ticketing (agent_cash / void / refund).
     * Tickets are issued by agency staff only after payment is confirmed.
     */
    public function allowsCustomerDowntownTicketing(): bool
    {
        if (! $this->isDowntownTravel()) {
            return true;
        }

        return ! $this->isPublicChannel();
    }

    /**
     * Card / bank FOPs on B2C cannot issue without collected payment details.
     */
    public function requiresCollectedPaymentBeforeIssue(): bool
    {
        $fop = strtolower(trim((string) $this->form_of_payment));

        return in_array($fop, ['credit', 'debit', 'banktransfer'], true);
    }

    public function providerLabel(): string
    {
        return \App\Support\FlightProvider::label($this->provider());
    }

    public function passengerName(): string
    {
        return trim(($this->passenger_prefix ? $this->passenger_prefix.' ' : '').($this->passenger_first ?? '').' '.($this->passenger_last ?? ''));
    }

    public function pnrLabel(): string
    {
        foreach ([$this->air_reservation_locator, $this->provider_locator, $this->universal_locator] as $code) {
            $code = trim((string) $code);
            if ($code !== '') {
                return $code;
            }
        }

        return '—';
    }

    public function ticketNumbersLabel(): string
    {
        $tickets = is_array($this->ticket_numbers) ? $this->ticket_numbers : [];
        $flat = [];
        foreach ($tickets as $ticket) {
            if (is_array($ticket)) {
                $n = trim((string) ($ticket['number'] ?? $ticket['ticket_number'] ?? ''));
                if ($n !== '') {
                    $flat[] = $n;
                }
            } else {
                $n = trim((string) $ticket);
                if ($n !== '') {
                    $flat[] = $n;
                }
            }
        }

        return $flat === [] ? '—' : implode(', ', $flat);
    }

    public function airlineSalesLabel(): string
    {
        $code = strtoupper(trim((string) $this->carrier));
        if ($code === '') {
            return '—';
        }
        $name = FlightDisplay::airlineName($code);

        return $name !== '' && strtoupper($name) !== $code ? $code.' · '.$name : $code;
    }

    public function salesAgencyLabel(): string
    {
        if ($this->tenant?->name) {
            return (string) $this->tenant->name;
        }
        if ($this->isPublicChannel()) {
            return 'B2C / Public';
        }

        return '—';
    }

    public function salesAgentLabel(): string
    {
        $user = $this->user;
        if (! $user) {
            return '—';
        }

        $name = trim((string) (($user->first_name ?? '').' '.($user->last_name ?? '')));
        if ($name !== '') {
            return $name;
        }

        return trim((string) ($user->username ?: $user->email)) ?: '—';
    }

    public function salesRouteLabel(): string
    {
        $codes = [];
        $segments = data_get($this->itinerary, 'segments');
        if (! is_array($segments) || $segments === []) {
            $segments = [];
            foreach (data_get($this->itinerary, 'journeys', []) as $journey) {
                foreach (($journey['segments'] ?? []) as $seg) {
                    $segments[] = $seg;
                }
            }
        }
        foreach ($segments as $seg) {
            if (! is_array($seg)) {
                continue;
            }
            $o = strtoupper(trim((string) ($seg['origin'] ?? '')));
            $d = strtoupper(trim((string) ($seg['destination'] ?? '')));
            if ($o !== '' && ($codes === [] || end($codes) !== $o)) {
                $codes[] = $o;
            }
            if ($d !== '') {
                $codes[] = $d;
            }
        }
        if ($codes !== []) {
            return implode('-', $codes);
        }

        $o = strtoupper(trim((string) $this->origin));
        $d = strtoupper(trim((string) $this->destination));

        return ($o !== '' && $d !== '') ? $o.'-'.$d : '—';
    }

    public function salesArrivalLabel(): string
    {
        if ($this->return_date) {
            return $this->return_date->format('Y-m-d');
        }

        $segments = data_get($this->itinerary, 'segments');
        if (is_array($segments) && $segments !== []) {
            $last = end($segments);
            $arrival = is_array($last) ? ($last['arrival'] ?? null) : null;
            if ($arrival) {
                try {
                    return \Illuminate\Support\Carbon::parse((string) $arrival)->format('Y-m-d');
                } catch (\Throwable) {
                }
            }
        }

        return optional($this->departure_date)?->format('Y-m-d') ?: '—';
    }

    public function salesCurrency(): string
    {
        $cur = strtoupper(trim((string) ($this->currency ?: $this->booking_currency)));
        if ($cur !== '') {
            return $cur;
        }
        foreach ([$this->total_price, $this->base_price, $this->taxes] as $raw) {
            $parsed = FlightDisplay::parsePrice(is_scalar($raw) ? (string) $raw : null);
            if ($parsed && ($parsed['currency'] ?? '') !== '') {
                return $parsed['currency'];
            }
        }

        return '—';
    }

    public function moneyAmount(mixed $raw): ?float
    {
        if ($raw === null || $raw === '') {
            return null;
        }
        if (is_int($raw) || is_float($raw)) {
            return round((float) $raw, 2);
        }

        $value = trim((string) $raw);
        if ($value === '') {
            return null;
        }

        $parsed = FlightDisplay::parsePrice($value);
        if ($parsed && isset($parsed['amount']) && is_numeric(str_replace(',', '', $parsed['amount']))) {
            return round((float) str_replace(',', '', $parsed['amount']), 2);
        }

        if (is_numeric($value)) {
            return round((float) $value, 2);
        }

        $cleaned = preg_replace('/[^\d.]/', '', $value) ?? '';
        if ($cleaned === '' || $cleaned === '.') {
            return null;
        }

        return round((float) $cleaned, 2);
    }

    public function salesTotalAmount(): ?float
    {
        return $this->moneyAmount($this->sell_total)
            ?? $this->moneyAmount($this->total_price);
    }

    public function salesBaseAmount(): ?float
    {
        return $this->moneyAmount($this->base_price);
    }

    public function salesTaxAmount(): ?float
    {
        return $this->moneyAmount($this->taxes);
    }

    public function salesFuelOtherAmount(): ?float
    {
        return $this->moneyAmount($this->airline_fees_total) ?? 0.0;
    }

    public function salesServiceFeeAmount(): ?float
    {
        // Per-person ticket fee stored on service_fee; fall back to markup total.
        if ($this->service_fee !== null && $this->service_fee !== '') {
            return $this->moneyAmount($this->service_fee);
        }

        return $this->moneyAmount($this->markup_amount) ?? 0.0;
    }

    public function salesMarkupAmount(): ?float
    {
        return $this->moneyAmount($this->markup_amount) ?? 0.0;
    }

    public function salesCommissionAmount(): ?float
    {
        return $this->moneyAmount($this->commission_amount) ?? 0.0;
    }

    public function salesProfitAmount(): ?float
    {
        return $this->moneyAmount($this->agent_profit)
            ?? $this->moneyAmount($this->commission_amount)
            ?? 0.0;
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
            $snapshot = $this->price_snapshot;
            if (empty($snapshot['provider'])) {
                $snapshot['provider'] = $this->provider();
            }

            return $snapshot;
        }

        if (! is_array($this->itinerary) || $this->itinerary === []) {
            return null;
        }

        return [
            'ok' => true,
            'provider' => $this->provider(),
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
