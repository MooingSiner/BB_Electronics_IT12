<?php

namespace Database\Factories;

use App\Enums\WarrantyClaimStatus;
use App\Enums\WarrantyOutcome;
use App\Models\SaleItem;
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
        $start = fake()->dateTimeBetween('-2 months', 'now');

        return [
            'sale_item_id' => SaleItem::factory(),
            'customer_name' => fake()->name(),
            'contact_number' => fake()->phoneNumber(),
            'start_date' => $start,
            'end_date' => (clone $start)->modify('+180 days'),
            'claim_status' => WarrantyClaimStatus::None,
            'claim_date' => null,
            'outcome' => WarrantyOutcome::NotApplicable,
        ];
    }
}
