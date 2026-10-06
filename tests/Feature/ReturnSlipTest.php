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

class ReturnSlipTest extends TestCase
{
    use RefreshDatabase;

    private function customerReturn(ReturnResolution $resolution, ReturnStatus $status = ReturnStatus::Open, ?Product $exchangeFor = null): ReturnRecord
    {
        $product = Product::factory()->for(Category::factory())->create(['product_name' => 'Slip Bulb', 'unit_price' => 200]);
        $sale = Sale::factory()->create(['subtotal' => 1000, 'discount_amount' => 100, 'total_amount' => 900]);
        SaleItem::factory()->create(['sale_id' => $sale->sale_id, 'product_id' => $product->product_id, 'quantity' => 5, 'unit_price' => 200]);

        return ReturnRecord::factory()->create([
            'sale_id' => $sale->sale_id,
            'product_id' => $product->product_id,
            'replacement_product_id' => $exchangeFor?->product_id,
            'quantity' => 2,
            'reason' => 'Stopped working',
            'condition' => ReturnCondition::Defective,
            'resolution' => $resolution,
            'status' => $status,
        ]);
    }

    public function test_the_owner_and_cashier_can_print_a_return_slip(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $cashier = User::factory()->cashierAttendant()->create();
        $return = $this->customerReturn(ReturnResolution::Refund);

        foreach ([[$owner, 'owner.returns.slip'], [$cashier, 'cashier.returns.slip']] as [$user, $route]) {
            $this->actingAs($user)->get(route($route, $return->return_id))
                ->assertOk()
                ->assertSee('Customer Return Slip')
                ->assertSee("Return Slip #{$return->return_id}", false)
                ->assertSee($return->sale->code())
                ->assertSee('Slip Bulb')
                ->assertSee('Stopped working')
                ->assertSee('Pending owner approval')
                ->assertSee('Refund (once approved)')
                ->assertSee('₱360.00')
                ->assertSee('Customer signature')
                ->assertSee('receipt-card', false)
                ->assertDontSee('User Management');
        }
    }

    public function test_a_resolved_return_slip_says_resolved(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $return = $this->customerReturn(ReturnResolution::Refund, ReturnStatus::Resolved);

        $this->actingAs($owner)->get(route('owner.returns.slip', $return->return_id))->assertSee('Resolved')->assertDontSee('Pending owner approval');
    }

    public function test_an_exchange_slip_shows_the_new_product_and_the_price_difference(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $wanted = Product::factory()->for(Category::factory())->create(['product_name' => 'Wanted Slip Bulb', 'unit_price' => 250]);
        $return = $this->customerReturn(ReturnResolution::Replacement, exchangeFor: $wanted);

        $this->actingAs($owner)->get(route('owner.returns.slip', $return->return_id))
            ->assertSee('Exchanged for')->assertSee('Wanted Slip Bulb')->assertSee('Customer pays ₱140.00 for the exchange.');
    }

    public function test_a_supplier_damage_report_has_no_customer_slip(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $damage = ReturnRecord::factory()->create(['sale_id' => null, 'condition' => ReturnCondition::Damaged]);

        $this->actingAs($owner)->get(route('owner.returns.slip', $damage->return_id))->assertNotFound();
    }

    public function test_the_return_pages_link_to_the_slip(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $cashier = User::factory()->cashierAttendant()->create();
        $return = $this->customerReturn(ReturnResolution::Refund);

        $this->actingAs($owner)->get(route('owner.returns.show', $return->return_id))->assertSee(route('owner.returns.slip', $return->return_id), false);
        $this->actingAs($cashier)->get(route('cashier.returns.show', $return->return_id))->assertSee(route('cashier.returns.slip', $return->return_id), false);
    }
}
