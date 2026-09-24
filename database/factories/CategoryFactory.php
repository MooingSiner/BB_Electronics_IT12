<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * @var array<int, string>|null
     */
    protected static ?array $queue = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        if (empty(static::$queue)) {
            static::$queue = collect([
                'Lighting',
                'Wiring & Cables',
                'Components',
                'Switches & Outlets',
                'Power & Batteries',
                'Connectors',
            ])->shuffle()->all();
        }

        return [
            'category_name' => array_pop(static::$queue),
        ];
    }
}
