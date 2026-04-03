<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    /**
     * 役割に紐づく権限
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    /**
     * 役割に紐づくユーザー
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }

    /**
     * 権限を追加
     */
    public function addPermission(Permission $permission): void
    {
        if (!$this->permissions()->where('permissions.id', $permission->id)->exists()) {
            $this->permissions()->attach($permission->id);
        }
    }

    /**
     * 権限を削除
     */
    public function removePermission(Permission $permission): void
    {
        $this->permissions()->detach($permission->id);
    }
}
