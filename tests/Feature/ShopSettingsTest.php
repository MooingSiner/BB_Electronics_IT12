<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_owner_sees_the_current_vat_rate(): void
    {
        Setting::setVatRate(8.5);

        $this->actingAs(User::factory()->ownerManager()->create())->get(route('owner.settings.edit'))
            ->assertOk()
            ->assertSee('Shop Settings')
            ->assertSee('value="8.5"', false);
    }

    public function test_the_default_rate_comes_from_the_config_until_the_owner_sets_one(): void
    {
        config(['shop.vat_rate' => 12]);

        $this->assertSame(12.0, Setting::vatRate());

        Setting::setVatRate(3);

        $this->assertSame(3.0, Setting::vatRate());
    }

    public function test_the_owner_can_change_the_vat_rate_and_it_is_written_to_the_audit_log(): void
    {
        $this->actingAs(User::factory()->ownerManager()->create())
            ->put(route('owner.settings.update'), ['vat_rate' => '5.5'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(5.5, Setting::vatRate());
        $this->assertDatabaseHas('audit_log', ['action' => 'setting']);
    }

    public function test_saving_the_same_rate_again_writes_nothing_to_the_audit_log(): void
    {
        Setting::setVatRate(12);

        $this->actingAs(User::factory()->ownerManager()->create())->put(route('owner.settings.update'), ['vat_rate' => '12']);

        $this->assertDatabaseMissing('audit_log', ['action' => 'setting']);
    }

    public function test_a_rate_outside_zero_to_one_hundred_is_refused(): void
    {
        Setting::setVatRate(12);
        $owner = User::factory()->ownerManager()->create();

        foreach (['-1', '100.5', 'abc', '', '1.234'] as $bad) {
            $this->actingAs($owner)->put(route('owner.settings.update'), ['vat_rate' => $bad])->assertSessionHasErrors('vat_rate');
        }

        $this->assertSame(12.0, Setting::vatRate());
    }

    public function test_a_cashier_cannot_open_or_change_the_settings(): void
    {
        $cashier = User::factory()->cashierAttendant()->create();

        $this->actingAs($cashier)->get(route('owner.settings.edit'))->assertForbidden();
        $this->actingAs($cashier)->put(route('owner.settings.update'), ['vat_rate' => '0'])->assertForbidden();
    }
}
