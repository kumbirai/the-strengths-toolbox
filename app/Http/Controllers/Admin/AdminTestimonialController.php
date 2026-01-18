<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class AdminTestimonialController extends Controller
{
    /**
     * Display a listing of testimonials
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Testimonial::query();

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('testimonial', 'like', "%{$search}%");
            });
        }

        // Filter by featured
        if ($request->has('is_featured')) {
            $query->where('is_featured', $request->get('is_featured'));
        }

        $testimonials = $query->orderBy('display_order')->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.testimonials.index', compact('testimonials'));
    }

    /**
     * Show the form for creating a new testimonial
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.testimonials.create');
    }

    /**
     * Store a newly created testimonial
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'testimonial' => 'required|string',
            'rating' => 'nullable|integer|min:1|max:5',
            'user_id' => 'nullable|exists:users,id',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'display_order' => 'nullable|integer|min:0',
        ]);

        if (! isset($validated['display_order'])) {
            $validated['display_order'] = Testimonial::max('display_order') + 1;
        }

        Testimonial::create($validated);

        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Testimonial created successfully.');
    }

    /**
     * Display the specified testimonial
     *
     * @return \Illuminate\View\View
     */
    public function show(Testimonial $testimonial)
    {
        return view('admin.testimonials.show', compact('testimonial'));
    }

    /**
     * Show the form for editing the specified testimonial
     *
     * @return \Illuminate\View\View
     */
    public function edit(Testimonial $testimonial)
    {
        return view('admin.testimonials.edit', compact('testimonial'));
    }

    /**
     * Update the specified testimonial
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Testimonial $testimonial)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'testimonial' => 'required|string',
            'rating' => 'nullable|integer|min:1|max:5',
            'user_id' => 'nullable|exists:users,id',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $testimonial->update($validated);

        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Testimonial updated successfully.');
    }

    /**
     * Remove the specified testimonial
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();

        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Testimonial deleted successfully.');
    }
}
