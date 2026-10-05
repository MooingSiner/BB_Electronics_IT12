<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\ReturnRecord;
use App\Models\Sale;
use App\Models\StockAdjustment;
use App\Models\User;
use App\Support\PerPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListPaginationTest extends TestCase
{
    use RefreshDatabase;

    private function names(int $count): array
    {
        return array_map(fn (int $n) => sprintf('Zitem %02d', $n), range(1, $count));
    }

    public function test_the_owner_and_cashier_inventory_lists_show_15_products_per_page(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $cashier = User::factory()->cashierAttendant()->create();
        $category = Category::factory()->create();

        foreach ($this->names(20) as $name) {
            Product::factory()->create(['category_id' => $category->category_id, 'product_name' => $name]);
        }

        foreach ([[$owner, 'owner.inventory.index'], [$cashier, 'cashier.inventory.index']] as [$user, $route]) {
            $first = $this->actingAs($user)->get(route($route))->assertSee('Zitem 15')->assertDontSee('Zitem 16');
            $first->assertSee('page=2', false);
            $this->actingAs($user)->get(route($route, ['page' => 2]))->assertSee('Zitem 16')->assertSee('Zitem 20')->assertDontSee('Zitem 15');
        }
    }

    public function test_the_sales_lists_show_15_transactions_per_page(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $cashier = User::factory()->cashierAttendant()->create();
        Sale::factory()->count(20)->create();

        foreach ([[$owner, 'owner.sales.index'], [$cashier, 'cashier.sales.index']] as [$user, $route]) {
            $html = $this->actingAs($user)->get(route($route))->getContent();
            $this->assertCount(15, array_unique($this->saleCodes($html)), $route);
        }
    }

    private function saleCodes(string $html): array
    {
        preg_match_all('/TXN-\d{5}/', $html, $matches);

        return $matches[0];
    }

    public function test_the_supplier_order_users_and_stock_history_lists_are_paged_at_15(): void
    {
        $owner = User::factory()->ownerManager()->create();
        PurchaseOrder::factory()->count(20)->create();
        User::factory()->cashierAttendant()->count(19)->create();

        $orders = $this->actingAs($owner)->get(route('owner.suppliers.index'))->getContent();
        $this->assertSame(15, count(array_unique($this->orderLinks($orders))));
        $this->actingAs($owner)->get(route('owner.suppliers.index', ['page' => 2]))->assertOk();

        $this->actingAs($owner)->get(route('owner.users.index'))->assertSee('page=2', false);

        $product = Product::factory()->for(Category::factory())->create();
        StockAdjustment::factory()->count(20)->create(['product_id' => $product->product_id]);
        $this->actingAs($owner)->get(route('owner.inventory.history', $product->product_id))->assertSee('page=2', false);
    }

    private function orderLinks(string $html): array
    {
        preg_match_all('#/owner/suppliers/(\d+)"#', $html, $matches);

        return $matches[1];
    }

    public function test_the_returns_list_is_paged_at_15(): void
    {
        $owner = User::factory()->ownerManager()->create();
        ReturnRecord::factory()->count(20)->create();

        $this->actingAs($owner)->get(route('owner.returns.index'))->assertSee('page=2', false);
    }

    public function test_phones_get_10_rows_per_page_and_everything_else_gets_15(): void
    {
        $iphone = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148 Safari/604.1';
        $androidPhone = 'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 Chrome/120.0 Mobile Safari/537.36';
        $androidTablet = 'Mozilla/5.0 (Linux; Android 14; SM-X700) AppleWebKit/537.36 Chrome/120.0 Safari/537.36';
        $desktop = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0 Safari/537.36';

        $this->assertTrue(PerPage::isMobile($iphone));
        $this->assertTrue(PerPage::isMobile($androidPhone));
        $this->assertFalse(PerPage::isMobile($androidTablet));
        $this->assertFalse(PerPage::isMobile($desktop));

        $owner = User::factory()->ownerManager()->create();
        $category = Category::factory()->create();
        foreach ($this->names(20) as $name) {
            Product::factory()->create(['category_id' => $category->category_id, 'product_name' => $name]);
        }

        $this->actingAs($owner)->withHeader('User-Agent', $iphone)->get(route('owner.inventory.index'))
            ->assertSee('Zitem 10')->assertDontSee('Zitem 11');
        $this->actingAs($owner)->withHeader('User-Agent', $desktop)->get(route('owner.inventory.index'))
            ->assertSee('Zitem 15')->assertDontSee('Zitem 16');
    }
}
