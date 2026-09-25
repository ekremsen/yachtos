<?php

namespace App\Modules\Tenants\Models;

use App\Modules\Users\Models\User;
use Database\Factories\TenantMembershipFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantMembership extends Model
{
    /** @use HasFactory<TenantMembershipFactory> */
    use HasFactory, HasUuids;

    // Memberships are provisioned internally; HTTP input must not assign access.
    protected $guarded = ['*'];

    protected static function newFactory(): TenantMembershipFactory
    {
        return TenantMembershipFactory::new();
    }

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeCurrentlyActive(Builder $query): Builder
    {
        $today = now('UTC')->toDateString();

        return $query->where('status', 'active')
            ->whereDate('start_date', '<=', $today)
            ->where(fn (Builder $query) => $query->whereNull('end_date')->orWhereDate('end_date', '>', $today));
    }
}
