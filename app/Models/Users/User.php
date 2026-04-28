<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\System\Permission;
use App\Models\System\Role;
use App\Models\System\Tenant;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'slug',
        'email',
        'password',
        'phone',
        'email_verified_at',
        'role_id',
        'tenant_id',
        'designation',
        'user_type',
        'is_active',
    ];

    /**
     * Get the role that belongs to the user.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the user-specific permissions (direct permissions assigned to this user).
     */
    public function userPermissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')
                    ->withTimestamps();
    }

    /**
     * Check if the user has a specific permission.
     * Admin users (role_id = 1) have all permissions by default.
     * For other users, checks both user-specific permissions and role permissions.
     * Role 4 (User) users don't have admin permissions.
     */
    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->user_type === 'super_admin') {
            return true;
        }

        if ($this->user_type === 'public') {
            return false;
        }

        if (!$this->role_id) {
            return false;
        }
        
        // Load userPermissions if not already loaded
        if (!$this->relationLoaded('userPermissions')) {
            $this->load('userPermissions');
        }
        
        // Check user-specific permissions first (takes precedence)
        if ($this->userPermissions && $this->userPermissions->where('slug', $permissionSlug)->isNotEmpty()) {
            return true;
        }
        
        // If no user-specific permission, check role permissions
        if (!$this->relationLoaded('role')) {
            $this->load('role');
        }
        
        if (!$this->role) {
            return false;
        }

        return $this->role->hasPermission($permissionSlug);
    }

    /**
     * Check if the user has a specific role.
     */
    public function hasRole(string $roleSlug): bool
    {
        if (!$this->role) {
            return false;
        }

        return $this->role->slug === $roleSlug;
    }

    public function canAccessAdminPanel(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->tenant_id) {
            if (!$this->relationLoaded('tenant')) {
                $this->load('tenant');
            }

            if (!$this->tenant || !$this->tenant->is_active || $this->tenant->status !== 'approved') {
                return false;
            }
        }

        if ($this->user_type === 'super_admin') {
            return true;
        }

        if ($this->user_type === 'tenant_admin' || $this->user_type === 'sub_agent') {
            return $this->hasPermission('dashboard.view');
        }

        return false;
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }
}
