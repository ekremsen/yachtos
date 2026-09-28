<?php

namespace App\Modules\Crew\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreCrewMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'position' => ['sometimes', 'nullable', 'string', 'max:100'],
            'email' => ['sometimes', 'nullable', 'email:rfc', 'max:254'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:40'],
            'nationality' => ['sometimes', 'nullable', 'string', 'size:2', 'alpha', 'uppercase'],
            'status' => ['sometimes', 'in:active,inactive'],
            'start_date' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
            'end_date' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $start = $this->input('start_date');
            $end = $this->input('end_date');
            if ($start && $end && $end <= $start) {
                $validator->errors()->add('end_date', 'End date must follow start date.');
            }
        }];
    }
}
