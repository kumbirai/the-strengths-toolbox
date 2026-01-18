<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Page;
use App\Models\PageSEO;
use App\Services\SEOService;
use Illuminate\Http\Request;

class AdminSEOController extends Controller
{
    protected SEOService $seoService;

    public function __construct(SEOService $seoService)
    {
        $this->seoService = $seoService;
    }

    /**
     * Display SEO dashboard
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $pages = Page::with('seo')->orderBy('title')->get();
        $posts = BlogPost::orderBy('title')->get();

        return view('admin.seo.index', compact('pages', 'posts'));
    }

    /**
     * Show form for editing page SEO
     *
     * @return \Illuminate\View\View
     */
    public function editPage(Page $page)
    {
        $seo = $page->seo ?? new PageSEO(['page_id' => $page->id]);

        return view('admin.seo.edit-page', compact('page', 'seo'));
    }

    /**
     * Update page SEO
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePage(Request $request, Page $page)
    {
        $validated = $request->validate([
            'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string|max:500',
            'og_image' => 'nullable|string|max:500',
            'twitter_card' => 'nullable|string|in:summary,summary_large_image',
            'twitter_title' => 'nullable|string|max:255',
            'twitter_description' => 'nullable|string|max:500',
            'twitter_image' => 'nullable|string|max:500',
            'canonical_url' => 'nullable|string|max:500',
            'schema_markup' => 'nullable|string',
        ]);

        // Handle schema_markup JSON
        if (isset($validated['schema_markup']) && ! empty($validated['schema_markup'])) {
            $decoded = json_decode($validated['schema_markup'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['schema_markup' => 'Invalid JSON format']);
            }
            $validated['schema_markup'] = $decoded;
        } else {
            unset($validated['schema_markup']);
        }

        $seo = $page->seo ?? new PageSEO(['page_id' => $page->id]);
        $seo->fill($validated);
        $seo->save();

        // Clear SEO cache
        $this->seoService->clearPageCache($page);

        return redirect()->route('admin.seo.index')
            ->with('success', 'SEO settings updated successfully.');
    }

    /**
     * Show form for editing blog post SEO
     *
     * @return \Illuminate\View\View
     */
    public function editPost(BlogPost $post)
    {
        return view('admin.seo.edit-post', compact('post'));
    }

    /**
     * Update blog post SEO
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePost(Request $request, BlogPost $post)
    {
        $validated = $request->validate([
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        $post->update($validated);

        // Clear SEO cache
        $this->seoService->clearBlogPostCache($post);

        return redirect()->route('admin.seo.index')
            ->with('success', 'SEO settings updated successfully.');
    }
}
