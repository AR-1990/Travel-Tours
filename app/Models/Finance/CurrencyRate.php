<?php

namespace App\Models\Finance;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CurrencyRate extends Model
{
    protected $fillable = [
        'base_currency',
        'quote_currency',
        'rate',
        'is_active',
        'updated_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:8',
            'is_active' => 'boolean',
        ];
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function pairLabel(): string
    {
        return strtoupper($this->base_currency).'/'.strtoupper($this->quote_currency);
    }
}
