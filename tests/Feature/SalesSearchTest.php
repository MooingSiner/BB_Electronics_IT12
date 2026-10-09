<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesSearchTest extends TestCase
{
    use RefreshDatabase;

    private function saleOf(Product $product): Sale
    {
        $sale = Sale::factory()->create();
        SaleItem::factory()->create(['sale_id' => $sale->sale_id, 'product_id' => $product->product_id, 'quantity' => 1]);

        return $sale;
    }

    public function test_the_list_finds_a_sale_by_a_scanned_barcode_a_product_code_or_the_receipt_number(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $product = Product::factory()->for(Category::factory())->create(['quantity_on_hand' => 50, 'barcode' => '4800999000111']);
        $other = Product::factory()->for(Category::factory())->create(['quantity_on_hand' => 50, 'barcode' => '4800999000222']);
        $wanted = $this->saleOf($product);
        $this->saleOf($other);

        foreach (['4800999000111', $product->product_code, $wanted->code(), (string) $wanted->sale_id] as $term) {
            $this->actingAs($owner)->get(route('owner.sales.index', ['search' => $term]))
                ->assertOk()->assertSee(route('owner.sales.show', $wanted->sale_id), false);
        }

        $this->actingAs($owner)->get(route('owner.sales.index', ['search' => '4800999000111']))
            ->assertDontSee(route('owner.sales.show', Sale::where('sale_id', '!=', $wanted->sale_id)->value('sale_id')), false);
    }

    public function test_a_search_with_no_match_says_so_instead_of_showing_sample_rows(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $this->saleOf(Product::factory()->for(Category::factory())->create(['quantity_on_hand' => 50]));

        $this->actingAs($owner)->get(route('owner.sales.index', ['search' => 'zzzz-nothing']))
            ->assertOk()->assertSee('No transactions found.')->assertDontSee('TXN-2024-001');
    }

    public function test_the_cashier_list_finds_a_sale_by_barcode_too(): void
    {
        $cashier = User::factory()->cashierAttendant()->create();
        $product = Product::factory()->for(Category::factory())->create(['quantity_on_hand' => 50, 'barcode' => '4800999000333']);
        $sale = $this->saleOf($product);

        $this->actingAs($cashier)->get(route('cashier.sales.index', ['search' => '4800999000333']))
            ->assertOk()->assertSee(route('cashier.sales.show', $sale->sale_id), false);
    }
}
