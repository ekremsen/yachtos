<?php

namespace App\Modules\Inventory\Models;

use App\Modules\Tenants\Models\Tenant;
use App\Modules\Users\Models\User;
use App\Modules\Yachts\Models\Yacht;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class StockMovement extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $guarded = ['*'];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:3', 'balance_after' => 'decimal:3', 'created_at' => 'immutable_datetime'];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Stock movements are immutable.'));
        static::deleting(fn () => throw new LogicException('Stock movements are immutable.'));
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by_user_id');
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
