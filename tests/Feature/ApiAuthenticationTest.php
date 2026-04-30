<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiAuthenticationTest extends TestCase
{
    public function test_api_user_requires_authentication(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertUnauthorized();
    }

    public function test_api_dashboard_requires_authentication(): void
    {
        $response = $this->getJson('/api/dashboard');

        $response->assertUnauthorized();
    }
}
