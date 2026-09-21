<?php

namespace Database\Factories;

use App\Enums\StockMovementReason;
use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMovement>
 */
class StockMovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(StockMovementType::cases());

        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'type' => $type,
            'reason' => $type === StockMovementType::In
                ? StockMovementReason::SupplierDelivery
                : fake()->randomElement([StockMovementReason::CustomerSale, StockMovementReason::ServiceUse]),
            'quantity' => fake()->numberBetween(1, 50),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
