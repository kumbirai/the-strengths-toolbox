<?php

namespace Tests\Unit\Services;

use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaServiceTest extends TestCase
{
    use RefreshDatabase;

    protected MediaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->service = new MediaService;
    }

    public function test_upload_creates_media_record(): void
    {
        $user = \App\Models\User::factory()->create();
        Auth::login($user);

        $file = UploadedFile::fake()->image('test.jpg', 800, 600);

        $media = $this->service->upload($file);

        $this->assertInstanceOf(Media::class, $media);
        $this->assertDatabaseHas('media', [
            'filename' => $media->filename,
            'original_filename' => 'test.jpg',
        ]);
    }

    public function test_upload_processes_image(): void
    {
        $user = \App\Models\User::factory()->create();
        Auth::login($user);

        $file = UploadedFile::fake()->image('test.jpg', 800, 600);

        $media = $this->service->upload($file, ['create_thumbnail' => true]);

        $this->assertNotNull($media->width);
        $this->assertNotNull($media->height);
    }

    public function test_upload_generates_unique_filename(): void
    {
        $user = \App\Models\User::factory()->create();
        Auth::login($user);

        $file = UploadedFile::fake()->image('test.jpg');

        $media = $this->service->upload($file);

        $this->assertNotEquals('test.jpg', $media->filename);
        $this->assertStringContainsString('test', $media->filename);
    }

    public function test_upload_stores_file_on_disk(): void
    {
        $user = \App\Models\User::factory()->create();
        Auth::login($user);

        $file = UploadedFile::fake()->image('test.jpg');

        $media = $this->service->upload($file);

        Storage::disk('public')->assertExists($media->path);
    }

    public function test_delete_removes_file_and_record(): void
    {
        $user = \App\Models\User::factory()->create();
        Auth::login($user);

        $file = UploadedFile::fake()->image('test.jpg');
        $media = $this->service->upload($file);

        $path = $media->path;
        $result = $this->service->delete($media);

        $this->assertTrue($result);
        Storage::disk('public')->assertMissing($path);
        // Media uses soft deletes, so check for soft deletion
        $this->assertSoftDeleted('media', ['id' => $media->id]);
    }

    public function test_upload_with_custom_directory(): void
    {
        $user = \App\Models\User::factory()->create();
        Auth::login($user);

        $file = UploadedFile::fake()->image('test.jpg');

        $media = $this->service->upload($file, ['directory' => 'custom']);

        $this->assertStringStartsWith('custom/', $media->path);
    }

    public function test_upload_with_alt_text(): void
    {
        $user = \App\Models\User::factory()->create();
        Auth::login($user);

        $file = UploadedFile::fake()->image('test.jpg');

        $media = $this->service->upload($file, ['alt_text' => 'Test image']);

        $this->assertEquals('Test image', $media->alt_text);
    }
}
