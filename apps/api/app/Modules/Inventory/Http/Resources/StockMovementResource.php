<?php

namespace App\Modules\Inventory\Http\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockMovementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'quantity' => $this->quantity,
            'balance_after' => $this->balance_after,
            'reason' => $this->reason,
            'note' => $this->note,
            'performed_by' => $this->whenLoaded('actor', fn () => trim($this->actor->first_name.' '.$this->actor->last_name)),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }

    public function withResponse(Request $request, JsonResponse $response): void
    {
        $response->headers->set('Cache-Control', 'no-store');
    }
}
