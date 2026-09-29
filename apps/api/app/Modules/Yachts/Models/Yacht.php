<?php

namespace App\Modules\Yachts\Models;

use App\Modules\Crew\Models\CrewMember;
use App\Modules\Maintenance\Models\MaintenanceTask;
use App\Modules\Tenants\Models\Tenant;
use App\Modules\Users\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class Yacht extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::updating(function (Yacht $yacht) {
            if ($yacht->isDirty('tenant_id')) {
                throw new LogicException('Yacht tenant ownership is immutable.');
            }
        });
        static::deleting(fn () => throw new LogicException('Archive yachts instead of deleting them.'));
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(YachtMembership::class);
    }

    public function crewMembers(): HasMany
    {
        return $this->hasMany(CrewMember::class);
    }

    public function maintenanceTasks(): HasMany
    {
        return $this->hasMany(MaintenanceTask::class);
    }

    public function scopeAccessibleTo(Builder $query, User $user, Tenant $tenant): Builder
    {
        return $query->where('tenant_id', $tenant->id)->where('status', 'active')
            ->whereHas('memberships', fn (Builder $memberships) => $memberships
                ->where('tenant_id', $tenant->id)->where('user_id', $user->id)->currentlyActive()
                ->whereHas('tenantMembership', fn (Builder $memberships) => $memberships->currentlyActive()));
    }
}
