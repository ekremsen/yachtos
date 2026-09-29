<?php

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\InventoryItem;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Inventory\Support\StockQuantity;
use App\Modules\Users\Models\User;
use App\Support\Tenancy\TenantContext;
use App\Support\Yachts\YachtContext;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryManager
{
    public function __construct(private TenantContext $tenantContext, private YachtContext $yachtContext) {}

    public function create(array $data): InventoryItem
    {
        $item = new InventoryItem(Arr::except($data, ['current_quantity', 'tenant_id', 'yacht_id']));
        $item->forceFill([
            'tenant_id' => $this->tenantContext->tenant()->id,
            'yacht_id' => $this->yachtContext->yacht()->id,
            'current_quantity' => '0.000',
        ])->save();

        return $item;
    }

    public function update(InventoryItem $item, array $data): InventoryItem
    {
        $item->fill(Arr::except($data, ['tenant_id', 'yacht_id', 'current_quantity']))->save();

        return $item->refresh();
    }

    public function move(InventoryItem $item, User $actor, array $data): StockMovement
    {
        return DB::transaction(function () use ($item, $actor, $data): StockMovement {
            $locked = InventoryItem::forYacht($this->yachtContext->yacht()->id)
                ->where('tenant_id', $this->tenantContext->tenant()->id)
                ->whereKey($item->id)->lockForUpdate()->firstOrFail();

            $requested = StockQuantity::milli((string) $data['quantity']);
            $delta = match ($data['type']) {
                'in' => $requested > 0 ? $requested : throw ValidationException::withMessages(['quantity' => ['Stock-in quantity must be positive.']]),
                'out' => $requested > 0 ? -$requested : throw ValidationException::withMessages(['quantity' => ['Stock-out quantity must be positive.']]),
                'adjustment' => $requested !== 0 ? $requested : throw ValidationException::withMessages(['quantity' => ['Adjustment must be non-zero.']]),
            };
            $current = StockQuantity::milli((string) $locked->current_quantity);
            $result = $current + $delta;
            if ($result < 0) {
                throw ValidationException::withMessages(['quantity' => ['This movement would make stock negative.']]);
            }
            if ($result > 999_999_999_999) {
                throw ValidationException::withMessages(['quantity' => ['Resulting balance exceeds decimal(12,3) capacity.']]);
            }

            $movement = new StockMovement;
            $movement->forceFill([
                'tenant_id' => $this->tenantContext->tenant()->id,
                'yacht_id' => $this->yachtContext->yacht()->id,
                'inventory_item_id' => $locked->id,
                'performed_by_user_id' => $actor->id,
                'type' => $data['type'],
                'quantity' => StockQuantity::format($delta),
                'balance_after' => StockQuantity::format($result),
                'reason' => $data['reason'],
                'note' => $data['note'] ?? null,
                'created_at' => now('UTC'),
            ])->save();
            $locked->applyMovementBalance(StockQuantity::format($result));

            return $movement->load('actor');
        }, 3);
    }
}
