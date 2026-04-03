<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'google_id',
        'name',
        'email',
        'avatar',
        'is_active',
        'last_login_at',
        'custom_permissions',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'custom_permissions' => 'array',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function hasPermission(string $permissionName): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissionName) {
                $query->where('name', $permissionName);
            })
            ->exists();
    }

    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }

    public function getLoginRedirectUrl(): string
    {
        return route('dashboard');
    }

    public function getPrimaryRole(): ?Role
    {
        return $this->roles()->first();
    }

    public function getPrimaryRoleName(): string
    {
        $role = $this->getPrimaryRole();
        return $role ? $role->display_name : '未設定';
    }
}
