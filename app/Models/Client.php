<?php

namespace App\Models;

use App\Enums\ClientStatus;
use App\Traits\AuditsSystemChanges;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory;
    use SoftDeletes;
    use AuditsSystemChanges;

    protected $fillable = [
        'client_code',
        'name',
        'company_name',
        'email',
        'phone',
        'whatsapp',
        'gst_number',
        'client_type',
        'status',
        'address',
        'city',
        'state',
        'pincode',
        'notes',
        'created_by',
    ];

    /*
    |--------------------------------------------------------------------------
    | System Audit Exclusions
    |--------------------------------------------------------------------------
    |
    | The routine updated_at timestamp is excluded so audit records focus on
    | meaningful client data changes.
    |
    */

    protected array $auditExclude = [
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ClientStatus::class,
        ];
    }

    public function projects(): HasMany
    {
        return $this->hasMany(
            Project::class
        );
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function scopeSearch(
        Builder $query,
        ?string $search
    ): Builder {
        return $query->when(
            filled($search),
            function (
                Builder $query
            ) use ($search): void {
                $query->where(
                    function (
                        Builder $query
                    ) use ($search): void {
                        $query
                            ->where(
                                'name',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'company_name',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'client_code',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'email',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'phone',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'whatsapp',
                                'like',
                                "%{$search}%"
                            );
                    }
                );
            }
        );
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->company_name
            ?: $this->name;
    }

    public function getLocationAttribute(): ?string
    {
        $location = collect([
            $this->city,
            $this->state,
        ])
            ->filter()
            ->implode(', ');

        return $location !== ''
            ? $location
            : null;
    }

    public function payments(): HasMany
    {
        return $this->hasMany(
            Payment::class
        );
    }

    public function paymentFollowups(): HasMany
    {
        return $this->hasMany(
            PaymentFollowup::class
        );
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(
            ProjectTicket::class
        );
    }

    public function portalUsers(): HasMany
    {
        return $this->hasMany(
            ClientUser::class
        );
    }
}

