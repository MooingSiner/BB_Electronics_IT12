<?php

namespace Tests\Feature;

use App\Enums\ReturnCondition;
use App\Enums\ReturnResolution;
use App\Enums\ReturnStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\ReturnRecord;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReplacementStockTest extends TestCase
{
    use RefreshDatabase;

    private function openReturn(ReturnCondition $condition, ReturnResolution $resolution, int $stock = 100, int $quantity = 2): ReturnRecord
    {
        $product = Product::factory()->for(Category::factory())->create(['quantity_on_hand' => $stock]);
        $sale = Sale::factory()->create();
        SaleItem::factory()->create(['sale_id' => $sale->sale_id, 'product_id' => $product->product_id, 'quantity' => 5]);
        $product->newQuery()->whereKey($product->product_id)->update(['quantity_on_hand' => $stock]);

        return ReturnRecord::factory()->create([
            'sale_id' => $sale->sale_id,
            'product_id' => $product->product_id,
            'quantity' => $quantity,
            'condition' => $condition,
            'resolution' => $resolution,
            'status' => ReturnStatus::Open,
        ]);
    }

    public function test_a_replacement_for_a_defective_item_takes_the_new_units_out_of_stock(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $return = $this->openReturn(ReturnCondition::Defective, ReturnResolution::Replacement);

        $this->actingAs($owner)->patch(route('owner.returns.resolve', $return->return_id))->assertSessionHas('success');

        $this->assertSame(98, $return->product->fresh()->quantity_on_hand);
        $this->assertSame(ReturnStatus::Resolved, $return->fresh()->status);
        $this->assertDatabaseHas('stock_adjustment', [
            'product_id' => $return->product_id,
            'quantity_change' => -2,
            'reason' => "Replacement issued for return #{$return->return_id}",
        ]);
        $this->assertDatabaseHas('audit_log', ['action' => 'refund']);
    }

    public function test_a_replacement_for_a_sellable_item_puts_the_old_units_back_and_takes_new_ones_out(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $return = $this->openReturn(ReturnCondition::CustomerChangedMind, ReturnResolution::Replacement);

        $this->actingAs($owner)->patch(route('owner.returns.resolve', $return->return_id));

        $this->assertSame(100, $return->product->fresh()->quantity_on_hand);
        $this->assertDatabaseHas('stock_adjustment', ['quantity_change' => 2, 'reason' => "Restocked from return #{$return->return_id}"]);
        $this->assertDatabaseHas('stock_adjustment', ['quantity_change' => -2, 'reason' => "Replacement issued for return #{$return->return_id}"]);
    }

    public function test_a_refund_return_does_not_take_replacement_units_out(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $return = $this->openReturn(ReturnCondition::Defective, ReturnResolution::Refund);

        $this->actingAs($owner)->patch(route('owner.returns.resolve', $return->return_id));

        $this->assertSame(100, $return->product->fresh()->quantity_on_hand);
        $this->assertDatabaseMissing('stock_adjustment', ['reason' => "Replacement issued for return #{$return->return_id}"]);
    }

    public function test_a_replacement_is_blocked_when_there_is_not_enough_stock(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $return = $this->openReturn(ReturnCondition::Defective, ReturnResolution::Replacement, stock: 1);

        $this->actingAs($owner)->patch(route('owner.returns.resolve', $return->return_id))->assertSessionHas('error');

        $this->assertSame(1, $return->product->fresh()->quantity_on_hand);
        $this->assertSame(ReturnStatus::Open, $return->fresh()->status);
    }

    public function test_the_pages_explain_the_replacement_stock_change(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $cashier = User::factory()->cashierAttendant()->create();
        $return = $this->openReturn(ReturnCondition::Defective, ReturnResolution::Replacement);

        $this->actingAs($owner)->get(route('owner.returns.show', $return->return_id))
            ->assertSee('2 unit(s) will be taken out of stock for the replacement when the owner marks it resolved.');
        $this->actingAs($cashier)->get(route('cashier.returns.show', $return->return_id))
            ->assertSee('will be taken out of stock for the replacement');

        $this->actingAs($owner)->patch(route('owner.returns.resolve', $return->return_id));

        $this->actingAs($owner)->get(route('owner.returns.show', $return->return_id))
            ->assertSee('2 unit(s) taken out of stock for the replacement on');
        $this->actingAs($owner)->get(route('owner.sales.show', $return->sale_id))
            ->assertSee('Replaced');
    }
}
