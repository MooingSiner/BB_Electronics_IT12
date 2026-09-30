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
}
