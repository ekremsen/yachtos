<?php

namespace Tests\Feature;

use App\Modules\Tenants\Models\TenantMembership;
use App\Modules\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DevelopmentSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_development_fixture_is_idempotent_and_can_authenticate_with_a_tenant(): void
    {
        $this->seed();
        $this->seed();

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('tenants', 1);
        $this->assertDatabaseCount('tenant_memberships', 1);
        $this->assertTrue(Hash::check('YachtOS-Dev-2026!', User::sole()->password));
        $this->assertSame(1, TenantMembership::currentlyActive()->count());

        $token = $this->postJson('/api/auth/login', [
            'email' => 'captain@azureyachting.com', 'password' => 'YachtOS-Dev-2026!',
        ])->assertOk()->json('data.access_token');

        $this->withToken($token)->getJson('/api/tenant')->assertOk()
            ->assertJsonPath('data.name', 'Azure Development');
    }

    public function test_seeder_never_creates_development_credentials_in_production(): void
    {
        $this->app->instance('env', 'production');
        $this->app->make(\Database\Seeders\DatabaseSeeder::class)->run();

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('tenants', 0);
        $this->assertDatabaseCount('tenant_memberships', 0);
    }
}
