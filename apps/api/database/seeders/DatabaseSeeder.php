<?php

namespace Database\Seeders;

use App\Modules\Users\Models\User;
use App\Modules\Tenants\Models\Tenant;
use App\Modules\Tenants\Models\TenantMembership;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
                'name' => 'Azure Development', 'type' => 'private', 'status' => 'active',
                'country' => 'TR', 'timezone' => 'Europe/Istanbul', 'currency' => 'TRY',
            ])->save();

            $membership = TenantMembership::firstOrNew(['user_id' => $user->id, 'tenant_id' => $tenant->id]);
            $membership->forceFill([
                'status' => 'active', 'start_date' => '2026-01-01', 'end_date' => null,
            ])->save();
        });
    }
}
