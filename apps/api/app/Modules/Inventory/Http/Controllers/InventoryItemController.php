<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Http\Requests\IndexInventoryRequest;
use App\Modules\Inventory\Http\Requests\StoreInventoryItemRequest;
use App\Modules\Inventory\Http\Requests\StoreStockMovementRequest;
use App\Modules\Inventory\Http\Requests\UpdateInventoryItemRequest;
use App\Modules\Inventory\Http\Resources\InventoryItemResource;
use App\Modules\Inventory\Http\Resources\StockMovementResource;
use App\Modules\Inventory\Models\InventoryItem;
use App\Modules\Inventory\Services\InventoryManager;
use App\Support\Yachts\YachtContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InventoryItemController extends Controller
{
    public function index(IndexInventoryRequest $request, YachtContext $yacht): AnonymousResourceCollection
    {
        $query = InventoryItem::forYacht($yacht->yacht()->id)->orderBy('name')->orderBy('id');
        if ($search = $request->validated('search')) {
            $query->where(fn ($builder) => $builder->where('name', 'like', "%{$search}%")
                ->orWhere('category', 'like', "%{$search}%")
                ->orWhere('storage_location', 'like', "%{$search}%"));
        }
        $items = $query->get();
        if ($request->boolean('low_stock')) {
            $items = $items->filter(fn (InventoryItem $item) => $item->isLowStock())->values();
        }

        return InventoryItemResource::collection($items);
    }

    public function show(string $inventoryItem, YachtContext $yacht): InventoryItemResource
    {
        return new InventoryItemResource($this->find($inventoryItem, $yacht)
            ->load(['movements' => fn ($query) => $query->with('actor')->orderByDesc('created_at')->orderByDesc('id')->limit(20)]));
    }

    public function store(StoreInventoryItemRequest $request, InventoryManager $manager): JsonResponse
    {
        $item = $manager->create($request->validated());

        return (new InventoryItemResource($item))->response()->setStatusCode(201);
    }

    public function update(UpdateInventoryItemRequest $request, string $inventoryItem, YachtContext $yacht, InventoryManager $manager): InventoryItemResource
    {
        return new InventoryItemResource($manager->update($this->find($inventoryItem, $yacht), $request->validated()));
    }

    public function move(StoreStockMovementRequest $request, string $inventoryItem, YachtContext $yacht, InventoryManager $manager): StockMovementResource
    {
        $item = $this->find($inventoryItem, $yacht);

        return new StockMovementResource($manager->move($item, $request->user(), $request->validated()));
    }

    private function find(string $id, YachtContext $yacht): InventoryItem
    {
        if (! Str::isUuid($id)) {
            throw new NotFoundHttpException;
        }

        return InventoryItem::forYacht($yacht->yacht()->id)->whereKey($id)->firstOrFail();
    }
}
