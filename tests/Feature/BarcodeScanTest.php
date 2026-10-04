<?php

namespace Tests\Feature;

use App\Livewire\Cashier\Pos;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BarcodeScanTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $attributes = []): Product
    {
        return Product::factory()->for(Category::factory())->create(array_merge([
            'quantity_on_hand' => 10,
            'unit_price' => 100,
        ], $attributes));
    }

    public function test_scanning_a_barcode_at_the_pos_adds_the_product_and_clears_the_box(): void
    {
        $cashier = User::factory()->cashierAttendant()->create();
        $product = $this->product(['barcode' => '4800123456789']);

        Livewire::actingAs($cashier)->test(Pos::class)
            ->set('search', '4800123456789')
            ->call('scan')
            ->assertSet("cart.{$product->product_id}.quantity", 1)
            ->assertSet('search', '');
    }

    public function test_scanning_twice_adds_a_second_unit_and_the_product_code_works_too(): void
    {
        $cashier = User::factory()->cashierAttendant()->create();
        $product = $this->product(['barcode' => '4800123456789']);

        Livewire::actingAs($cashier)->test(Pos::class)
            ->set('search', '4800123456789')->call('scan')
            ->set('search', $product->product_code)->call('scan')
            ->assertSet("cart.{$product->product_id}.quantity", 2);
    }

    public function test_an_unknown_barcode_tells_the_cashier_and_adds_nothing(): void
    {
        $cashier = User::factory()->cashierAttendant()->create();
        $this->product();

        Livewire::actingAs($cashier)->test(Pos::class)
            ->set('search', '0000000000000')
            ->call('scan')
            ->assertSet('cart', [])
            ->assertSet('errorMessage', 'No product found for "0000000000000".');
    }

    public function test_an_archived_product_cannot_be_scanned(): void
    {
        $cashier = User::factory()->cashierAttendant()->create();
        $this->product(['barcode' => '111', 'is_active' => false]);

        Livewire::actingAs($cashier)->test(Pos::class)
            ->set('search', '111')->call('scan')
            ->assertSet('cart', []);
    }

    public function test_owner_can_save_a_barcode_and_it_must_be_unique(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $this->product(['barcode' => '4800123456789']);
        $category = Category::first();

        $payload = [
            'name' => 'Test Plug', 'category' => $category->category_name, 'unit_price' => 50,
            'initial_qty' => 5, 'reorder_level' => 2,
        ];

        $this->actingAs($owner)->post(route('owner.inventory.store'), $payload + ['barcode' => '4800123456789'])
            ->assertSessionHasErrors('barcode');

        $this->actingAs($owner)->post(route('owner.inventory.store'), $payload + ['barcode' => '999888'])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('product', ['product_name' => 'Test Plug', 'barcode' => '999888']);
    }

    public function test_a_product_added_without_a_barcode_falls_back_to_its_product_code(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $category = Category::factory()->create();

        $this->actingAs($owner)->post(route('owner.inventory.store'), [
            'name' => 'No Barcode Item', 'category' => $category->category_name, 'unit_price' => 50,
            'initial_qty' => 5, 'reorder_level' => 2,
        ])->assertSessionHasNoErrors();

        $product = Product::where('product_name', 'No Barcode Item')->firstOrFail();
        $this->assertSame($product->product_code, $product->barcode);
    }
}
