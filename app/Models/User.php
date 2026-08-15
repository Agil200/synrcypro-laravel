<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
        'role',
        'role_id',
        'is_active',
        'last_login_at',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * Role database baru. Kolom string `role` tetap dipertahankan sementara
     * untuk kompatibilitas Notification dan module existing.
     */
    public function accessRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function isSuperAdministrator(): bool
    {
        return $this->accessRole?->slug === 'super-administrator';
    }

    public function hasPermission(string $permission): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        if ($this->isSuperAdministrator()) {
            return true;
        }

        if (! $this->accessRole) {
            return false;
        }

        return $this->accessRole
            ->permissions()
            ->where('slug', $permission)
            ->exists();
    }

    public function initials(): string
    {
        return collect(explode(' ', $this->name))
            ->filter()
            ->take(2)
            ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
    }
}
