<?php

namespace Tests\Feature;

use Tests\TestCase;

class WebSmokeTest extends TestCase
{
    public function test_landing_page_is_available(): void
    {
        $response = $this->get(route('landing'));

        $response->assertOk();
    }

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_health_endpoint_is_available(): void
    {
        $response = $this->get('/up');

        $response->assertOk();
    }
}
