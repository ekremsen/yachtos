<?php

namespace App\Support\Tenancy\Http\Middleware;

use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $context = app(TenantContext::class);

        try {
            // No request header, query parameter or payload controls tenant selection.
            $context->resolveFor($request->user());

            return $next($request);
        } finally {
            // Also clear on exceptions and repeated requests in the same test process.
            $context->clear();
        }
    }
}
