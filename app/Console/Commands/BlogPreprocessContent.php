<?php

namespace App\Console\Commands;

use App\Services\ContentReplacementService;
use Illuminate\Console\Command;

/**
 * Pre-process blog content from scraped sources.
 *
 * This command:
 *  1. Reads database/data/scraped-blogs.json
 *  2. Adds the missing "3-myths-about-great-sales-people-debunked" post from the WordPress XML
 *  3. Fills published_at dates from the original inventory sources
 *  4. Applies all content transformations via ContentReplacementService (brand names, URLs, emails, images, HTML cleanup)
 *  5. Sorts posts newest-first by published_at
 *  6. Writes the result back to database/data/scraped-blogs.json
 *
 * After running this command the scraped-blogs.json contains production-ready
 * content and BlogSeeder no longer needs ContentReplacementService.
 */
class BlogPreprocessContent extends Command
{
    protected $signature = 'blog:preprocess-content';

    protected $description = 'Pre-process blog content: fill published dates, apply text transformations, sort newest-first.';

    public function __construct(
        protected ContentReplacementService $contentReplacement
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Loading source data...');

        $scrapedPath = database_path('data/scraped-blogs.json');
        $tsaInventoryPath = base_path('content-migration/tsa-blog-inventory.json');
        $wordpressXmlPath = base_path('documentation/00-business-requirement/WordPress.2026-01-17.xml');
        $mediaMappingPath = database_path('data/blog-media-mapping.json');

        // ── Load scraped posts ────────────────────────────────────────────────
        if (! file_exists($scrapedPath)) {
            $this->error("scraped-blogs.json not found at {$scrapedPath}");
            return self::FAILURE;
        }
        $posts = json_decode(file_get_contents($scrapedPath), true);
        $this->line('  Loaded '.count($posts).' posts from scraped-blogs.json');

        // ── Load TSA inventory (dates keyed by slug) ──────────────────────────
        $tsaDates = [];
        if (file_exists($tsaInventoryPath)) {
            $tsaInventory = json_decode(file_get_contents($tsaInventoryPath), true);
            foreach ($tsaInventory as $item) {
                $slug = $item['slug'] ?? '';
                if (! $slug) {
                    continue;
                }
                $date = $this->parseDateFromUrl($item['listing_url'] ?? '');
                if ($date) {
                    $tsaDates[$slug] = $date;
                }
            }
            $this->line('  Loaded '.count($tsaDates).' TSA dates from tsa-blog-inventory.json');
        } else {
            $this->warn('  tsa-blog-inventory.json not found – TSA dates will be empty');
        }

        // ── Load WordPress XML (dates + missing post content keyed by slug) ───
        $wpData = [];
        $missingToolboxPost = null;
        if (file_exists($wordpressXmlPath)) {
            $wpData = $this->parseWordpressXml($wordpressXmlPath);
            $this->line('  Loaded '.count($wpData).' toolbox posts from WordPress XML');

            $existingSlugs = array_column($posts, 'slug');
            if (! in_array('3-myths-about-great-sales-people-debunked', $existingSlugs, true)) {
                $missingToolboxPost = $wpData['3-myths-about-great-sales-people-debunked'] ?? null;
            }
        } else {
            $this->warn('  WordPress.2026-01-17.xml not found – toolbox dates will be empty');
        }

        // ── Load image URL mapping ────────────────────────────────────────────
        $imageUrlMap = [];
        if (file_exists($mediaMappingPath)) {
            $imageUrlMap = json_decode(file_get_contents($mediaMappingPath), true) ?? [];
            $this->line('  Loaded '.count($imageUrlMap).' image URL mappings');
        } else {
            $this->warn('  blog-media-mapping.json not found – image URLs will not be replaced');
        }

        // ── Add missing toolbox post ──────────────────────────────────────────
        if ($missingToolboxPost) {
            $posts[] = $missingToolboxPost;
            $this->line('  Added missing post: 3-myths-about-great-sales-people-debunked');
        }

        // ── Process each post ─────────────────────────────────────────────────
        $this->info('Processing '.count($posts).' posts...');
        $processed = 0;
        $datesAdded = 0;

        foreach ($posts as &$post) {
            $slug = $post['slug'] ?? '';
            $source = $post['source_site'] ?? '';

            // Fill published_at
            if (empty($post['published_at'])) {
                if ($source === 'tsa' && isset($tsaDates[$slug])) {
                    $post['published_at'] = $tsaDates[$slug];
                    $datesAdded++;
                } elseif ($source === 'toolbox' && isset($wpData[$slug]['published_at'])) {
                    $post['published_at'] = $wpData[$slug]['published_at'];
                    $datesAdded++;
                }
            }

            // Transform content
            if (! empty($post['content_html'])) {
                $post['content_html'] = $this->contentReplacement->cleanHtml(
                    $this->contentReplacement->processContent($post['content_html'], $imageUrlMap)
                );
            }

            // Transform excerpt
            if (! empty($post['excerpt'])) {
                $post['excerpt'] = $this->contentReplacement->cleanHtml(
                    $this->contentReplacement->processContent($post['excerpt'], [])
                );
            }

            // Transform title (brand names only)
            if (! empty($post['title'])) {
                $post['title'] = $this->contentReplacement->replaceBrandNames($post['title']);
            }

            $processed++;
        }
        unset($post);

        // ── Sort newest-first ─────────────────────────────────────────────────
        usort($posts, function ($a, $b) {
            $da = $a['published_at'] ?? '';
            $db = $b['published_at'] ?? '';
            return strcmp($db, $da); // descending
        });

        // ── Write output ──────────────────────────────────────────────────────
        file_put_contents($scrapedPath, json_encode($posts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $this->newLine();
        $this->info("Done. Processed {$processed} posts, filled {$datesAdded} dates.");
        $this->info('First post (newest): '.($posts[0]['slug'] ?? '(none)').' | '.($posts[0]['published_at'] ?? ''));
        $this->info('Last post (oldest):  '.($posts[count($posts) - 1]['slug'] ?? '(none)').' | '.($posts[count($posts) - 1]['published_at'] ?? ''));
        $this->info('Output: '.$scrapedPath);

        return self::SUCCESS;
    }

    // ── Source data helpers ───────────────────────────────────────────────────

    /**
     * Extract a YYYY-MM-DD date from a URL like
     * https://www.tsabusinessschool.co.za/2025/11/05/slug
     */
    protected function parseDateFromUrl(string $url): ?string
    {
        if (preg_match('#/(\d{4})/(\d{2})/(\d{2})/#', $url, $m)) {
            return "{$m[1]}-{$m[2]}-{$m[3]} 00:00:00";
        }

        return null;
    }

    /**
     * Parse WordPress export XML.
     * Returns an array keyed by post slug with:
     *   published_at, content_html, title, excerpt, listing_url
     *
     * Also builds the full data array for the missing "3-myths" post.
     */
    protected function parseWordpressXml(string $path): array
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($path);
        if (! $xml) {
            $this->warn('  Could not parse WordPress XML');
            return [];
        }

        $ns = $xml->getNamespaces(true);
        $wpNs      = $ns['wp']      ?? 'http://wordpress.org/export/1.2/';
        $contentNs = $ns['content'] ?? 'http://purl.org/rss/1.0/modules/content/';

        $result = [];

        foreach ($xml->channel->item as $item) {
            $wp      = $item->children($wpNs);
            $content = $item->children($contentNs);

            if ((string) $wp->post_type !== 'post' || (string) $wp->status !== 'publish') {
                continue;
            }

            $slug        = (string) $wp->post_name;
            $rawDate     = (string) $wp->post_date; // "2023-09-21 00:00:00"
            $title       = (string) $item->title;
            $link        = (string) $item->link;
            $contentHtml = (string) $content->encoded;

            // Excerpt: try excerpt:encoded namespace
            $excerptNs = $ns['excerpt'] ?? 'http://wordpress.org/export/1.2/excerpt/';
            $excerptData = $item->children($excerptNs);
            $excerpt = (string) ($excerptData->encoded ?? '');

            // Categories
            $categories = [];
            $tags = [];
            foreach ($item->category as $cat) {
                $domain = (string) $cat->attributes()->domain;
                $name   = (string) $cat;
                if ($domain === 'category') {
                    $categories[] = $name;
                } elseif ($domain === 'post_tag') {
                    $tags[] = $name;
                }
            }

            $result[$slug] = [
                'source_site'        => 'toolbox',
                'listing_url'        => $link,
                'slug'               => $slug,
                'title'              => $title,
                'excerpt'            => $excerpt,
                'published_at'       => $rawDate,
                'category'           => $categories[0] ?? '',
                'featured_image_url' => '',
                'content_html'       => $contentHtml,
                'content_length'     => strlen($contentHtml),
                'author'             => 'Eberhard Niklaus',
                'categories'         => $categories,
                'tags'               => $tags,
                'all_images'         => [],
            ];
        }

        return $result;
    }
}
