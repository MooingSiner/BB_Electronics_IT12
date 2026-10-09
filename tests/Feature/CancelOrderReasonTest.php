<?php

namespace Tests\Feature;

use App\Enums\PurchaseOrderStatus;
use App\Models\AuditLog;
use App\Models\OrderItem;
use App\Models\PurchaseOrder;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CancelOrderReasonTest extends TestCase
{
    use RefreshDatabase;

    private function supplierOrder(): PurchaseOrder
    {
        $order = PurchaseOrder::factory()->create(['status' => PurchaseOrderStatus::Pending]);
        OrderItem::factory()->create(['order_id' => $order->order_id, 'quantity_received' => 0]);

        return $order;
    }

    private function storeOrder(): PurchaseOrder
    {
        $order = PurchaseOrder::factory()->create(['status' => PurchaseOrderStatus::Pending, 'supplier_id' => null, 'store_id' => Store::factory()]);
        OrderItem::factory()->create(['order_id' => $order->order_id, 'quantity_received' => 0]);

        return $order;
    }

    public function test_an_order_cannot_be_cancelled_without_a_reason(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $supplierOrder = $this->supplierOrder();
        $storeOrder = $this->storeOrder();

        $this->actingAs($owner)->post(route('owner.suppliers.cancel', $supplierOrder->order_id))->assertSessionHasErrors('reason');
        $this->actingAs($owner)->post(route('owner.purchase-orders.cancel', $storeOrder->order_id), ['reason' => 'Not a real reason'])->assertSessionHasErrors('reason');

        $this->assertSame(PurchaseOrderStatus::Pending, $supplierOrder->fresh()->status);
        $this->assertSame(PurchaseOrderStatus::Pending, $storeOrder->fresh()->status);
    }

    public function test_the_reason_is_saved_shown_and_written_to_the_audit_log_for_both_kinds_of_order(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $supplierOrder = $this->supplierOrder();
        $storeOrder = $this->storeOrder();

        $this->actingAs($owner)->post(route('owner.suppliers.cancel', $supplierOrder->order_id), ['reason' => 'Price changed'])->assertSessionHas('success');
        $this->actingAs($owner)->post(route('owner.purchase-orders.cancel', $storeOrder->order_id), ['reason' => 'Duplicate order'])->assertSessionHas('success');

        $this->assertSame('Price changed', $supplierOrder->fresh()->cancel_reason);
        $this->assertSame('Duplicate order', $storeOrder->fresh()->cancel_reason);
        $this->assertStringContainsString('Price changed', AuditLog::where('action', 'order_cancelled')->orderBy('audit_id')->value('description'));

        $this->actingAs($owner)->get(route('owner.suppliers.show', $supplierOrder->order_id))->assertSee('Price changed');
        $this->actingAs($owner)->get(route('owner.purchase-orders.show', $storeOrder->order_id))->assertSee('Duplicate order');
    }

    public function test_other_needs_a_typed_reason(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $order = $this->supplierOrder();

        $this->actingAs($owner)->post(route('owner.suppliers.cancel', $order->order_id), ['reason' => 'Other'])->assertSessionHasErrors('note');
        $this->assertSame(PurchaseOrderStatus::Pending, $order->fresh()->status);

        $this->actingAs($owner)->post(route('owner.suppliers.cancel', $order->order_id), ['reason' => 'Other', 'note' => 'Shop is closing'])->assertSessionHas('success');
        $this->assertSame('Other: Shop is closing', $order->fresh()->cancel_reason);
    }

    public function test_the_order_pages_offer_the_reason_choices_and_the_new_order_forms_confirm_before_discarding(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $supplierOrder = $this->supplierOrder();
        $storeOrder = $this->storeOrder();

        $this->actingAs($owner)->get(route('owner.suppliers.show', $supplierOrder->order_id))
            ->assertSee('Ordered by mistake')->assertSee('Keep order')->assertSee('data-confirm="Cancel this order?', false);
        $this->actingAs($owner)->get(route('owner.purchase-orders.show', $storeOrder->order_id))
            ->assertSee('Supplier is out of stock')->assertSee('Keep order');

        foreach ([route('owner.suppliers.create'), route('owner.purchase-orders.create')] as $url) {
            $this->actingAs($owner)->get($url)->assertOk()
                ->assertSee('id="discardOrder"', false)
                ->assertSee('Cancel this new order? What you entered will not be saved.', false);
        }
    }
}
