<?php

namespace Tests\Feature\Auth;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeactivatedUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_deactivated_account_cannot_log_in(): void
    {
        $user = User::factory()->cashierAttendant()->create(['status' => UserStatus::Inactive]);

        $this->post('/login', ['username' => $user->username, 'password' => 'password'])
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_an_active_account_still_logs_in(): void
    {
        $user = User::factory()->cashierAttendant()->create();

        $this->post('/login', ['username' => $user->username, 'password' => 'password'])->assertSessionHasNoErrors();

        $this->assertAuthenticatedAs($user);
    }

    public function test_a_user_deactivated_while_logged_in_is_signed_out_on_the_next_request(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $cashier = User::factory()->cashierAttendant()->create();

        $this->actingAs($cashier)->get(route('cashier.dashboard'))->assertOk();

        $cashier->update(['status' => UserStatus::Inactive]);

        $this->actingAs($cashier->fresh())->get(route('cashier.dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('username');

        $this->assertGuest();
        $this->assertTrue($owner->fresh()->status === UserStatus::Active);
    }
}
