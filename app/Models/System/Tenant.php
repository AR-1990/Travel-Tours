<?php

namespace App\Models\System;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    public const OFFICE_B2B = 'b2b_agent';

    public const OFFICE_GSA = 'gsa_agent';

    public const OFFICE_API = 'api_agent';

    protected $fillable = [
        'name',
        'slug',
        'agency_code',
        'agent_code',
        'email',
        'phone',
        'assigned_by',
        'office_type',
        'debtor_type_id',
        'tax_number',
        'reg_number',
        'address_country',
        'address_state',
        'address_city',
        'address_line',
        'documents',
        'logo',
        'currency',
        'parent_tenant_id',
        'is_active',
        'status',
        'approved_at',
        'approved_by',
        'approval_notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'approved_at' => 'datetime',
        'documents' => 'array',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function debtorType(): BelongsTo
    {
        return $this->belongsTo(DebtorType::class, 'debtor_type_id');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'parent_tenant_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Tenant::class, 'parent_tenant_id');
    }
}
