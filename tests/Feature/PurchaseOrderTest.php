<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_a_purchase_order_for_a_new_store(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $product = Product::factory()->for(Category::factory())->create(['quantity_on_hand' => 5]);

        $response = $this->actingAs($owner)->post(route('owner.purchase-orders.store'), [
            'store' => 'Ace Hardware',
            'order_date' => now()->format('Y-m-d'),
            'invoice_number' => 'INV-2026-00123',
            'items' => [
                ['product_id' => $product->product_id, 'qty' => 10, 'unit_cost' => 25.5],
            ],
        ]);

        $order = Store::where('store_name', 'Ace Hardware')->first()->purchaseOrders()->first();

        $response->assertRedirect(route('owner.purchase-orders.show', $order->order_id));
        $this->assertDatabaseHas('store', ['store_name' => 'Ace Hardware']);
        $this->assertDatabaseHas('order_item', [
            'order_id' => $order->order_id,
            'product_id' => $product->product_id,
            'quantity_ordered' => 10,
        ]);
        $this->assertSame('INV-2026-00123', $order->invoice_number);

        $this->actingAs($owner)
            ->get(route('owner.purchase-orders.index', ['search' => 'INV-2026-00123']))
            ->assertSee('PO-'.str_pad((string) $order->order_id, 4, '0', STR_PAD_LEFT));
    }

    public function test_receiving_a_purchase_order_increases_product_stock(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $store = Store::factory()->create();
        $product = Product::factory()->for(Category::factory())->create(['quantity_on_hand' => 5]);

        $this->actingAs($owner)->post(route('owner.purchase-orders.store'), [
            'store' => $store->store_name,
            'order_date' => now()->format('Y-m-d'),
            'items' => [
                ['product_id' => $product->product_id, 'qty' => 10, 'unit_cost' => 25.5],
            ],
        ]);

        $order = $store->purchaseOrders()->first();
        $orderItem = $order->items()->first();

        $this->actingAs($owner)->post(route('owner.purchase-orders.receive', $order->order_id), [
            'date_received' => now()->format('Y-m-d'),
            'items' => [
                $orderItem->order_item_id => ['qty_received' => 10],
            ],
        ])->assertRedirect(route('owner.purchase-orders.show', $order->order_id));

        $this->assertSame(15, $product->fresh()->quantity_on_hand);
        $this->assertSame('received', $order->fresh()->status->value);
    }

    public function test_cancelling_one_item_does_not_block_the_order_from_completing(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $store = Store::factory()->create();
        $available = Product::factory()->for(Category::factory())->create(['quantity_on_hand' => 0]);
        $unavailable = Product::factory()->for(Category::factory())->create(['quantity_on_hand' => 0]);

        $this->actingAs($owner)->post(route('owner.purchase-orders.store'), [
            'store' => $store->store_name,
            'order_date' => now()->format('Y-m-d'),
            'items' => [
                ['product_id' => $available->product_id, 'qty' => 10, 'unit_cost' => 25.5],
                ['product_id' => $unavailable->product_id, 'qty' => 5, 'unit_cost' => 12],
            ],
        ]);

        $order = $store->purchaseOrders()->first();
        $availableItem = $order->items()->where('product_id', $available->product_id)->first();
        $unavailableItem = $order->items()->where('product_id', $unavailable->product_id)->first();

        $this->actingAs($owner)->post(route('owner.purchase-orders.receive', $order->order_id), [
            'date_received' => now()->format('Y-m-d'),
            'items' => [
                $availableItem->order_item_id => ['qty_received' => 10],
                $unavailableItem->order_item_id => ['qty_received' => 0, 'cancelled' => '1'],
            ],
        ])->assertRedirect(route('owner.purchase-orders.show', $order->order_id));

        $this->assertSame(10, $available->fresh()->quantity_on_hand);
        $this->assertSame(0, $unavailable->fresh()->quantity_on_hand);
        $this->assertTrue($unavailableItem->fresh()->is_cancelled);
        $this->assertSame('received', $order->fresh()->status->value);
    }

    public function test_a_supplier_order_does_not_appear_in_purchase_orders_and_vice_versa(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $product = Product::factory()->for(Category::factory())->create();

        $this->actingAs($owner)->post(route('owner.suppliers.store'), [
            'supplier' => 'TechWorld Distributors',
            'order_date' => now()->format('Y-m-d'),
            'items' => [
                ['product_id' => $product->product_id, 'qty' => 5, 'unit_cost' => 10],
            ],
        ]);

        $this->actingAs($owner)->post(route('owner.purchase-orders.store'), [
            'store' => 'Ace Hardware',
            'order_date' => now()->format('Y-m-d'),
            'items' => [
                ['product_id' => $product->product_id, 'qty' => 5, 'unit_cost' => 10],
            ],
        ]);

        $supplierOrderId = PurchaseOrder::whereNotNull('supplier_id')->first()->order_id;
        $storeOrderId = PurchaseOrder::whereNotNull('store_id')->first()->order_id;

        $this->actingAs($owner)->get(route('owner.purchase-orders.show', $supplierOrderId))->assertNotFound();
        $this->actingAs($owner)->get(route('owner.suppliers.show', $storeOrderId))->assertNotFound();
    }

    public function test_owner_can_archive_and_restore_a_purchase_order(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $store = Store::factory()->create();
        $product = Product::factory()->for(Category::factory())->create();

        $this->actingAs($owner)->post(route('owner.purchase-orders.store'), [
            'store' => $store->store_name,
            'order_date' => now()->format('Y-m-d'),
            'items' => [
                ['product_id' => $product->product_id, 'qty' => 5, 'unit_cost' => 10],
            ],
        ]);

        $order = $store->purchaseOrders()->first();

        $this->actingAs($owner)->post(route('owner.purchase-orders.archive', $order->order_id))
            ->assertRedirect();

        $this->assertTrue($order->fresh()->is_archived);

        $activeList = $this->actingAs($owner)->get(route('owner.purchase-orders.index'));
        $activeList->assertDontSee('PO-'.str_pad((string) $order->order_id, 4, '0', STR_PAD_LEFT));

        $archivedList = $this->actingAs($owner)->get(route('owner.purchase-orders.index', ['archived' => 1]));
        $archivedList->assertSee('PO-'.str_pad((string) $order->order_id, 4, '0', STR_PAD_LEFT));

        $this->actingAs($owner)->post(route('owner.purchase-orders.restore', $order->order_id))
            ->assertRedirect();

        $this->assertFalse($order->fresh()->is_archived);
    }
}
