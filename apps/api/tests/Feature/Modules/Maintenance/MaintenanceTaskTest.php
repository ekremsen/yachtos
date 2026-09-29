<?php

namespace Tests\Feature\Modules\Maintenance;

use App\Modules\Crew\Models\CrewMember;
use App\Modules\Maintenance\Models\MaintenanceAssignment;
use App\Modules\Maintenance\Models\MaintenanceTask;
use App\Modules\Tenants\Models\Tenant;
use App\Modules\Tenants\Models\TenantMembership;
use App\Modules\Yachts\Models\Yacht;
use App\Modules\Yachts\Models\YachtMembership;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class MaintenanceTaskTest extends TestCase
{
    use RefreshDatabase;

    private function access(): array
    {
        $membership = TenantMembership::factory()->create();
        $user = $membership->user;
        $yacht = Yacht::create(['tenant_id' => $membership->tenant_id, 'name' => 'Maintenance yacht', 'status' => 'active']);
        YachtMembership::create([
            'tenant_id' => $membership->tenant_id, 'tenant_membership_id' => $membership->id,
            'user_id' => $user->id, 'yacht_id' => $yacht->id, 'role' => 'captain', 'status' => 'active',
            'start_date' => today('UTC'),
        ]);

        return [$membership, $user, $yacht, $user->createToken('test', ['*'], now()->addDay())->plainTextToken];
    }

    private function crew(string $tenantId, string $yachtId, array $overrides = []): CrewMember
    {
        $member = new CrewMember;
        $member->forceFill(array_merge([
            'tenant_id' => $tenantId, 'yacht_id' => $yachtId,
            'first_name' => 'Taylor', 'last_name' => 'Sailor', 'position' => 'Engineer', 'status' => 'active',
        ], $overrides))->save();

        return $member;
    }

    private function task(string $tenantId, string $yachtId, array $overrides = []): MaintenanceTask
    {
        $task = new MaintenanceTask;
        $task->forceFill(array_merge([
            'tenant_id' => $tenantId, 'yacht_id' => $yachtId, 'title' => 'Pump inspection',
            'description' => 'Inspect pump seals.', 'type' => 'inspection', 'status' => 'planned',
            'priority' => 'normal', 'due_date' => today('UTC')->toDateString(), 'completed_at' => null,
        ], $overrides))->save();

        return $task;
    }

    private function assignment(string $tenantId, string $yachtId, MaintenanceTask $task, CrewMember $crew): MaintenanceAssignment
    {
        $assignment = new MaintenanceAssignment;
        $assignment->forceFill([
            'tenant_id' => $tenantId, 'yacht_id' => $yachtId,
            'maintenance_task_id' => $task->id, 'crew_member_id' => $crew->id,
        ])->save();

        return $assignment;
    }

    private function api(string $method, string $path, string $token, string $yachtId, array $data = [])
    {
        $this->app['auth']->forgetGuards();

        return $this->withToken($token)->withHeader('X-Yacht-Id', $yachtId)->json($method, $path, $data);
    }

    public function test_list_is_yacht_scoped_exposes_assignees_and_orders_open_due_work_first(): void
    {
        [$tenant, , $yacht, $token] = $this->access();
        $crew = $this->crew($tenant->tenant_id, $yacht->id);
        $soon = $this->task($tenant->tenant_id, $yacht->id, ['title' => 'Upcoming service', 'due_date' => today('UTC')->addDays(2)->toDateString()]);
        $this->assignment($tenant->tenant_id, $yacht->id, $soon, $crew);
        $done = $this->task($tenant->tenant_id, $yacht->id, ['title' => 'Old completed task', 'status' => 'completed', 'due_date' => today('UTC')->subDays(9)->toDateString(), 'completed_at' => now('UTC')]);

        $otherYacht = Yacht::create(['tenant_id' => $tenant->tenant_id, 'name' => 'Other yacht', 'status' => 'active']);
        $hiddenYachtTask = $this->task($tenant->tenant_id, $otherYacht->id, ['title' => 'Hidden yacht item']);
        $otherTenant = Tenant::factory()->create();
        $foreignYacht = Yacht::create(['tenant_id' => $otherTenant->id, 'name' => 'Foreign yacht', 'status' => 'active']);
        $hiddenTenantTask = $this->task($otherTenant->id, $foreignYacht->id, ['title' => 'Hidden tenant item']);

        $this->api('GET', '/api/maintenance', $token, $yacht->id)->assertOk()->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $soon->id)->assertJsonPath('data.0.assignees.0.id', $crew->id)
            ->assertJsonPath('data.1.id', $done->id)->assertDontSee($hiddenYachtTask->id)
            ->assertDontSee($hiddenTenantTask->id)->assertHeader('Cache-Control', 'no-store, private');
        $this->api('GET', '/api/maintenance?status=completed', $token, $yacht->id)->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_detail_hides_cross_yacht_and_cross_tenant_ids(): void
    {
        [$tenant, , $yacht, $token] = $this->access();
        $sameTenantYacht = Yacht::create(['tenant_id' => $tenant->tenant_id, 'name' => 'Other', 'status' => 'active']);
        $otherYachtTask = $this->task($tenant->tenant_id, $sameTenantYacht->id, ['title' => 'Secret yacht work']);
        $otherTenant = Tenant::factory()->create();
        $foreignYacht = Yacht::create(['tenant_id' => $otherTenant->id, 'name' => 'Foreign', 'status' => 'active']);
        $foreignTask = $this->task($otherTenant->id, $foreignYacht->id, ['title' => 'Secret tenant work']);

        $this->api('GET', "/api/maintenance/{$otherYachtTask->id}", $token, $yacht->id)->assertNotFound()->assertDontSee('Secret yacht work');
        $this->api('GET', "/api/maintenance/{$foreignTask->id}", $token, $yacht->id)->assertNotFound()->assertDontSee('Secret tenant work');
    }

    public function test_create_derives_ownership_ignores_payload_and_assigns_current_yacht_crew(): void
    {
        [$tenant, , $yacht, $token] = $this->access();
        $crew = $this->crew($tenant->tenant_id, $yacht->id);
        $foreign = Tenant::factory()->create();
        $foreignYacht = Yacht::create(['tenant_id' => $foreign->id, 'name' => 'Foreign', 'status' => 'active']);

        $response = $this->api('POST', '/api/maintenance', $token, $yacht->id, [
            'title' => 'Replace impeller', 'description' => 'Keep engine cooling reliable.', 'type' => 'corrective',
            'priority' => 'high', 'due_date' => today('UTC')->addDays(3)->toDateString(), 'assignee_ids' => [$crew->id],
            'tenant_id' => $foreign->id, 'yacht_id' => $foreignYacht->id, 'status' => 'completed',
        ])->assertCreated()->assertJsonPath('data.status', 'planned')->assertJsonPath('data.is_overdue', false)
            ->assertJsonPath('data.assignees.0.id', $crew->id)->assertJsonMissingPath('data.tenant_id');

        $this->assertDatabaseHas('maintenance_tasks', [
            'id' => $response->json('data.id'), 'tenant_id' => $tenant->tenant_id, 'yacht_id' => $yacht->id,
        ]);
        $this->assertDatabaseHas('maintenance_assignments', [
            'maintenance_task_id' => $response->json('data.id'), 'crew_member_id' => $crew->id,
            'tenant_id' => $tenant->tenant_id, 'yacht_id' => $yacht->id,
        ]);
    }

    public function test_invalid_task_fields_and_foreign_assignees_are_rejected(): void
    {
        [$tenant, , $yacht, $token] = $this->access();
        $otherYacht = Yacht::create(['tenant_id' => $tenant->tenant_id, 'name' => 'Other', 'status' => 'active']);
        $crossYachtCrew = $this->crew($tenant->tenant_id, $otherYacht->id);
        $otherTenant = Tenant::factory()->create();
        $foreignYacht = Yacht::create(['tenant_id' => $otherTenant->id, 'name' => 'Foreign', 'status' => 'active']);
        $crossTenantCrew = $this->crew($otherTenant->id, $foreignYacht->id);

        $this->api('POST', '/api/maintenance', $token, $yacht->id, [
            'title' => '', 'type' => 'scheduled', 'priority' => 'urgent', 'due_date' => 'tomorrow',
        ])->assertUnprocessable()->assertJsonStructure(['errors' => ['title', 'type', 'priority', 'due_date']]);
        foreach ([$crossYachtCrew, $crossTenantCrew] as $crew) {
            $this->api('POST', '/api/maintenance', $token, $yacht->id, [
                'title' => 'Unsafe assignment', 'type' => 'corrective', 'due_date' => today('UTC')->toDateString(),
                'assignee_ids' => [$crew->id],
            ])->assertUnprocessable()->assertJsonValidationErrors(['assignee_ids.0']);
        }
    }

    public function test_update_changes_work_and_status_but_cannot_move_ownership_or_skip_lifecycle(): void
    {
        [$tenant, , $yacht, $token] = $this->access();
        $crew = $this->crew($tenant->tenant_id, $yacht->id);
        $task = $this->task($tenant->tenant_id, $yacht->id);
        $other = Tenant::factory()->create();
        $foreignYacht = Yacht::create(['tenant_id' => $other->id, 'name' => 'Foreign', 'status' => 'active']);

        $this->api('PATCH', "/api/maintenance/{$task->id}", $token, $yacht->id, [
            'title' => 'Updated pump inspection', 'status' => 'in_progress', 'assignee_ids' => [$crew->id],
            'tenant_id' => $other->id, 'yacht_id' => $foreignYacht->id,
        ])->assertOk()->assertJsonPath('data.title', 'Updated pump inspection')->assertJsonPath('data.status', 'in_progress')
            ->assertJsonPath('data.assignees.0.id', $crew->id);
        $this->assertDatabaseHas('maintenance_tasks', ['id' => $task->id, 'tenant_id' => $tenant->tenant_id, 'yacht_id' => $yacht->id]);

        $this->api('PATCH', "/api/maintenance/{$task->id}", $token, $yacht->id, ['status' => 'planned'])
            ->assertUnprocessable()->assertJsonStructure(['errors' => ['status']]);
        $this->api('PATCH', "/api/maintenance/{$task->id}", $token, $yacht->id, ['status' => 'completed'])
            ->assertUnprocessable()->assertJsonStructure(['errors' => ['status']]);
    }

    public function test_completion_sets_server_timestamp_and_overdue_is_derived_for_open_work_only(): void
    {
        $this->freezeTime();
        [$tenant, , $yacht, $token] = $this->access();
        $past = today('UTC')->subDay()->toDateString();
        $open = $this->task($tenant->tenant_id, $yacht->id, ['title' => 'Overdue open', 'due_date' => $past]);
        $progress = $this->task($tenant->tenant_id, $yacht->id, ['title' => 'Overdue in progress', 'status' => 'in_progress', 'due_date' => $past]);
        $cancelled = $this->task($tenant->tenant_id, $yacht->id, ['title' => 'Cancelled old', 'status' => 'cancelled', 'due_date' => $past]);
        $completed = $this->task($tenant->tenant_id, $yacht->id, ['title' => 'Completed old', 'status' => 'completed', 'due_date' => $past, 'completed_at' => now('UTC')]);

        $list = $this->api('GET', '/api/maintenance', $token, $yacht->id)->assertOk();
        $this->assertTrue(collect($list->json('data'))->firstWhere('id', $open->id)['is_overdue']);
        $this->assertTrue(collect($list->json('data'))->firstWhere('id', $progress->id)['is_overdue']);
        $this->assertFalse(collect($list->json('data'))->firstWhere('id', $cancelled->id)['is_overdue']);
        $this->assertFalse(collect($list->json('data'))->firstWhere('id', $completed->id)['is_overdue']);

        $this->api('POST', "/api/maintenance/{$open->id}/complete", $token, $yacht->id)
            ->assertOk()->assertJsonPath('data.status', 'completed')->assertJsonPath('data.is_overdue', false);
        $this->assertSame(now('UTC')->format('Y-m-d H:i:s'), $open->refresh()->completed_at->format('Y-m-d H:i:s'));
        $this->api('PATCH', "/api/maintenance/{$open->id}", $token, $yacht->id, ['title' => 'Attempt correction'])
            ->assertUnprocessable();
        $this->api('POST', "/api/maintenance/{$cancelled->id}/complete", $token, $yacht->id)->assertUnprocessable();
    }

    public function test_database_composite_constraints_reject_cross_yacht_and_cross_tenant_assignments(): void
    {
        [$tenant, , $yacht] = $this->access();
        $task = $this->task($tenant->tenant_id, $yacht->id);
        $sameTenantOtherYacht = Yacht::create(['tenant_id' => $tenant->tenant_id, 'name' => 'Other', 'status' => 'active']);
        $otherYachtCrew = $this->crew($tenant->tenant_id, $sameTenantOtherYacht->id);
        $otherTenant = Tenant::factory()->create();
        $foreignYacht = Yacht::create(['tenant_id' => $otherTenant->id, 'name' => 'Foreign', 'status' => 'active']);
        $foreignCrew = $this->crew($otherTenant->id, $foreignYacht->id);

        foreach ([$otherYachtCrew, $foreignCrew] as $crew) {
            try {
                DB::table('maintenance_assignments')->insert([
                    'id' => (string) Str::uuid(), 'tenant_id' => $tenant->tenant_id, 'yacht_id' => $yacht->id,
                    'maintenance_task_id' => $task->id, 'crew_member_id' => $crew->id,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                $this->fail('Composite assignment boundary accepted a foreign CrewMember.');
            } catch (QueryException) {
                $this->assertTrue(true);
            }
        }
    }

    public function test_unknown_context_requires_auth_and_valid_yacht_membership(): void
    {
        $this->getJson('/api/maintenance')->assertUnauthorized();
        [, , $yacht, $token] = $this->access();
        YachtMembership::query()->delete();
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->withHeader('X-Yacht-Id', $yacht->id)->getJson('/api/maintenance')->assertForbidden();
    }
}
