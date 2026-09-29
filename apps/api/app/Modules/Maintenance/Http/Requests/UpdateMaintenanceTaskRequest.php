<?php

namespace App\Modules\Maintenance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMaintenanceTaskRequest extends FormRequest
{
    use MaintenanceRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge([
            'title' => ['sometimes', 'required', 'string', 'max:180'],
            'description' => ['sometimes', 'nullable', 'string', 'max:10000'],
            'type' => ['sometimes', 'in:preventive,corrective,inspection'],
            'priority' => ['sometimes', 'in:low,normal,high,critical'],
            'due_date' => ['sometimes', 'date_format:Y-m-d'],
            'status' => ['sometimes', 'in:planned,in_progress,cancelled'],
        ], $this->assigneeRules());
    }
}
