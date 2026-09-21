<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\SupplierOrder;
use App\Models\SupplierOrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupplierOrderItem>
 */
class SupplierOrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'supplier_order_id' => SupplierOrder::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->numberBetween(10, 100),
            'unit_cost' => fake()->randomFloat(2, 3, 1000),
        ];
    }
}
