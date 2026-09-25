<?php

namespace Tests\Feature;

use Tests\TestCase;

class CorsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['cors.allowed_origins' => ['http://localhost:3000', 'http://127.0.0.1:3000']]);
    }

    public function test_local_frontend_can_preflight_bearer_requests(): void
    {
        foreach (['/api/auth/login', '/api/tenant', '/api/auth/logout'] as $path) {
            $response = $this->withHeaders([
                'Origin' => 'http://localhost:3000',
                'Access-Control-Request-Method' => $path === '/api/tenant' ? 'GET' : 'POST',
                'Access-Control-Request-Headers' => 'authorization,content-type',
            ])->options($path)->assertNoContent()
                ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:3000')
                ->assertHeaderMissing('Access-Control-Allow-Credentials');

            $this->assertStringContainsString('authorization', strtolower($response->headers->get('Access-Control-Allow-Headers')));
        }
    }

    public function test_unapproved_origin_receives_no_cors_permission(): void
    {
        $this->withHeaders([
            'Origin' => 'https://unapproved.example',
            'Access-Control-Request-Method' => 'POST',
        ])->options('/api/auth/login')->assertHeaderMissing('Access-Control-Allow-Origin');
    }

    public function test_authentication_errors_are_readable_by_the_allowed_frontend(): void
    {
        $this->withHeader('Origin', 'http://127.0.0.1:3000')->getJson('/api/tenant')
            ->assertUnauthorized()->assertHeader('Access-Control-Allow-Origin', 'http://127.0.0.1:3000')
            ->assertJsonPath('code', 'unauthenticated');
    }
}
