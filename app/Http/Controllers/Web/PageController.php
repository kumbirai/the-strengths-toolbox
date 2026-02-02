<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\PageService;
use App\Services\SEOService;
use Illuminate\Http\Request;

class PageController extends Controller
{
    protected PageService $pageService;

    protected SEOService $seoService;

    public function __construct(PageService $pageService, SEOService $seoService)
    {
        $this->pageService = $pageService;
        $this->seoService = $seoService;
    }

    /**
     * Display a page by slug
     *
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function show(Request $request, ?string $slug = null)
    {
        // Handle specific routes - always use custom views for these pages
        if ($request->routeIs('strengths-programme')) {
            // Always return custom view with SEO (ignore CMS pages for this route)
            $seo = $this->seoService->getDefaultMeta();
            $seo['title'] = 'Strengths Programme - The Strengths Toolbox';
            $seo['description'] = 'Unlock growth through the Power of Strengths. Discover proven programs for individuals, managers, salespeople, and teams.';
            $seo['canonical'] = route('strengths-programme');
            $seo['og_url'] = route('strengths-programme');

            return view('pages.strengths-programme', compact('seo'));
        }

        if ($request->routeIs('about-us')) {
            // Always return custom view with SEO (ignore CMS pages for this route)
            $seo = $this->seoService->getDefaultMeta();
            $seo['title'] = 'About Us - The Strengths Toolbox';
            $seo['description'] = 'Learn about The Strengths Toolbox and our 30+ years of experience helping teams build strength and drive profits through strengths-based development.';
            $seo['canonical'] = route('about-us');
            $seo['og_url'] = route('about-us');

            // Get Eberhard's image from media library (sales-courses or legacy sales-training filename)
            $eberhardImage = \App\Models\Media::where(function ($query) {
                $query->where('filename', 'like', '%sales-courses%')
                    ->orWhere('filename', 'like', '%sales-training%')
                    ->orWhere('original_filename', 'like', '%Sales-Courses%')
                    ->orWhere('original_filename', 'like', '%Sales-Training%');
            })->first();

            // Fallback to Eberhard_Pic if not found
            if (! $eberhardImage) {
                $eberhardImage = \App\Models\Media::where(function ($query) {
                    $query->where('filename', 'like', '%eberhard%')
                        ->orWhere('original_filename', 'like', '%eberhard%')
                        ->orWhere('alt_text', 'like', '%eberhard%');
                })->first();
            }

            // Get teamwork image for track record section
            $teamworkImage = \App\Models\Media::where(function ($query) {
                $query->where('filename', 'like', '%hands-with-support-gears%')
                    ->orWhere('filename', 'like', '%hands%support%');
            })->first();

            return view('pages.about-us', compact('seo', 'eberhardImage', 'teamworkImage'));
        }

        // Handle dynamic pages
        if ($slug) {
            $page = $this->pageService->getBySlug($slug);

            if (! $page) {
                abort(404);
            }

            // Get SEO metadata
            $seo = $this->seoService->getPageMeta($page);

            // No hero image from media for dynamic pages; Sales Courses use per-course images in content
            $pageImage = null;
            $response = response()->view('web.pages.show', compact('page', 'seo', 'pageImage'));

            // Set ETag based on page content and last modified
            $etag = md5($page->id.$page->updated_at->timestamp);
            $response->setEtag($etag);
            $response->setLastModified($page->updated_at);

            // Check if not modified
            if ($response->isNotModified($request)) {
                return $response;
            }

            return $response;
        }

        abort(404);
    }
}
