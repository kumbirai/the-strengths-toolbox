<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminCategoryController extends Controller
{
    /**
     * Display a listing of categories
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $categories = Category::withCount('blogPosts')->orderBy('name')->paginate(20);

        return view('admin.blog.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.blog.categories.create');
    }

    /**
     * Store a newly created category
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'description' => 'nullable|string|max:1000',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        } else {
            $validated['slug'] = Str::slug($validated['slug']);
        }

        Category::create($validated);

        return redirect()->route('admin.blog.categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Show the form for editing the specified category
     *
     * @return \Illuminate\View\View
     */
    public function edit(Category $category)
    {
        return view('admin.blog.categories.edit', compact('category'));
    }

    /**
     * Update the specified category
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,'.$category->id,
            'slug' => 'nullable|string|max:255|unique:categories,slug,'.$category->id,
            'description' => 'nullable|string|max:1000',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        } else {
            $validated['slug'] = Str::slug($validated['slug']);
        }

        $category->update($validated);

        return redirect()->route('admin.blog.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified category
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Category $category)
    {
        if ($category->blogPosts()->count() > 0) {
            return redirect()->back()
                ->withErrors(['error' => 'Cannot delete category with associated blog posts.']);
        }

        $category->delete();

        return redirect()->route('admin.blog.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
