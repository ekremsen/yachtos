<?php

namespace Tests\Feature\Modules\Users;

use App\Modules\Tenants\Models\TenantMembership;
use App\Modules\Users\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_records_have_uuid_keys_and_relationships(): void
    {
        $membership = TenantMembership::factory()->create();

        foreach ([$membership, $membership->user, $membership->tenant] as $record) {
            $this->assertTrue(Str::isUuid($record->id));
        }

        $this->assertTrue($membership->user->tenantMemberships->first()->is($membership));
        $this->assertTrue($membership->tenant->memberships->first()->is($membership));
        $this->assertArrayNotHasKey('password', $membership->user->toArray());
        $this->assertArrayNotHasKey('remember_token', $membership->user->toArray());
    }

    public function test_normalized_emails_remain_unique(): void
    {
        User::factory()->create(['email' => ' Captain@Example.com ']);
        $this->assertDatabaseHas('users', ['email' => 'captain@example.com']);

        $this->expectException(QueryException::class);
        User::factory()->create(['email' => 'CAPTAIN@example.com']);
    }

    public function test_memberships_cannot_reference_missing_users(): void
    {
        $this->expectException(QueryException::class);
        TenantMembership::factory()->create(['user_id' => (string) Str::uuid()]);
    }

    public function test_memberships_cannot_reference_missing_tenants(): void
    {
        $this->expectException(QueryException::class);
        TenantMembership::factory()->create(['tenant_id' => (string) Str::uuid()]);
    }

    public function test_deleting_an_identity_with_membership_history_is_restricted(): void
    {
        $membership = TenantMembership::factory()->create();

        $this->expectException(QueryException::class);
        $membership->user->delete();
    }

    public function test_deleting_a_tenant_with_membership_history_is_restricted(): void
    {
        $membership = TenantMembership::factory()->create();

        $this->expectException(QueryException::class);
        $membership->tenant->delete();
    }
}
