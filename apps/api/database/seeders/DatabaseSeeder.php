<?php

namespace Database\Seeders;

use App\Modules\Crew\Models\CrewMember;
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
        });
    }
}
