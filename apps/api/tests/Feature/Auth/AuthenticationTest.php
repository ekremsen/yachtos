<?php

namespace Tests\Feature\Auth;

use App\Modules\Tenants\Models\TenantMembership;
use App\Modules\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_a_hashed_expiring_token_and_minimal_identity(): void
    {
        $this->freezeSecond();
        $membership = TenantMembership::factory()->create();
        $user = $membership->user;

        $response = $this->postJson('/api/auth/login', [
            'email' => '  '.strtoupper($user->email).'  ',
            'password' => 'password',
        ])->assertOk()->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.expires_at', now()->addDay()->toISOString())
            ->assertJsonMissingPath('data.user.password')
            ->assertJsonMissingPath('data.user.remember_token');

        $plainToken = $response->json('data.access_token');
        $token = PersonalAccessToken::findToken($plainToken);
        $this->assertNotNull($token);
        $this->assertSame($user->id, $token->tokenable_id);
        $this->assertSame(hash('sha256', explode('|', $plainToken, 2)[1]), $token->token);
        $this->assertTrue($token->expires_at->equalTo(now()->addDay()));
        $this->assertTrue($user->refresh()->last_login_at->equalTo(now()));
        $this->assertTrue(Hash::check('password', $user->password));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));

        $this->withToken($plainToken)->getJson('/api/tenant')->assertOk()
            ->assertJsonPath('data.id', $membership->tenant_id);
    }

    public function test_expiration_is_configurable_and_enforced(): void
    {
        config(['sanctum.expiration' => 15]);
        $this->freezeSecond();
        $user = TenantMembership::factory()->create()->user;
        $response = $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'password'])
            ->assertOk()->assertJsonPath('data.expires_at', now()->addMinutes(15)->toISOString());

        $this->travel(16)->minutes();
        $this->withToken($response->json('data.access_token'))->getJson('/api/tenant')->assertUnauthorized();
    }

    #[DataProvider('invalidCredentials')]
    public function test_invalid_credentials_and_inactive_accounts_do_not_issue_tokens(string $status, string $email, string $password): void
    {
        $user = User::factory()->create(['email' => 'captain@example.com', 'status' => $status]);
        TenantMembership::factory()->for($user)->create();

        $this->postJson('/api/auth/login', compact('email', 'password'))->assertUnauthorized()
            ->assertExactJson(['message' => 'Authentication failed.', 'code' => 'unauthenticated']);
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertNull($user->refresh()->last_login_at);
    }

    public static function invalidCredentials(): array
    {
        return [
            'wrong password' => ['active', 'captain@example.com', 'incorrect'],
            'unknown account' => ['active', 'unknown@example.com', 'password'],
            'inactive account' => ['inactive', 'captain@example.com', 'password'],
            'archived account' => ['archived', 'captain@example.com', 'password'],
        ];
    }

    #[DataProvider('invalidPayloads')]
    public function test_login_validates_input(array $payload): void
    {
        $this->postJson('/api/auth/login', $payload)->assertUnprocessable()
            ->assertJsonPath('code', 'validation_failed')->assertJsonStructure(['message', 'code', 'errors']);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public static function invalidPayloads(): array
    {
        return [
            'missing fields' => [[]],
            'invalid email' => [['email' => 'not-an-email', 'password' => 'password']],
            'non-string fields' => [['email' => ['captain@example.com'], 'password' => ['password']]],
            'oversized password' => [['email' => 'captain@example.com', 'password' => str_repeat('x', 1025)]],
        ];
    }

    public function test_login_is_rate_limited_using_normalized_email_and_ip(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/auth/login', ['email' => 'captain@example.com', 'password' => 'wrong'])
                ->assertUnauthorized();
        }

        $this->postJson('/api/auth/login', ['email' => ' CAPTAIN@EXAMPLE.COM ', 'password' => 'wrong'])
            ->assertStatus(429)->assertJsonPath('code', 'too_many_requests')->assertHeader('Retry-After');
    }

    public function test_ip_limit_also_limits_attempts_against_different_accounts(): void
    {
        for ($attempt = 0; $attempt < 30; $attempt++) {
            $this->postJson('/api/auth/login', ['email' => "captain{$attempt}@example.com", 'password' => 'wrong'])
                ->assertUnauthorized();
        }

        $this->postJson('/api/auth/login', ['email' => 'another@example.com', 'password' => 'wrong'])
            ->assertStatus(429)->assertHeader('Retry-After');
    }

    public function test_logout_revokes_only_the_current_token_even_after_membership_ends(): void
    {
        $membership = TenantMembership::factory()->create();
        $user = $membership->user;
        $current = $user->createToken('current', ['*'], now()->addDay());
        $other = $user->createToken('other', ['*'], now()->addDay());
        $membership->forceFill(['status' => 'inactive'])->save();

        $this->withToken($current->plainTextToken)->getJson('/api/tenant')->assertForbidden();
        $this->app['auth']->forgetGuards();
        $this->postJson('/api/auth/logout')->assertNoContent();

        $this->assertNull(PersonalAccessToken::findToken($current->plainTextToken));
        $this->assertNotNull(PersonalAccessToken::findToken($other->plainTextToken));
        $this->app['auth']->forgetGuards();
        $this->postJson('/api/auth/logout')->assertUnauthorized();
    }

    public function test_archived_user_can_still_logout_with_a_valid_token(): void
    {
        $user = User::factory()->create(['status' => 'archived']);
        $token = $user->createToken('current', ['*'], now()->addDay());

        $this->withToken($token->plainTextToken)->postJson('/api/auth/logout')->assertNoContent();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_web_session_does_not_replace_a_bearer_token(): void
    {
        $this->actingAs(TenantMembership::factory()->create()->user, 'web');

        $this->getJson('/api/tenant')->assertUnauthorized();
        $this->postJson('/api/auth/logout')->assertUnauthorized();
    }

    public function test_missing_bearer_token_returns_json_even_without_accept_header(): void
    {
        $this->get('/api/tenant')->assertUnauthorized()
            ->assertExactJson(['message' => 'Authentication failed.', 'code' => 'unauthenticated']);
    }
}
