<?php

namespace App\Modules\Crew\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Crew\Http\Requests\StoreCrewMemberRequest;
use App\Modules\Crew\Http\Requests\UpdateCrewMemberRequest;
use App\Modules\Crew\Http\Resources\CrewMemberResource;
use App\Modules\Crew\Models\CrewMember;
use App\Support\Tenancy\TenantContext;
use App\Support\Yachts\YachtContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CrewMemberController extends Controller
{
    public function index(YachtContext $yacht): AnonymousResourceCollection
    {
        return CrewMemberResource::collection(CrewMember::forYacht($yacht->yacht()->id)
            ->orderBy('last_name')->orderBy('first_name')->orderBy('id')->get());
    }

    public function show(string $crewMember, YachtContext $yacht): CrewMemberResource
    {
        return new CrewMemberResource($this->find($crewMember, $yacht));
    }

    public function store(StoreCrewMemberRequest $request, TenantContext $tenant, YachtContext $yacht): JsonResponse
    {
        $member = new CrewMember($request->validated());
        $member->forceFill(['tenant_id' => $tenant->tenant()->id, 'yacht_id' => $yacht->yacht()->id]);
        $member->save();

        return (new CrewMemberResource($member))->response()->setStatusCode(201);
    }

    public function update(UpdateCrewMemberRequest $request, string $crewMember, YachtContext $yacht): CrewMemberResource
    {
        $member = $this->find($crewMember, $yacht);
        $member->fill($request->validated())->save();

        return new CrewMemberResource($member->refresh());
    }

    private function find(string $id, YachtContext $yacht): CrewMember
    {
        if (! Str::isUuid($id)) {
            throw new NotFoundHttpException;
        }

        return CrewMember::forYacht($yacht->yacht()->id)->whereKey($id)->firstOrFail();
    }
}
