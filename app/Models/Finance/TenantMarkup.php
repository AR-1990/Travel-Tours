<?php

namespace App\Models\Finance;

use App\Models\System\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantMarkup extends Model
{
    public const SCOPE_GLOBAL = 'global';

    public const SCOPE_PROVIDER = 'provider';

    public const SCOPE_AIRLINE = 'airline';

    public const SCOPE_AGENT = 'agent';

    public const MODE_PERCENT = 'percent';

    public const MODE_FIXED = 'fixed';

    protected $fillable = [
        'tenant_id',
        'scope',
        'scope_key',
        'mode',
        'value',
        'is_active',
        'priority',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:4',
            'is_active' => 'boolean',
            'priority' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function label(): string
    {
        return match ($this->scope) {
            self::SCOPE_AIRLINE => 'Airline '.strtoupper((string) $this->scope_key),
            self::SCOPE_PROVIDER => 'Provider '.strtolower((string) $this->scope_key),
            self::SCOPE_AGENT => 'Agent #'.((string) ($this->tenant_id ?: $this->scope_key)),
            default => 'Global',
        };
    }
}
