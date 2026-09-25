<?php

namespace App\Support\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Auth\Http\Requests\LoginRequest;
use App\Support\Auth\Services\AuthenticationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function login(LoginRequest $request, AuthenticationService $auth): JsonResponse
    {
        $credentials = $request->validated();

        return response()->json([
            'data' => $auth->login($credentials['email'], $credentials['password']),
        ])->header('Cache-Control', 'no-store');
    }

    public function logout(Request $request, AuthenticationService $auth): Response
    {
        $auth->logout($request->user());

        return response()->noContent()->header('Cache-Control', 'no-store');
    }
}
