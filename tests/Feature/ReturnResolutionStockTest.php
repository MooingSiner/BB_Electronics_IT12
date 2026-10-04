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

class ReturnResolutionStockTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A return the cashier recorded for 2 units of a product that was sold in a completed sale.
     */
    private function openReturn(ReturnCondition $condition, int $quantity = 2): ReturnRecord
    {
        $product = Product::factory()->for(Category::factory())->create(['quantity_on_hand' => 100]);
        $sale = Sale::factory()->create();
        SaleItem::factory()->create(['sale_id' => $sale->sale_id, 'product_id' => $product->product_id, 'quantity' => 5]);

        return ReturnRecord::factory()->create([
            'sale_id' => $sale->sale_id,
            'product_id' => $product->product_id,
            'quantity' => $quantity,
            'condition' => $condition,
            'resolution' => ReturnResolution::Refund,
            'status' => ReturnStatus::Open,
        ]);
    }

    public function test_resolving_a_return_of_a_sellable_item_puts_it_back_in_stock(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $return = $this->openReturn(ReturnCondition::CustomerChangedMind);
        $before = $return->product->fresh()->quantity_on_hand;

        $this->actingAs($owner)->patch(route('owner.returns.resolve', $return->return_id))
            ->assertRedirect(route('owner.returns.show', $return->return_id));

        $this->assertSame($before + 2, $return->product->fresh()->quantity_on_hand);
        $this->assertSame(ReturnStatus::Resolved, $return->fresh()->status);
        $this->assertDatabaseHas('stock_adjustment', [
            'product_id' => $return->product_id,
            'quantity_change' => 2,
            'reason' => "Restocked from return #{$return->return_id}",
        ]);
    }

    public function test_resolving_a_return_of_a_defective_or_damaged_item_does_not_add_it_back(): void
    {
        $owner = User::factory()->ownerManager()->create();

        foreach ([ReturnCondition::Defective, ReturnCondition::Damaged] as $condition) {
            $return = $this->openReturn($condition);
            $before = $return->product->fresh()->quantity_on_hand;

            $this->actingAs($owner)->patch(route('owner.returns.resolve', $return->return_id));

            $this->assertSame($before, $return->product->fresh()->quantity_on_hand, "{$condition->value} must not be restocked");
            $this->assertSame(ReturnStatus::Resolved, $return->fresh()->status);
            $this->assertDatabaseMissing('stock_adjustment', ['reason' => "Restocked from return #{$return->return_id}"]);
        }
    }

    public function test_resolving_the_same_return_twice_does_not_add_stock_twice(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $return = $this->openReturn(ReturnCondition::WrongItem);
        $before = $return->product->fresh()->quantity_on_hand;

        $this->actingAs($owner)->patch(route('owner.returns.resolve', $return->return_id));
        $this->actingAs($owner)->patch(route('owner.returns.resolve', $return->return_id))
            ->assertSessionHas('error');

        $this->assertSame($before + 2, $return->product->fresh()->quantity_on_hand);
    }

    public function test_a_cashier_cannot_resolve_a_return(): void
    {
        $cashier = User::factory()->cashierAttendant()->create();
        $return = $this->openReturn(ReturnCondition::CustomerChangedMind);
        $before = $return->product->fresh()->quantity_on_hand;

        $response = $this->actingAs($cashier)->patch(route('owner.returns.resolve', $return->return_id));

        $this->assertNotSame(200, $response->getStatusCode());
        $this->assertSame(ReturnStatus::Open, $return->fresh()->status);
        $this->assertSame($before, $return->product->fresh()->quantity_on_hand);
    }

    public function test_the_return_pages_say_what_happens_to_stock(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $cashier = User::factory()->cashierAttendant()->create();
        $sellable = $this->openReturn(ReturnCondition::CustomerChangedMind);
        $defective = $this->openReturn(ReturnCondition::Defective);

        $this->actingAs($owner)->get(route('owner.returns.show', $sellable->return_id))
            ->assertOk()->assertSee('Will be added back to stock when the owner marks it resolved.');
        $this->actingAs($owner)->get(route('owner.returns.show', $defective->return_id))
            ->assertOk()->assertSee('Will not be added back to stock, because the item is defective.');
        $this->actingAs($cashier)->get(route('cashier.returns.show', $sellable->return_id))
            ->assertOk()->assertSee('Will be added back to stock when the owner marks it resolved.');

        $this->actingAs($owner)->patch(route('owner.returns.resolve', $sellable->return_id));

        $this->actingAs($owner)->get(route('owner.returns.show', $sellable->return_id))
            ->assertSee('2 unit(s) added back to stock on');
    }
}
