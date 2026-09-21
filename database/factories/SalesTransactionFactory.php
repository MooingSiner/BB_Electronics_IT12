<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\SalesTransactionStatus;
use App\Models\SalesTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalesTransaction>
 */
class SalesTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'total_price' => fake()->randomFloat(2, 20, 5000),
            'payment_method' => fake()->randomElement(PaymentMethod::cases()),
            'status' => SalesTransactionStatus::Completed,
        ];
    }
}
