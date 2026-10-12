<?php

namespace Tests\Feature;

use App\Livewire\Cashier\Pos;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PosVatTest extends TestCase
{
    use RefreshDatabase;

    private function pos(float $price = 100, int $quantity = 1)
    {
        $cashier = User::factory()->cashierAttendant()->create();
        $product = Product::factory()->for(Category::factory())->create(['quantity_on_hand' => 20, 'unit_price' => $price]);

        $component = Livewire::actingAs($cashier)->test(Pos::class)->call('addToCart', $product->product_id);

        if ($quantity > 1) {
            $component->call('updateQuantity', (string) $product->product_id, $quantity);
        }

        return $component;
    }

    public function test_vat_is_added_to_the_total_and_saved_with_the_sale(): void
    {
        Setting::setVatRate(12);

        $this->pos()->set('amountReceived', 112)->call('completeSale')->assertSet('errorMessage', null);

        $sale = Sale::first();
        $this->assertSame('100.00', $sale->subtotal);
        $this->assertSame('12.00', $sale->vat_rate);
        $this->assertSame('12.00', $sale->vat_amount);
        $this->assertSame('112.00', $sale->total_amount);
    }

    public function test_vat_is_charged_on_the_amount_left_after_the_discount(): void
    {
        Setting::setVatRate(12);

        $this->pos()->call('setDiscountType', 'percent')->set('discountValue', 10)->set('amountReceived', 200)->call('completeSale');

        $sale = Sale::first();
        $this->assertSame('10.00', $sale->discount_amount);
        $this->assertSame('10.80', $sale->vat_amount);
        $this->assertSame('100.80', $sale->total_amount);
        $this->assertSame('99.20', $sale->change_amount);
    }

    public function test_the_cash_received_must_cover_the_vat_too(): void
    {
        Setting::setVatRate(12);

        $this->pos()->set('amountReceived', 100)->call('completeSale')
            ->assertSet('errorMessage', 'Amount received is less than the total due.')
            ->assertSet('completedSale', null);

        $this->assertSame(0, Sale::count());
    }

    public function test_a_vat_rate_of_zero_adds_nothing(): void
    {
        Setting::setVatRate(0);

        $this->pos()->set('amountReceived', 100)->call('completeSale');

        $sale = Sale::first();
        $this->assertSame('0.00', $sale->vat_amount);
        $this->assertSame('100.00', $sale->total_amount);
    }

    public function test_a_new_rate_applies_to_new_sales_and_old_sales_keep_theirs(): void
    {
        Setting::setVatRate(12);
        $this->pos()->set('amountReceived', 112)->call('completeSale');

        Setting::setVatRate(5);
        $this->pos()->set('amountReceived', 105)->call('completeSale');

        $this->assertSame(['12.00', '5.00'], Sale::orderBy('sale_id')->pluck('vat_rate')->all());
        $this->assertSame(['112.00', '105.00'], Sale::orderBy('sale_id')->pluck('total_amount')->all());
    }

    public function test_the_cart_shows_the_vat_line(): void
    {
        Setting::setVatRate(12);

        $this->pos()->assertSee('VAT (12%)')->assertSee('₱12.00');
    }

    public function test_a_discount_needs_no_reason_and_is_still_written_to_the_audit_log(): void
    {
        $this->pos()->call('setDiscountType', 'fixed')->set('discountValue', 20)->set('amountReceived', 100)->call('completeSale')
            ->assertSet('errorMessage', null);

        $this->assertSame('20.00', Sale::first()->discount_amount);
        $this->assertDatabaseHas('audit_log', ['action' => 'discount']);
        $this->assertStringNotContainsString('(', AuditLog::where('action', 'discount')->value('description'));
    }

    public function test_the_vat_shows_on_the_sale_details_and_the_receipt(): void
    {
        Setting::setVatRate(12);
        $this->pos()->set('amountReceived', 112)->call('completeSale');
        $sale = Sale::first();

        $this->actingAs(User::factory()->cashierAttendant()->create())->get(route('cashier.sales.show', $sale->sale_id))->assertSee('VAT (12%)');
        $this->actingAs(User::factory()->cashierAttendant()->create())->get(route('cashier.sales.receipt', $sale->sale_id))->assertSee('VAT (12%)');
        $this->actingAs(User::factory()->ownerManager()->create())->get(route('owner.sales.show', $sale->sale_id))->assertSee('VAT (12%)');
    }

    public function test_a_refund_includes_the_vat_the_customer_paid(): void
    {
        Setting::setVatRate(12);
        $this->pos(100, 2)->set('amountReceived', 224)->call('completeSale');
        $sale = Sale::first()->load('items');
        $returned = $sale->returnRecords()->create([
            'product_id' => $sale->items->first()->product_id,
            'quantity' => 1,
            'reason' => 'Defective',
            'condition' => 'defective',
            'resolution' => 'refund',
            'status' => 'resolved',
        ]);

        $this->assertSame(112.0, $returned->setRelation('sale', $sale)->refundAmount());
    }
}
