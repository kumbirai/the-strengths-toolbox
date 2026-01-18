<?php

namespace Tests\Unit\Models;

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_belongs_to_uploader(): void
    {
        $user = User::factory()->create();
        $media = Media::factory()->create(['uploaded_by' => $user->id]);

        $this->assertInstanceOf(User::class, $media->uploader);
        $this->assertEquals($user->id, $media->uploader->id);
    }

    public function test_get_url_attribute_returns_correct_url(): void
    {
        $media = Media::factory()->create(['path' => 'media/test.jpg']);

        $url = $media->url;

        $this->assertStringContainsString('storage/media/test.jpg', $url);
    }

    public function test_get_thumbnail_url_attribute_returns_thumbnail(): void
    {
        $media = Media::factory()->create([
            'path' => 'media/test.jpg',
            'thumbnail_path' => 'media/thumbnails/test_thumb.jpg',
        ]);

        $url = $media->thumbnail_url;

        $this->assertStringContainsString('storage/media/thumbnails/test_thumb.jpg', $url);
    }

    public function test_get_thumbnail_url_returns_null_when_no_thumbnail(): void
    {
        $media = Media::factory()->create(['thumbnail_path' => null]);

        $this->assertNull($media->thumbnail_url);
    }

    public function test_is_image_returns_true_for_images(): void
    {
        $media = Media::factory()->create(['mime_type' => 'image/jpeg']);

        $this->assertTrue($media->isImage());
    }

    public function test_is_image_returns_false_for_non_images(): void
    {
        $media = Media::factory()->create(['mime_type' => 'application/pdf']);

        $this->assertFalse($media->isImage());
    }
}
