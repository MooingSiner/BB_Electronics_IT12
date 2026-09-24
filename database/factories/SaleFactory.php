<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $total = fake()->randomFloat(2, 20, 5000);

        return [
            'user_id' => User::factory(),
            'sale_date' => fake()->dateTimeBetween('-2 months', 'now'),
            'subtotal' => $total,
            'discount_amount' => 0,
            'total_amount' => $total,
            'payment_method' => fake()->randomElement(PaymentMethod::cases()),
            'amount_paid' => $total,
            'change_amount' => 0,
            'status' => SaleStatus::Completed,
        ];
    }
}
