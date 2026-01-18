<?php

namespace Tests\Feature\Admin;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($this->admin, 'admin');
    }

    public function test_admin_can_view_blog_posts_list(): void
    {
        BlogPost::factory()->count(5)->create();

        $response = $this->get('/admin/blog');

        $response->assertStatus(200);
    }

    public function test_admin_can_create_blog_post(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->post('/admin/blog', [
            'title' => 'Test Post',
            'slug' => 'test-post',
            'content' => 'Test content',
            'excerpt' => 'Test excerpt',
            'author_id' => $this->admin->id,
            'category_ids' => [$category->id],
            'tag_ids' => [$tag->id],
            'is_published' => true,
        ]);

        $response->assertRedirect('/admin/blog');
        $post = BlogPost::where('slug', 'test-post')->first();
        $this->assertNotNull($post);
        $this->assertTrue($post->categories->contains($category));
        $this->assertTrue($post->tags->contains($tag));
    }

    public function test_admin_can_update_blog_post(): void
    {
        $post = BlogPost::factory()->create();

        $response = $this->put("/admin/blog/{$post->id}", [
            'title' => 'Updated Post',
            'slug' => $post->slug,
            'content' => $post->content,
            'author_id' => $post->author_id,
        ]);

        $response->assertRedirect('/admin/blog');
        $this->assertDatabaseHas('blog_posts', [
            'id' => $post->id,
            'title' => 'Updated Post',
        ]);
    }

    public function test_admin_can_delete_blog_post(): void
    {
        $post = BlogPost::factory()->create();

        $response = $this->delete("/admin/blog/{$post->id}");

        $response->assertRedirect('/admin/blog');
        $this->assertSoftDeleted('blog_posts', ['id' => $post->id]);
    }
}
