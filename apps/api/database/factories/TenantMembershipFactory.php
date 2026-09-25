<?php

namespace Database\Factories;

use App\Modules\Tenants\Models\Tenant;
use App\Modules\Tenants\Models\TenantMembership;
use App\Modules\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TenantMembership> */
class TenantMembershipFactory extends Factory
{
    protected $model = TenantMembership::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'user_id' => User::factory(),
            'status' => 'active',
            'start_date' => today('UTC')->toDateString(),
            'end_date' => null,
        ];
    }
}
