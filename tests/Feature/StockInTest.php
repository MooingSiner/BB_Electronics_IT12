<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockInTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_backdate_a_stock_in(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $product = Product::factory()->for(Category::factory())->create(['quantity_on_hand' => 10]);
        $receivedOn = now()->subDays(3)->format('Y-m-d');

        $response = $this->actingAs($owner)->post(route('owner.inventory.stockin.store', $product->product_id), [
            'quantity' => 5,
            'reason' => 'Found stock during count',
            'date_received' => $receivedOn,
        ]);

        $response->assertRedirect(route('owner.inventory.show', $product->product_id));
        $this->assertSame(15, $product->fresh()->quantity_on_hand);
        $this->assertDatabaseHas('stock_adjustment', [
            'product_id' => $product->product_id,
            'quantity_change' => 5,
            'adjustment_date' => $receivedOn.' 00:00:00',
        ]);
    }

    public function test_it_rejects_a_future_date_received(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $product = Product::factory()->for(Category::factory())->create();

        $response = $this->actingAs($owner)->post(route('owner.inventory.stockin.store', $product->product_id), [
            'quantity' => 5,
            'date_received' => now()->addDay()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('date_received');
    }

    public function test_owner_can_stock_in_multiple_products_at_once(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $productA = Product::factory()->for(Category::factory()->state(['category_name' => 'Bulk Test Category A']))->create(['quantity_on_hand' => 10]);
        $productB = Product::factory()->for(Category::factory()->state(['category_name' => 'Bulk Test Category B']))->create(['quantity_on_hand' => 20]);

        $response = $this->actingAs($owner)->post(route('owner.inventory.stockin.bulk.store'), [
            'date_received' => now()->format('Y-m-d'),
            'items' => [
                ['product_id' => $productA->product_id, 'quantity' => 5, 'reason' => 'Found stock'],
                ['product_id' => $productB->product_id, 'quantity' => 3, 'reason' => 'Count correction'],
            ],
        ]);

        $response->assertRedirect(route('owner.inventory.index'));
        $this->assertSame(15, $productA->fresh()->quantity_on_hand);
        $this->assertSame(23, $productB->fresh()->quantity_on_hand);
    }

    public function test_owner_can_stock_out_to_correct_a_miscount(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $product = Product::factory()->for(Category::factory())->create(['quantity_on_hand' => 10]);

        $response = $this->actingAs($owner)->post(route('owner.inventory.stockout.store', $product->product_id), [
            'quantity' => 4,
            'reason' => 'Accidentally added extra during stock-in',
            'date_adjusted' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('owner.inventory.show', $product->product_id));
        $this->assertSame(6, $product->fresh()->quantity_on_hand);
        $this->assertDatabaseHas('stock_adjustment', [
            'product_id' => $product->product_id,
            'quantity_change' => -4,
        ]);
    }

    public function test_stock_out_cannot_exceed_current_stock(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $product = Product::factory()->for(Category::factory())->create(['quantity_on_hand' => 5]);

        $response = $this->actingAs($owner)->post(route('owner.inventory.stockout.store', $product->product_id), [
            'quantity' => 6,
            'reason' => 'Too many',
            'date_adjusted' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('quantity');
        $this->assertSame(5, $product->fresh()->quantity_on_hand);
    }

    public function test_stock_out_requires_a_reason(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $product = Product::factory()->for(Category::factory())->create(['quantity_on_hand' => 10]);

        $response = $this->actingAs($owner)->post(route('owner.inventory.stockout.store', $product->product_id), [
            'quantity' => 2,
            'date_adjusted' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('reason');
        $this->assertSame(10, $product->fresh()->quantity_on_hand);
    }
}
