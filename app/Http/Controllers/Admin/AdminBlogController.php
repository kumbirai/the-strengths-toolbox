<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Tag;
use App\Services\BlogPostService;
use Illuminate\Http\Request;

class AdminBlogController extends Controller
{
    protected BlogPostService $blogPostService;

    public function __construct(BlogPostService $blogPostService)
    {
        $this->blogPostService = $blogPostService;
    }

    /**
     * Display a listing of blog posts
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $filters = [
            'search' => $request->get('search'),
            'is_published' => $request->get('is_published'),
            'author_id' => $request->get('author_id'),
            'category_id' => $request->get('category_id'),
        ];

        $posts = $this->blogPostService->getPaginated(15, $filters);
        $categories = Category::all();

        return view('admin.blog.index', compact('posts', 'categories'));
    }

    /**
     * Show the form for creating a new blog post
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $categories = Category::all();
        $tags = Tag::all();

        return view('admin.blog.create', compact('categories', 'tags'));
    }

    /**
     * Store a newly created blog post
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts,slug',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'author_id' => 'required|exists:users,id',
            'published_at' => 'nullable|date',
            'is_published' => 'boolean',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
            'featured_image' => 'nullable|image|max:2048',
        ]);

        try {
            $post = $this->blogPostService->create($validated);

            return redirect()->route('admin.blog.index')
                ->with('success', 'Blog post created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create blog post: '.$e->getMessage()]);
        }
    }

    /**
     * Display the specified blog post
     *
     * @return \Illuminate\View\View
     */
    public function show(int $id)
    {
        $post = $this->blogPostService->getById($id);

        if (! $post) {
            abort(404);
        }

        return view('admin.blog.show', compact('post'));
    }

    /**
     * Show the form for editing the specified blog post
     *
     * @return \Illuminate\View\View
     */
    public function edit(int $id)
    {
        $post = $this->blogPostService->getById($id);

        if (! $post) {
            abort(404);
        }

        $categories = Category::all();
        $tags = Tag::all();

        return view('admin.blog.edit', compact('post', 'categories', 'tags'));
    }

    /**
     * Update the specified blog post
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts,slug,'.$id,
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'author_id' => 'required|exists:users,id',
            'published_at' => 'nullable|date',
            'is_published' => 'boolean',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
            'featured_image' => 'nullable|image|max:2048',
        ]);

        try {
            $post = $this->blogPostService->update($id, $validated);

            return redirect()->route('admin.blog.index')
                ->with('success', 'Blog post updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update blog post: '.$e->getMessage()]);
        }
    }

    /**
     * Remove the specified blog post
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(int $id)
    {
        try {
            $this->blogPostService->delete($id);

            return redirect()->route('admin.blog.index')
                ->with('success', 'Blog post deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete blog post: '.$e->getMessage()]);
        }
    }
}
