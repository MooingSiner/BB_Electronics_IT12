<?php

namespace Tests\Feature;

use App\Enums\ReturnCondition;
use App\Enums\ReturnResolution;
use App\Enums\ReturnStatus;
use App\Enums\SaleStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\ReturnRecord;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReturnsOnSalesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A sale of 5 units at 200 each (subtotal 1000) with a 10% discount, so the customer paid 900.
     *
     * @return array{0: Sale, 1: Product}
     */
    private function sale(): array
    {
        $product = Product::factory()->for(Category::factory())->create(['quantity_on_hand' => 100]);
        $sale = Sale::factory()->create([
            'status' => SaleStatus::Completed,
            'sale_date' => now(),
            'subtotal' => 1000,
            'discount_amount' => 100,
            'total_amount' => 900,
        ]);
        SaleItem::factory()->create(['sale_id' => $sale->sale_id, 'product_id' => $product->product_id, 'quantity' => 5, 'unit_price' => 200]);

        return [$sale, $product];
    }

    private function giveBack(Sale $sale, Product $product, int $quantity, ReturnStatus $status, ReturnResolution $resolution = ReturnResolution::Refund): ReturnRecord
    {
        return ReturnRecord::factory()->create([
            'sale_id' => $sale->sale_id,
            'product_id' => $product->product_id,
            'quantity' => $quantity,
            'condition' => ReturnCondition::CustomerChangedMind,
            'resolution' => $resolution,
            'status' => $status,
        ]);
    }

    public function test_a_refund_is_the_units_share_of_what_the_customer_paid(): void
    {
        [$sale, $product] = $this->sale();
        $this->giveBack($sale, $product, 2, ReturnStatus::Resolved);

        $this->assertSame(360.0, $sale->fresh()->refundedAmount());
        $this->assertSame(540.0, $sale->fresh()->netTotal());
    }

    public function test_only_resolved_refunds_count_not_pending_ones_or_replacements(): void
    {
        [$sale, $product] = $this->sale();
        $this->giveBack($sale, $product, 1, ReturnStatus::Open);
        $this->giveBack($sale, $product, 1, ReturnStatus::Resolved, ReturnResolution::Replacement);

        $this->assertSame(0.0, $sale->fresh()->refundedAmount());
        $this->assertSame(900.0, $sale->fresh()->netTotal());
    }

    public function test_the_sale_label_follows_the_returns(): void
    {
        [$sale, $product] = $this->sale();
        $this->assertNull($sale->fresh()->returnStatusLabel());

        $pending = $this->giveBack($sale, $product, 1, ReturnStatus::Open);
        $this->assertSame('Return pending', $sale->fresh()->returnStatusLabel());

        $pending->update(['status' => ReturnStatus::Resolved]);
        $this->assertSame('Partly returned', $sale->fresh()->returnStatusLabel());

        $this->giveBack($sale, $product, 4, ReturnStatus::Resolved);
        $this->assertSame('Returned', $sale->fresh()->returnStatusLabel());
    }

    public function test_the_sale_pages_show_the_return_refund_and_net_total(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $cashier = User::factory()->cashierAttendant()->create();
        [$sale, $product] = $this->sale();
        $this->giveBack($sale, $product, 2, ReturnStatus::Resolved);

        $this->actingAs($owner)->get(route('owner.sales.index'))->assertSee('Partly returned');
        $this->actingAs($cashier)->get(route('cashier.sales.index'))->assertSee('Partly returned');

        $this->actingAs($owner)->get(route('owner.sales.show', $sale->sale_id))
            ->assertSee('Returns on this sale')->assertSee('Refunded')->assertSee('Net Total')->assertSee('₱540.00')->assertSee('₱360.00');
        $this->actingAs($cashier)->get(route('cashier.sales.show', $sale->sale_id))
            ->assertSee('Returns on this sale')->assertSee('Refunded')->assertSee('Net Total')->assertSee('₱540.00');
    }

    public function test_a_sale_without_returns_shows_no_return_section(): void
    {
        $owner = User::factory()->ownerManager()->create();
        [$sale] = $this->sale();

        $this->actingAs($owner)->get(route('owner.sales.show', $sale->sale_id))
            ->assertDontSee('Returns on this sale')->assertDontSee('Net Total');
    }

    public function test_revenue_on_the_dashboards_and_reports_is_net_of_refunds(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $cashier = User::factory()->cashierAttendant()->create();
        [$sale, $product] = $this->sale();
        $this->giveBack($sale, $product, 2, ReturnStatus::Resolved);

        $this->assertSame(540.0, Sale::netRevenue(Sale::where('status', SaleStatus::Completed)));

        $this->actingAs($owner)->get(route('owner.dashboard', ['period' => 'today']))->assertSee('540.00');
        $this->actingAs($cashier)->get(route('cashier.dashboard', ['period' => 'today']))->assertSee('540.00');
        $this->actingAs($owner)->get(route('owner.reports.index', ['type' => 'sales', 'date_from' => today()->toDateString(), 'date_to' => today()->toDateString()]))
            ->assertSee('540.00');
    }
}
