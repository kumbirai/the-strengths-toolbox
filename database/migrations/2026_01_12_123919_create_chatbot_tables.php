<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('session_id')->unique();
            $table->text('context')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });

        Schema::create('chatbot_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('chatbot_conversations')->onDelete('cascade');
            $table->enum('role', ['user', 'assistant', 'system']);
            $table->text('message');
            $table->unsignedInteger('tokens_used')->nullable();
            $table->timestamp('created_at');

            $table->index('conversation_id');
            $table->index('created_at');
        });

        Schema::create('chatbot_configs', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->string('type')->default('string');
            $table->string('group')->default('general');
            $table->timestamps();

            $table->index('group');
        });

        Schema::create('chatbot_prompts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('template');
            $table->json('variables')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->integer('version')->default(1);
            $table->timestamps();

            $table->index(['is_active', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_prompts');
        Schema::dropIfExists('chatbot_configs');
        Schema::dropIfExists('chatbot_messages');
        Schema::dropIfExists('chatbot_conversations');
    }
};
