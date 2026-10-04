<?php

namespace Tests\Feature;

use App\Enums\WarrantyClaimStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Models\Warranty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarrantyClaimTest extends TestCase
{
    use RefreshDatabase;

    private function soldItem(int $warrantyDays, int $soldDaysAgo = 10): SaleItem
    {
        $product = Product::factory()->for(Category::factory())->create([
            'quantity_on_hand' => 100,
            'warranty_period_days' => $warrantyDays,
        ]);
        $sale = Sale::factory()->create(['sale_date' => now()->subDays($soldDaysAgo)]);

        return SaleItem::factory()->create(['sale_id' => $sale->sale_id, 'product_id' => $product->product_id]);
    }

    private function claimPayload(SaleItem $item, array $overrides = []): array
    {
        return array_merge([
            'sale_item_id' => $item->sale_item_id,
            'customer_name' => 'Juan Dela Cruz',
            'contact_number' => '09171234567',
            'issue' => 'Stopped working after two weeks.',
        ], $overrides);
    }

    public function test_cashier_can_file_a_warranty_claim_for_an_item_still_under_warranty(): void
    {
        $cashier = User::factory()->cashierAttendant()->create();
        $item = $this->soldItem(warrantyDays: 180, soldDaysAgo: 10);

        $response = $this->actingAs($cashier)->post(route('cashier.returns.claim.store'), $this->claimPayload($item));

        $warranty = Warranty::where('sale_item_id', $item->sale_item_id)->firstOrFail();
        $response->assertRedirect(route('cashier.returns.warranty', $warranty->warranty_id));
        $this->assertSame(WarrantyClaimStatus::Claimed, $warranty->claim_status);
        $this->assertSame('Stopped working after two weeks.', $warranty->issue);
        $this->assertSame('Juan Dela Cruz', $warranty->customer_name);
        $this->assertTrue($warranty->claim_date->isToday());
        $this->assertTrue($warranty->end_date->isSameDay($item->sale->sale_date->copy()->addDays(180)));
    }

    public function test_the_claim_screen_only_offers_items_that_have_a_valid_warranty(): void
    {
        $cashier = User::factory()->cashierAttendant()->create();
        $covered = $this->soldItem(warrantyDays: 180);
        $noWarranty = $this->soldItem(warrantyDays: 0);

        $this->actingAs($cashier)
            ->get(route('cashier.returns.claim', ['transaction_id' => $covered->sale_id]))
            ->assertOk()
            ->assertSee($covered->product->product_name);

        $this->actingAs($cashier)
            ->get(route('cashier.returns.claim', ['transaction_id' => $noWarranty->sale_id]))
            ->assertOk()
            ->assertSee('None of the items')
            ->assertDontSee('File Claim');
    }

    public function test_an_item_without_a_warranty_cannot_be_claimed(): void
    {
        $cashier = User::factory()->cashierAttendant()->create();
        $item = $this->soldItem(warrantyDays: 0);

        $this->actingAs($cashier)->post(route('cashier.returns.claim.store'), $this->claimPayload($item))
            ->assertSessionHasErrors('sale_item_id');

        $this->assertDatabaseCount('warranty', 0);
    }

    public function test_an_expired_warranty_cannot_be_claimed(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $item = $this->soldItem(warrantyDays: 90, soldDaysAgo: 120);

        $this->actingAs($owner)->post(route('owner.returns.claim.store'), $this->claimPayload($item))
            ->assertSessionHasErrors('sale_item_id');

        $this->assertDatabaseCount('warranty', 0);
    }

    public function test_the_same_item_cannot_be_claimed_twice(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $item = $this->soldItem(warrantyDays: 180);

        $this->actingAs($owner)->post(route('owner.returns.claim.store'), $this->claimPayload($item))
            ->assertSessionHasNoErrors();

        $this->actingAs($owner)->post(route('owner.returns.claim.store'), $this->claimPayload($item, ['issue' => 'Again']))
            ->assertSessionHasErrors('sale_item_id');

        $this->assertDatabaseCount('warranty', 1);
    }

    public function test_a_claim_needs_the_customer_and_the_problem(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $item = $this->soldItem(warrantyDays: 180);

        $this->actingAs($owner)
            ->post(route('owner.returns.claim.store'), $this->claimPayload($item, ['customer_name' => '', 'issue' => '']))
            ->assertSessionHasErrors(['customer_name', 'issue']);
    }

    public function test_owner_can_record_the_repair_outcome_and_notes_and_sees_the_real_issue(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $item = $this->soldItem(warrantyDays: 180);
        $this->actingAs($owner)->post(route('owner.returns.claim.store'), $this->claimPayload($item));
        $warranty = Warranty::firstOrFail();

        $this->actingAs($owner)->patch(route('owner.returns.warrantyUpdate', $warranty->warranty_id), [
            'status' => 'in_progress',
            'outcome' => 'repair',
            'resolution_notes' => 'Sent to the supplier for repair.',
        ])->assertRedirect();

        $fresh = $warranty->fresh();
        $this->assertSame('Sent to the supplier for repair.', $fresh->resolution_notes);

        $this->actingAs($owner)->get(route('owner.returns.warranty', $warranty->warranty_id))
            ->assertOk()
            ->assertSee('WAR-'.str_pad((string) $warranty->warranty_id, 5, '0', STR_PAD_LEFT))
            ->assertSee('Stopped working after two weeks.')
            ->assertSee('Sent to the supplier for repair.')
            ->assertSee('In Repair');
    }
}
