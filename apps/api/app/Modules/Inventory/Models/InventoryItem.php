<?php

namespace App\Modules\Inventory\Models;

use App\Modules\Inventory\Support\StockQuantity;
use App\Modules\Tenants\Models\Tenant;
use App\Modules\Yachts\Models\Yacht;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class InventoryItem extends Model
{
    use HasUuids;

    protected bool $balanceMutationAllowed = false;

    protected $guarded = ['id', 'tenant_id', 'yacht_id', 'current_quantity', 'created_at', 'updated_at'];

    protected function casts(): array
    {
        return ['current_quantity' => 'decimal:3', 'minimum_quantity' => 'decimal:3'];
    }

    protected static function booted(): void
    {
        static::updating(function (InventoryItem $item): void {
            if ($item->isDirty(['tenant_id', 'yacht_id'])) {
                throw new LogicException('Inventory ownership is immutable.');
            }
            if ($item->isDirty('current_quantity') && ! $item->balanceMutationAllowed) {
                throw new LogicException('Inventory balance changes require a StockMovement.');
            }
        });
        static::deleting(fn () => throw new LogicException('Retain inventory and stock history; hard deletion is prohibited.'));
    }

    public function scopeForYacht(Builder $query, string $yachtId): Builder
    {
        return $query->where('yacht_id', $yachtId);
    }

    public function isLowStock(): bool
    {
        return $this->minimum_quantity !== null
            && StockQuantity::milli((string) $this->current_quantity) <= StockQuantity::milli((string) $this->minimum_quantity);
    }

    public function applyMovementBalance(string $balance): void
    {
        $this->balanceMutationAllowed = true;
        try {
            $this->forceFill(['current_quantity' => $balance])->save();
        } finally {
            $this->balanceMutationAllowed = false;
        }
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function yacht(): BelongsTo
    {
        return $this->belongsTo(Yacht::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
