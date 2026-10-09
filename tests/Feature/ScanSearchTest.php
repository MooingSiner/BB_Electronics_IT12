<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScanSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_owner_lists_with_a_barcode_search_box_load_the_scan_script(): void
    {
        $owner = User::factory()->ownerManager()->create();

        foreach ([route('owner.sales.index'), route('owner.inventory.index')] as $url) {
            $this->actingAs($owner)->get($url)->assertOk()
                ->assertSee('data-scan-search', false)
                ->assertSee("document.querySelector('input[data-scan-search]')", false);
        }
    }

    public function test_the_cashier_lists_with_a_barcode_search_box_load_the_scan_script_and_submit_by_themselves(): void
    {
        $cashier = User::factory()->cashierAttendant()->create();

        foreach ([route('cashier.sales.index'), route('cashier.inventory.index')] as $url) {
            $this->actingAs($cashier)->get($url)->assertOk()
                ->assertSee('data-scan-search data-scan-submit', false)
                ->assertSee("document.querySelector('input[data-scan-search]')", false);
        }
    }

    public function test_the_point_of_sale_search_box_takes_scans_without_clicking_and_presses_enter_for_the_scanner(): void
    {
        $this->actingAs(User::factory()->cashierAttendant()->create())
            ->get(route('cashier.pos'))
            ->assertOk()
            ->assertSee('data-scan-search data-scan-enter', false)
            ->assertSee("document.querySelector('input[data-scan-search]')", false);
    }
}
