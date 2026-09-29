<?php

namespace App\Modules\Maintenance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Maintenance\Http\Requests\IndexMaintenanceTasksRequest;
use App\Modules\Maintenance\Http\Requests\StoreMaintenanceTaskRequest;
use App\Modules\Maintenance\Http\Requests\UpdateMaintenanceTaskRequest;
use App\Modules\Maintenance\Http\Resources\MaintenanceTaskResource;
use App\Modules\Maintenance\Models\MaintenanceTask;
use App\Modules\Maintenance\Services\MaintenanceTaskManager;
use App\Support\Yachts\YachtContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MaintenanceTaskController extends Controller
{
    public function index(IndexMaintenanceTasksRequest $request, YachtContext $yacht): JsonResponse
    {
        $query = MaintenanceTask::forYacht($yacht->yacht()->id)->with('assignments.crewMember');
        $filters = $request->validated();
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(fn ($builder) => $builder->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%"));
        }

        return MaintenanceTaskResource::collection($query
            ->orderByRaw("CASE WHEN status IN ('completed', 'cancelled') THEN 1 ELSE 0 END")
            ->orderBy('due_date')->orderByRaw("CASE priority WHEN 'critical' THEN 0 WHEN 'high' THEN 1 WHEN 'normal' THEN 2 ELSE 3 END")
            ->orderBy('id')->get())->response()->header('Cache-Control', 'no-store');
    }

    public function show(string $maintenanceTask, YachtContext $yacht): MaintenanceTaskResource
    {
        return new MaintenanceTaskResource($this->find($maintenanceTask, $yacht));
    }

    public function store(StoreMaintenanceTaskRequest $request, MaintenanceTaskManager $manager): JsonResponse
    {
        $task = $manager->create($request->validated());

        return (new MaintenanceTaskResource($task))->response()->setStatusCode(201);
    }

    public function update(UpdateMaintenanceTaskRequest $request, string $maintenanceTask, YachtContext $yacht, MaintenanceTaskManager $manager): MaintenanceTaskResource
    {
        $task = $this->find($maintenanceTask, $yacht);

        return new MaintenanceTaskResource($manager->update($task, $request->validated()));
    }

    public function complete(string $maintenanceTask, YachtContext $yacht, MaintenanceTaskManager $manager): MaintenanceTaskResource
    {
        return new MaintenanceTaskResource($manager->complete($this->find($maintenanceTask, $yacht)));
    }

    private function find(string $id, YachtContext $yacht): MaintenanceTask
    {
        if (! Str::isUuid($id)) {
            throw new NotFoundHttpException;
        }

        return MaintenanceTask::forYacht($yacht->yacht()->id)
            ->with('assignments.crewMember')->whereKey($id)->firstOrFail();
    }
}
