<?php

namespace App\Modules\Maintenance\Services;

use App\Modules\Crew\Models\CrewMember;
use App\Modules\Maintenance\Models\MaintenanceAssignment;
use App\Modules\Maintenance\Models\MaintenanceTask;
use App\Support\Tenancy\TenantContext;
use App\Support\Yachts\YachtContext;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MaintenanceTaskManager
{
    public function __construct(private TenantContext $tenantContext, private YachtContext $yachtContext) {}

    public function create(array $data): MaintenanceTask
    {
        return DB::transaction(function () use ($data): MaintenanceTask {
            $assigneeIds = $data['assignee_ids'] ?? [];
            $task = new MaintenanceTask(Arr::except($data, ['assignee_ids']));
            $task->forceFill([
                'tenant_id' => $this->tenantContext->tenant()->id,
                'yacht_id' => $this->yachtContext->yacht()->id,
                'status' => 'planned',
                'completed_at' => null,
            ])->save();
            $this->replaceAssignments($task, $assigneeIds);

            return $task->load('assignments.crewMember');
        });
    }

    public function update(MaintenanceTask $task, array $data): MaintenanceTask
    {
        return DB::transaction(function () use ($task, $data): MaintenanceTask {
            if (in_array($task->status, ['completed', 'cancelled'], true)) {
                throw ValidationException::withMessages(['status' => ['Completed or cancelled maintenance is read-only.']]);
            }
            $assigneeIds = $data['assignee_ids'] ?? null;
            $attributes = Arr::except($data, ['assignee_ids']);
            if (isset($attributes['status'])) {
                $this->assertTransition($task, $attributes['status']);
            }
            $task->fill($attributes)->save();
            if ($assigneeIds !== null) {
                $this->replaceAssignments($task, $assigneeIds);
            }

            return $task->refresh()->load('assignments.crewMember');
        });
    }

    public function complete(MaintenanceTask $task): MaintenanceTask
    {
        if ($task->status === 'completed') {
            return $task->load('assignments.crewMember');
        }
        if (! in_array($task->status, ['planned', 'in_progress'], true)) {
            throw ValidationException::withMessages(['status' => ['Cancelled maintenance cannot be completed.']]);
        }

        $task->forceFill(['status' => 'completed', 'completed_at' => now('UTC')])->save();

        return $task->refresh()->load('assignments.crewMember');
    }

    private function assertTransition(MaintenanceTask $task, string $next): void
    {
        if ($task->status === $next) {
            return;
        }
        $transitions = [
            'planned' => ['in_progress', 'cancelled'],
            'in_progress' => ['cancelled'],
            'completed' => [],
            'cancelled' => [],
        ];
        if (! in_array($next, $transitions[$task->status] ?? [], true)) {
            throw ValidationException::withMessages(['status' => ['This maintenance status transition is not allowed.']]);
        }
    }

    private function replaceAssignments(MaintenanceTask $task, array $assigneeIds): void
    {
        $assigneeIds = array_values(array_unique($assigneeIds));
        if ($assigneeIds !== []) {
            $validCount = CrewMember::forYacht($this->yachtContext->yacht()->id)
                ->where('tenant_id', $this->tenantContext->tenant()->id)
                ->where('status', 'active')->whereIn('id', $assigneeIds)->count();
            if ($validCount !== count($assigneeIds)) {
                throw ValidationException::withMessages(['assignee_ids' => ['Choose active crew members assigned to this yacht.']]);
            }
        }

        $current = $task->assignments()->get()->keyBy('crew_member_id');
        foreach ($current as $crewId => $assignment) {
            if (! in_array($crewId, $assigneeIds, true)) {
                $assignment->delete();
            }
        }
        foreach ($assigneeIds as $crewId) {
            if ($current->has($crewId)) {
                continue;
            }
            $assignment = new MaintenanceAssignment;
            $assignment->forceFill([
                'tenant_id' => $this->tenantContext->tenant()->id,
                'yacht_id' => $this->yachtContext->yacht()->id,
                'maintenance_task_id' => $task->id,
                'crew_member_id' => $crewId,
            ])->save();
        }
    }
}
