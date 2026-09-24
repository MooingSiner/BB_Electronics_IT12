<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @var array<int, string>|null
     */
    protected static ?array $queue = null;

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Product $product) {
            $product->update(['product_code' => Product::generateCode($product->category)]);
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        if (empty(static::$queue)) {
            static::$queue = collect([
                'LED Bulb 9W', 'LED Bulb 12W', 'Fluorescent Tube 18W', 'LED Strip Light 5m',
                'Electrical Wire 2.0mm (per meter)', 'Electrical Wire 3.5mm (per meter)', 'Extension Cord 5m', 'HDMI Cable 2m',
                'Ceramic Capacitor 104', 'Electrolytic Capacitor 1000uF', 'Resistor 1K Ohm', 'Resistor 10K Ohm', 'Diode 1N4007',
                'Toggle Switch', 'Wall Outlet Duplex', 'Automatic Voltage Regulator', 'Circuit Breaker 20A',
                'AA Battery (pack of 4)', '9V Battery', 'Power Adapter 5V 2A', 'Universal Charger',
                'USB Connector Type-C', 'RCA Connector Pair', 'Terminal Block 12-way', 'Wire Nut Connector (pack)',
            ])->shuffle()->all();
        }

        $unitPrice = fake()->randomFloat(2, 5, 1500);

        return [
            'category_id' => Category::factory(),
            'product_name' => array_pop(static::$queue),
            'unit_price' => $unitPrice,
            'cost_price' => round($unitPrice * 0.7, 2),
            'quantity_on_hand' => fake()->numberBetween(0, 200),
            'reorder_level' => fake()->numberBetween(5, 20),
            'warranty_period_days' => fake()->randomElement([0, 90, 180, 365]),
            'is_active' => true,
        ];
    }
}
