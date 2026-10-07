<?php

namespace Tests\Feature;

use App\Livewire\Cashier\Pos;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PosNumericInputTest extends TestCase
{
    use RefreshDatabase;

    public function test_clearing_the_amount_received_field_does_not_crash_the_pos(): void
    {
        $cashier = User::factory()->cashierAttendant()->create();
        $product = Product::factory()->for(Category::factory())->create(['quantity_on_hand' => 10, 'unit_price' => 50]);

        Livewire::actingAs($cashier)
            ->test(Pos::class)
            ->call('addToCart', $product->product_id)
            ->set('amountReceived', 100)
            ->set('amountReceived', '')
            ->assertOk()
            ->call('addToCart', $product->product_id)
            ->assertOk();
    }

    public function test_clearing_the_discount_field_does_not_crash_the_pos(): void
    {
        $cashier = User::factory()->cashierAttendant()->create();
        $product = Product::factory()->for(Category::factory())->create(['quantity_on_hand' => 10, 'unit_price' => 50]);

        Livewire::actingAs($cashier)
            ->test(Pos::class)
            ->call('addToCart', $product->product_id)
            ->call('setDiscountType', 'percent')
            ->set('discountValue', 10)
            ->set('discountValue', '')
            ->assertOk()
            ->call('addToCart', $product->product_id)
            ->assertOk();
    }
}
