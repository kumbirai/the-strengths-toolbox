<?php

namespace App\Console\Commands;

use App\Models\Media;
use Illuminate\Console\Command;

class UpdateImageAltText extends Command
{
    protected $signature = 'images:update-alt-text 
                            {--file=content-migration/images/image-mapping.json : Mapping file}';

    protected $description = 'Update alt text for images in media library';

    public function handle(): int
    {
        $mappingFile = base_path($this->option('file'));

        if (! file_exists($mappingFile)) {
            $this->error("Mapping file not found: {$mappingFile}");

            return Command::FAILURE;
        }

        $mapping = json_decode(file_get_contents($mappingFile), true);

        if (empty($mapping)) {
            $this->warn('Mapping file is empty or invalid');

            return Command::FAILURE;
        }

        $updated = 0;
        $notFound = 0;

        foreach ($mapping as $path => $data) {
            if (empty($data['alt_text'])) {
                continue;
            }

            $filename = $data['new_filename'] ?? basename($path);
            $media = Media::where('filename', $filename)->first();

            if ($media) {
                $media->alt_text = $data['alt_text'];
                $media->save();
                $updated++;
                $this->line("  ✓ Updated: {$filename}");
            } else {
                $notFound++;
                $this->warn("  ⊘ Not found: {$filename}");
            }
        }

        $this->newLine();
        $this->info("Updated {$updated} images");
        if ($notFound > 0) {
            $this->warn("Not found: {$notFound} images");
        }

        return Command::SUCCESS;
    }
}
