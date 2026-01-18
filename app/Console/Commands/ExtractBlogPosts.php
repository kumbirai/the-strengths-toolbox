<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Extract blog posts from existing website
 * This command helps extract blog post data from the existing website
 */
class ExtractBlogPosts extends Command
{
    protected $signature = 'blog:extract 
                            {--url= : Base URL of existing website}
                            {--output=blog-posts.json : Output file for extracted posts}
                            {--format=json : Output format (json|seeder)}';

    protected $description = 'Extract blog posts from existing website for migration';

    public function handle(): int
    {
        $baseUrl = $this->option('url') ?? $this->ask('Enter the base URL of the existing website', 'https://www.thestrengthstoolbox.com');
        $outputFile = $this->option('output');
        $format = $this->option('format');

        $this->info("Extracting blog posts from: {$baseUrl}");
        $this->newLine();

        // Try to find blog listing page
        $blogUrls = $this->discoverBlogUrls($baseUrl);

        if (empty($blogUrls)) {
            $this->warn('Could not automatically discover blog posts.');
            $this->newLine();
            $this->line('Manual extraction guide:');
            $this->line('1. Visit the blog listing page on the existing website');
            $this->line('2. Note the URL pattern for blog posts');
            $this->line('3. Visit each blog post and extract:');
            $this->line('   - Title');
            $this->line('   - Content');
            $this->line('   - Publication date');
            $this->line('   - Categories/Tags');
            $this->line('   - Featured image');
            $this->line('4. Use the template in BlogPostMigrationSeeder.php to add posts');
            $this->newLine();

            return $this->showManualExtractionGuide();
        }

        $posts = [];
        foreach ($blogUrls as $url) {
            $this->line("Extracting: {$url}");
            $post = $this->extractPostData($url);
            if ($post) {
                $posts[] = $post;
            }
        }

        if (empty($posts)) {
            $this->error('No blog posts could be extracted automatically.');

            return $this->showManualExtractionGuide();
        }

        // Save extracted posts
        if ($format === 'seeder') {
            $this->generateSeederFile($posts, $outputFile);
        } else {
            $this->saveJsonFile($posts, $outputFile);
        }

        $this->newLine();
        $this->info('✓ Extracted '.count($posts).' blog posts');
        $this->info("Saved to: {$outputFile}");

        return Command::SUCCESS;
    }

    protected function discoverBlogUrls(string $baseUrl): array
    {
        // Common blog URL patterns
        $patterns = [
            '/blog',
            '/blog/',
            '/articles',
            '/posts',
            '/news',
        ];

        $urls = [];
        foreach ($patterns as $pattern) {
            try {
                $response = Http::timeout(10)->get($baseUrl.$pattern);
                if ($response->successful()) {
                    // Try to extract blog post URLs from HTML
                    $html = $response->body();
                    preg_match_all('/href=["\']([^"\']*blog[^"\']*\/[^"\']+)["\']/i', $html, $matches);
                    if (! empty($matches[1])) {
                        foreach ($matches[1] as $match) {
                            $url = Str::startsWith($match, 'http') ? $match : $baseUrl.$match;
                            $urls[] = $url;
                        }
                    }
                }
            } catch (\Exception $e) {
                // Continue to next pattern
            }
        }

        return array_unique($urls);
    }

