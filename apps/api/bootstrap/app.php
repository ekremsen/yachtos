<?php

use App\Support\Auth\Http\Middleware\EnsureUserIsActive;
use App\Support\Tenancy\Http\Middleware\ResolveTenantContext;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('api/*') ? null : '/');

        $middleware->alias([
            'user.active' => EnsureUserIsActive::class,
            'tenant.context' => ResolveTenantContext::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(fn (Request $request, Throwable $exception) => $request->is('api/*') || $request->expectsJson());

        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            if (! $request->is('api/*') || $response->getStatusCode() < 400) {
                return $response;
            }

            $status = $response->getStatusCode();
            [$code, $message] = match ($status) {
                401 => ['unauthenticated', 'Authentication failed.'],
                403 => ['forbidden', 'Access is unavailable.'],
                404 => ['not_found', 'Resource not found.'],
                405 => ['method_not_allowed', 'Method not allowed.'],
                409 => ['conflict', 'The request conflicts with the current state.'],
                422 => ['validation_failed', 'The given data was invalid.'],
                429 => ['too_many_requests', 'Too many requests. Please try again later.'],
                default => ['server_error', 'An unexpected error occurred.'],
            };
            $body = ['message' => $message, 'code' => $code];

            if ($exception instanceof ValidationException) {
                $body['errors'] = $exception->errors();
            }

            // Preserve Retry-After and authentication headers without exposing debug data.
            $headers = $response->headers->all();
            unset($headers['content-type'], $headers['content-length']);

            return response()->json($body, $status, $headers)->header('Cache-Control', 'no-store');
        });
    })->create();
