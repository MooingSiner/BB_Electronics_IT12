<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Support\Code128;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarcodeImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_encoding_matches_a_known_code_128_value(): void
    {
        // "A" in subset B: start B, 'A' (33), checksum (104 + 33) % 103 = 34, stop.
        $this->assertSame(
            array_map('intval', str_split('211214'.'111323'.'131123'.'2331112')),
            Code128::modules('A')
        );
    }

    public function test_every_symbol_is_eleven_modules_wide_and_the_stop_is_thirteen(): void
    {
        $widths = Code128::modules('CON-0001');

        $this->assertSame(0, (count($widths) - 7) % 6);
        $this->assertSame(13, array_sum(array_slice($widths, -7)));
        $this->assertSame(11 * ((count($widths) - 7) / 6) + 13, array_sum($widths));
    }

    public function test_the_product_page_shows_the_barcode_image_and_number(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $product = Product::factory()->for(Category::factory())->create(['barcode' => '4800123456789']);

        $this->actingAs($owner)->get(route('owner.inventory.show', $product->product_id))
            ->assertOk()
            ->assertSee('<svg', false)
            ->assertSee('Barcode 4800123456789', false)
            ->assertSee('4800123456789');
    }

    public function test_the_label_page_has_only_the_label_and_repeats_for_copies(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $product = Product::factory()->for(Category::factory())->create(['barcode' => '4800123456789']);

        $response = $this->actingAs($owner)->get(route('owner.inventory.label', [$product->product_id, 'copies' => 3]))
            ->assertOk()
            ->assertDontSee('Stock In');

        $this->assertSame(3, substr_count($response->getContent(), 'class="label"'));
    }

    public function test_the_label_size_can_be_made_larger(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $product = Product::factory()->for(Category::factory())->create(['barcode' => '123']);

        $this->actingAs($owner)->get(route('owner.inventory.label', [$product->product_id, 'size' => 'large']))
            ->assertOk()->assertSee('width: 186mm', false);
        $this->actingAs($owner)->get(route('owner.inventory.label', [$product->product_id, 'size' => 'bogus']))
            ->assertOk()->assertSee('width: 62mm', false);
    }

    public function test_the_label_can_also_be_printed_extra_small_and_extra_extra_small(): void
    {
        $owner = User::factory()->ownerManager()->create();
        $product = Product::factory()->for(Category::factory())->create(['barcode' => '123']);

        $this->actingAs($owner)->get(route('owner.inventory.label', [$product->product_id, 'size' => 'xsmall']))
            ->assertSee('width: 45mm', false);
        $this->actingAs($owner)->get(route('owner.inventory.label', [$product->product_id, 'size' => 'xxsmall']))
            ->assertSee('width: 32mm', false);
    }
}
