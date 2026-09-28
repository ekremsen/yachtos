<?php

namespace App\Modules\Crew\Models;

use App\Modules\Tenants\Models\Tenant;
use App\Modules\Yachts\Models\Yacht;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class CrewMember extends Model
{
    use HasUuids;

    protected $guarded = ['id', 'tenant_id', 'yacht_id', 'created_at', 'updated_at'];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date'];
    }

    protected static function booted(): void
    {
        static::updating(function (CrewMember $member): void {
            if ($member->isDirty(['tenant_id', 'yacht_id'])) {
                throw new LogicException('Crew ownership is immutable.');
            }
        });

        static::deleting(fn () => throw new LogicException('Deactivate crew members instead of deleting them.'));
    }

    public function scopeForYacht(Builder $query, string $yachtId): Builder
    {
        return $query->where('yacht_id', $yachtId);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function yacht(): BelongsTo
    {
        return $this->belongsTo(Yacht::class);
    }
}
