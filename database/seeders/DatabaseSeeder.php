<?php

namespace Database\Seeders;

use App\Enums\PurchaseOrderStatus;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\ReturnRecord;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockAdjustment;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warranty;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $owner = User::factory()->ownerManager()->create([
            'full_name' => 'Bea Bautista',
            'username' => 'owner',
        ]);

        $cashier = User::factory()->cashierAttendant()->create([
            'full_name' => 'Test Cashier',
            'username' => 'cashier',
        ]);

        $categories = Category::factory(6)->create();

        $products = Product::factory(20)->create([
            'category_id' => fn () => $categories->random()->category_id,
        ]);

        $supplier = Supplier::factory()->create([
            'supplier_name' => 'Davao Electro Parts Trading',
        ]);

        $order = PurchaseOrder::factory()->create([
            'supplier_id' => $supplier->supplier_id,
            'user_id' => $owner->user_id,
            'status' => PurchaseOrderStatus::Received,
        ]);

        // Receiving is a separate UPDATE (not the initial insert) so the
        // trg_orderitem_after_update trigger fires and stock is credited.
        foreach ($products->random(8) as $product) {
            $quantityOrdered = fake()->numberBetween(20, 60);

            $item = OrderItem::factory()->create([
                'order_id' => $order->order_id,
                'product_id' => $product->product_id,
                'quantity_ordered' => $quantityOrdered,
                'quantity_received' => 0,
                'unit_cost' => $product->cost_price,
            ]);

            $item->update(['quantity_received' => $quantityOrdered]);
        }

        // Refresh in-memory quantities now that supplier deliveries have posted.
        $products = $products->map(fn (Product $product) => $product->fresh());

        for ($i = 0; $i < 15; $i++) {
            $sale = Sale::factory()->create(['user_id' => $cashier->user_id]);
            $total = 0;

            foreach ($products->random(fake()->numberBetween(1, 3)) as $product) {
                if ($product->quantity_on_hand < 1) {
                    continue;
                }

                $quantity = min($product->quantity_on_hand, fake()->numberBetween(1, 4));
                $subtotal = $quantity * $product->unit_price;
                $total += $subtotal;

                SaleItem::factory()->create([
                    'sale_id' => $sale->sale_id,
                    'product_id' => $product->product_id,
                    'quantity' => $quantity,
                    'unit_price' => $product->unit_price,
                    'subtotal' => $subtotal,
                ]);

                $product->quantity_on_hand -= $quantity;
            }

            $sale->update([
                'total_amount' => $total,
                'amount_paid' => $total,
                'change_amount' => 0,
            ]);
        }

        $firstSaleItem = SaleItem::query()->first();

        if ($firstSaleItem) {
            ReturnRecord::factory()->create([
                'sale_id' => $firstSaleItem->sale_id,
                'product_id' => $firstSaleItem->product_id,
                'supplier_id' => null,
                'quantity' => 1,
            ]);

            Warranty::factory()->create([
                'sale_item_id' => $firstSaleItem->sale_item_id,
            ]);
        }

        StockAdjustment::factory()->create([
            'product_id' => $products->first()->product_id,
            'user_id' => $owner->user_id,
            'quantity_change' => -2,
            'reason' => 'Damaged in storage',
        ]);
    }
}
