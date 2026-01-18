<?php

/**
 * Brand Name Replacement Script
 * Replaces TSA Business School references with The Strengths Toolbox
 *
 * Usage: php brand-replacement.php [--dry-run] [--path=content-extraction]
 */
$options = getopt('', ['dry-run', 'path::']);
$dryRun = isset($options['dry-run']);
$contentDir = $options['path'] ?? __DIR__.'/../content-extraction';

if (! is_dir($contentDir)) {
    echo "Error: Content directory not found: {$contentDir}\n";
    exit(1);
}

$replacements = [
    // Primary replacements
    'TSA Business School' => 'The Strengths Toolbox',
    'TSA Business' => 'The Strengths Toolbox',

    // Possessive forms
    "TSA Business School's" => "The Strengths Toolbox's",
    "TSA Business's" => "The Strengths Toolbox's",

    // Context-specific replacements
    'at TSA Business School' => 'at The Strengths Toolbox',
    'from TSA Business School' => 'from The Strengths Toolbox',
    'with TSA Business School' => 'with The Strengths Toolbox',
    'TSA Business School website' => 'The Strengths Toolbox website',

    // URL references
    'tsabusinessschool.co.za' => 'thestrengthstoolbox.com',
    'www.tsabusinessschool.co.za' => 'www.thestrengthstoolbox.com',
    'https://www.tsabusinessschool.co.za' => 'https://www.thestrengthstoolbox.com',
    'http://www.tsabusinessschool.co.za' => 'https://www.thestrengthstoolbox.com',
];

function replaceInFile($filePath, $replacements, $dryRun)
{
    if (! file_exists($filePath)) {
        return false;
    }

    $content = file_get_contents($filePath);
    $originalContent = $content;

    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }

    if ($content !== $originalContent) {
        if (! $dryRun) {
            file_put_contents($filePath, $content);
        }

        return true;
    }

    return false;
}

function processDirectory($dir, $replacements, $dryRun)
{
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir),
        RecursiveIteratorIterator::SELF_FIRST
    );

    $processed = 0;
    $skipped = 0;

    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() === 'md') {
            if (replaceInFile($file->getPathname(), $replacements, $dryRun)) {
                $processed++;
                $relativePath = str_replace($dir.'/', '', $file->getPathname());
                echo ($dryRun ? '[DRY RUN] Would process: ' : 'Processed: ').$relativePath."\n";
            } else {
                $skipped++;
            }
        }
    }

    return ['processed' => $processed, 'skipped' => $skipped];
}

// Run replacement
echo "Starting brand name replacement...\n";
if ($dryRun) {
    echo "[DRY RUN MODE - No files will be modified]\n";
}
echo "Scanning directory: {$contentDir}\n\n";

$result = processDirectory($contentDir, $replacements, $dryRun);

echo "\n";
echo "Completed!\n";
echo "Files processed: {$result['processed']}\n";
echo "Files skipped (no changes): {$result['skipped']}\n";

if ($dryRun) {
    echo "\nRun without --dry-run to apply changes.\n";
}
