<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pages table - add indexes if they don't exist
        Schema::table('pages', function (Blueprint $table) {
            // Slug already has unique index, but ensure it exists
            if (! $this->hasIndex('pages', 'pages_slug_unique')) {
                $table->index('slug');
            }
            // is_published index (if not already in composite)
            if (! $this->hasIndex('pages', 'pages_is_published_index')) {
                $table->index('is_published');
            }
            // published_at index (if not already in composite)
            if (! $this->hasIndex('pages', 'pages_published_at_index')) {
                $table->index('published_at');
            }
        });

        // Blog posts table - add indexes if they don't exist
        Schema::table('blog_posts', function (Blueprint $table) {
            // Slug already has unique index, but ensure it exists
            if (! $this->hasIndex('blog_posts', 'blog_posts_slug_unique')) {
                $table->index('slug');
            }
            // is_published index (if not already in composite)
            if (! $this->hasIndex('blog_posts', 'blog_posts_is_published_index')) {
                $table->index('is_published');
            }
            // published_at index (if not already in composite)
            if (! $this->hasIndex('blog_posts', 'blog_posts_published_at_index')) {
                $table->index('published_at');
            }
        });

        // Chatbot messages table - add indexes if they don't exist
        Schema::table('chatbot_messages', function (Blueprint $table) {
            if (! $this->hasIndex('chatbot_messages', 'chatbot_messages_conversation_id_created_at_index')) {
                $table->index(['conversation_id', 'created_at']);
            }
            if (! $this->hasIndex('chatbot_messages', 'chatbot_messages_role_created_at_index')) {
                $table->index(['role', 'created_at']);
            }
            if (! $this->hasIndex('chatbot_messages', 'chatbot_messages_created_at_index')) {
                $table->index('created_at');
            }
        });

        // Chatbot conversations table - add indexes if they don't exist
        Schema::table('chatbot_conversations', function (Blueprint $table) {
            if (! $this->hasIndex('chatbot_conversations', 'chatbot_conversations_user_id_created_at_index')) {
                $table->index(['user_id', 'created_at']);
            }
            if (! $this->hasIndex('chatbot_conversations', 'chatbot_conversations_session_id_created_at_index')) {
                $table->index(['session_id', 'created_at']);
            }
            if (! $this->hasIndex('chatbot_conversations', 'chatbot_conversations_created_at_index')) {
                $table->index('created_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            if ($this->hasIndex('pages', 'pages_slug_index')) {
                $table->dropIndex(['slug']);
            }
            if ($this->hasIndex('pages', 'pages_is_published_index')) {
                $table->dropIndex(['is_published']);
            }
            if ($this->hasIndex('pages', 'pages_published_at_index')) {
                $table->dropIndex(['published_at']);
            }
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            if ($this->hasIndex('blog_posts', 'blog_posts_slug_index')) {
                $table->dropIndex(['slug']);
            }
            if ($this->hasIndex('blog_posts', 'blog_posts_is_published_index')) {
                $table->dropIndex(['is_published']);
            }
            if ($this->hasIndex('blog_posts', 'blog_posts_published_at_index')) {
                $table->dropIndex(['published_at']);
            }
        });

        Schema::table('chatbot_messages', function (Blueprint $table) {
            if ($this->hasIndex('chatbot_messages', 'chatbot_messages_conversation_id_created_at_index')) {
                $table->dropIndex(['conversation_id', 'created_at']);
            }
            if ($this->hasIndex('chatbot_messages', 'chatbot_messages_role_created_at_index')) {
                $table->dropIndex(['role', 'created_at']);
            }
            if ($this->hasIndex('chatbot_messages', 'chatbot_messages_created_at_index')) {
                $table->dropIndex(['created_at']);
            }
        });

        Schema::table('chatbot_conversations', function (Blueprint $table) {
            if ($this->hasIndex('chatbot_conversations', 'chatbot_conversations_user_id_created_at_index')) {
                $table->dropIndex(['user_id', 'created_at']);
            }
            if ($this->hasIndex('chatbot_conversations', 'chatbot_conversations_session_id_created_at_index')) {
                $table->dropIndex(['session_id', 'created_at']);
            }
            if ($this->hasIndex('chatbot_conversations', 'chatbot_conversations_created_at_index')) {
                $table->dropIndex(['created_at']);
            }
        });
    }

    /**
     * Check if an index exists on a table
     */
    protected function hasIndex(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite doesn't have information_schema, use pragma instead
            try {
                $indexes = DB::select("PRAGMA index_list({$table})");
                foreach ($indexes as $idx) {
                    if ($idx->name === $index) {
                        return true;
                    }
                }

                return false;
            } catch (\Exception $e) {
                return false;
            }
        }

        // For MySQL/PostgreSQL
        $databaseName = $connection->getDatabaseName();

        try {
            $result = DB::select(
                'SELECT COUNT(*) as count 
                 FROM information_schema.statistics 
                 WHERE table_schema = ? 
                 AND table_name = ? 
                 AND index_name = ?',
                [$databaseName, $table, $index]
            );

            return $result[0]->count > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
};
