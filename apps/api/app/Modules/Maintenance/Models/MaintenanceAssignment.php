<?php

namespace App\Modules\Maintenance\Models;

use App\Modules\Crew\Models\CrewMember;
use App\Modules\Tenants\Models\Tenant;
use App\Modules\Yachts\Models\Yacht;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class MaintenanceAssignment extends Model
{
    use HasUuids;

    protected $guarded = ['*'];

    protected static function booted(): void
    {
        static::saving(function (MaintenanceAssignment $assignment): void {
            $taskMatches = MaintenanceTask::whereKey($assignment->maintenance_task_id)
                ->where('tenant_id', $assignment->tenant_id)->where('yacht_id', $assignment->yacht_id)->exists();
            $crewMatches = CrewMember::whereKey($assignment->crew_member_id)
                ->where('tenant_id', $assignment->tenant_id)->where('yacht_id', $assignment->yacht_id)
                ->where('status', 'active')->exists();
            if (! $taskMatches || ! $crewMatches) {
                throw ValidationException::withMessages(['assignee_ids' => ['Assignees must be active crew members on this yacht.']]);
            }
        });
    }

    public function maintenanceTask(): BelongsTo
    {
        return $this->belongsTo(MaintenanceTask::class);
    }

    public function crewMember(): BelongsTo
    {
        return $this->belongsTo(CrewMember::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function yacht(): BelongsTo
    {
        return $this->belongsTo(Yacht::class);
    }
}
