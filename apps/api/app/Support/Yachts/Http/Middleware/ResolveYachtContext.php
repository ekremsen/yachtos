<?php

namespace App\Support\Yachts\Http\Middleware;

use App\Support\Tenancy\TenantContext;
use App\Support\Yachts\YachtContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveYachtContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $context = app(YachtContext::class);
        try {
            $context->resolveFor($request->user(), app(TenantContext::class), $request->header('X-Yacht-Id'));

            return $next($request);
        } finally {
            $context->clear();
        }
    }
}
