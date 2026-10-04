<?php

namespace Tests\Feature;

use App\Livewire\Cashier\Pos;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Support\CostCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Livewire\Livewire;
use Tests\TestCase;

class CostCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_costs_are_encoded_with_the_key_where_the_last_letter_is_zero(): void
    {
        $this->assertSame('TT.SD', CostCode::encode(66.50));
        $this->assertSame('SDD', CostCode::encode(500));
        $this->assertSame('CHRISTYNED', CostCode::encode(1234567890));
        $this->assertSame('ES', CostCode::encode('95.00'));
        $this->assertSame('D.CC', CostCode::encode(0.11));
    }

    public function test_a_bad_key_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        CostCode::encode(10, 'AABBCCDDEE');
    }

    public function test_the_cashier_sees_the_code_but_never_the_real_cost(): void
    {
        $cashier = User::factory()->cashierAttendant()->create();
        $product = Product::factory()->for(Category::factory())->create(['cost_price' => 66.50, 'unit_price' => 95, 'quantity_on_hand' => 10]);

        Livewire::actingAs($cashier)->test(Pos::class)->assertSee('Capital price: TT.SD')->assertDontSee('66.50');

        $this->actingAs($cashier)->get(route('cashier.inventory.index'))->assertSee('Capital price: TT.SD')->assertDontSee('66.50');
        $this->actingAs($cashier)->get(route('cashier.inventory.show', $product->product_id))->assertSee('Capital price: TT.SD')->assertDontSee('66.50');
    }

    public function test_the_owner_label_shows_the_code(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $product = Product::factory()->for(Category::factory())->create(['cost_price' => 66.50, 'barcode' => '123']);

        $this->actingAs($owner)->get(route('owner.inventory.label', $product->product_id))->assertSee('TT.SD');
    }

    public function test_the_owner_inventory_table_shows_the_cost_price_but_the_cashier_table_does_not(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $cashier = User::factory()->cashierAttendant()->create();
        Product::factory()->for(Category::factory())->create(['cost_price' => 66.50, 'unit_price' => 95]);

        $this->actingAs($owner)->get(route('owner.inventory.index'))->assertSee('Capital Price')->assertSee('₱66.50');
        $this->actingAs($cashier)->get(route('cashier.inventory.index'))->assertDontSee('₱66.50');
    }

    public function test_the_cart_popup_shows_the_cost_code_of_each_item(): void
    {
        $cashier = User::factory()->cashierAttendant()->create();
        $product = Product::factory()->for(Category::factory())->create(['cost_price' => 66.50, 'unit_price' => 95, 'quantity_on_hand' => 10]);

        Livewire::actingAs($cashier)->test(Pos::class)
            ->call('addToCart', $product->product_id)
            ->assertSeeHtml('Capital price: TT.SD</p>')
            ->assertDontSee('66.50');
    }

    public function test_owner_and_cashier_pages_include_the_confirm_popup(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $cashier = User::factory()->cashierAttendant()->create();

        $this->actingAs($owner)->get(route('owner.sales.index'))->assertSee('id="confirmDialog"', false);
        $this->actingAs($cashier)->get(route('cashier.sales.index'))->assertSee('id="confirmDialog"', false);
    }
}
