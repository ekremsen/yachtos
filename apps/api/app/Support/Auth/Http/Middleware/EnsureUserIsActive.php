<?php

namespace App\Support\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()->status !== 'active') {
            throw new AccessDeniedHttpException('Account access is unavailable.');
        }

        return $next($request);
    }
}
