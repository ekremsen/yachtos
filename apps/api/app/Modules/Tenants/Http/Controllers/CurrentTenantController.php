<?php

namespace App\Modules\Tenants\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Tenants\Http\Resources\TenantResource;
use App\Support\Tenancy\TenantContext;

class CurrentTenantController extends Controller
{
    public function __invoke(TenantContext $context): TenantResource
    {
        return new TenantResource($context->tenant());
    }
}
