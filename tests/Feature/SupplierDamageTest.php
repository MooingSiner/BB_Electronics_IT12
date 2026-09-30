<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ReturnRecord;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierDamageTest extends TestCase
{
    use RefreshDatabase;

    public function test_reporting_damage_is_capped_to_the_quantity_received_for_that_order(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->for(Category::factory())->create();

        $this->actingAs($owner)->post(route('owner.suppliers.store'), [
            'supplier' => $supplier->supplier_name,
            'order_date' => now()->format('Y-m-d'),
            'items' => [
                ['product_id' => $product->product_id, 'qty' => 10, 'unit_cost' => 10],
            ],
        ]);

        $order = $supplier->purchaseOrders()->first();
        $orderItem = $order->items()->first();

        $this->actingAs($owner)->post(route('owner.suppliers.receive', $order->order_id), [
            'date_received' => now()->format('Y-m-d'),
            'items' => [
                $orderItem->order_item_id => ['qty_received' => 10],
            ],
        ]);

        // Only 10 were received, so reporting 20 damaged must fail.
        $this->actingAs($owner)->post(route('owner.suppliers.damage', $order->order_id), [
            'items' => [
                ['product_id' => $product->product_id, 'quantity' => 20, 'reason' => 'Crushed in transit'],
            ],
        ])->assertSessionHasErrors('items');

        $this->assertDatabaseMissing('return_record', ['product_id' => $product->product_id]);

        $this->actingAs($owner)->post(route('owner.suppliers.damage', $order->order_id), [
            'items' => [
                ['product_id' => $product->product_id, 'quantity' => 4, 'reason' => 'Crushed in transit'],
            ],
        ])->assertSessionDoesntHaveErrors();

        $this->assertDatabaseHas('return_record', [
            'order_id' => $order->order_id,
            'product_id' => $product->product_id,
            'quantity' => 4,
        ]);
    }

    public function test_damaged_and_accepted_counts_show_on_the_order_page(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->for(Category::factory())->create();

        $this->actingAs($owner)->post(route('owner.suppliers.store'), [
            'supplier' => $supplier->supplier_name,
            'order_date' => now()->format('Y-m-d'),
            'items' => [
                ['product_id' => $product->product_id, 'qty' => 10, 'unit_cost' => 10],
            ],
        ]);

        $order = $supplier->purchaseOrders()->first();
        $orderItem = $order->items()->first();

        $this->actingAs($owner)->post(route('owner.suppliers.receive', $order->order_id), [
            'date_received' => now()->format('Y-m-d'),
            'items' => [
                $orderItem->order_item_id => ['qty_received' => 10],
            ],
        ]);

        $this->actingAs($owner)->post(route('owner.suppliers.damage', $order->order_id), [
            'items' => [
                ['product_id' => $product->product_id, 'quantity' => 3, 'reason' => 'Crushed in transit'],
            ],
        ]);

        $response = $this->actingAs($owner)->get(route('owner.suppliers.show', $order->order_id));

        $response->assertOk();
        $response->assertViewHas('order', function ($viewOrder) {
            $item = $viewOrder->items->first();

            return $item->qty_damaged === 3 && $item->qty_accepted === 7;
        });
    }

    public function test_mark_returned_only_resolves_the_selected_reports(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $supplier = Supplier::factory()->create();
        $productA = Product::factory()->for(Category::factory()->state(['category_name' => 'Return Select Category A']))->create();
        $productB = Product::factory()->for(Category::factory()->state(['category_name' => 'Return Select Category B']))->create();

        $this->actingAs($owner)->post(route('owner.suppliers.store'), [
            'supplier' => $supplier->supplier_name,
            'order_date' => now()->format('Y-m-d'),
            'items' => [
                ['product_id' => $productA->product_id, 'qty' => 10, 'unit_cost' => 10],
                ['product_id' => $productB->product_id, 'qty' => 10, 'unit_cost' => 10],
            ],
        ]);

        $order = $supplier->purchaseOrders()->first();

        $this->actingAs($owner)->post(route('owner.suppliers.receive', $order->order_id), [
            'date_received' => now()->format('Y-m-d'),
            'items' => $order->items->mapWithKeys(fn ($item) => [$item->order_item_id => ['qty_received' => 10]])->all(),
        ]);

        $this->actingAs($owner)->post(route('owner.suppliers.damage', $order->order_id), [
            'items' => [
                ['product_id' => $productA->product_id, 'quantity' => 2, 'reason' => 'Crushed in transit'],
                ['product_id' => $productB->product_id, 'quantity' => 3, 'reason' => 'Water damage'],
            ],
        ]);

        $reportA = ReturnRecord::where('product_id', $productA->product_id)->first();
        $reportB = ReturnRecord::where('product_id', $productB->product_id)->first();

        $this->actingAs($owner)->patch(route('owner.suppliers.return', $order->order_id), [
            'return_ids' => [$reportA->return_id],
        ])->assertSessionHasNoErrors();

        $this->assertSame('resolved', $reportA->fresh()->status->value);
        $this->assertSame('open', $reportB->fresh()->status->value);
    }

    public function test_owner_can_cancel_an_open_damage_report(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->for(Category::factory())->create();

        $this->actingAs($owner)->post(route('owner.suppliers.store'), [
            'supplier' => $supplier->supplier_name,
            'order_date' => now()->format('Y-m-d'),
            'items' => [
                ['product_id' => $product->product_id, 'qty' => 10, 'unit_cost' => 10],
            ],
        ]);

        $order = $supplier->purchaseOrders()->first();
        $orderItem = $order->items()->first();

        $this->actingAs($owner)->post(route('owner.suppliers.receive', $order->order_id), [
            'date_received' => now()->format('Y-m-d'),
            'items' => [
                $orderItem->order_item_id => ['qty_received' => 10],
            ],
        ]);

        $this->actingAs($owner)->post(route('owner.suppliers.damage', $order->order_id), [
            'items' => [
                ['product_id' => $product->product_id, 'quantity' => 3, 'reason' => 'Reported by mistake'],
            ],
        ]);

        $report = ReturnRecord::where('product_id', $product->product_id)->first();

        $this->actingAs($owner)
            ->delete(route('owner.suppliers.damage.cancel', [$order->order_id, $report->return_id]))
            ->assertRedirect();

        $this->assertDatabaseMissing('return_record', ['return_id' => $report->return_id]);
    }

    public function test_receive_delivery_is_hidden_once_the_order_is_fully_received(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->for(Category::factory())->create();

        $this->actingAs($owner)->post(route('owner.suppliers.store'), [
            'supplier' => $supplier->supplier_name,
            'order_date' => now()->format('Y-m-d'),
            'items' => [
                ['product_id' => $product->product_id, 'qty' => 10, 'unit_cost' => 10],
            ],
        ]);

        $order = $supplier->purchaseOrders()->first();
        $orderItem = $order->items()->first();

        $this->actingAs($owner)->post(route('owner.suppliers.receive', $order->order_id), [
            'date_received' => now()->format('Y-m-d'),
            'items' => [
                $orderItem->order_item_id => ['qty_received' => 10],
            ],
        ]);

        $response = $this->actingAs($owner)->get(route('owner.suppliers.show', $order->order_id));

        $response->assertOk();
        $response->assertDontSee('id="openReceiveModal"', false);
    }

    public function test_owner_can_archive_and_restore_a_supplier_order(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->for(Category::factory())->create();

        $this->actingAs($owner)->post(route('owner.suppliers.store'), [
            'supplier' => $supplier->supplier_name,
            'order_date' => now()->format('Y-m-d'),
            'items' => [
                ['product_id' => $product->product_id, 'qty' => 5, 'unit_cost' => 10],
            ],
        ]);

        $order = $supplier->purchaseOrders()->first();

        $this->actingAs($owner)->post(route('owner.suppliers.archive', $order->order_id))
            ->assertRedirect();

        $this->assertTrue($order->fresh()->is_archived);

        $activeList = $this->actingAs($owner)->get(route('owner.suppliers.index'));
        $activeList->assertDontSee($supplier->supplier_name);

        $archivedList = $this->actingAs($owner)->get(route('owner.suppliers.index', ['archived' => 1]));
        $archivedList->assertSee($supplier->supplier_name);

        $this->actingAs($owner)->post(route('owner.suppliers.restore', $order->order_id))
            ->assertRedirect();

        $this->assertFalse($order->fresh()->is_archived);
    }

    public function test_owner_can_report_damage_on_multiple_products_in_one_submission(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $supplier = Supplier::factory()->create();
        $productA = Product::factory()->for(Category::factory()->state(['category_name' => 'Damage Multi Category A']))->create();
        $productB = Product::factory()->for(Category::factory()->state(['category_name' => 'Damage Multi Category B']))->create();

        $this->actingAs($owner)->post(route('owner.suppliers.store'), [
            'supplier' => $supplier->supplier_name,
            'order_date' => now()->format('Y-m-d'),
            'items' => [
                ['product_id' => $productA->product_id, 'qty' => 10, 'unit_cost' => 10],
                ['product_id' => $productB->product_id, 'qty' => 10, 'unit_cost' => 10],
            ],
        ]);

        $order = $supplier->purchaseOrders()->first();

        $this->actingAs($owner)->post(route('owner.suppliers.receive', $order->order_id), [
            'date_received' => now()->format('Y-m-d'),
            'items' => $order->items->mapWithKeys(fn ($item) => [$item->order_item_id => ['qty_received' => 10]])->all(),
        ]);

        $this->actingAs($owner)->post(route('owner.suppliers.damage', $order->order_id), [
            'items' => [
                ['product_id' => $productA->product_id, 'quantity' => 2, 'reason' => 'Crushed in transit'],
                ['product_id' => $productB->product_id, 'quantity' => 5, 'reason' => 'Water damage'],
            ],
        ])->assertSessionDoesntHaveErrors()->assertRedirect();

        $this->assertDatabaseHas('return_record', [
            'order_id' => $order->order_id,
            'product_id' => $productA->product_id,
            'quantity' => 2,
        ]);
        $this->assertDatabaseHas('return_record', [
            'order_id' => $order->order_id,
            'product_id' => $productB->product_id,
            'quantity' => 5,
        ]);
    }
}
