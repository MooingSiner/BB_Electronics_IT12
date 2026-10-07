<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    private function png(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='));
    }

    public function test_a_user_can_upload_replace_and_remove_a_profile_picture(): void
    {
        Storage::fake('public');
        $user = User::factory()->cashierAttendant()->create();

        $this->actingAs($user)->post(route('cashier.profile.photo'), ['photo' => $this->png('a.png')])
            ->assertSessionHas('success');
        $first = $user->fresh()->profile_photo;
        Storage::disk('public')->assertExists($first);

        $this->actingAs($user)->post(route('cashier.profile.photo'), ['photo' => $this->png('b.png')]);
        Storage::disk('public')->assertMissing($first);
        $second = $user->fresh()->profile_photo;
        Storage::disk('public')->assertExists($second);

        $this->actingAs($user)->get(route('cashier.profile'))->assertOk()->assertSee('Remove picture');

        $this->actingAs($user)->delete(route('cashier.profile.photo.remove'))->assertSessionHas('success');
        $this->assertNull($user->fresh()->profile_photo);
        Storage::disk('public')->assertMissing($second);
    }

    public function test_only_small_images_are_accepted(): void
    {
        Storage::fake('public');
        $owner = User::factory()->ownerManager()->create();

        $this->actingAs($owner)->post(route('owner.profile.photo'), ['photo' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')])
            ->assertSessionHasErrors('photo');
        $this->actingAs($owner)->post(route('owner.profile.photo'), ['photo' => UploadedFile::fake()->create('big.png', 3000, 'image/png')])
            ->assertSessionHasErrors('photo');

        $this->assertNull($owner->fresh()->profile_photo);
    }

    public function test_the_top_right_account_block_is_gone_but_the_sidebar_still_shows_the_user(): void
    {
        $owner = User::factory()->ownerManager()->create(['full_name' => 'Zed Quill']);

        $page = $this->actingAs($owner)->get(route('owner.dashboard'))->assertOk();

        $page->assertDontSee('hidden sm:block text-left', false);
        $this->assertStringContainsString('Zed Quill', $page->getContent());
    }
}
