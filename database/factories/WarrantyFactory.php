<?php

namespace Database\Factories;

use App\Enums\WarrantyStatus;
use App\Models\Product;
use App\Models\SalesTransaction;
use App\Models\User;
use App\Models\Warranty;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Warranty>
 */
class WarrantyFactory extends Factory
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
            'warranty_reference' => strtoupper(fake()->bothify('WR-####??')),
            'issue' => fake()->randomElement([
                'Unit does not power on',
                'Intermittent connection issue',
                'Component overheating',
                'Physical defect found after purchase',
            ]),
            'resolution' => fake()->optional()->sentence(),
            'status' => fake()->randomElement(WarrantyStatus::cases()),
            'date' => fake()->dateTimeBetween('-2 months', 'now'),
        ];
    }
}
