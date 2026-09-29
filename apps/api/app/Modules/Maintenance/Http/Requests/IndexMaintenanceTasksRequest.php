<?php

namespace App\Modules\Maintenance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexMaintenanceTasksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'in:planned,in_progress,completed,cancelled'],
            'priority' => ['sometimes', 'in:low,normal,high,critical'],
            'search' => ['sometimes', 'string', 'max:100'],
        ];
    }
}
