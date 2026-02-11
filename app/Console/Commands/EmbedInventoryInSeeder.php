<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Generate embedded inventory PHP code from localized inventory JSON
 * This helps populate the getEmbeddedInventory() method in BlogSeeder
 */
class EmbedInventoryInSeeder extends Command
{
    protected $signature = 'blog:embed-inventory
                            {--inventory=content-migration/tsa-blog-inventory.json : Path to localized inventory JSON file}
                            {--output= : Output file path (default: output to console)}';

    protected $description = 'Generate embedded PHP array code from localized inventory JSON for BlogSeeder';

    public function handle(): int
    {
        $inventoryPath = $this->option('inventory');
        $outputPath = $this->option('output');

        $fullInventoryPath = str_starts_with($inventoryPath, '/') ? $inventoryPath : base_path($inventoryPath);

        if (! file_exists($fullInventoryPath)) {
            $this->error("Inventory file not found: {$fullInventoryPath}");

            return Command::FAILURE;
        }

        $inventory = json_decode(file_get_contents($fullInventoryPath), true);
        if (! is_array($inventory)) {
            $this->error('Invalid inventory file format');

            return Command::FAILURE;
        }

        $this->info('Generating embedded inventory PHP code...');
        $this->line("Processing ".count($inventory).' posts');
        $this->newLine();

        // Generate PHP array code
        $phpCode = $this->generatePhpArray($inventory);

        if ($outputPath) {
            $fullOutputPath = str_starts_with($outputPath, '/') ? $outputPath : base_path($outputPath);
            file_put_contents($fullOutputPath, $phpCode);
            $this->info("✓ Generated PHP code written to: {$outputPath}");
        } else {
            $this->line('Generated PHP code:');
            $this->newLine();
            $this->line($phpCode);
            $this->newLine();
            $this->comment('Copy this code into the getEmbeddedInventory() method in BlogSeeder');
        }

        return Command::SUCCESS;
    }

    /**
     * Generate PHP array code from inventory data
     */
    protected function generatePhpArray(array $inventory): string
    {
        $lines = ['return ['];

        foreach ($inventory as $index => $item) {
            $lines[] = '    [';
            $lines[] = "        'slug' => ".$this->escapeString($item['slug'] ?? '').',';
            $lines[] = "        'title' => ".$this->escapeString($item['title'] ?? '').',';
            $lines[] = "        'excerpt' => ".$this->escapeString($item['excerpt'] ?? '').',';
            $lines[] = "        'published_at' => ".$this->escapeString($item['published_at'] ?? '').',';
            $lines[] = "        'featured_image_url' => ".$this->escapeString($item['featured_image_url'] ?? '').',';
            $lines[] = "        'content_html' => ".$this->escapeString($item['content_html'] ?? '').',';
            $lines[] = '    ],';
        }

        $lines[] = '];';

        return implode("\n", $lines);
    }

    /**
     * Escape string for PHP code
     */
    protected function escapeString(string $value): string
    {
        // For very long strings (like content_html), use heredoc
        if (strlen($value) > 500) {
            $hash = substr(md5($value), 0, 8);
            // Escape $ and { for heredoc
            $escaped = str_replace(['$', '{'], ['\\$', '\\{'], $value);
            return "<<<'HTML{$hash}'\n{$escaped}\nHTML{$hash}";
        }

        // Escape for single-quoted string
        $escaped = str_replace(['\\', "'"], ['\\\\', "\\'"], $value);

        return "'{$escaped}'";
    }
}
