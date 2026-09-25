<?php

namespace App\Support\Tenancy;

use App\Modules\Tenants\Models\Tenant;
use App\Modules\Users\Models\User;
use LogicException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class TenantContext
{
    private ?Tenant $tenant = null;

    public function resolveFor(User $user): void
    {
        $this->clear();

        // Count memberships before checking tenant status: never select a convenient
        // tenant when provisioning accidentally grants multiple active memberships.
        $memberships = $user->tenantMemberships()->currentlyActive()->with('tenant')->limit(2)->get();

        if ($memberships->count() !== 1 || $memberships->first()->tenant?->status !== 'active') {
            throw new AccessDeniedHttpException('Tenant access is unavailable.');
        }

        $this->tenant = $memberships->first()->tenant;
    }

    public function tenant(): Tenant
    {
        return $this->tenant ?? throw new LogicException('Tenant context has not been resolved.');
    }

    public function clear(): void
    {
        $this->tenant = null;
    }
}
