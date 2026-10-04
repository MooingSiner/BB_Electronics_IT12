<?php

namespace Tests\Feature;

use App\Enums\PurchaseOrderStatus;
use App\Enums\SaleStatus;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\ReturnRecord;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoidSaleAndCancelOrderTest extends TestCase
{
    use RefreshDatabase;

    private function saleWithItem(int $quantity = 3): array
    {
        $product = Product::factory()->for(Category::factory())->create(['quantity_on_hand' => 100]);
        $sale = Sale::factory()->create(['status' => SaleStatus::Completed]);
        SaleItem::factory()->create(['sale_id' => $sale->sale_id, 'product_id' => $product->product_id, 'quantity' => $quantity]);

        return [$sale, $product];
    }

    public function test_owner_voids_a_sale_and_stock_comes_back(): void
    {
        $owner = User::factory()->ownerManager()->create();
        [$sale, $product] = $this->saleWithItem(3);
        $before = $product->fresh()->quantity_on_hand;

        $this->actingAs($owner)->post(route('owner.sales.void', $sale->sale_id), ['reason' => 'Wrong item'])
            ->assertSessionHas('success');

        $sale->refresh();
        $this->assertSame(SaleStatus::Voided, $sale->status);
        $this->assertSame('Wrong item', $sale->void_reason);
        $this->assertNotNull($sale->voided_at);
        $this->assertSame($before + 3, $product->fresh()->quantity_on_hand);
        $this->assertDatabaseHas('audit_log', ['action' => 'void']);
    }

    public function test_a_void_needs_a_reason_and_cannot_happen_twice(): void
    {
        $owner = User::factory()->ownerManager()->create();
        [$sale, $product] = $this->saleWithItem(3);

        $this->actingAs($owner)->post(route('owner.sales.void', $sale->sale_id), ['reason' => ''])
            ->assertSessionHasErrors('reason');
        $this->assertSame(SaleStatus::Completed, $sale->fresh()->status);

        $this->actingAs($owner)->post(route('owner.sales.void', $sale->sale_id), ['reason' => 'x']);
        $after = $product->fresh()->quantity_on_hand;
        $this->actingAs($owner)->post(route('owner.sales.void', $sale->sale_id), ['reason' => 'x'])
            ->assertSessionHas('error');

        $this->assertSame($after, $product->fresh()->quantity_on_hand);
    }

    public function test_a_sale_with_a_return_cannot_be_voided(): void
    {
        $owner = User::factory()->ownerManager()->create();
        [$sale, $product] = $this->saleWithItem();
        ReturnRecord::factory()->create(['sale_id' => $sale->sale_id, 'product_id' => $product->product_id]);

        $this->actingAs($owner)->post(route('owner.sales.void', $sale->sale_id), ['reason' => 'x'])
            ->assertSessionHas('error');

        $this->assertSame(SaleStatus::Completed, $sale->fresh()->status);
    }

    public function test_a_cashier_cannot_void_a_sale(): void
    {
        $cashier = User::factory()->cashierAttendant()->create();
        [$sale] = $this->saleWithItem();

        $this->actingAs($cashier)->post(route('owner.sales.void', $sale->sale_id), ['reason' => 'x']);

        $this->assertSame(SaleStatus::Completed, $sale->fresh()->status);
    }

    public function test_owner_cancels_an_untouched_supplier_order_and_a_purchase_order(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $supplierOrder = PurchaseOrder::factory()->create(['status' => PurchaseOrderStatus::Pending]);
        OrderItem::factory()->create(['order_id' => $supplierOrder->order_id, 'quantity_received' => 0]);

        $this->actingAs($owner)->post(route('owner.suppliers.cancel', $supplierOrder->order_id))
            ->assertSessionHas('success');

        $this->assertSame(PurchaseOrderStatus::Cancelled, $supplierOrder->fresh()->status);
        $this->assertTrue($supplierOrder->items()->first()->is_cancelled);
        $this->assertDatabaseHas('audit_log', ['action' => 'order_cancelled']);
    }

    public function test_an_order_with_received_items_cannot_be_cancelled(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $order = PurchaseOrder::factory()->create(['status' => PurchaseOrderStatus::Pending]);
        OrderItem::factory()->create(['order_id' => $order->order_id, 'quantity_ordered' => 5, 'quantity_received' => 2]);

        $this->actingAs($owner)->post(route('owner.suppliers.cancel', $order->order_id))
            ->assertSessionHas('error');

        $this->assertSame(PurchaseOrderStatus::Pending, $order->fresh()->status);
    }

    public function test_the_sales_lists_show_the_unit_price_of_each_product(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $cashier = User::factory()->cashierAttendant()->create();
        $product = Product::factory()->for(Category::factory())->create(['quantity_on_hand' => 100]);
        $sale = Sale::factory()->create();
        SaleItem::factory()->create(['sale_id' => $sale->sale_id, 'product_id' => $product->product_id, 'quantity' => 2, 'unit_price' => 95]);

        $this->actingAs($owner)->get(route('owner.sales.index'))->assertSee('Unit Price')->assertSee('₱95.00');
        $this->actingAs($cashier)->get(route('cashier.sales.index'))->assertSee('Unit Price')->assertSee('₱95.00');
    }

    public function test_the_owner_sales_list_return_button_opens_the_return_form(): void
    {
        $owner = User::factory()->ownerManager()->create();
        [$sale] = $this->saleWithItem();

        $this->actingAs($owner)->get(route('owner.sales.index'))
            ->assertSee(route('owner.returns.process', ['transaction_id' => $sale->sale_id]), false);
        $this->actingAs($owner)->get(route('owner.returns.process', ['transaction_id' => $sale->sale_id]))->assertOk();
    }
}
