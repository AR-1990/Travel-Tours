<?php

namespace App\Models\Finance;

use App\Models\System\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AirlineFee extends Model
{
    protected $fillable = [
        'airline_code',
        'fee_type',
        'label',
        'mode',
        'amount',
        'currency',
        'is_active',
        'tenant_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
