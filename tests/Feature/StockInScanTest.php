<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockInScanTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_stock_in_page_focuses_the_scan_box_and_adds_a_scanned_product_without_enter(): void
    {
        $this->actingAs(User::factory()->ownerManager()->create())
            ->get(route('owner.inventory.stockin.bulk'))
            ->assertOk()
            ->assertSee('id="productSearch" autocomplete="off" data-scan-search', false)
            ->assertSee("document.querySelector('input[data-scan-search]')", false)
            ->assertSee('function addScanned', false)
            ->assertDontSee('data-scan-search data-scan-submit', false);
    }
}
