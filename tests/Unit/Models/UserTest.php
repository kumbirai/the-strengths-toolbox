<?php

namespace Tests\Unit\Models;

use App\Models\BlogPost;
use App\Models\FormSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_blog_posts_relationship(): void
    {
        $user = User::factory()->create();
        $post = BlogPost::factory()->create(['author_id' => $user->id]);

        $this->assertTrue($user->blogPosts->contains($post));
        $this->assertEquals(1, $user->blogPosts->count());
    }

    public function test_user_has_form_submissions_relationship(): void
    {
        $user = User::factory()->create();
        $submission = FormSubmission::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->formSubmissions->contains($submission));
    }

    public function test_user_has_chatbot_conversations_relationship(): void
    {
        $user = User::factory()->create();
        $conversation = \App\Models\ChatbotConversation::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->chatbotConversations->contains($conversation));
    }

    public function test_is_admin_returns_true_for_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertTrue($admin->isAdmin());
    }

    public function test_is_admin_returns_false_for_non_admin(): void
    {
        $user = User::factory()->create(['role' => 'author']);

        $this->assertFalse($user->isAdmin());
    }

    public function test_is_editor_returns_true_for_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertTrue($admin->isEditor());
    }

    public function test_is_editor_returns_true_for_editor(): void
    {
        $editor = User::factory()->create(['role' => 'editor']);

        $this->assertTrue($editor->isEditor());
    }

    public function test_password_is_hashed(): void
    {
        $user = User::factory()->create(['password' => 'plaintext']);

        $this->assertNotEquals('plaintext', $user->password);
        $this->assertTrue(Hash::check('plaintext', $user->password));
    }
}
