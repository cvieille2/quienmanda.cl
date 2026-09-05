<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    protected $table = 'users';

    protected $fillable = ['name', 'email', 'password', 'role', 'status'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'role'              => UserRole::class,
        'status'            => UserStatus::class,
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
    ];

    public const ROLE_ADMIN = UserRole::Admin->value;

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'actor_id', 'id')
            ->where('actor_type', AuditLog::ACTOR_ADMIN);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }
}
