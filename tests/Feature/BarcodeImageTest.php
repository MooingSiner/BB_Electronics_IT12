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
}
