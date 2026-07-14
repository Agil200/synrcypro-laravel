<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('SYNRCYPRO');
    }

    public function test_guest_can_enter_dashboard(): void
    {
        $this->post('/auth/guest')
            ->assertRedirect('/dashboard');

        $this->assertAuthenticated();
    }
}
