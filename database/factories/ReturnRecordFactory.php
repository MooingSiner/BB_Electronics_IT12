<?php

namespace Database\Factories;

use App\Enums\ReturnCondition;
use App\Enums\ReturnResolution;
use App\Enums\ReturnStatus;
use App\Models\Product;
use App\Models\ReturnRecord;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReturnRecord>
 */
class ReturnRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sale_id' => Sale::factory(),
            'product_id' => Product::factory(),
            'supplier_id' => null,
            'return_date' => fake()->dateTimeBetween('-2 months', 'now'),
            'quantity' => fake()->numberBetween(1, 5),
            'reason' => fake()->randomElement([
                'Product arrived damaged',
                'Wrong item delivered',
                'Customer changed mind',
                'Defective on arrival',
            ]),
            'condition' => fake()->randomElement(ReturnCondition::cases()),
            'resolution' => fake()->randomElement(ReturnResolution::cases()),
            'status' => fake()->randomElement(ReturnStatus::cases()),
        ];
    }
}
