<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\System\Permission;
use App\Models\Users\User;
use App\Models\System\Tenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Role extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'category',
        'description',
        'is_system',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the permissions for the role.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions')
                    ->withTimestamps();
    }

    /**
     * Get the users with this role.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if the role has a specific permission.
     * Admin role (id = 1) has all permissions by default.
     */
    public function hasPermission(string $permissionSlug): bool
    {
        // Global super-admin role keeps full access.
        if ($this->slug === 'super-admin' && $this->tenant_id === null) {
            return true;
        }
        
        return $this->permissions()->where('slug', $permissionSlug)->exists();
    }
}
