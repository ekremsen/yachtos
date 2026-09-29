<?php

namespace App\Modules\Maintenance\Models;

use App\Modules\Tenants\Models\Tenant;
use App\Modules\Yachts\Models\Yacht;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class MaintenanceTask extends Model
{
    use HasUuids;

    protected $guarded = ['id', 'tenant_id', 'yacht_id', 'completed_at', 'created_at', 'updated_at'];

    protected function casts(): array
    {
        return ['due_date' => 'date', 'completed_at' => 'immutable_datetime'];
    }

    protected static function booted(): void
    {
        static::updating(function (MaintenanceTask $task): void {
            if ($task->isDirty(['tenant_id', 'yacht_id'])) {
                throw new LogicException('Maintenance ownership is immutable.');
            }
        });
        static::deleting(fn () => throw new LogicException('Retain maintenance history; hard deletion is prohibited.'));
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

    public function assignments(): HasMany
    {
        return $this->hasMany(MaintenanceAssignment::class);
    }

    public function isOverdue(?string $today = null): bool
    {
        return in_array($this->status, ['planned', 'in_progress'], true)
            && $this->due_date->toDateString() < ($today ?? now('UTC')->toDateString());
    }
}
