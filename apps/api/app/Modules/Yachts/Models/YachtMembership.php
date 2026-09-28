<?php

namespace App\Modules\Yachts\Models;

use App\Modules\Tenants\Models\Tenant;
use App\Modules\Tenants\Models\TenantMembership;
use App\Modules\Users\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class YachtMembership extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date'];
    }

    protected static function booted(): void
    {
        static::saving(function (YachtMembership $membership) {
            if (! Yacht::whereKey($membership->yacht_id)->where('tenant_id', $membership->tenant_id)->exists()
                || ! TenantMembership::whereKey($membership->tenant_membership_id)
                    ->where('tenant_id', $membership->tenant_id)->where('user_id', $membership->user_id)->exists()) {
                throw ValidationException::withMessages(['membership' => ['The yacht membership boundary is invalid.']]);
            }
            if ($membership->end_date && $membership->end_date->lte($membership->start_date)) {
                throw ValidationException::withMessages(['end_date' => ['End date must follow start date.']]);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function yacht(): BelongsTo
    {
        return $this->belongsTo(Yacht::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function tenantMembership(): BelongsTo
    {
        return $this->belongsTo(TenantMembership::class);
    }

    public function scopeCurrentlyActive(Builder $query): Builder
    {
        $today = now('UTC')->toDateString();

        return $query->where('status', 'active')->whereDate('start_date', '<=', $today)
            ->where(fn (Builder $query) => $query->whereNull('end_date')->orWhereDate('end_date', '>', $today));
    }
}
