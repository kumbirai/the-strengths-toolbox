<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\MediaService;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class DownloadGallupMedia extends Command
{
    protected $signature = 'media:download-gallup';

    protected $description = 'Download Gallup Certified badge and UnlockYourPotential video';

    protected MediaService $mediaService;

    protected ?int $userId = null;

    public function __construct(MediaService $mediaService)
    {
        parent::__construct();
        $this->mediaService = $mediaService;
    }

    public function handle(): int
    {
        $this->info('Downloading Gallup media assets...');
        $this->newLine();

        // Get user for media uploads
        try {
            $this->userId = User::where('role', 'admin')->orWhere('role', 'author')->first()?->id ?? User::first()?->id;
        } catch (\Exception $e) {
            $this->warn('Database not available. Files will be stored but not uploaded to media library.');
            $this->userId = null;
        }

        $mediaDir = storage_path('app/public/media');
        if (! is_dir($mediaDir)) {
            mkdir($mediaDir, 0755, true);
        }

        // Download Gallup Certified badge
        $this->info('Downloading Gallup Certified badge...');
        $badgeResult = $this->downloadBadge();
        if ($badgeResult['success']) {
            $this->info("✓ Badge downloaded: {$badgeResult['path']}");
        } else {
            $this->error("✗ Badge download failed: {$badgeResult['error']}");
        }

        $this->newLine();

        // Download UnlockYourPotential video
        $this->info('Downloading UnlockYourPotential video...');
        $videoResult = $this->downloadVideo();
        if ($videoResult['success']) {
            $this->info("✓ Video downloaded: {$videoResult['path']}");
        } else {
            $this->error("✗ Video download failed: {$videoResult['error']}");
        }

        $this->newLine();
        $this->info('✓ Media download complete!');

        return Command::SUCCESS;
    }

    protected function downloadBadge(): array
    {
        $url = 'https://www.thestrengthstoolbox.com/wp-content/uploads/2023/10/GallupCertified.png';
        $filename = 'gallup-certified.png';
        $relativePath = 'media/'.$filename;
        $fullPath = storage_path('app/public/'.$relativePath);

        // Check if already exists
        if (file_exists($fullPath)) {
            $this->line("  Badge already exists, skipping download");
            return $this->uploadToMediaLibrary($fullPath, $filename, 'image/png', 'Gallup Certified Strengths Coach');
        }

        try {
            $response = Http::timeout(60)->get($url);

            if (! $response->successful()) {
                return ['success' => false, 'error' => "HTTP {$response->status()}"];
            }

            $content = $response->body();
            $contentType = $response->header('Content-Type', '');

            // Verify it's an image
            if (! str_starts_with($contentType, 'image/')) {
                return ['success' => false, 'error' => 'Not an image'];
            }

            // Save file
            file_put_contents($fullPath, $content);

            return $this->uploadToMediaLibrary($fullPath, $filename, $contentType, 'Gallup Certified Strengths Coach');
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function downloadVideo(): array
    {
        $url = 'https://www.thestrengthstoolbox.com/wp-content/uploads/2023/08/UnlockYourPotential.mp4';
        $filename = 'unlock-your-potential.mp4';
        $relativePath = 'media/'.$filename;
        $fullPath = storage_path('app/public/'.$relativePath);

        // Check if already exists
        if (file_exists($fullPath)) {
            $this->line("  Video already exists, skipping download");
            return $this->uploadToMediaLibrary($fullPath, $filename, 'video/mp4', 'Unlock Your Potential Video');
        }

        try {
            $response = Http::timeout(300)->get($url); // 5 minute timeout for video

            if (! $response->successful()) {
                return ['success' => false, 'error' => "HTTP {$response->status()}"];
            }

            $content = $response->body();
            $contentType = $response->header('Content-Type', 'video/mp4');

            // Save file
            file_put_contents($fullPath, $content);

            return $this->uploadToMediaLibrary($fullPath, $filename, $contentType, 'Unlock Your Potential Video');
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function uploadToMediaLibrary(string $filePath, string $filename, string $mimeType, string $altText): array
    {
        if (! $this->userId) {
            return ['success' => true, 'path' => str_replace(storage_path('app/public/'), '', $filePath)];
        }

        try {
            // Check if media already exists
            $existingMedia = \App\Models\Media::where('filename', $filename)
                ->orWhere('original_filename', $filename)
                ->first();

            if ($existingMedia) {
                $this->line("  Media already exists in library (ID: {$existingMedia->id})");
                return ['success' => true, 'path' => $existingMedia->path, 'media_id' => $existingMedia->id];
            }

            // Create UploadedFile instance
            $uploadedFile = new UploadedFile(
                $filePath,
                $filename,
                $mimeType,
                null,
                true
            );

            $media = $this->mediaService->upload($uploadedFile, [
                'directory' => 'media',
                'uploaded_by' => $this->userId,
                'alt_text' => $altText,
            ]);

            return ['success' => true, 'path' => $media->path, 'media_id' => $media->id];
        } catch (\Exception $e) {
            $this->warn("  Warning: Could not upload to media library: {$e->getMessage()}");
            return ['success' => true, 'path' => str_replace(storage_path('app/public/'), '', $filePath)];
        }
    }
}
