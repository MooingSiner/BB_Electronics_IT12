<?php

namespace Database\Seeders;

use App\Enums\StockMovementReason;
use App\Enums\StockMovementType;
use App\Enums\SupplierOrderStatus;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\SalesItem;
use App\Models\SalesReturn;
use App\Models\SalesTransaction;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupplierOrder;
use App\Models\SupplierOrderItem;
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
            'name' => 'Bea Bautista',
            'email' => 'owner@bbelectronics.test',
        ]);

        $cashier = User::factory()->cashier()->create([
            'name' => 'Test Cashier',
            'email' => 'cashier@bbelectronics.test',
        ]);

        $supplier = Supplier::factory()->create([
            'name' => 'Davao Electro Parts Trading',
        ]);

        $products = Product::factory(20)->create();

        $products->each(fn (Product $product) => Inventory::factory()->create([
            'product_id' => $product->id,
            'quantity' => fake()->numberBetween(10, 150),
        ]));

        $order = SupplierOrder::factory()->create([
            'supplier_id' => $supplier->id,
            'user_id' => $owner->id,
            'status' => SupplierOrderStatus::Received,
        ]);

        $orderedProducts = $products->random(5);

        foreach ($orderedProducts as $product) {
            $quantity = fake()->numberBetween(20, 60);

            SupplierOrderItem::factory()->create([
                'supplier_order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_cost' => $product->unit_price * 0.7,
            ]);

            StockMovement::factory()->create([
                'product_id' => $product->id,
                'user_id' => $owner->id,
                'type' => StockMovementType::In,
                'reason' => StockMovementReason::SupplierDelivery,
                'quantity' => $quantity,
                'notes' => 'Supplier delivery from '.$supplier->name,
            ]);
        }

        SalesTransaction::factory(15)
            ->create(['user_id' => $cashier->id])
            ->each(function (SalesTransaction $transaction) use ($products, $cashier) {
                $lineItems = $products->random(fake()->numberBetween(1, 3));
                $total = 0;

                foreach ($lineItems as $product) {
                    $quantity = fake()->numberBetween(1, 4);
                    $subtotal = $quantity * $product->unit_price;
                    $total += $subtotal;

                    SalesItem::factory()->create([
                        'sales_transaction_id' => $transaction->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $product->unit_price,
                        'subtotal' => $subtotal,
                    ]);

                    StockMovement::factory()->create([
                        'product_id' => $product->id,
                        'user_id' => $cashier->id,
                        'type' => StockMovementType::Out,
                        'reason' => StockMovementReason::CustomerSale,
                        'quantity' => $quantity,
                        'notes' => null,
                    ]);
                }

                $transaction->update(['total_price' => $total]);
            });

        $returnTransaction = SalesTransaction::query()->with('items')->first();

        if ($returnTransaction && $returnTransaction->items->isNotEmpty()) {
            $item = $returnTransaction->items->first();

            SalesReturn::factory()->create([
                'sales_transaction_id' => $returnTransaction->id,
                'product_id' => $item->product_id,
                'user_id' => $cashier->id,
                'quantity' => 1,
            ]);
        }

        $warrantyTransaction = SalesTransaction::query()->with('items')->skip(1)->first();

        if ($warrantyTransaction && $warrantyTransaction->items->isNotEmpty()) {
            $item = $warrantyTransaction->items->first();

            Warranty::factory()->create([
                'sales_transaction_id' => $warrantyTransaction->id,
                'product_id' => $item->product_id,
                'user_id' => $cashier->id,
            ]);
        }
    }
}
