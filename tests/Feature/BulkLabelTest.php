<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkLabelTest extends TestCase
{
    use RefreshDatabase;

    private function product(string $name, string $barcode): Product
    {
        return Product::factory()->for(Category::factory())->create(['product_name' => $name, 'barcode' => $barcode, 'unit_price' => 50, 'quantity_on_hand' => 10]);
    }

    public function test_the_picker_opens_when_no_products_are_chosen_and_the_inventory_page_links_to_it(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $this->product('Alpha Plug', '4800000000011');

        $this->actingAs($owner)->get(route('owner.inventory.labels'))
            ->assertOk()->assertSee('Print Barcode Labels')->assertSee('Alpha Plug')->assertSee('data-scan-search', false);

        $this->actingAs($owner)->get(route('owner.inventory.index'))->assertSee(route('owner.inventory.labels'), false);
    }

    public function test_the_sheet_has_the_chosen_number_of_labels_for_each_product(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $first = $this->product('Alpha Plug', '4800000000011');
        $second = $this->product('Beta Cable', '4800000000022');
        $this->product('Gamma Not Chosen', '4800000000033');

        $page = $this->actingAs($owner)->get(route('owner.inventory.labels', [
            'items' => [$first->product_id => 3, $second->product_id => 2],
            'size' => 'medium',
        ]))->assertOk()->assertSee('Print 5 labels')->assertDontSee('Gamma Not Chosen');

        $this->assertSame(3, substr_count($page->getContent(), '<div class="name">Alpha Plug</div>'));
        $this->assertSame(2, substr_count($page->getContent(), '<div class="name">Beta Cable</div>'));
        $this->assertStringContainsString('width: 92mm', $page->getContent());
    }

    public function test_copies_are_limited_and_unknown_or_bad_entries_are_ignored(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $product = $this->product('Alpha Plug', '4800000000011');

        $page = $this->actingAs($owner)->get(route('owner.inventory.labels', ['items' => [$product->product_id => 999, 99999 => 2, 'abc' => 1]]))->assertOk();

        $this->assertSame(60, substr_count($page->getContent(), '<div class="name">Alpha Plug</div>'));
    }

    public function test_a_cashier_cannot_print_bulk_labels(): void
    {
        $this->actingAs(User::factory()->cashierAttendant()->create())->get(route('owner.inventory.labels'))->assertForbidden();
    }

    public function test_the_single_product_label_still_works(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $product = $this->product('Alpha Plug', '4800000000011');

        $page = $this->actingAs($owner)->get(route('owner.inventory.label', [$product->product_id, 'copies' => 2]))->assertOk();

        $this->assertSame(2, substr_count($page->getContent(), '<div class="name">Alpha Plug</div>'));
    }
}
