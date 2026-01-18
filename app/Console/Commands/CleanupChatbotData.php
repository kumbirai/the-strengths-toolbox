<?php

namespace App\Console\Commands;

use App\Services\ChatbotService;
use Illuminate\Console\Command;

class CleanupChatbotData extends Command
{
    protected $signature = 'chatbot:cleanup 
                            {--days=90 : Number of days old to consider for archiving}
                            {--delete-messages : Delete old messages instead of archiving}';

    protected $description = 'Clean up old chatbot conversations and messages';

    protected ChatbotService $chatbotService;

    public function __construct(ChatbotService $chatbotService)
    {
        parent::__construct();
        $this->chatbotService = $chatbotService;
    }

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $deleteMessages = $this->option('delete-messages');

        $this->info("Cleaning up chatbot data older than {$days} days...");

        // Archive old conversations
        $archived = $this->chatbotService->archiveOldConversations($days);
        $this->info("Archived {$archived} conversations.");

        // Optionally delete old messages
        if ($deleteMessages) {
            $deleted = $this->chatbotService->cleanupOldMessages($days);
            $this->info("Deleted {$deleted} old messages.");
        }

        // Show statistics
        $stats = $this->chatbotService->getStorageStats();
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Conversations', $stats['total_conversations']],
                ['Active Conversations', $stats['active_conversations']],
                ['Total Messages', $stats['total_messages']],
                ['Total Tokens', $stats['total_tokens']],
            ]
        );

        $this->info('Cleanup completed successfully.');

        return Command::SUCCESS;
    }
}
