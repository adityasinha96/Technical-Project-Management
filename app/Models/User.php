<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory;
    use HasRoles;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar',
        'status',
        'password',
        'email_verified_at',
        'last_login_at',
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
            'password' => 'hashed',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function managedProjects(): HasMany
    {
        return $this->hasMany(
            Project::class,
            'manager_id'
        );
    }

    public function assignedProjects(): BelongsToMany
    {
        return $this
            ->belongsToMany(Project::class)
            ->withPivot([
                'assignment_role',
                'assigned_by',
                'assigned_at',
            ])
            ->withTimestamps();
    }

    public function uploadedProjectFiles(): HasMany
    {
        return $this->hasMany(
            ProjectFile::class,
            'uploaded_by'
        );
    }
}