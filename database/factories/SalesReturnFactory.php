<?php

namespace Database\Factories;

use App\Enums\ReturnResolution;
use App\Enums\ReturnStatus;
use App\Models\Product;
use App\Models\SalesReturn;
use App\Models\SalesTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalesReturn>
 */
class SalesReturnFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sales_transaction_id' => SalesTransaction::factory(),
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'quantity' => fake()->numberBetween(1, 5),
            'reason' => fake()->randomElement([
                'Product arrived damaged',
                'Wrong item delivered',
                'Customer changed mind',
                'Defective on arrival',
            ]),
            'resolution' => fake()->randomElement(ReturnResolution::cases()),
            'status' => fake()->randomElement(ReturnStatus::cases()),
            'return_date' => fake()->dateTimeBetween('-2 months', 'now'),
        ];
    }
}
