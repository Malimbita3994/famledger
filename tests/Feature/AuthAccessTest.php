<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthAccessTest extends TestCase
{
    public function test_profile_requires_authentication(): void
    {
        $response = $this->get(route('profile.edit'));

        $response->assertRedirect(route('login'));
    }

    public function test_settings_requires_authentication(): void
    {
        $response = $this->get(route('settings.index'));

        $response->assertRedirect(route('login'));
    }
}
