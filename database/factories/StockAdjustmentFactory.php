<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockAdjustment>
 */
class StockAdjustmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'adjustment_date' => fake()->dateTimeBetween('-2 months', 'now'),
            'quantity_change' => fake()->randomElement([-5, -3, -2, -1, 1, 2, 5, 10]),
            'reason' => fake()->randomElement([
                'Physical count correction',
                'Damaged in storage',
                'Lost item',
                'Found during inventory count',
            ]),
        ];
    }
}
