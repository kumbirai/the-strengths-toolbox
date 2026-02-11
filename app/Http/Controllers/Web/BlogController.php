<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Tag;
use App\Services\BlogPostService;
use App\Services\SEOService;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    protected BlogPostService $blogPostService;

    protected SEOService $seoService;

    public function __construct(BlogPostService $blogPostService, SEOService $seoService)
    {
        $this->blogPostService = $blogPostService;
        $this->seoService = $seoService;
    }

    /**
     * Display blog listing page
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        try {
            $posts = $this->blogPostService->getPublishedPaginated(10);

            $categories = Category::all();
            $tags = Tag::all();

            $seo = $this->seoService->getDefaultMeta();
            $seo['title'] = 'Blog - The Strengths Toolbox';
            $seo['description'] = 'Read articles about strengths-based development, team building, sales courses, and leadership insights from The Strengths Toolbox.';
            $seo['keywords'] = 'blog, articles, strengths development, team building, sales courses';
            $seo['canonical'] = route('blog.index');
            $seo['og_url'] = route('blog.index');

            return view('blog.index', compact('posts', 'categories', 'tags', 'seo'));
        } catch (\Exception $e) {
            \Log::error('Blog index page error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            abort(500, 'Unable to load blog posts. Please try again later.');
        }
    }

    /**
     * Display a single blog post
     *
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function show(string $slug)
    {
        try {
            $post = $this->blogPostService->getPublishedBySlug($slug);

            if (! $post) {
                abort(404, 'Blog post not found.');
            }

            // Get related posts
            $relatedPosts = $this->blogPostService->getRelated($post, 3);

            // Get approved comments (top-level only, replies loaded via relationship)
            $comments = $post->approvedComments()->with(['approvedReplies' => function ($query) {
                $query->orderBy('created_at', 'asc');
            }])->get();

            // Get SEO metadata
            $seo = $this->seoService->getBlogPostMeta($post);

            return view('blog.show', compact('post', 'relatedPosts', 'comments', 'seo'));
        } catch (\Exception $e) {
            \Log::error('Blog post show error', [
                'slug' => $slug,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            abort(500, 'Unable to load blog post. Please try again later.');
        }
    }

    /**
     * Display posts by category
     *
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function category(string $slug)
    {
        try {
            $category = Category::where('slug', $slug)->first();

            if (! $category) {
                abort(404, 'Category not found.');
            }

            $posts = $this->blogPostService->getByCategory($slug, 10);

            if (! $posts) {
                $posts = \Illuminate\Pagination\LengthAwarePaginator::make([], 0, 10);
            }

            $seo = $this->seoService->getDefaultMeta();
            $seo['title'] = "{$category->name} - Blog - The Strengths Toolbox";
            $seo['description'] = "Browse blog posts in the {$category->name} category.";
            $seo['canonical'] = route('blog.category', $category->slug);
            $seo['og_url'] = route('blog.category', $category->slug);

            return view('blog.category', compact('category', 'posts', 'seo'));
        } catch (\Exception $e) {
            \Log::error('Blog category page error', [
                'slug' => $slug,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            abort(500, 'Unable to load category posts. Please try again later.');
        }
    }

    /**
     * Display posts by tag
     *
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function tag(string $slug)
    {
        try {
            $tag = Tag::where('slug', $slug)->first();

            if (! $tag) {
                abort(404, 'Tag not found.');
            }

            $posts = $this->blogPostService->getByTag($slug, 10);

            if (! $posts) {
                $posts = \Illuminate\Pagination\LengthAwarePaginator::make([], 0, 10);
            }

            $seo = $this->seoService->getDefaultMeta();
            $seo['title'] = "{$tag->name} - Blog - The Strengths Toolbox";
            $seo['description'] = "Browse blog posts tagged with {$tag->name}.";
            $seo['canonical'] = route('blog.tag', $tag->slug);
            $seo['og_url'] = route('blog.tag', $tag->slug);

            return view('blog.tag', compact('tag', 'posts', 'seo'));
        } catch (\Exception $e) {
            \Log::error('Blog tag page error', [
                'slug' => $slug,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            abort(500, 'Unable to load tag posts. Please try again later.');
        }
    }

    /**
     * Search blog posts
     *
     * @return \Illuminate\View\View
     */
    public function search(Request $request)
    {
        try {
            $query = trim($request->get('q', ''));

            if (empty($query) || strlen($query) < 2) {
                return redirect()->route('blog.index')
                    ->with('info', 'Please enter at least 2 characters to search.');
            }

            // Sanitize search query
            $query = strip_tags($query);
            $query = substr($query, 0, 100); // Limit length

            $posts = $this->blogPostService->search($query, 10);

            $seo = $this->seoService->getDefaultMeta();
            $seo['title'] = "Search: {$query} - Blog - The Strengths Toolbox";
            $seo['description'] = "Search results for: {$query}";
            $seo['canonical'] = route('blog.search', ['q' => $query]);
            $seo['og_url'] = route('blog.search', ['q' => $query]);
            $seo['robots'] = 'noindex, follow'; // Don't index search results

            return view('blog.search', compact('posts', 'query', 'seo'));
        } catch (\Exception $e) {
            \Log::error('Blog search error', [
                'query' => $request->get('q'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('blog.index')
                ->with('error', 'Search failed. Please try again later.');
        }
    }
}
