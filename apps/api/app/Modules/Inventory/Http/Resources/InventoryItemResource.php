<?php

namespace App\Modules\Inventory\Http\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'unit' => $this->unit,
            'current_quantity' => $this->current_quantity,
            'minimum_quantity' => $this->minimum_quantity,
            'is_low_stock' => $this->isLowStock(),
            'storage_location' => $this->storage_location,
            'recent_movements' => StockMovementResource::collection($this->whenLoaded('movements')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    public function withResponse(Request $request, JsonResponse $response): void
    {
        $response->headers->set('Cache-Control', 'no-store');
    }
}
