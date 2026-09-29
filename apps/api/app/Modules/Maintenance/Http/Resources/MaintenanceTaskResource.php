<?php

namespace App\Modules\Maintenance\Http\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceTaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'status' => $this->status,
            'priority' => $this->priority,
            'due_date' => $this->due_date?->toDateString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'is_overdue' => $this->isOverdue(),
            'assignees' => $this->whenLoaded('assignments', fn () => $this->assignments
                ->map(fn ($assignment) => $assignment->crewMember)
                ->filter()
                ->map(fn ($crew) => [
                    'id' => $crew->id,
                    'first_name' => $crew->first_name,
                    'last_name' => $crew->last_name,
                    'position' => $crew->position,
                ])->values()),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    public function withResponse(Request $request, JsonResponse $response): void
    {
        $response->headers->set('Cache-Control', 'no-store');
    }
}
