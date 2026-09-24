<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'password_hash' => static::$password ??= Hash::make('password'),
            'role' => UserRole::CashierAttendant,
            'status' => UserStatus::Active,
        ];
    }

    /**
     * Indicate that the user is an owner/manager.
     */
    public function ownerManager(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::OwnerManager,
        ]);
    }

    /**
     * Indicate that the user is a cashier/store attendant.
     */
    public function cashierAttendant(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::CashierAttendant,
        ]);
    }

    /**
     * Indicate that the user is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => UserStatus::Inactive,
        ]);
    }
}
