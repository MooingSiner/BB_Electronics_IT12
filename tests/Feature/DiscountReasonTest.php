<?php

namespace Tests\Feature;

use App\Livewire\Cashier\Pos;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DiscountReasonTest extends TestCase
{
    use RefreshDatabase;

    private function sellingWithDiscount(string $type = 'percent', float $value = 10)
    {
        $cashier = User::factory()->cashierAttendant()->create();
        $product = Product::factory()->for(Category::factory())->create(['quantity_on_hand' => 10, 'unit_price' => 100]);

        return Livewire::actingAs($cashier)->test(Pos::class)
            ->call('addToCart', $product->product_id)
            ->call('setDiscountType', $type)
            ->set('discountValue', $value)
            ->set('amountReceived', 500);
    }

    public function test_a_discount_cannot_be_given_without_a_reason(): void
    {
        $this->sellingWithDiscount()->call('completeSale')
            ->assertSet('errorMessage', 'Choose a reason for the discount.')
            ->assertSet('completedSale', null);

        $this->assertSame(0, Sale::count());
    }

    public function test_the_chosen_reason_is_saved_with_the_sale_and_written_to_the_audit_log(): void
    {
        $this->sellingWithDiscount()->set('discountReason', 'Senior citizen')->call('completeSale')
            ->assertSet('errorMessage', null);

        $sale = Sale::first();
        $this->assertSame('Senior citizen', $sale->discount_reason);
        $this->assertDatabaseHas('audit_log', ['action' => 'discount']);
        $this->assertStringContainsString('Senior citizen', AuditLog::where('action', 'discount')->value('description'));
    }

    public function test_other_needs_a_typed_reason_and_it_is_saved_after_the_word_other(): void
    {
        $this->sellingWithDiscount('fixed', 20)->set('discountReason', 'Other')->call('completeSale')
            ->assertSet('errorMessage', 'Type the reason for the discount.');

        $this->assertSame(0, Sale::count());

        $this->sellingWithDiscount('fixed', 20)->set('discountReason', 'Other')->set('discountNote', 'Friend of the owner')->call('completeSale');

        $this->assertSame('Other: Friend of the owner', Sale::first()->discount_reason);
    }

    public function test_a_sale_without_a_discount_needs_no_reason(): void
    {
        $cashier = User::factory()->cashierAttendant()->create();
        $product = Product::factory()->for(Category::factory())->create(['quantity_on_hand' => 10, 'unit_price' => 100]);

        Livewire::actingAs($cashier)->test(Pos::class)
            ->call('addToCart', $product->product_id)
            ->set('amountReceived', 100)
            ->call('completeSale')
            ->assertSet('errorMessage', null);

        $this->assertNull(Sale::first()->discount_reason);
        $this->assertDatabaseMissing('audit_log', ['action' => 'discount']);
    }

    public function test_switching_the_discount_off_forgets_the_reason(): void
    {
        $this->sellingWithDiscount()->set('discountReason', 'PWD')->call('setDiscountType', 'none')
            ->assertSet('discountReason', '');
    }

    public function test_the_reason_shows_on_the_sale_details_and_the_receipt(): void
    {
        $this->sellingWithDiscount()->set('discountReason', 'Bulk purchase')->call('completeSale');
        $sale = Sale::first();

        $this->actingAs(User::factory()->cashierAttendant()->create())->get(route('cashier.sales.show', $sale->sale_id))->assertSee('Bulk purchase');
        $this->actingAs(User::factory()->cashierAttendant()->create())->get(route('cashier.sales.receipt', $sale->sale_id))->assertSee('Bulk purchase');
        $this->actingAs(User::factory()->ownerManager()->create())->get(route('owner.sales.show', $sale->sale_id))->assertSee('Bulk purchase');
    }
}
