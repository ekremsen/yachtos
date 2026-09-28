<?php

namespace Tests\Feature\Modules\Yachts;

use App\Modules\Tenants\Models\TenantMembership;
use App\Modules\Users\Models\User;
use App\Modules\Yachts\Models\Yacht;
use App\Modules\Yachts\Models\YachtMembership;
use App\Support\Yachts\YachtContext;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use LogicException;
use Tests\TestCase;

class YachtContextTest extends TestCase
{
    use RefreshDatabase;

    private function setupAccess(int $yachtCount = 1): array
    {
        $tenantMembership = TenantMembership::factory()->create();
        $user = $tenantMembership->user;
        $yachts = collect();

        for ($i = 1; $i <= $yachtCount; $i++) {
            $yacht = Yacht::create(['tenant_id' => $tenantMembership->tenant_id, 'name' => "Yacht {$i}", 'status' => 'active']);
            YachtMembership::create([
                'tenant_id' => $tenantMembership->tenant_id, 'tenant_membership_id' => $tenantMembership->id,
                'user_id' => $user->id, 'yacht_id' => $yacht->id, 'role' => 'captain', 'status' => 'active',
                'start_date' => today('UTC'),
            ]);
            $yachts->push($yacht);
        }

        return [$tenantMembership, $user, $yachts];
    }

    private function token(User $user): string
    {
        return $user->createToken('test', ['*'], now()->addDay())->plainTextToken;
    }

    private function api(string $token, string $path, array $headers = [])
    {
        $this->app['auth']->forgetGuards();

        return $this->withToken($token)->getJson($path, $headers);
    }

    public function test_yachts_listing_excludes_other_tenants_and_inactive_access(): void
    {
        [$membership, $user, $yachts] = $this->setupAccess();
        [$otherMembership, , $otherYachts] = $this->setupAccess();
        $inactiveYacht = Yacht::create(['tenant_id' => $membership->tenant_id, 'name' => 'Inactive yacht', 'status' => 'inactive']);
        $inactiveJoin = YachtMembership::create([
            'tenant_id' => $membership->tenant_id, 'tenant_membership_id' => $membership->id,
            'user_id' => $user->id, 'yacht_id' => $inactiveYacht->id, 'role' => 'crew', 'status' => 'inactive',
            'start_date' => today('UTC'),
        ]);

        $this->api($this->token($user), '/api/yachts')->assertOk()
            ->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $yachts->first()->id)
            ->assertDontSee($otherMembership->tenant_id)->assertDontSee($otherYachts->first()->id)
            ->assertDontSee($inactiveJoin->id)->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_inactive_yacht_membership_yacht_and_parent_tenant_membership_deny_access(): void
    {
        [$membership, $user, $yachts] = $this->setupAccess();
        $token = $this->token($user);
        $yacht = $yachts->first();

        YachtMembership::where('yacht_id', $yacht->id)->update(['status' => 'inactive']);
        $this->api($token, '/api/yachts')->assertOk()->assertJsonCount(0, 'data');
        $this->api($token, '/api/yacht')->assertForbidden();

        YachtMembership::where('yacht_id', $yacht->id)->update(['status' => 'active']);
        $yacht->update(['status' => 'inactive']);
        $this->api($token, '/api/yacht')->assertForbidden();

        $yacht->update(['status' => 'active']);
        $membership->forceFill(['status' => 'inactive'])->save();
        $this->api($token, '/api/yachts')->assertForbidden();
        $this->api($token, '/api/yacht')->assertForbidden();
    }

    public function test_date_bound_parent_and_yacht_memberships_use_inclusive_start_exclusive_end(): void
    {
        [$membership, $user, $yachts] = $this->setupAccess();
        $token = $this->token($user);
        $yacht = $yachts->first();

        YachtMembership::where('yacht_id', $yacht->id)->update(['start_date' => today('UTC')->addDay()]);
        $this->api($token, '/api/yacht')->assertForbidden();
        YachtMembership::where('yacht_id', $yacht->id)->update(['start_date' => today('UTC'), 'end_date' => today('UTC')]);
        $this->api($token, '/api/yacht')->assertForbidden();
        YachtMembership::where('yacht_id', $yacht->id)->update(['end_date' => null]);

        $membership->forceFill(['end_date' => today('UTC')])->save();
        $this->api($token, '/api/yacht')->assertForbidden();
    }

