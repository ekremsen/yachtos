<?php

namespace App\Modules\Maintenance\Http\Requests;

use App\Support\Tenancy\TenantContext;
use App\Support\Yachts\YachtContext;
use Illuminate\Validation\Rule;

trait MaintenanceRules
{
    protected function assigneeRules(): array
    {
        $tenantId = app(TenantContext::class)->tenant()->id;
        $yachtId = app(YachtContext::class)->yacht()->id;

        return [
            'assignee_ids' => ['sometimes', 'array', 'max:10'],
            'assignee_ids.*' => [
                'required', 'uuid', 'distinct',
                Rule::exists('crew_members', 'id')->where(fn ($query) => $query
                    ->where('tenant_id', $tenantId)->where('yacht_id', $yachtId)->where('status', 'active')),
            ],
        ];
    }
}
