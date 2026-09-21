<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @var array<string, array<int, string>>
     */
    protected static array $catalog = [
        'Lighting' => ['LED Bulb 9W', 'LED Bulb 12W', 'Fluorescent Tube 18W', 'LED Strip Light 5m'],
        'Wiring & Cables' => ['Electrical Wire 2.0mm (per meter)', 'Electrical Wire 3.5mm (per meter)', 'Extension Cord 5m', 'HDMI Cable 2m'],
        'Components' => ['Ceramic Capacitor 104', 'Electrolytic Capacitor 1000uF', 'Resistor 1K Ohm', 'Resistor 10K Ohm', 'Diode 1N4007'],
        'Switches & Outlets' => ['Toggle Switch', 'Wall Outlet Duplex', 'Automatic Voltage Regulator', 'Circuit Breaker 20A'],
        'Power & Batteries' => ['AA Battery (pack of 4)', '9V Battery', 'Power Adapter 5V 2A', 'Universal Charger'],
        'Connectors' => ['USB Connector Type-C', 'RCA Connector Pair', 'Terminal Block 12-way', 'Wire Nut Connector (pack)'],
    ];

    /**
     * Remaining [category, name] pairs to hand out before the catalog repeats.
     *
     * @var array<int, array{0: string, 1: string}>|null
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
            static::$queue = collect(static::$catalog)
                ->flatMap(fn (array $names, string $category) => collect($names)->map(fn (string $name) => [$category, $name]))
                ->shuffle()
                ->all();
        }

        [$category, $name] = array_pop(static::$queue);

        return [
            'sku' => strtoupper(fake()->unique()->bothify('??-####')),
            'name' => $name,
            'category' => $category,
            'description' => fake()->optional()->sentence(),
            'unit_price' => fake()->randomFloat(2, 5, 1500),
            'reorder_level' => fake()->numberBetween(5, 20),
        ];
    }
}
