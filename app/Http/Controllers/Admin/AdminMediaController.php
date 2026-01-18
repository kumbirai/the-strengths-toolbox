<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Http\Request;

class AdminMediaController extends Controller
{
    protected MediaService $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    /**
     * Display a listing of media
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Media::query();

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('filename', 'like', "%{$search}%")
                    ->orWhere('original_filename', 'like', "%{$search}%")
                    ->orWhere('alt_text', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->has('type')) {
            $type = $request->get('type');
            if ($type === 'image') {
                $query->where('mime_type', 'like', 'image/%');
            } else {
                $query->where('mime_type', 'not like', 'image/%');
            }
        }

        $media = $query->orderBy('created_at', 'desc')->paginate(24);

        return view('admin.media.index', compact('media'));
    }

    /**
     * Upload media file
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'alt_text' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'directory' => 'nullable|string|max:255',
        ]);

        try {
            $file = $request->file('file');
            $options = [
                'alt_text' => $validated['alt_text'] ?? null,
                'description' => $validated['description'] ?? null,
                'directory' => $validated['directory'] ?? null,
                'max_width' => 2000,
                'max_height' => 2000,
                'create_thumbnail' => true,
            ];

            $media = $this->mediaService->upload($file, $options);

            return response()->json([
                'success' => true,
                'media' => $media,
                'location' => $media->url,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Show media details
     *
     * @return \Illuminate\View\View
     */
    public function show(Media $media)
    {
        return view('admin.media.show', compact('media'));
    }

    /**
     * Update media metadata
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Media $media)
    {
        $validated = $request->validate([
            'alt_text' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $media->update($validated);

        return redirect()->route('admin.media.show', $media)
            ->with('success', 'Media updated successfully.');
    }

    /**
     * Delete media
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Media $media)
    {
        try {
            $this->mediaService->delete($media);

            return redirect()->route('admin.media.index')
                ->with('success', 'Media deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete media: '.$e->getMessage()]);
        }
    }
}
