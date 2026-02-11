<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;

class MediaService
{
    protected string $disk = 'public';

    protected string $basePath = 'media';

    /**
     * Upload a file
     */
    public function upload(UploadedFile $file, array $options = []): Media
    {
        $originalFilename = $file->getClientOriginalName();
        $filename = $this->generateFilename($originalFilename);
        $path = $this->storeFile($file, $filename, $options);

        $mediaData = [
            'filename' => $filename,
            'original_filename' => $originalFilename,
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'disk' => $this->disk,
            'uploaded_by' => $options['uploaded_by'] ?? auth()->id(),
            'alt_text' => $options['alt_text'] ?? null,
            'description' => $options['description'] ?? null,
        ];

        // If image, get dimensions and optimize
        if ($this->isImage($file)) {
            $imageData = $this->processImage($file, $path, $options);
            $mediaData = array_merge($mediaData, $imageData);
        }

        return Media::create($mediaData);
    }

    /**
     * Store file on disk
     */
    protected function storeFile(UploadedFile $file, string $filename, array $options): string
    {
        $directory = $options['directory'] ?? $this->basePath;
        $fullPath = $directory.'/'.$filename;

        $file->storeAs($directory, $filename, $this->disk);

        return $fullPath;
    }

    /**
     * Process image (resize, optimize, create thumbnails)
     */
    protected function processImage(UploadedFile $file, string $path, array $options): array
    {
        try {
            // Try Imagick first, fallback to GD
            if (extension_loaded('imagick')) {
                $manager = new ImageManager(new ImagickDriver);
            } else {
                $manager = new ImageManager(new GdDriver);
            }

            // Read from stored file path
            $storedPath = Storage::disk($this->disk)->path($path);
            $image = $manager->read($storedPath);
        } catch (\Exception $e) {
            // If Intervention Image fails, just get dimensions from file
            $imageInfo = getimagesize($file->getRealPath());

            return [
                'width' => $imageInfo[0] ?? null,
                'height' => $imageInfo[1] ?? null,
                'thumbnail_path' => null,
            ];
        }

        $width = $image->width();
        $height = $image->height();

        // Resize if max dimensions specified
        $maxWidth = $options['max_width'] ?? null;
        $maxHeight = $options['max_height'] ?? null;

        if ($maxWidth || $maxHeight) {
            $image->scale(
                width: $maxWidth ?? $width,
                height: $maxHeight ?? $height
            );
            $image->save(Storage::disk($this->disk)->path($path));
            $width = $image->width();
            $height = $image->height();
        }

        // Create thumbnail if requested
        $thumbnailPath = null;
        if ($options['create_thumbnail'] ?? false) {
            // Read image again for thumbnail to avoid modifying the main image
            $thumbnailImage = $manager->read(Storage::disk($this->disk)->path($path));
            $thumbnailPath = $this->createThumbnail($thumbnailImage, $path, $options);
        }

        return [
            'width' => $width,
            'height' => $height,
            'thumbnail_path' => $thumbnailPath,
        ];
    }

    /**
     * Create thumbnail from image
     *
     * @param  mixed  $image
     */
    protected function createThumbnail($image, string $originalPath, array $options): string
    {
        $thumbnailSize = $options['thumbnail_size'] ?? 300;
        $directory = dirname($originalPath);
        $filename = pathinfo($originalPath, PATHINFO_FILENAME);
        $extension = pathinfo($originalPath, PATHINFO_EXTENSION);

        $thumbnailFilename = $filename.'_thumb.'.$extension;
        $thumbnailPath = $directory.'/thumbnails/'.$thumbnailFilename;

        // Create thumbnails directory if it doesn't exist
        Storage::disk($this->disk)->makeDirectory($directory.'/thumbnails');

        // Create and save thumbnail - cover modifies in place
        $image->cover($thumbnailSize, $thumbnailSize);
        $image->save(Storage::disk($this->disk)->path($thumbnailPath));

        return $thumbnailPath;
    }

    /**
     * Delete media and associated files
     */
    public function delete(Media $media): bool
    {
        // Delete main file
        if (Storage::disk($media->disk)->exists($media->path)) {
            Storage::disk($media->disk)->delete($media->path);
        }

        // Delete thumbnail if exists
        if ($media->thumbnail_path && Storage::disk($media->disk)->exists($media->thumbnail_path)) {
            Storage::disk($media->disk)->delete($media->thumbnail_path);
        }

        return $media->delete();
    }

    /**
     * Generate unique filename
     */
    protected function generateFilename(string $originalFilename): string
    {
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        $name = pathinfo($originalFilename, PATHINFO_FILENAME);

        return time().'_'.Str::slug($name).'_'.Str::random(8).'.'.$extension;
    }

    /**
     * Check if file is an image
     */
    protected function isImage(UploadedFile $file): bool
    {
        return str_starts_with($file->getMimeType(), 'image/');
    }
}
