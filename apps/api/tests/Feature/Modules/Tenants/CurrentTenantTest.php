<?php

namespace Tests\Feature\Modules\Tenants;

use App\Modules\Tenants\Models\Tenant;
use App\Modules\Tenants\Models\TenantMembership;
use App\Modules\Users\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CurrentTenantTest extends TestCase
{
    use RefreshDatabase;

    private function tokenFor(User $user): string
    {
        return $user->createToken('test', ['*'], now()->addDay())->plainTextToken;
    }

    private function getTenant(string $token): TestResponse
    {
        // Laravel's HTTP test harness reuses the application between requests.
        $this->app['auth']->forgetGuards();

        return $this->withToken($token)->getJson('/api/tenant');
    }

    public function test_only_the_current_tenant_is_returned_with_an_explicit_resource_shape(): void
    {
        $membership = TenantMembership::factory()->create();
        $tenant = $membership->tenant;
        $other = Tenant::factory()->create();

        $this->getTenant($this->tokenFor($membership->user))->assertOk()->assertExactJson([
            'data' => $tenant->only(['id', 'name', 'slug', 'type', 'status', 'country', 'timezone', 'currency']),
        ])->assertDontSee($other->id)->assertJsonMissingPath('data.memberships');
    }

    #[DataProvider('invalidTokens')]
    public function test_invalid_expired_and_revoked_tokens_are_rejected(string $condition): void
    {
        $user = TenantMembership::factory()->create()->user;
        $token = $user->createToken('test', ['*'], now()->addDay());

        if ($condition === 'expired') {
            $token->accessToken->forceFill(['expires_at' => now()->subMinute()])->save();
        } elseif ($condition === 'revoked') {
            $token->accessToken->delete();
        } elseif ($condition === 'aged') {
            $token->accessToken->forceFill(['created_at' => now()->subDays(2)])->save();
        }

        $value = match ($condition) {
            'invalid' => '1|not-a-valid-token',
            'malformed' => 'invalid|token',
            default => $token->plainTextToken,
        };

        $this->getTenant($value)->assertUnauthorized()->assertJsonPath('code', 'unauthenticated');
    }

    public static function invalidTokens(): array
    {
        return array_map(fn ($value) => [$value], ['invalid', 'malformed', 'expired', 'revoked', 'aged']);
    }

    #[DataProvider('inactiveStatuses')]
    public function test_existing_tokens_cannot_access_tenants_after_user_deactivation(string $status): void
    {
        $membership = TenantMembership::factory()->create();
        $token = $this->tokenFor($membership->user);
        $this->getTenant($token)->assertOk();
        $membership->user->forceFill(['status' => $status])->save();

        $this->getTenant($token)->assertForbidden()->assertJsonPath('code', 'forbidden');
    }

    public static function inactiveStatuses(): array
    {
        return [['inactive'], ['archived']];
    }

    #[DataProvider('invalidMemberships')]
    public function test_invalid_membership_denies_login_and_existing_tokens(string $condition): void
    {
        $this->freezeTime();
        $user = User::factory()->create();
        $membership = $condition === 'missing' ? null : TenantMembership::factory()->for($user)->create();

        match ($condition) {
            'inactive', 'archived' => $membership->forceFill(['status' => $condition])->save(),
            'ended' => $membership->forceFill(['start_date' => today('UTC')->subDays(2), 'end_date' => today('UTC')->subDay()])->save(),
            'ends today' => $membership->forceFill(['end_date' => today('UTC')])->save(),
            'future' => $membership->forceFill(['start_date' => today('UTC')->addDay()])->save(),
            'ambiguous' => TenantMembership::factory()->for($user)->create(),
            'duplicate' => TenantMembership::factory()->for($user)->for($membership->tenant)->create(),
            default => null,
        };

        $token = $this->tokenFor($user);
        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'password'])
            ->assertForbidden()->assertJsonPath('code', 'forbidden');
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertNull($user->refresh()->last_login_at);
        $this->getTenant($token)->assertForbidden()->assertJsonMissingPath('data');
    }

    public static function invalidMemberships(): array
    {
        return array_map(fn ($value) => [$value], ['missing', 'inactive', 'archived', 'ended', 'ends today', 'future', 'ambiguous', 'duplicate']);
    }

    #[DataProvider('unavailableTenantStatuses')]
    public function test_unavailable_tenant_denies_login_and_existing_tokens(string $status): void
    {
        $membership = TenantMembership::factory()->create();
        $user = $membership->user;
        $token = $this->tokenFor($user);
        $this->getTenant($token)->assertOk();
        $membership->tenant->forceFill(['status' => $status])->save();

        $this->getTenant($token)->assertForbidden();
        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'password'])->assertForbidden();
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public static function unavailableTenantStatuses(): array
    {
        return [['suspended'], ['archived']];
    }

    public function test_a_second_active_membership_is_ambiguous_even_when_its_tenant_is_suspended(): void
    {
        $membership = TenantMembership::factory()->create();
        TenantMembership::factory()->for($membership->user)
            ->for(Tenant::factory()->state(['status' => 'suspended']))->create();

        $this->getTenant($this->tokenFor($membership->user))->assertForbidden();
    }

    public function test_historical_and_future_memberships_do_not_change_the_current_tenant(): void
    {
        $membership = TenantMembership::factory()->create(['end_date' => today('UTC')->addDay()]);
        TenantMembership::factory()->for($membership->user)->create(['status' => 'inactive']);
        TenantMembership::factory()->for($membership->user)->create(['start_date' => today('UTC')->addDay()]);

        $this->getTenant($this->tokenFor($membership->user))->assertOk()->assertJsonPath('data.id', $membership->tenant_id);
    }

    public function test_client_supplied_tenant_identifiers_never_select_another_tenant(): void
    {
        $membership = TenantMembership::factory()->create();
        $other = Tenant::factory()->create();
        $token = $this->postJson('/api/auth/login', [
            'email' => $membership->user->email,
            'password' => 'password',
            'tenant_id' => $other->id,
        ], ['X-Tenant-ID' => $other->id])->assertOk()->json('data.access_token');

        $this->withToken($token)->json('GET', '/api/tenant?tenant_id='.$other->id,
            ['tenant_id' => $other->id], ['X-Tenant-ID' => $other->id])
            ->assertOk()->assertJsonPath('data.id', $membership->tenant_id)->assertDontSee($other->id);

        $this->getJson('/api/tenant/'.$other->id)->assertNotFound();
        $this->putJson('/api/tenant', ['name' => 'Changed'])->assertStatus(405);
        $this->assertNotSame('Changed', $membership->tenant->refresh()->name);
    }

    public function test_membership_loss_takes_effect_on_the_next_request(): void
    {
        $membership = TenantMembership::factory()->create();
        $token = $this->tokenFor($membership->user);
        $this->getTenant($token)->assertOk();
        $membership->forceFill(['status' => 'inactive'])->save();

        $this->getTenant($token)->assertForbidden();
        $this->assertDatabaseHas('tenant_memberships', ['id' => $membership->id]);
    }

    public function test_supplying_a_tenant_id_cannot_grant_a_missing_membership(): void
    {
        $user = User::factory()->create();
        $other = Tenant::factory()->create();

        $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
            'tenant_id' => $other->id,
        ], ['X-Tenant-ID' => $other->id])->assertForbidden();

        $this->withToken($this->tokenFor($user))->json('GET', '/api/tenant?tenant_id='.$other->id,
            ['tenant_id' => $other->id], ['X-Tenant-ID' => $other->id])->assertForbidden();
    }

    public function test_context_does_not_leak_between_users_or_after_a_denied_request(): void
    {
        $first = TenantMembership::factory()->create();
        $second = TenantMembership::factory()->create();
        $firstToken = $this->tokenFor($first->user);
        $secondToken = $this->tokenFor($second->user);
        $context = app(TenantContext::class);

        $this->getTenant($firstToken)->assertOk()->assertJsonPath('data.id', $first->tenant_id);
        $this->getTenant($secondToken)->assertOk()->assertJsonPath('data.id', $second->tenant_id);
        $second->forceFill(['status' => 'inactive'])->save();
        $this->getTenant($secondToken)->assertForbidden();
        $this->getTenant($firstToken)->assertOk()->assertJsonPath('data.id', $first->tenant_id);

        $this->expectException(LogicException::class);
        $context->tenant();
    }

    public function test_context_is_cleared_when_downstream_code_throws_and_errors_hide_debug_details(): void
    {
        config(['app.debug' => true]);
        Route::middleware(['auth:sanctum', 'user.active', 'tenant.context'])->get('/api/context-failure', function () {
            throw new LogicException('Internal sensitive detail');
        });
        $membership = TenantMembership::factory()->create();
        $context = app(TenantContext::class);

        $this->withToken($this->tokenFor($membership->user))->getJson('/api/context-failure')
            ->assertStatus(500)->assertExactJson(['message' => 'An unexpected error occurred.', 'code' => 'server_error']);

        $this->expectException(LogicException::class);
        $context->tenant();
    }

    public function test_container_scopes_receive_distinct_context_instances(): void
    {
        $context = app(TenantContext::class);
        $context->resolveFor(TenantMembership::factory()->create()->user);
        $this->app->forgetScopedInstances();
        $nextContext = app(TenantContext::class);
        $this->assertNotSame($context, $nextContext);

        $this->expectException(LogicException::class);
        $nextContext->tenant();
    }
}
