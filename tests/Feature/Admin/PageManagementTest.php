<?php

namespace Tests\Feature\Admin;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($this->admin, 'admin');
    }

    public function test_admin_can_view_pages_list(): void
    {
        Page::factory()->count(5)->create();

        $response = $this->get('/admin/pages');

        $response->assertStatus(200);
        $response->assertViewIs('admin.pages.index');
    }

    public function test_admin_can_create_page(): void
    {
        $response = $this->post('/admin/pages', [
            'title' => 'Test Page',
            'slug' => 'test-page',
            'content' => 'Test content',
            'is_published' => true,
        ]);

        $response->assertRedirect('/admin/pages');
        $this->assertDatabaseHas('pages', [
            'title' => 'Test Page',
            'slug' => 'test-page',
        ]);
    }

    public function test_admin_can_update_page(): void
    {
        $page = Page::factory()->create();

        $response = $this->put("/admin/pages/{$page->id}", [
            'title' => 'Updated Title',
            'slug' => $page->slug,
            'content' => $page->content,
        ]);

        $response->assertRedirect('/admin/pages');
        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_admin_can_delete_page(): void
    {
        $page = Page::factory()->create();

        $response = $this->delete("/admin/pages/{$page->id}");

        $response->assertRedirect('/admin/pages');
        $this->assertSoftDeleted('pages', ['id' => $page->id]);
    }

    public function test_admin_can_publish_page(): void
    {
        $page = Page::factory()->create(['is_published' => false]);

        $response = $this->put("/admin/pages/{$page->id}", [
            'title' => $page->title,
            'slug' => $page->slug,
            'content' => $page->content,
            'is_published' => true,
        ]);

        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'is_published' => true,
        ]);
    }
}