    public function test_single_yacht_auto_resolves_and_context_is_cleared_after_response(): void
    {
        [, $user, $yachts] = $this->setupAccess();
        $context = app(YachtContext::class);

        $this->api($this->token($user), '/api/yacht')->assertOk()->assertJsonPath('data.id', $yachts->first()->id);
        $this->expectException(LogicException::class);
        $context->yacht();
    }

    public function test_multiple_yachts_require_selection_and_valid_selection_resolves(): void
    {
        [, $user, $yachts] = $this->setupAccess(2);
        $token = $this->token($user);

        $this->api($token, '/api/yacht')->assertStatus(409)->assertJsonPath('code', 'conflict');
        $this->api($token, '/api/yacht', ['X-Yacht-Id' => $yachts[1]->id])->assertOk()
            ->assertJsonPath('data.id', $yachts[1]->id);
    }

    public function test_invalid_cross_tenant_and_malformed_yacht_selection_are_safely_denied(): void
    {
        [, $user] = $this->setupAccess();
        [, , $foreignYachts] = $this->setupAccess();
        $token = $this->token($user);

        $this->api($token, '/api/yacht', ['X-Yacht-Id' => $foreignYachts->first()->id])->assertForbidden()
            ->assertExactJson(['message' => 'Access is unavailable.', 'code' => 'forbidden'])
            ->assertDontSee($foreignYachts->first()->id);
        $this->api($token, '/api/yacht', ['X-Yacht-Id' => 'not-a-uuid'])->assertForbidden()
            ->assertExactJson(['message' => 'Access is unavailable.', 'code' => 'forbidden']);
        $this->api($token, '/api/yacht', ['X-Yacht-Id' => '00000000-0000-4000-8000-000000000099'])->assertForbidden();
    }

    public function test_tenant_endpoint_and_logout_do_not_require_yacht_membership(): void
    {
        $tenantMembership = TenantMembership::factory()->create();
        $token = $this->token($tenantMembership->user);
        $this->api($token, '/api/tenant')->assertOk()->assertJsonPath('data.id', $tenantMembership->tenant_id);
        $this->withToken($token)->postJson('/api/auth/logout')->assertNoContent();
    }

    public function test_composite_foreign_keys_enforce_tenant_user_and_yacht_boundaries(): void
    {
        [$firstMembership, $firstUser, $firstYachts] = $this->setupAccess();
        [$otherMembership, $otherUser, $otherYachts] = $this->setupAccess();

        foreach ([
            ['tenant_id' => $firstMembership->tenant_id, 'tenant_membership_id' => $firstMembership->id, 'user_id' => $firstUser->id, 'yacht_id' => $otherYachts->first()->id],
            ['tenant_id' => $firstMembership->tenant_id, 'tenant_membership_id' => $otherMembership->id, 'user_id' => $otherUser->id, 'yacht_id' => $firstYachts->first()->id],
            ['tenant_id' => $firstMembership->tenant_id, 'tenant_membership_id' => $firstMembership->id, 'user_id' => $otherUser->id, 'yacht_id' => $firstYachts->first()->id],
        ] as $invalid) {
            try {
                DB::table('yacht_memberships')->insert($invalid + [
                    'id' => (string) Str::uuid(), 'role' => 'captain', 'status' => 'active',
                    'start_date' => today('UTC')->toDateString(), 'created_at' => now(), 'updated_at' => now(),
                ]);
                $this->fail('Composite boundary constraint accepted an invalid yacht membership.');
            } catch (QueryException) {
                $this->assertTrue(true);
            }
        }
    }

    public function test_invalid_end_date_is_rejected_by_application_boundary_validation(): void
    {
        [$membership, $user, $yachts] = $this->setupAccess();

        $this->expectException(ValidationException::class);
        YachtMembership::create([
            'tenant_id' => $membership->tenant_id, 'tenant_membership_id' => $membership->id,
            'user_id' => $user->id, 'yacht_id' => $yachts->first()->id, 'role' => 'captain', 'status' => 'active',
            'start_date' => today('UTC'), 'end_date' => today('UTC'),
        ]);
    }
}
