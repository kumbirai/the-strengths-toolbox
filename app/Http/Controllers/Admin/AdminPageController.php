<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PageService;
use App\Services\SEOService;
use Illuminate\Http\Request;

class AdminPageController extends Controller
{
    protected PageService $pageService;

    protected SEOService $seoService;

    public function __construct(PageService $pageService, SEOService $seoService)
    {
        $this->pageService = $pageService;
        $this->seoService = $seoService;
    }

    /**
     * Display a listing of pages
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $filters = [
            'search' => $request->get('search'),
            'is_published' => $request->get('is_published'),
        ];

        $pages = $this->pageService->getPaginated(15, $filters);

        return view('admin.pages.index', compact('pages'));
    }

    /**
     * Show the form for creating a new page
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.pages.create');
    }

    /**
     * Store a newly created page
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'is_published' => 'boolean',
        ]);

        try {
            $page = $this->pageService->create($validated);

            return redirect()->route('admin.pages.index')
                ->with('success', 'Page created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create page: '.$e->getMessage()]);
        }
    }

    /**
     * Display the specified page
     *
     * @return \Illuminate\View\View
     */
    public function show(int $id)
    {
        $page = $this->pageService->getById($id);

        if (! $page) {
            abort(404);
        }

        return view('admin.pages.show', compact('page'));
    }

    /**
     * Show the form for editing the specified page
     *
     * @return \Illuminate\View\View
     */
    public function edit(int $id)
    {
        $page = $this->pageService->getById($id);

        if (! $page) {
            abort(404);
        }

        return view('admin.pages.edit', compact('page'));
    }

    /**
     * Update the specified page
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug,'.$id,
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'is_published' => 'boolean',
        ]);

        try {
            $page = $this->pageService->update($id, $validated);

            return redirect()->route('admin.pages.index')
                ->with('success', 'Page updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update page: '.$e->getMessage()]);
        }
    }

    /**
     * Preview the specified page
     *
     * @return \Illuminate\View\View
     */
    public function preview(int $id)
    {
        $page = $this->pageService->getById($id);

        if (! $page) {
            abort(404);
        }

        return view('web.pages.show', compact('page'));
    }

    /**
     * Remove the specified page
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(int $id)
    {
        try {
            $this->pageService->delete($id);

            return redirect()->route('admin.pages.index')
                ->with('success', 'Page deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete page: '.$e->getMessage()]);
        }
    }
}
