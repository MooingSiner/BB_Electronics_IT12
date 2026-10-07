<?php

namespace Tests\Feature;

use App\Livewire\Cashier\Pos;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PosStockAlertsTest extends TestCase
{
    use RefreshDatabase;

    private function product(int $stock, int $reorder = 5): Product
    {
        return Product::factory()->for(Category::factory())->create([
            'quantity_on_hand' => $stock,
            'reorder_level' => $reorder,
            'unit_price' => 100,
        ]);
    }

    public function test_asking_for_more_than_the_stock_sets_the_quantity_to_the_stock_and_tells_the_cashier(): void
    {
        $cashier = User::factory()->cashierAttendant()->create();
        $product = $this->product(160);
        $key = (string) $product->product_id;

        Livewire::actingAs($cashier)
            ->test(Pos::class)
            ->call('addToCart', $product->product_id)
            ->call('updateQuantity', $key, 200)
            ->assertSet("cart.{$key}.quantity", 160)
            ->assertSet('errorMessage', "Only 160 unit(s) of {$product->product_name} in stock, so the quantity was set to 160.")
            ->assertDispatched('cart-quantity-corrected', cartKey: $key, quantity: 160);
    }

    public function test_a_valid_quantity_clears_the_previous_stock_warning(): void
    {
        $cashier = User::factory()->cashierAttendant()->create();
        $product = $this->product(20);
        $key = (string) $product->product_id;

        Livewire::actingAs($cashier)
            ->test(Pos::class)
            ->call('addToCart', $product->product_id)
            ->call('updateQuantity', $key, 50)
            ->assertNotSet('errorMessage', null)
            ->call('updateQuantity', $key, 5)
            ->assertSet('errorMessage', null);
    }

    public function test_the_cart_shows_how_many_of_each_product_are_in_stock(): void
    {
        $cashier = User::factory()->cashierAttendant()->create();
        $product = $this->product(42);

        Livewire::actingAs($cashier)
            ->test(Pos::class)
            ->call('addToCart', $product->product_id)
            ->assertSee('42 in stock');
    }

    public function test_the_completed_sale_lists_the_products_sold(): void
    {
        $cashier = User::factory()->cashierAttendant()->create();
        $product = $this->product(50);

        Livewire::actingAs($cashier)
            ->test(Pos::class)
            ->call('addToCart', $product->product_id)
            ->call('updateQuantity', (string) $product->product_id, 3)
            ->call('setPayment', 'GCash')
            ->call('completeSale')
            ->assertSet('completedSale.items.0.name', $product->product_name)
            ->assertSet('completedSale.items.0.quantity', 3)
            ->assertSet('completedSale.items.0.subtotal', '300.00')
            ->assertSet('completedSale.low_stock', [])
            ->assertSee($product->product_name);
    }

    public function test_the_cashier_is_warned_when_a_sale_leaves_a_product_low_on_stock(): void
    {
        $cashier = User::factory()->cashierAttendant()->create();
        $product = $this->product(stock: 10, reorder: 5);

        Livewire::actingAs($cashier)
            ->test(Pos::class)
            ->call('addToCart', $product->product_id)
            ->call('updateQuantity', (string) $product->product_id, 6)
            ->call('setPayment', 'GCash')
            ->call('completeSale')
            ->assertSet('completedSale.low_stock.0.name', $product->product_name)
            ->assertSet('completedSale.low_stock.0.left', 4)
            ->assertSee('Low stock alert');
    }
}
