<?php

namespace Tests\Feature\Admin;

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($this->admin, 'admin');
    }

    public function test_admin_can_view_media_list(): void
    {
        Media::factory()->count(5)->create();

        $response = $this->get('/admin/media');

        $response->assertStatus(200);
    }

    public function test_admin_can_upload_media_file(): void
    {
        $file = UploadedFile::fake()->image('test.jpg', 800, 600);

        $response = $this->post('/admin/media/upload', [
            'file' => $file,
            'alt_text' => 'Test image',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('media', [
            'original_filename' => 'test.jpg',
        ]);
    }

    public function test_admin_can_delete_media_file(): void
    {
        $media = Media::factory()->create();

        $response = $this->delete("/admin/media/{$media->id}");

        $response->assertRedirect('/admin/media');
        // Media uses soft deletes
        $this->assertSoftDeleted('media', ['id' => $media->id]);
    }
}
