<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTagManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($this->admin, 'admin');
    }

    public function test_admin_can_create_category(): void
    {
        $response = $this->post('/admin/blog/categories', [
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'Test description',
        ]);

        $response->assertRedirect('/admin/blog/categories');
        $this->assertDatabaseHas('categories', [
            'name' => 'Test Category',
        ]);
    }

    public function test_admin_can_update_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->put("/admin/blog/categories/{$category->id}", [
            'name' => 'Updated Category',
            'slug' => $category->slug,
        ]);

        $response->assertRedirect('/admin/blog/categories');
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category',
        ]);
    }

    public function test_admin_can_delete_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->delete("/admin/blog/categories/{$category->id}");

        $response->assertRedirect('/admin/blog/categories');
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_admin_can_create_tag(): void
    {
        $response = $this->post('/admin/blog/tags', [
            'name' => 'Test Tag',
            'slug' => 'test-tag',
        ]);

        $response->assertRedirect('/admin/blog/tags');
        $this->assertDatabaseHas('tags', [
            'name' => 'Test Tag',
        ]);
    }

    public function test_admin_can_update_tag(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->put("/admin/blog/tags/{$tag->id}", [
            'name' => 'Updated Tag',
            'slug' => $tag->slug,
        ]);

        $response->assertRedirect('/admin/blog/tags');
        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'Updated Tag',
        ]);
    }

    public function test_admin_can_delete_tag(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->delete("/admin/blog/tags/{$tag->id}");

        $response->assertRedirect('/admin/blog/tags');
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }
}
