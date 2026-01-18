<?php

/**
 * Contact Information Replacement Script
 * Updates contact information throughout content
 *
 * Usage: php contact-replacement.php [--dry-run] [--path=content-extraction]
 */
$options = getopt('', ['dry-run', 'path::']);
$dryRun = isset($options['dry-run']);
$contentDir = $options['path'] ?? __DIR__.'/../content-extraction';

if (! is_dir($contentDir)) {
    echo "Error: Content directory not found: {$contentDir}\n";
    exit(1);
}

// Old contact information patterns (various formats)
$oldContacts = [
    // Phone numbers (various formats)
    '/\+27\s*83\s*294\s*8033/i',
    '/083\s*294\s*8033/i',
    '/\(083\)\s*294-8033/i',
    '/0832948033/i',
    '/\+27832948033/i',

    // Email addresses
    '/info@tsabusinessschool\.co\.za/i',
    '/contact@tsabusinessschool\.co\.za/i',
    '/admin@tsabusinessschool\.co\.za/i',
];

// New contact information
$newPhone = '+27 83 294 8033';
$newEmail = 'welcome@eberhardniklaus.co.za';

function replaceContactInFile($filePath, $oldContacts, $newPhone, $newEmail, $dryRun)
{
    if (! file_exists($filePath)) {
        return false;
    }

    $content = file_get_contents($filePath);
    $originalContent = $content;

    // Replace phone numbers
    foreach ($oldContacts as $pattern) {
        if (preg_match('/phone|mobile|tel|call/i', $pattern) || preg_match('/\d+/', $pattern)) {
            $content = preg_replace($pattern, $newPhone, $content);
        }
    }

    // Replace email addresses
    $content = preg_replace('/[a-zA-Z0-9._%+-]+@tsabusinessschool\.co\.za/i', $newEmail, $content);

    if ($content !== $originalContent) {
        if (! $dryRun) {
            file_put_contents($filePath, $content);
        }

        return true;
    }

    return false;
}

function processDirectory($dir, $oldContacts, $newPhone, $newEmail, $dryRun)
{
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir),
        RecursiveIteratorIterator::SELF_FIRST
    );

    $processed = 0;
    $skipped = 0;

    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() === 'md') {
            if (replaceContactInFile($file->getPathname(), $oldContacts, $newPhone, $newEmail, $dryRun)) {
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
echo "Starting contact information replacement...\n";
if ($dryRun) {
    echo "[DRY RUN MODE - No files will be modified]\n";
}
echo "Scanning directory: {$contentDir}\n";
echo "New Phone: {$newPhone}\n";
echo "New Email: {$newEmail}\n\n";

$result = processDirectory($contentDir, $oldContacts, $newPhone, $newEmail, $dryRun);

echo "\n";
echo "Completed!\n";
echo "Files processed: {$result['processed']}\n";
echo "Files skipped (no changes): {$result['skipped']}\n";

if ($dryRun) {
    echo "\nRun without --dry-run to apply changes.\n";
}