    protected function extractPostData(string $url): ?array
    {
        try {
            $response = Http::timeout(10)->get($url);
            if (! $response->successful()) {
                return null;
            }

            $html = $response->body();

            // Extract title
            preg_match('/<title[^>]*>([^<]+)<\/title>/i', $html, $titleMatch);
            $title = $titleMatch[1] ?? 'Untitled';

            // Extract content (try to find main content area)
            preg_match('/<article[^>]*>(.*?)<\/article>/is', $html, $articleMatch);
            preg_match('/<main[^>]*>(.*?)<\/main>/is', $html, $mainMatch);
            preg_match('/<div[^>]*class=["\'][^"\']*content[^"\']*["\'][^>]*>(.*?)<\/div>/is', $html, $contentMatch);

            $content = $articleMatch[1] ?? $mainMatch[1] ?? $contentMatch[1] ?? '';

            // Extract date
            preg_match('/<time[^>]*datetime=["\']([^"\']+)["\']/i', $html, $dateMatch);
            preg_match('/(\d{4}-\d{2}-\d{2})/', $html, $dateMatch2);
            $date = $dateMatch[1] ?? $dateMatch2[1] ?? date('Y-m-d');

            // Extract excerpt (meta description or first paragraph)
            preg_match('/<meta[^>]*name=["\']description["\'][^>]*content=["\']([^"\']+)["\']/i', $html, $excerptMatch);
            preg_match('/<p[^>]*>(.{0,200})<\/p>/i', $content, $excerptMatch2);
            $excerpt = $excerptMatch[1] ?? $excerptMatch2[1] ?? Str::limit(strip_tags($content), 200);

            return [
                'title' => trim($title),
                'slug' => Str::slug($title),
                'excerpt' => trim($excerpt),
                'content' => $content ?: '<p>Content extracted from: '.$url.'</p>',
                'published_at' => $date,
                'url' => $url,
            ];
        } catch (\Exception $e) {
            $this->warn("Error extracting from {$url}: ".$e->getMessage());

            return null;
        }
    }

    protected function saveJsonFile(array $posts, string $filename): void
    {
        $data = ['blog_posts' => $posts];
        file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    protected function generateSeederFile(array $posts, string $filename): void
    {
        $seederContent = "<?php\n\n";
        $seederContent .= "// Extracted blog posts - Update with actual content\n";
        $seederContent .= '// Generated: '.date('Y-m-d H:i:s')."\n\n";
        $seederContent .= "protected function seedExtractedBlogPosts(User \$author): void\n{\n";
        $seederContent .= "    \$posts = [\n";

        foreach ($posts as $post) {
            $seederContent .= "        [\n";
            $seederContent .= "            'title' => ".var_export($post['title'], true).",\n";
            $seederContent .= "            'slug' => ".var_export($post['slug'], true).",\n";
            $seederContent .= "            'excerpt' => ".var_export($post['excerpt'], true).",\n";
            $seederContent .= "            'content' => ".var_export($post['content'], true).",\n";
            $seederContent .= "            'author_id' => \$author->id,\n";
            $seederContent .= "            'is_published' => true,\n";
            $seederContent .= "            'published_at' => ".var_export($post['published_at'], true).",\n";
            $seederContent .= "            'meta_title' => ".var_export($post['title'], true).",\n";
            $seederContent .= "            'meta_description' => ".var_export($post['excerpt'], true).",\n";
            $seederContent .= "            'category_slugs' => [], // Update with actual categories\n";
            $seederContent .= "            'tag_slugs' => [], // Update with actual tags\n";
            $seederContent .= "        ],\n";
        }

        $seederContent .= "    ];\n\n";
        $seederContent .= "    // Use the same logic as seedSampleBlogPosts() to create posts\n";
        $seederContent .= "}\n";

        file_put_contents($filename, $seederContent);
    }

    protected function showManualExtractionGuide(): int
    {
        $this->newLine();
        $this->info('Manual Blog Post Extraction Guide');
        $this->newLine();
        $this->line('Since automatic extraction may not work, follow these steps:');
        $this->newLine();
        $this->line('1. Visit https://www.thestrengthstoolbox.com/blog (or blog listing page)');
        $this->line('2. For each blog post, collect:');
        $this->line('   - Title');
        $this->line('   - Full content (HTML or plain text)');
        $this->line('   - Publication date');
        $this->line('   - Categories');
        $this->line('   - Tags');
        $this->line('   - Featured image URL');
        $this->line('   - Excerpt/summary');
        $this->newLine();
        $this->line('3. Update database/seeders/BlogPostMigrationSeeder.php');
        $this->line('   - Add each blog post to the $posts array');
        $this->line('   - Use the existing structure as a template');
        $this->newLine();
        $this->line('4. Or create a JSON file and import:');
        $this->line('   php artisan content:migrate --source=json --file=blog-posts.json');
        $this->newLine();

        return Command::SUCCESS;
    }
}
