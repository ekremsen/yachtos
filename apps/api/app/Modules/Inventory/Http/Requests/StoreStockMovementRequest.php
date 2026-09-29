<?php

namespace App\Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreStockMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:in,out,adjustment'],
            'quantity' => ['required', 'regex:/^-?\d{1,9}(?:\.\d{1,3})?$/'],
            'reason' => ['required', 'string', 'max:180'],
            'note' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $quantity = (string) $this->input('quantity', '0');
            $zero = preg_match('/^-?0(?:\.0{1,3})?$/', $quantity) === 1;
            $negative = str_starts_with($quantity, '-');
            $type = $this->input('type');
            if ($type === 'adjustment' && $zero) {
                $validator->errors()->add('quantity', 'Adjustment must be non-zero.');
            } elseif (in_array($type, ['in', 'out'], true) && ($zero || $negative)) {
                $validator->errors()->add('quantity', 'Stock in/out quantity must be positive.');
            }
        }];
    }
}
