<?php

namespace App\Modules\Yachts\Http\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class YachtResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return $this->resource->only(['id', 'name', 'status', 'home_port']);
    }

    public function withResponse(Request $request, JsonResponse $response): void
    {
        $response->headers->set('Cache-Control', 'no-store');
    }
}
