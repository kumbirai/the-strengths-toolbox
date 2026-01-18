<?php

namespace Tests\Unit\Models;

use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestimonialTest extends TestCase
{
    use RefreshDatabase;

    public function test_testimonial_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $testimonial = Testimonial::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $testimonial->user);
        $this->assertEquals($user->id, $testimonial->user->id);
    }

    public function test_scope_featured_returns_only_featured_testimonials(): void
    {
        $featured = Testimonial::factory()->create(['is_featured' => true]);
        $notFeatured = Testimonial::factory()->create(['is_featured' => false]);

        $results = Testimonial::featured()->get();

        $this->assertTrue($results->contains($featured));
        $this->assertFalse($results->contains($notFeatured));
    }
}
