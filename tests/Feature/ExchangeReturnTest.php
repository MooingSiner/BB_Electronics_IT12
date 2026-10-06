<?php

namespace Tests\Feature;

use App\Enums\ReturnStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\ReturnRecord;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExchangeReturnTest extends TestCase
{
    use RefreshDatabase;

    private Sale $sale;

    private Product $wrong;

    private Product $wanted;

    protected function setUp(): void
    {
        parent::setUp();

        $category = Category::factory()->create();
        $this->wrong = Product::factory()->create(['category_id' => $category->category_id, 'product_name' => 'Wrong Bulb', 'unit_price' => 200]);
        $this->wanted = Product::factory()->create(['category_id' => $category->category_id, 'product_name' => 'Wanted Bulb', 'unit_price' => 250]);

        // 5 units at 200 with a 10% discount: the customer paid 180 per unit.
        $this->sale = Sale::factory()->create(['subtotal' => 1000, 'discount_amount' => 100, 'total_amount' => 900, 'sale_date' => now()]);
        SaleItem::factory()->create(['sale_id' => $this->sale->sale_id, 'product_id' => $this->wrong->product_id, 'quantity' => 5, 'unit_price' => 200]);

        Product::whereKey([$this->wrong->product_id, $this->wanted->product_id])->update(['quantity_on_hand' => 50]);
    }

    private function stock(Product $product): int
    {
        return $product->fresh()->quantity_on_hand;
    }

    private function cashierRecordsExchange(array $overrides = []): ReturnRecord
    {
        $cashier = User::factory()->cashierAttendant()->create();

        $this->actingAs($cashier)->post(route('cashier.returns.store'), array_merge([
            'sale_id' => $this->sale->sale_id,
            'product_id' => $this->wrong->product_id,
            'quantity' => 1,
            'reason' => 'Got the wrong bulb',
            'condition' => 'wrong_item',
            'resolution' => 'replacement',
            'replacement_product_id' => $this->wanted->product_id,
        ], $overrides))->assertSessionHasNoErrors();

        return ReturnRecord::latest('return_id')->firstOrFail();
    }

    public function test_the_cashier_records_an_exchange_that_waits_for_the_owner(): void
    {
        $return = $this->cashierRecordsExchange();

        $this->assertSame(ReturnStatus::Open, $return->status);
        $this->assertSame($this->wanted->product_id, $return->replacement_product_id);
        $this->assertSame(50, $this->stock($this->wrong));
        $this->assertSame(50, $this->stock($this->wanted));

        $cashier = User::factory()->cashierAttendant()->create();
        $this->actingAs($cashier)->get(route('cashier.returns.show', $return->return_id))
            ->assertSee('Exchange for')->assertSee('Wanted Bulb')->assertSee('Customer pays ₱70.00 for the exchange.')
            ->assertSee('will be taken out of stock for the exchange when the owner marks it resolved');
    }

    public function test_the_owner_approving_the_exchange_moves_both_products_and_fixes_the_price_difference(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $return = $this->cashierRecordsExchange(['quantity' => 2]);

        $this->actingAs($owner)->patch(route('owner.returns.resolve', $return->return_id))->assertSessionHas('success');

        $return->refresh();
        $this->assertSame(ReturnStatus::Resolved, $return->status);
        $this->assertSame(52, $this->stock($this->wrong), 'the wrong item is put back');
        $this->assertSame(48, $this->stock($this->wanted), 'the wanted product leaves stock');
        $this->assertSame('140.00', $return->price_difference);
        $this->assertDatabaseHas('stock_adjustment', ['product_id' => $this->wanted->product_id, 'quantity_change' => -2, 'reason' => "Replacement issued for return #{$return->return_id}"]);

        $this->actingAs($owner)->get(route('owner.returns.show', $return->return_id))
            ->assertSee('Wanted Bulb ×2')->assertSee('Customer pays ₱140.00')->assertSee('2 unit(s) of Wanted Bulb taken out of stock for the exchange');
    }

    public function test_a_cheaper_product_means_money_back_to_the_customer(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $cheap = Product::factory()->create(['category_id' => $this->wrong->category_id, 'product_name' => 'Cheap Bulb', 'unit_price' => 100, 'quantity_on_hand' => 50]);
        $return = $this->cashierRecordsExchange(['replacement_product_id' => $cheap->product_id]);

        $this->actingAs($owner)->patch(route('owner.returns.resolve', $return->return_id));

        $this->assertSame('-80.00', $return->fresh()->price_difference);
        $this->actingAs($owner)->get(route('owner.returns.show', $return->return_id))->assertSee('Give the customer ₱80.00 back');
    }

    public function test_the_exchange_is_blocked_when_the_wanted_product_is_out_of_stock(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $return = $this->cashierRecordsExchange(['quantity' => 3]);
        Product::whereKey($this->wanted->product_id)->update(['quantity_on_hand' => 2]);

        $this->actingAs($owner)->patch(route('owner.returns.resolve', $return->return_id))
            ->assertSessionHas('error');

        $this->assertSame(ReturnStatus::Open, $return->fresh()->status);
        $this->assertSame(50, $this->stock($this->wrong));
        $this->assertSame(2, $this->stock($this->wanted));
    }

    public function test_choosing_the_same_product_is_a_normal_replacement(): void
    {
        $return = $this->cashierRecordsExchange(['replacement_product_id' => $this->wrong->product_id]);

        $this->assertNull($return->replacement_product_id);
        $this->assertFalse($return->isExchange());
    }

    public function test_a_wanted_product_is_ignored_unless_the_resolution_is_replacement(): void
    {
        $return = $this->cashierRecordsExchange(['resolution' => 'refund']);

        $this->assertNull($return->replacement_product_id);
    }

    public function test_the_owner_can_process_an_exchange_straight_away_and_revenue_counts_the_extra_payment(): void
    {
        $owner = User::factory()->ownerManager()->create();

        $this->actingAs($owner)->post(route('owner.returns.store'), [
            'transaction_id' => $this->sale->sale_id,
            'items' => [[
                'product_id' => $this->wrong->product_id,
                'qty' => 1,
                'reason' => 'Wrong bulb',
                'resolution' => 'replacement',
                'condition' => 'wrong_item',
                'replacement_product_id' => $this->wanted->product_id,
            ]],
        ])->assertSessionHasNoErrors();

        $return = ReturnRecord::firstOrFail();
        $this->assertSame(ReturnStatus::Resolved, $return->status);
        $this->assertSame(51, $this->stock($this->wrong), 'the wrong item is put back');
        $this->assertSame(49, $this->stock($this->wanted));
        $this->assertSame('70.00', $return->price_difference);

        $this->assertSame(-70.0, $this->sale->fresh()->refundedAmount());
        $this->assertSame(970.0, $this->sale->fresh()->netTotal());
        $this->assertSame(970.0, Sale::netRevenue(Sale::query()));

        $this->actingAs($owner)->get(route('owner.sales.show', $this->sale->sale_id))
            ->assertSee('Exchanged for Wanted Bulb ×1')->assertSee('Paid extra for exchange')->assertSee('₱970.00');
    }

    public function test_an_owner_exchange_with_too_little_stock_changes_nothing(): void
    {
        $owner = User::factory()->ownerManager()->create();
        Product::whereKey($this->wanted->product_id)->update(['quantity_on_hand' => 0]);

        $this->actingAs($owner)->post(route('owner.returns.store'), [
            'transaction_id' => $this->sale->sale_id,
            'items' => [[
                'product_id' => $this->wrong->product_id, 'qty' => 1, 'reason' => 'Wrong', 'resolution' => 'replacement',
                'condition' => 'wrong_item', 'replacement_product_id' => $this->wanted->product_id,
            ]],
        ])->assertSessionHasErrors('items');

        $this->assertSame(0, ReturnRecord::count());
        $this->assertSame(50, $this->stock($this->wrong));
    }

    public function test_both_return_forms_offer_the_exchange_choice(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $cashier = User::factory()->cashierAttendant()->create();

        $this->actingAs($owner)->get(route('owner.returns.process', ['transaction_id' => $this->sale->sale_id]))
            ->assertOk()->assertSee('Exchange for a different product')->assertSee('Wanted Bulb')->assertDontSee('handleSubmit');
        $this->actingAs($cashier)->get(route('cashier.returns.process', ['transaction_id' => $this->sale->sale_id]))
            ->assertOk()->assertSee('Exchange for a different product')->assertSee('Wanted Bulb');
    }
}
