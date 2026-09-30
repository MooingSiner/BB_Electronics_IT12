<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReturnProcessingTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_return_multiple_products_from_one_transaction_in_a_single_submission(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $sale = Sale::factory()->create();
        $productA = Product::factory()->for(Category::factory()->state(['category_name' => 'Return Test Category A']))->create(['quantity_on_hand' => 5]);
        $productB = Product::factory()->for(Category::factory()->state(['category_name' => 'Return Test Category B']))->create(['quantity_on_hand' => 5]);

        SaleItem::factory()->create(['sale_id' => $sale->sale_id, 'product_id' => $productA->product_id, 'quantity' => 3]);
        SaleItem::factory()->create(['sale_id' => $sale->sale_id, 'product_id' => $productB->product_id, 'quantity' => 2]);

        $response = $this->actingAs($owner)->post(route('owner.returns.store'), [
            'transaction_id' => $sale->sale_id,
            'items' => [
                ['product_id' => $productA->product_id, 'qty' => 1, 'reason' => 'Wrong item sent', 'resolution' => 'replacement', 'condition' => 'wrong_item'],
                ['product_id' => $productB->product_id, 'qty' => 2, 'reason' => 'Arrived damaged', 'resolution' => 'refund', 'condition' => 'damaged'],
            ],
        ]);

        $response->assertRedirect(route('owner.returns.index'));
        $this->assertDatabaseHas('return_record', ['sale_id' => $sale->sale_id, 'product_id' => $productA->product_id, 'quantity' => 1]);
        $this->assertDatabaseHas('return_record', ['sale_id' => $sale->sale_id, 'product_id' => $productB->product_id, 'quantity' => 2]);

        // The sale_item insert trigger already decremented stock by the sold quantity
        // (5 - 3 = 2, 5 - 2 = 3); wrong_item is then restocked, damaged is not.
        $this->assertSame(3, $productA->fresh()->quantity_on_hand);
        $this->assertSame(3, $productB->fresh()->quantity_on_hand);
    }

    public function test_it_rejects_returning_more_than_was_purchased_across_the_submitted_lines(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $sale = Sale::factory()->create();
        $product = Product::factory()->for(Category::factory())->create();

        SaleItem::factory()->create(['sale_id' => $sale->sale_id, 'product_id' => $product->product_id, 'quantity' => 3]);

        $response = $this->actingAs($owner)->post(route('owner.returns.store'), [
            'transaction_id' => $sale->sale_id,
            'items' => [
                ['product_id' => $product->product_id, 'qty' => 2, 'reason' => 'Changed mind', 'resolution' => 'refund', 'condition' => 'customer_changed_mind'],
                ['product_id' => $product->product_id, 'qty' => 2, 'reason' => 'Changed mind again', 'resolution' => 'refund', 'condition' => 'customer_changed_mind'],
            ],
        ]);

        $response->assertSessionHasErrors('items');
        $this->assertDatabaseMissing('return_record', ['sale_id' => $sale->sale_id, 'product_id' => $product->product_id]);
    }
}
