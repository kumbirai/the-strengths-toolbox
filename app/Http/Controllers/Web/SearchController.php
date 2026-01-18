<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\SearchService;
use App\Services\SEOService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    protected SearchService $searchService;

    protected SEOService $seoService;

    public function __construct(SearchService $searchService, SEOService $seoService)
    {
        $this->searchService = $searchService;
        $this->seoService = $seoService;
    }

    /**
     * Display search results
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = trim($request->get('q', ''));

        if (empty($query) || strlen($query) < 2) {
            $seo = $this->seoService->getDefaultMeta();
            $seo['title'] = 'Search - The Strengths Toolbox';
            $seo['description'] = 'Search pages and blog posts on The Strengths Toolbox website.';
            $seo['canonical'] = route('search', ['q' => $query]);
            $seo['og_url'] = route('search', ['q' => $query]);
            $seo['robots'] = 'noindex, follow'; // Don't index search results

            return view('search.index', [
                'query' => $query,
                'results' => [
                    'pages' => collect(),
                    'posts' => collect(),
                    'total' => 0,
                ],
                'seo' => $seo,
            ])->with('info', 'Please enter at least 2 characters to search.');
        }

        $results = $this->searchService->search($query, 10);

        $seo = $this->seoService->getDefaultMeta();
        $seo['title'] = $query ? "Search: {$query} - The Strengths Toolbox" : 'Search - The Strengths Toolbox';
        $seo['description'] = $query
            ? "Search results for '{$query}' on The Strengths Toolbox website."
            : 'Search pages and blog posts on The Strengths Toolbox website.';
        $seo['canonical'] = route('search', ['q' => $query]);
        $seo['og_url'] = route('search', ['q' => $query]);
        $seo['robots'] = 'noindex, follow'; // Don't index search results

        return view('search.index', [
            'query' => $query,
            'results' => $results,
            'seo' => $seo,
        ]);
    }
}
