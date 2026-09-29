<?php

namespace App\Modules\Maintenance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaintenanceTaskRequest extends FormRequest
{
    use MaintenanceRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['sometimes', 'nullable', 'string', 'max:10000'],
            'type' => ['required', 'in:preventive,corrective,inspection'],
            'priority' => ['sometimes', 'in:low,normal,high,critical'],
            'due_date' => ['required', 'date_format:Y-m-d'],
        ], $this->assigneeRules());
    }
}
