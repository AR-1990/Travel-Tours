<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\System\Permission;
use App\Models\System\Role;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
    ];

    /**
     * Get the role that belongs to the user.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
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
        // Admin users (role_id = 1) have all permissions
        if ($this->role_id == 1) {
            return true;
        }
        
        // Role 4 (User) users don't have admin permissions
        if ($this->role_id == 4) {
            return false;
        }
        
        // If role_id is null or invalid, return false
        if (!$this->role_id || !in_array($this->role_id, [1, 2, 3])) {
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

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
