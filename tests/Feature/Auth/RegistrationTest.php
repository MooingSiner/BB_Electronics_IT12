<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_there_is_no_public_registration_page(): void
    {
        $this->get('/register')->assertNotFound();
    }

    public function test_nobody_can_create_an_account_by_posting_to_the_registration_url(): void
    {
        $this->post('/register', [
            'full_name' => 'Test User',
            'username' => 'testuser',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertStatus(404);

        $this->assertGuest();
        $this->assertSame(0, User::where('username', 'testuser')->count());
    }
}
