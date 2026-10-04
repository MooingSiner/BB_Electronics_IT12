<?php

namespace Tests\Feature;

use App\Enums\ReturnCondition;
use App\Enums\ReturnResolution;
use App\Enums\ReturnStatus;
use App\Enums\WarrantyClaimStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\ReturnRecord;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Models\Warranty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CancelReturnAndClaimTest extends TestCase
{
    use RefreshDatabase;

    private function customerReturn(ReturnStatus $status = ReturnStatus::Open): ReturnRecord
    {
        $product = Product::factory()->for(Category::factory())->create(['quantity_on_hand' => 100]);
        $sale = Sale::factory()->create();
        SaleItem::factory()->create(['sale_id' => $sale->sale_id, 'product_id' => $product->product_id, 'quantity' => 5]);

        return ReturnRecord::factory()->create([
            'sale_id' => $sale->sale_id,
            'product_id' => $product->product_id,
            'quantity' => 2,
            'condition' => ReturnCondition::CustomerChangedMind,
            'resolution' => ReturnResolution::Refund,
            'status' => $status,
        ]);
    }

    private function claim(WarrantyClaimStatus $status): Warranty
    {
        return Warranty::factory()->create([
            'claim_status' => $status,
            'claim_date' => $status === WarrantyClaimStatus::None ? null : today(),
            'issue' => $status === WarrantyClaimStatus::None ? null : 'Stopped working.',
        ]);
    }

    public function test_cashier_can_cancel_a_pending_return_without_touching_stock(): void
    {
        $cashier = User::factory()->cashierAttendant()->create();
        $return = $this->customerReturn();
        $stock = $return->product->fresh()->quantity_on_hand;

        $this->actingAs($cashier)->delete(route('cashier.returns.cancel', $return->return_id))
            ->assertRedirect(route('cashier.returns.index'));

        $this->assertDatabaseMissing('return_record', ['return_id' => $return->return_id]);
        $this->assertSame($stock, $return->product->fresh()->quantity_on_hand);
        $this->assertDatabaseHas('audit_log', ['action' => 'refund']);
    }

    public function test_owner_can_cancel_a_pending_return(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $return = $this->customerReturn();

        $this->actingAs($owner)->delete(route('owner.returns.cancel', $return->return_id))
            ->assertRedirect(route('owner.returns.index'));

        $this->assertDatabaseMissing('return_record', ['return_id' => $return->return_id]);
    }

    public function test_a_resolved_return_cannot_be_cancelled(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $cashier = User::factory()->cashierAttendant()->create();
        $return = $this->customerReturn(ReturnStatus::Resolved);

        $this->actingAs($owner)->delete(route('owner.returns.cancel', $return->return_id))->assertSessionHas('error');
        $this->actingAs($cashier)->delete(route('cashier.returns.cancel', $return->return_id))->assertSessionHas('error');

        $this->assertDatabaseHas('return_record', ['return_id' => $return->return_id]);
    }

    public function test_a_supplier_damage_report_cannot_be_cancelled_through_the_customer_return_route(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $damage = ReturnRecord::factory()->create([
            'sale_id' => null,
            'condition' => ReturnCondition::Damaged,
            'status' => ReturnStatus::Open,
        ]);

        $this->actingAs($owner)->delete(route('owner.returns.cancel', $damage->return_id))->assertSessionHas('error');

        $this->assertDatabaseHas('return_record', ['return_id' => $damage->return_id]);
    }

    public function test_the_cancel_button_only_shows_on_a_pending_return(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $cashier = User::factory()->cashierAttendant()->create();
        $pending = $this->customerReturn();
        $resolved = $this->customerReturn(ReturnStatus::Resolved);

        $this->actingAs($owner)->get(route('owner.returns.show', $pending->return_id))->assertSee('Cancel Return');
        $this->actingAs($cashier)->get(route('cashier.returns.show', $pending->return_id))->assertSee('Cancel Return');
        $this->actingAs($owner)->get(route('owner.returns.show', $resolved->return_id))->assertDontSee('Cancel Return');
        $this->actingAs($cashier)->get(route('cashier.returns.show', $resolved->return_id))->assertDontSee('Cancel Return');
    }

    public function test_a_claim_under_review_can_be_cancelled_by_the_cashier_or_the_owner(): void
    {
        $cashier = User::factory()->cashierAttendant()->create();
        $owner = User::factory()->ownerManager()->create();
        $first = $this->claim(WarrantyClaimStatus::Claimed);
        $second = $this->claim(WarrantyClaimStatus::Claimed);

        $this->actingAs($cashier)->patch(route('cashier.returns.warranty.cancel', $first->warranty_id))->assertSessionHas('success');
        $this->actingAs($owner)->patch(route('owner.returns.warranty.cancel', $second->warranty_id))->assertSessionHas('success');

        foreach ([$first, $second] as $warranty) {
            $fresh = $warranty->fresh();
            $this->assertSame(WarrantyClaimStatus::None, $fresh->claim_status);
            $this->assertNull($fresh->issue);
            $this->assertNull($fresh->claim_date);
        }
    }

    public function test_a_claim_that_is_already_in_repair_cannot_be_cancelled(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $warranty = $this->claim(WarrantyClaimStatus::InProgress);

        $this->actingAs($owner)->patch(route('owner.returns.warranty.cancel', $warranty->warranty_id))->assertSessionHas('error');

        $this->assertSame(WarrantyClaimStatus::InProgress, $warranty->fresh()->claim_status);
    }

    public function test_the_cancel_claim_button_only_shows_while_under_review(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $cashier = User::factory()->cashierAttendant()->create();
        $review = $this->claim(WarrantyClaimStatus::Claimed);
        $repair = $this->claim(WarrantyClaimStatus::InProgress);

        $this->actingAs($owner)->get(route('owner.returns.warranty', $review->warranty_id))->assertSee('Cancel Claim');
        $this->actingAs($cashier)->get(route('cashier.returns.warranty', $review->warranty_id))->assertSee('Cancel Claim');
        $this->actingAs($owner)->get(route('owner.returns.warranty', $repair->warranty_id))->assertDontSee('Cancel Claim');
        $this->actingAs($cashier)->get(route('cashier.returns.warranty', $repair->warranty_id))->assertDontSee('Cancel Claim');
    }
}
