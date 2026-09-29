<?php

namespace Database\Seeders;

use App\Modules\Crew\Models\CrewMember;
use App\Modules\Maintenance\Models\MaintenanceAssignment;
use App\Modules\Maintenance\Models\MaintenanceTask;
use App\Modules\Tenants\Models\Tenant;
use App\Modules\Tenants\Models\TenantMembership;
use App\Modules\Users\Models\User;
use App\Modules\Yachts\Models\Yacht;
use App\Modules\Yachts\Models\YachtMembership;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        DB::transaction(function () {
            $user = User::firstOrNew(['email' => 'captain@azureyachting.com']);
            $user->forceFill([
                'first_name' => 'Demo', 'last_name' => 'Captain',
                'password' => 'YachtOS-Dev-2026!', 'status' => 'active',
                'language' => 'tr', 'timezone' => 'Europe/Istanbul',
            ])->save();

            $tenant = Tenant::firstOrNew(['slug' => 'azure-development']);
            $tenant->forceFill([
                'name' => 'Azure Yachting', 'type' => 'private', 'status' => 'active',
                'country' => 'TR', 'timezone' => 'Europe/Istanbul', 'currency' => 'TRY',
            ])->save();

            $membership = TenantMembership::firstOrNew(['user_id' => $user->id, 'tenant_id' => $tenant->id]);
            $membership->forceFill([
                'status' => 'active', 'start_date' => '2026-01-01', 'end_date' => null,
            ])->save();

            // Fixed development UUID keeps reruns deterministic even after a rename.
            $yacht = Yacht::firstOrNew(['id' => 'a7de0000-0000-4000-8000-000000000001']);
            $yacht->forceFill([
                'tenant_id' => $tenant->id, 'name' => 'M/Y Azure',
                'home_port' => 'Göcek Marina', 'status' => 'active',
            ])->save();

            $yachtMembership = YachtMembership::firstOrNew(['id' => 'a7de0000-0000-4000-8000-000000000002']);
            $yachtMembership->forceFill([
                'tenant_id' => $tenant->id, 'tenant_membership_id' => $membership->id,
                'user_id' => $user->id, 'yacht_id' => $yacht->id, 'role' => 'captain',
                'status' => 'active', 'start_date' => '2026-01-01', 'end_date' => null,
            ])->save();

            $crew = [
                ['captain', 'Cem', 'Arslan', 'Captain', 'captain.crew@azureyachting.com', '+90 532 555 0101', 'TR'],
                ['engineer', 'Mert', 'Kaya', 'Chief Engineer', 'mert.kaya@example.test', '+90 532 555 0102', 'TR'],
                ['deckhand', 'Arda', 'Tunç', 'Deckhand', 'arda.tunc@example.test', '+90 532 555 0103', 'TR'],
                ['steward', 'Selin', 'Yılmaz', 'Chief Stewardess', 'selin.yilmaz@example.test', '+90 532 555 0104', 'TR'],
                ['chef', 'Deniz', 'Acar', 'Chef', 'deniz.acar@example.test', '+90 532 555 0105', 'TR'],
            ];

            foreach ($crew as $index => [$key, $first, $last, $position, $email, $phone, $nationality]) {
                $member = CrewMember::firstOrNew(['id' => sprintf('a7de0000-0000-4000-8000-%012d', 10 + $index)]);
                $member->forceFill([
                    'tenant_id' => $tenant->id, 'yacht_id' => $yacht->id, 'first_name' => $first,
                    'last_name' => $last, 'position' => $position, 'email' => $email, 'phone' => $phone,
                    'nationality' => $nationality, 'status' => 'active', 'start_date' => '2026-01-01', 'end_date' => null,
                ])->save();
            }

            $tasks = [
                ['Overdue sea-water pump inspection', 'Inspect pump seals and confirm cooling flow.', 'inspection', 'planned', 'critical', '2026-09-24', null, [11]],
                ['Port generator belt replacement', 'Replace worn drive belt and verify tension.', 'corrective', 'planned', 'high', '2026-10-04', null, [11, 12]],
                ['Quarterly fire suppression check', 'Check pressure indicators and engine-room nozzles.', 'preventive', 'planned', 'normal', '2026-10-12', null, [13]],
                ['Hydraulic windlass leak repair', 'Replace the return-line seal and test under load.', 'corrective', 'in_progress', 'high', '2026-09-28', null, [11, 10]],
                ['Navigation light inspection', 'Verify port and starboard navigation lights.', 'inspection', 'completed', 'normal', '2026-09-18', '2026-09-19 11:30:00', [12]],
                ['Tender davit lubrication', 'Apply approved lubricant to davit pivots.', 'preventive', 'cancelled', 'low', '2026-09-20', null, [12]],
            ];

            $assignmentNumber = 30;
            foreach ($tasks as $index => [$title, $description, $type, $status, $priority, $dueDate, $completedAt, $crewNumbers]) {
                $task = MaintenanceTask::firstOrNew(['id' => sprintf('a7de0000-0000-4000-8000-%012d', 20 + $index)]);
                $task->forceFill([
                    'tenant_id' => $tenant->id, 'yacht_id' => $yacht->id, 'title' => $title,
                    'description' => $description, 'type' => $type, 'status' => $status,
                    'priority' => $priority, 'due_date' => $dueDate, 'completed_at' => $completedAt,
                ])->save();

                foreach ($crewNumbers as $crewNumber) {
                    $assignment = MaintenanceAssignment::firstOrNew([
                        'id' => sprintf('a7de0000-0000-4000-8000-%012d', $assignmentNumber++),
                    ]);
                    $assignment->forceFill([
                        'tenant_id' => $tenant->id, 'yacht_id' => $yacht->id,
                        'maintenance_task_id' => $task->id,
                        'crew_member_id' => sprintf('a7de0000-0000-4000-8000-%012d', $crewNumber),
                    ])->save();
                }
            }
        });
    }
}
