<?php

namespace Tests\Feature\Modules\Crew;

use App\Modules\Crew\Models\CrewMember;
use App\Modules\Tenants\Models\Tenant;
use App\Modules\Tenants\Models\TenantMembership;
use App\Modules\Yachts\Models\Yacht;
use App\Modules\Yachts\Models\YachtMembership;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class CrewMemberTest extends TestCase
{
    use RefreshDatabase;

    private function access(): array
    {
        $membership = TenantMembership::factory()->create();
        $user = $membership->user;
        $yacht = Yacht::create(['tenant_id' => $membership->tenant_id, 'name' => 'Active yacht', 'status' => 'active']);
        YachtMembership::create([
            'tenant_id' => $membership->tenant_id, 'tenant_membership_id' => $membership->id,
            'user_id' => $user->id, 'yacht_id' => $yacht->id, 'role' => 'captain', 'status' => 'active', 'start_date' => today('UTC'),
        ]);

        return [$membership, $user, $yacht, $user->createToken('test', ['*'], now()->addDay())->plainTextToken];
    }

    private function member(array $ownership, array $data = []): CrewMember
    {
        $member = new CrewMember(array_merge([
            'first_name' => 'Morgan', 'last_name' => 'Reed', 'position' => 'Deckhand',
            'status' => 'active', 'start_date' => today('UTC')->toDateString(),
        ], $data));
        $member->forceFill($ownership)->save();

        return $member;
    }

    private function request(string $method, string $path, string $token, string $yachtId, array $data = [])
    {
        $this->app['auth']->forgetGuards();

        return $this->withToken($token)->withHeader('X-Yacht-Id', $yachtId)->json($method, $path, $data);
    }

    public function test_roster_and_detail_are_scoped_to_current_yacht(): void
    {
        [$tenantMembership, , $yacht, $token] = $this->access();
        $member = $this->member(['tenant_id' => $tenantMembership->tenant_id, 'yacht_id' => $yacht->id]);
        $otherYacht = Yacht::create(['tenant_id' => $tenantMembership->tenant_id, 'name' => 'Other yacht', 'status' => 'active']);
        $other = $this->member(['tenant_id' => $tenantMembership->tenant_id, 'yacht_id' => $otherYacht->id], ['first_name' => 'Secret']);

        $this->request('GET', '/api/crew', $token, $yacht->id)->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $member->id)->assertDontSee($other->id)->assertDontSee('Secret');
        $this->request('GET', "/api/crew/{$member->id}", $token, $yacht->id)->assertOk()->assertJsonPath('data.id', $member->id);
        $this->request('GET', "/api/crew/{$other->id}", $token, $yacht->id)->assertNotFound()->assertDontSee($other->id);
    }

    public function test_cross_tenant_member_cannot_be_listed_or_retrieved(): void
    {
        [$tenantMembership, , $yacht, $token] = $this->access();
        $otherTenant = Tenant::factory()->create();
        $otherYacht = Yacht::create(['tenant_id' => $otherTenant->id, 'name' => 'Foreign yacht', 'status' => 'active']);
        $other = $this->member(['tenant_id' => $otherTenant->id, 'yacht_id' => $otherYacht->id]);

        $this->request('GET', '/api/crew', $token, $yacht->id)->assertOk()->assertJsonCount(0, 'data');
        $this->request('GET', "/api/crew/{$other->id}", $token, $yacht->id)->assertNotFound()->assertDontSee($other->id);
        $this->assertSame($tenantMembership->tenant_id, $yacht->tenant_id);
    }

    public function test_create_derives_ownership_ignores_client_boundary_values_and_returns_resource(): void
    {
        [$membership, , $yacht, $token] = $this->access();
        $foreign = Tenant::factory()->create();
        $foreignYacht = Yacht::create(['tenant_id' => $foreign->id, 'name' => 'Untrusted', 'status' => 'active']);

        $response = $this->request('POST', '/api/crew', $token, $yacht->id, [
            'first_name' => 'Alex', 'last_name' => 'Stone', 'position' => 'Chef',
            'tenant_id' => $foreign->id, 'yacht_id' => $foreignYacht->id,
        ])->assertCreated()->assertJsonPath('data.first_name', 'Alex')->assertJsonMissingPath('data.tenant_id');

        $this->assertDatabaseHas('crew_members', [
            'id' => $response->json('data.id'), 'tenant_id' => $membership->tenant_id, 'yacht_id' => $yacht->id,
        ]);
    }

    public function test_validation_rejects_invalid_names_email_nationality_status_and_dates(): void
    {
        [, , $yacht, $token] = $this->access();

        $this->request('POST', '/api/crew', $token, $yacht->id, [
            'first_name' => '', 'last_name' => str_repeat('x', 101), 'email' => 'bad',
            'nationality' => 'TUR', 'status' => 'on-leave', 'start_date' => '2026-05-02', 'end_date' => '2026-05-02',
        ])->assertUnprocessable()->assertJsonStructure(['errors' => ['first_name', 'last_name', 'email', 'nationality', 'status', 'end_date']]);
    }

    public function test_update_changes_profile_but_cannot_move_member_and_date_pair_is_checked(): void
    {
        [$membership, , $yacht, $token] = $this->access();
        $member = $this->member(['tenant_id' => $membership->tenant_id, 'yacht_id' => $yacht->id]);
        $otherTenant = Tenant::factory()->create();
        $otherYacht = Yacht::create(['tenant_id' => $otherTenant->id, 'name' => 'Foreign', 'status' => 'active']);

        $this->request('PATCH', "/api/crew/{$member->id}", $token, $yacht->id, [
            'position' => 'Bosun', 'tenant_id' => $otherTenant->id, 'yacht_id' => $otherYacht->id,
        ])->assertOk()->assertJsonPath('data.position', 'Bosun');
        $this->assertDatabaseHas('crew_members', ['id' => $member->id, 'tenant_id' => $membership->tenant_id, 'yacht_id' => $yacht->id]);

        $this->request('PATCH', "/api/crew/{$member->id}", $token, $yacht->id, ['end_date' => today('UTC')->toDateString()])
            ->assertUnprocessable()->assertJsonStructure(['errors' => ['end_date']]);
        $this->request('PATCH', "/api/crew/{$member->id}", $token, $yacht->id, ['status' => 'inactive', 'end_date' => today('UTC')->addDay()->toDateString()])
            ->assertOk()->assertJsonPath('data.status', 'inactive');
    }

    public function test_composite_foreign_key_rejects_yacht_and_tenant_mismatch_and_delete_is_prohibited(): void
    {
        [$membership, , $yacht] = $this->access();
        $otherTenant = Tenant::factory()->create();
        $otherYacht = Yacht::create(['tenant_id' => $otherTenant->id, 'name' => 'Foreign', 'status' => 'active']);

        try {
            DB::table('crew_members')->insert([
                'id' => (string) Str::uuid(), 'tenant_id' => $membership->tenant_id, 'yacht_id' => $otherYacht->id,
                'first_name' => 'Invalid', 'last_name' => 'Boundary', 'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
            ]);
            $this->fail('Composite yacht boundary accepted a tenant mismatch.');
        } catch (QueryException) {
            $this->assertTrue(true);
        }

        $member = $this->member(['tenant_id' => $membership->tenant_id, 'yacht_id' => $yacht->id]);
        $this->expectException(\LogicException::class);
        $member->delete();
    }

    public function test_unauthenticated_and_missing_context_requests_are_rejected(): void
    {
        $this->getJson('/api/crew')->assertUnauthorized();
        [, $user, $yacht, $token] = $this->access();
        YachtMembership::query()->delete();
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/crew')->assertForbidden();
    }
}
