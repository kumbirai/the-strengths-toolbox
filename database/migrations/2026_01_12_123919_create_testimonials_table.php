<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company')->nullable();
            $table->text('testimonial');
            $table->tinyInteger('rating')->nullable()->unsigned();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();

            $table->index(['is_featured', 'display_order']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
