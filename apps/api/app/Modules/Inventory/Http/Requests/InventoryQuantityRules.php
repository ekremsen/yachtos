<?php

namespace App\Modules\Inventory\Http\Requests;

trait InventoryQuantityRules
{
    protected function quantityRules(bool $nullable = false): array
    {
        return array_filter([
            $nullable ? 'nullable' : null,
            'regex:/^\d{1,9}(?:\.\d{1,3})?$/',
        ]);
    }
}
