<?php

namespace Database\Factories;

use App\Enums\SupplierOrderStatus;
use App\Models\Supplier;
use App\Models\SupplierOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupplierOrder>
 */
class SupplierOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::factory(),
            'user_id' => User::factory(),
            'order_date' => fake()->dateTimeBetween('-2 months', 'now'),
            'status' => fake()->randomElement(SupplierOrderStatus::cases()),
        ];
    }
}
