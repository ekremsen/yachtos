<?php

namespace App\Modules\Yachts\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Yachts\Http\Resources\YachtResource;
use App\Modules\Yachts\Models\Yacht;
use App\Support\Tenancy\TenantContext;
use App\Support\Yachts\YachtContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class YachtController extends Controller
{
    public function index(Request $request, TenantContext $context): JsonResponse
    {
        return YachtResource::collection(Yacht::accessibleTo($request->user(), $context->tenant())
            ->orderBy('name')->orderBy('id')->get())->response()->header('Cache-Control', 'no-store');
    }

    public function current(YachtContext $context): YachtResource
    {
        return new YachtResource($context->yacht());
    }
}
