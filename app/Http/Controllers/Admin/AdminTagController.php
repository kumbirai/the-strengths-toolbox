<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminTagController extends Controller
{
    /**
     * Display a listing of tags
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $tags = Tag::withCount('blogPosts')->orderBy('name')->paginate(20);

        return view('admin.blog.tags.index', compact('tags'));
    }

    /**
     * Show the form for creating a new tag
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.blog.tags.create');
    }

    /**
     * Store a newly created tag
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tags,name',
            'slug' => 'nullable|string|max:255|unique:tags,slug',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        } else {
            $validated['slug'] = Str::slug($validated['slug']);
        }

        Tag::create($validated);

        return redirect()->route('admin.blog.tags.index')
            ->with('success', 'Tag created successfully.');
    }

    /**
     * Show the form for editing the specified tag
     *
     * @return \Illuminate\View\View
     */
    public function edit(Tag $tag)
    {
        return view('admin.blog.tags.edit', compact('tag'));
    }

    /**
     * Update the specified tag
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Tag $tag)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tags,name,'.$tag->id,
            'slug' => 'nullable|string|max:255|unique:tags,slug,'.$tag->id,
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        } else {
            $validated['slug'] = Str::slug($validated['slug']);
        }

        $tag->update($validated);

        return redirect()->route('admin.blog.tags.index')
            ->with('success', 'Tag updated successfully.');
    }

    /**
     * Remove the specified tag
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Tag $tag)
    {
        $tag->delete();

        return redirect()->route('admin.blog.tags.index')
            ->with('success', 'Tag deleted successfully.');
    }
}
