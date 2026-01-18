<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class SchemaService
{
    protected int $cacheTtl;

    public function __construct()
    {
        $this->cacheTtl = config('cache.ttl.schema', 86400); // 24 hours
    }

    /**
     * Generate Organization schema
     */
    public function getOrganizationSchema(): array
    {
        return Cache::remember(
            'schema.organization',
            $this->cacheTtl,
            function () {
                return [
                    '@context' => 'https://schema.org',
                    '@type' => 'Organization',
                    'name' => config('app.name', 'The Strengths Toolbox'),
                    'url' => config('app.url'),
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => asset('images/logo.png'),
                        'width' => 300,
                        'height' => 100,
                    ],
                    'description' => config('app.description', 'Build Strong Teams. Unlock Strong Profits.'),
                    'foundingDate' => '1995', // Update with actual founding date
                    'contactPoint' => [
                        '@type' => 'ContactPoint',
                        'telephone' => '+27-83-294-8033',
                        'contactType' => 'Customer Service',
                        'email' => config('mail.from.address', 'welcome@eberhardniklaus.co.za'),
                        'areaServed' => [
                            '@type' => 'Country',
                            'name' => 'South Africa',
                        ],
                        'availableLanguage' => ['English'],
                    ],
                    'address' => [
                        '@type' => 'PostalAddress',
                        'addressCountry' => 'ZA',
                        'addressRegion' => 'Gauteng', // Update with actual region
                    ],
                    'sameAs' => [
                        // Add social media profiles when available
                        // 'https://www.facebook.com/thestrengthstoolbox',
                        // 'https://www.linkedin.com/company/thestrengthstoolbox',
                        // 'https://twitter.com/strengthstoolbox',
                    ],
                    'founder' => [
                        '@type' => 'Person',
                        'name' => 'Eberhard Niklaus',
                    ],
                ];
            }
        );
    }

    /**
     * Generate WebSite schema
     */
    public function getWebSiteSchema(): array
    {
        return Cache::remember(
            'schema.website',
            $this->cacheTtl,
            function () {
                return [
                    '@context' => 'https://schema.org',
                    '@type' => 'WebSite',
                    'name' => config('app.name', 'The Strengths Toolbox'),
                    'url' => config('app.url'),
                    'description' => config('app.description', 'Build Strong Teams. Unlock Strong Profits.'),
                    'inLanguage' => config('app.locale', 'en-ZA'),
                    'publisher' => [
                        '@type' => 'Organization',
                        'name' => config('app.name', 'The Strengths Toolbox'),
                        'logo' => [
                            '@type' => 'ImageObject',
                            'url' => asset('images/logo.png'),
                        ],
                    ],
                    'potentialAction' => [
                        '@type' => 'SearchAction',
                        'target' => [
                            '@type' => 'EntryPoint',
                            'urlTemplate' => route('search').'?q={search_term_string}',
                        ],
                        'query-input' => 'required name=search_term_string',
                    ],
                ];
            }
        );
    }

    /**
     * Generate WebPage schema
     *
     * @param  object  $page
     */
    public function getWebPageSchema($page): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => $page->title ?? $page->name ?? '',
            'description' => $page->meta_description ?? $this->generateDescription($page->content ?? ''),
            'url' => $page->slug ? url('/'.$page->slug) : url()->current(),
            'inLanguage' => config('app.locale', 'en-ZA'),
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => config('app.name', 'The Strengths Toolbox'),
                'url' => config('app.url'),
            ],
            'datePublished' => isset($page->published_at) ? $page->published_at->toIso8601String() : null,
            'dateModified' => isset($page->updated_at) ? $page->updated_at->toIso8601String() : now()->toIso8601String(),
            'author' => [
                '@type' => 'Organization',
                'name' => config('app.name', 'The Strengths Toolbox'),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('app.name', 'The Strengths Toolbox'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('images/logo.png'),
                ],
            ],
        ];
    }

    /**
     * Generate Article schema
     *
     * @param  \App\Models\BlogPost  $post
     */
    public function getArticleSchema($post): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $post->title,
            'description' => $post->meta_description ?? $post->excerpt ?? $this->generateDescription($post->content),
            'url' => url('/blog/'.$post->slug),
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at->toIso8601String(),
            'author' => [
                '@type' => 'Person',
                'name' => $post->author->name ?? config('app.name'),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('app.name', 'The Strengths Toolbox'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('images/logo.png'),
                    'width' => 300,
                    'height' => 100,
                ],
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => url('/blog/'.$post->slug),
            ],
            'inLanguage' => config('app.locale', 'en-ZA'),
        ];

        // Add featured image
        if ($post->featured_image) {
            $schema['image'] = [
                '@type' => 'ImageObject',
                'url' => asset('storage/'.$post->featured_image),
            ];
        }

        // Add article section (category)
        if ($post->categories && $post->categories->isNotEmpty()) {
            $schema['articleSection'] = $post->categories->first()->name;
        }

        // Add keywords (tags)
        if ($post->tags && $post->tags->isNotEmpty()) {
            $schema['keywords'] = $post->tags->pluck('name')->join(', ');
        }

        return $schema;
    }

    /**
     * Generate BreadcrumbList schema
     *
     * @param  array  $items  Array of ['name' => string, 'url' => string]
     */
    public function getBreadcrumbSchema(array $items): array
    {
        $breadcrumbList = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [],
        ];

        foreach ($items as $position => $item) {
            $breadcrumbList['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => $position + 1,
                'name' => $item['name'],
                'item' => $item['url'] ?? null,
            ];
        }

        return $breadcrumbList;
    }

    /**
     * Generate description from content
     */
    protected function generateDescription(string $content, int $length = 160): string
    {
        $text = strip_tags($content);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length - 3).'...';
    }

    /**
     * Clear schema cache
     */
    public function clearCache(): void
    {
        Cache::forget('schema.organization');
        Cache::forget('schema.website');
    }
}
