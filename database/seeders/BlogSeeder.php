<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use App\Services\BlogImageService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seed blog posts from pre-processed scraped inventory.
 *
 * Content in database/data/scraped-blogs.json is production-ready:
 * brand names, URLs, emails, and image paths have been transformed by
 * `php artisan blog:preprocess-content`. Posts are sorted newest-first.
 *
 * To regenerate the source data:
 *   php artisan blog:scrape-wordpress
 *   php artisan blog:download-media
 *   php artisan blog:preprocess-content
 */
class BlogSeeder extends Seeder
{
    protected BlogImageService $blogImageService;

    public function __construct(BlogImageService $blogImageService)
    {
        $this->blogImageService = $blogImageService;
    }

    public function run(): void
    {
        $this->command->info('Seeding blog posts from scraped inventory...');
        $this->command->newLine();

        $author = User::where('role', 'author')->orWhere('role', 'admin')->first()
            ?? User::first();

        if (! $author) {
            $this->command->error('No users found. Please run FoundationSeeder first.');
            return;
        }

        $inventory = $this->loadScrapedInventory();
        if (empty($inventory)) {
            $this->command->error('No blog posts found. Run: php artisan blog:preprocess-content');
            return;
        }

        $imageUrlMap = $this->loadImageUrlMapping();

        $this->seedBlogPosts($author, $inventory, $imageUrlMap);

        $this->command->newLine();
        $this->command->info('✓ Blog posts seeded successfully!');
        $this->command->info('Total blog posts: '.BlogPost::count());

        $postsWithImages = BlogPost::whereNotNull('featured_image')->where('featured_image', '!=', '')->count();
        if ($postsWithImages > 0) {
            $this->command->info("Posts with featured images: {$postsWithImages}");
        }
    }

    protected function loadScrapedInventory(): array
    {
        $jsonPath = database_path('data/scraped-blogs.json');
        if (! file_exists($jsonPath)) {
            $this->command->warn('Blog data file not found. Using empty data.');
            return [];
        }

        $inventory = json_decode(file_get_contents($jsonPath), true);
        if (! is_array($inventory)) {
            $this->command->error('Invalid scraped-blogs.json format');
            return [];
        }

        $this->command->line('Loaded '.count($inventory).' posts from scraped-blogs.json');
        return $inventory;
    }

    protected function loadImageUrlMapping(): array
    {
        $jsonPath = database_path('data/blog-media-mapping.json');
        if (! file_exists($jsonPath)) {
            return [];
        }

        $mapping = json_decode(file_get_contents($jsonPath), true);
        return is_array($mapping) ? $mapping : [];
    }

    protected function seedBlogPosts(User $author, array $inventory, array $imageUrlMap): void
    {
        $this->command->info('Processing scraped blog posts...');
        $this->command->newLine();

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($inventory as $item) {
            $slug  = $item['slug'] ?? Str::slug($item['title'] ?? 'untitled');
            $title = $item['title'] ?? 'Untitled';

            if (empty($slug) || empty($title)) {
                $skipped++;
                continue;
            }

            $publishedAt = ! empty($item['published_at'])
                ? Carbon::parse($item['published_at'])
                : now();

            $content = ! empty($item['content_html']) ? $item['content_html'] : '<p></p>';
            $excerpt = $item['excerpt'] ?? '';

            // Resolve featured image
            $featuredImage = null;
            if (! empty($item['featured_image_url'])) {
                $imageUrl = $item['featured_image_url'];
                if (isset($imageUrlMap[$imageUrl])) {
                    $featuredImage = $this->blogImageService->getStandardPath($imageUrlMap[$imageUrl]);
                } else {
                    $featuredImage = $this->blogImageService->resolveUrl($imageUrl, $slug);
                }
            } else {
                $featuredImage = $this->blogImageService->findBySlug($slug);
            }

            $post = BlogPost::where('slug', $slug)->first();

            if ($post) {
                $post->update([
                    'title'          => $title,
                    'excerpt'        => $excerpt,
                    'content'        => $content,
                    'featured_image' => $featuredImage ?? $post->featured_image,
                    'published_at'   => $publishedAt,
                    'is_published'   => true,
                ]);
                $updated++;
                $this->command->line("  ↻ Updated: {$title}");
            } else {
                $post = BlogPost::create([
                    'title'            => $title,
                    'slug'             => $slug,
                    'excerpt'          => $excerpt,
                    'content'          => $content,
                    'author_id'        => $author->id,
                    'is_published'     => true,
                    'published_at'     => $publishedAt,
                    'featured_image'   => $featuredImage,
                    'meta_title'       => $title.' - The Strengths Toolbox',
                    'meta_description' => Str::limit(strip_tags($excerpt ?: $content), 160),
                ]);
                $created++;
                $this->command->line("  ✓ Created: {$title}");
            }

            // Sync categories
            $categorySlugs = $this->extractCategorySlugs($item);
            if (! empty($categorySlugs)) {
                $categoryIds = Category::whereIn('slug', $categorySlugs)->pluck('id');
                $post->categories()->sync($categoryIds);
            }

            // Sync tags
            $tagNames = $this->extractTagNames($item);
            if (! empty($tagNames)) {
                $tagIds = [];
                foreach ($tagNames as $tagName) {
                    $tag = Tag::firstOrCreate(
                        ['slug' => Str::slug($tagName)],
                        ['name' => $tagName]
                    );
                    $tagIds[] = $tag->id;
                }
                $post->tags()->sync($tagIds);
            }
        }

        $this->command->newLine();
        $this->command->info("Summary: Created {$created}, Updated {$updated}, Skipped {$skipped}");
    }

    protected function extractCategorySlugs(array $item): array
    {
        $slugs = [];

        if (! empty($item['category'])) {
            $slugs[] = Str::slug($item['category']);
        }

        if (! empty($item['categories']) && is_array($item['categories'])) {
            foreach ($item['categories'] as $categoryName) {
                $slugs[] = Str::slug($categoryName);
            }
        }

        if (empty($slugs)) {
            $slugs[] = 'business-coaching';
        }

        return array_unique($slugs);
    }

    protected function extractTagNames(array $item): array
    {
        $tags = $item['tags'] ?? [];
        return is_array($tags) ? array_unique($tags) : [];
    }
}
