<?php

namespace App\Console\Commands;

use App\Models\Page;
use Illuminate\Console\Command;

/**
 * One-off command to remove legacy Sales Training pages from the database.
 * Run after migrating to Sales Courses so existing environments stay consistent.
 */
class RemoveSalesTrainingPages extends Command
{
    protected $signature = 'content:remove-sales-training';

    protected $description = 'Remove legacy Sales Training pages from the database (parent and all children)';

    public function handle(): int
    {
        $this->info('Removing Sales Training pages...');

        $deleted = Page::where('slug', 'sales-training')
            ->orWhere('slug', 'like', 'sales-training/%')
            ->delete();

        $this->info("Deleted {$deleted} Sales Training page(s).");

        return Command::SUCCESS;
    }
}
