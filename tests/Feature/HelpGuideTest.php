<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\HelpGuide;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HelpGuideTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_help_topic_has_a_screenshot(): void
    {
        foreach (['owner', 'cashier'] as $role) {
            foreach (HelpGuide::topics($role) as $topic) {
                $this->assertFileExists(public_path('help/'.$topic['image'].'.jpg'), $topic['title']);
            }
        }
    }

    public function test_owner_pages_have_the_help_button_and_guide(): void
    {
        $this->actingAs(User::factory()->ownerManager()->create())
            ->get(route('owner.dashboard'))
            ->assertOk()->assertSee('open-help', false)->assertSee('Help guide')->assertSee('Audit Log');
    }

    public function test_cashier_pages_have_the_help_button_and_guide(): void
    {
        $cashier = User::factory()->cashierAttendant()->create();

        $this->actingAs($cashier)->get(route('cashier.dashboard'))
            ->assertOk()->assertSee('open-help', false)->assertSee('Help guide')->assertDontSee('Audit Log');

        $this->actingAs($cashier)->get(route('cashier.pos'))
            ->assertOk()->assertSee('open-help', false)->assertSee('Help guide');
    }

    public function test_hidden_until_ready_elements_stay_hidden_before_alpine_loads(): void
    {
        $this->actingAs(User::factory()->ownerManager()->create())
            ->get(route('owner.dashboard'))
            ->assertSee('[x-cloak] { display: none !important; }', false);
    }
}
