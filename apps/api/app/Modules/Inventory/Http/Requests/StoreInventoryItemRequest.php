<?php

namespace App\Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryItemRequest extends FormRequest
{
    use InventoryQuantityRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:180'],
            'description' => ['sometimes', 'nullable', 'string', 'max:10000'],
            'category' => ['required', 'string', 'max:80'],
            'unit' => ['required', 'in:piece,liter,kilogram,meter,pack'],
            'minimum_quantity' => [...$this->quantityRules(true)],
            'storage_location' => ['sometimes', 'nullable', 'string', 'max:120'],
        ];
    }
}
