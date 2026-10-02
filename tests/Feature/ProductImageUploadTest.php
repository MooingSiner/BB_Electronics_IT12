<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_upload_a_product_image_when_creating_a_product(): void
    {
        Storage::fake('public');

        $owner = User::factory()->ownerManager()->create();
        $category = Category::factory()->create();
        $file = UploadedFile::fake()->create('battery.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($owner)->post(route('owner.inventory.store'), [
            'name' => 'Uploaded Product',
            'category' => $category->category_name,
            'image_file' => $file,
            'unit_price' => 50,
            'cost_price' => 30,
            'initial_qty' => 10,
            'reorder_level' => 5,
        ]);

        $product = Product::where('product_name', 'Uploaded Product')->first();

        $response->assertRedirect(route('owner.inventory.show', $product->product_id));
        $this->assertNotNull($product->image_url);
        $this->assertStringContainsString('/storage/products/', $product->image_url);

        $storedPath = str_replace('/storage/', '', parse_url($product->image_url, PHP_URL_PATH));
        Storage::disk('public')->assertExists($storedPath);
    }

    public function test_uploaded_image_takes_priority_over_image_url(): void
    {
        Storage::fake('public');

        $owner = User::factory()->ownerManager()->create();
        $category = Category::factory()->create();
        $file = UploadedFile::fake()->create('battery.jpg', 100, 'image/jpeg');

        $this->actingAs($owner)->post(route('owner.inventory.store'), [
            'name' => 'Priority Test Product',
            'category' => $category->category_name,
            'image_file' => $file,
            'image_url' => 'https://example.com/other-photo.jpg',
            'unit_price' => 50,
            'initial_qty' => 10,
            'reorder_level' => 5,
        ]);

        $product = Product::where('product_name', 'Priority Test Product')->first();

        $this->assertStringContainsString('/storage/products/', $product->image_url);
        $this->assertNotSame('https://example.com/other-photo.jpg', $product->image_url);
    }

    public function test_owner_can_replace_a_product_image_on_update(): void
    {
        Storage::fake('public');

        $owner = User::factory()->ownerManager()->create();
        $product = Product::factory()->for(Category::factory())->create(['image_url' => 'https://example.com/old.jpg']);
        $file = UploadedFile::fake()->create('new-photo.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($owner)->put(route('owner.inventory.update', $product->product_id), [
            'name' => $product->product_name,
            'category' => $product->category->category_name,
            'image_file' => $file,
            'unit_price' => $product->unit_price,
            'reorder_level' => $product->reorder_level,
        ]);

        $response->assertRedirect(route('owner.inventory.show', $product->product_id));
        $this->assertStringContainsString('/storage/products/', $product->fresh()->image_url);
    }
}
