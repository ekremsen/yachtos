<?php

namespace App\Support\Yachts;

use App\Modules\Users\Models\User;
use App\Modules\Yachts\Models\Yacht;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Str;
use LogicException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class YachtContext
{
    private ?Yacht $yacht = null;

    public function resolveFor(User $user, TenantContext $tenantContext, ?string $selection = null): void
    {
        $this->clear();
        $query = Yacht::accessibleTo($user, $tenantContext->tenant());

        if ($selection !== null) {
            // Validate syntax before passing a value to PostgreSQL's UUID comparison.
            $yacht = Str::isUuid($selection) ? $query->whereKey($selection)->first() : null;
            if (! $yacht) {
                throw new AccessDeniedHttpException('Yacht access is unavailable.');
            }
            $this->yacht = $yacht;

            return;
        }

        $yachts = $query->limit(2)->get();
        if ($yachts->isEmpty()) {
            throw new AccessDeniedHttpException('Yacht access is unavailable.');
        }
        if ($yachts->count() !== 1) {
            throw new ConflictHttpException('Explicit yacht selection is required.');
        }
        $this->yacht = $yachts->first();
    }

    public function yacht(): Yacht
    {
        return $this->yacht ?? throw new LogicException('Yacht context has not been resolved.');
    }

    public function clear(): void
    {
        $this->yacht = null;
    }
}
