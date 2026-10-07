<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockReverseTest extends TestCase
{
    use RefreshDatabase;

    private function product(int $stock = 100): Product
    {
        return Product::factory()->for(Category::factory())->create(['quantity_on_hand' => $stock]);
    }

    private function manualEntry(Product $product, int $change, string $reason = 'Typed by hand'): StockAdjustment
    {
        return StockAdjustment::create([
            'product_id' => $product->product_id,
            'user_id' => User::factory()->ownerManager()->create()->user_id,
            'adjustment_date' => now(),
            'quantity_change' => $change,
            'reason' => $reason,
        ]);
    }

    private function reverse(User $user, Product $product, StockAdjustment $entry)
    {
        return $this->actingAs($user)->post(route('owner.inventory.adjustment.reverse', [$product->product_id, $entry->adjustment_id]));
    }

    public function test_reversing_a_stock_in_takes_those_units_out_again_and_keeps_both_entries(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $product = $this->product(100);
        $entry = $this->manualEntry($product, 20);

        $this->reverse($owner, $product, $entry)->assertSessionHas('success');

        $this->assertSame(100, $product->fresh()->quantity_on_hand);
        $this->assertDatabaseHas('stock_adjustment', ['adjustment_id' => $entry->adjustment_id]);
        $this->assertDatabaseHas('stock_adjustment', ['product_id' => $product->product_id, 'quantity_change' => -20, 'reason' => "Reversal of #{$entry->adjustment_id}: Typed by hand"]);
        $this->assertDatabaseHas('audit_log', ['action' => 'stock_adjustment']);
    }

    public function test_reversing_a_stock_out_puts_the_units_back(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $product = $this->product(100);
        $entry = $this->manualEntry($product, -15, 'Miscount');

        $this->reverse($owner, $product, $entry);

        $this->assertSame(100, $product->fresh()->quantity_on_hand);
    }

    public function test_an_entry_can_only_be_reversed_once(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $product = $this->product(100);
        $entry = $this->manualEntry($product, 20);

        $this->reverse($owner, $product, $entry);
        $stock = $product->fresh()->quantity_on_hand;

        $this->reverse($owner, $product, $entry)->assertSessionHas('error');

        $this->assertSame($stock, $product->fresh()->quantity_on_hand);
        $this->assertSame(1, StockAdjustment::where('reason', 'like', "Reversal of #{$entry->adjustment_id}:%")->count());
    }

    public function test_a_stock_in_cannot_be_reversed_when_those_units_are_no_longer_there(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $product = $this->product(100);
        $entry = $this->manualEntry($product, 50);
        Product::whereKey($product->product_id)->update(['quantity_on_hand' => 10]);

        $this->reverse($owner, $product, $entry)->assertSessionHas('error');

        $this->assertSame(10, $product->fresh()->quantity_on_hand);
    }

    public function test_entries_the_system_wrote_cannot_be_reversed(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $product = $this->product(100);

        foreach (['Restocked from return #5', 'Replacement issued for return #5', 'Returned to supplier for damaged report #3', 'Restocked from voided sale TXN-00001'] as $reason) {
            $entry = $this->manualEntry($product, 3, $reason);
            $before = $product->fresh()->quantity_on_hand;

            $this->reverse($owner, $product, $entry)->assertSessionHas('error');

            $this->assertSame($before, $product->fresh()->quantity_on_hand, $reason);
        }
    }

    public function test_an_entry_of_another_product_cannot_be_reversed_through_this_product(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $product = $this->product();
        $other = $this->product();
        $entry = $this->manualEntry($other, 5);

        $this->reverse($owner, $product, $entry)->assertNotFound();
    }

    public function test_a_cashier_cannot_reverse_an_entry(): void
    {
        $cashier = User::factory()->cashierAttendant()->create();
        $product = $this->product(100);
        $entry = $this->manualEntry($product, 20);

        $this->reverse($cashier, $product, $entry)->assertForbidden();

        $this->assertSame(120, $product->fresh()->quantity_on_hand);
    }

    public function test_the_history_offers_reverse_only_on_manual_entries_that_can_still_be_reversed(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $product = $this->product(100);
        $manual = $this->manualEntry($product, 20, 'Typed by hand');
        $this->manualEntry($product, 4, 'Restocked from return #9');

        $page = $this->actingAs($owner)->get(route('owner.inventory.history', $product->product_id))->assertOk();
        $this->assertSame(1, substr_count($page->getContent(), '>Reverse</button>'));

        $this->reverse($owner, $product, $manual);

        $this->actingAs($owner)->get(route('owner.inventory.history', $product->product_id))
            ->assertSee('Reversed')->assertSee('Reversal')
            ->assertDontSee('>Reverse</button>', false);
    }

    public function test_stock_in_and_stock_out_show_an_undo_button_that_works(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $product = $this->product(100);

        $this->actingAs($owner)->post(route('owner.inventory.stockin.store', $product->product_id), [
            'quantity' => 30, 'reason' => 'Delivery', 'date_received' => today()->toDateString(),
        ])->assertSessionHas('undo');

        $this->assertSame(130, $product->fresh()->quantity_on_hand);

        $undoUrl = route('owner.inventory.adjustment.reverse', [$product->product_id, StockAdjustment::where('quantity_change', 30)->value('adjustment_id')]);

        $this->actingAs($owner)->get(route('owner.inventory.show', $product->product_id))
            ->assertSee('Added 30 unit(s) to stock.')->assertSee('>Undo</button>', false)->assertSee($undoUrl, false);

        $this->actingAs($owner)->get(route('owner.inventory.show', $product->product_id))->assertDontSee('>Undo</button>', false);

        $this->actingAs($owner)->post($undoUrl)->assertSessionHas('success');
        $this->assertSame(100, $product->fresh()->quantity_on_hand);

        $this->actingAs($owner)->post(route('owner.inventory.stockout.store', $product->product_id), [
            'quantity' => 10, 'reason' => 'Miscount', 'date_adjusted' => today()->toDateString(),
        ])->assertSessionHas('undo');
    }
}
