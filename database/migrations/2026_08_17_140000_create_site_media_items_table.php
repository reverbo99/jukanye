<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_media_items', function (Blueprint $table) {
            $table->id();
            $table->string('slot', 64);
            $table->string('kind', 16);
            $table->string('image')->nullable();
            $table->string('youtube_url', 500)->nullable();
            $table->string('title_en')->nullable();
            $table->string('title_sw')->nullable();
            $table->string('caption_en', 500)->nullable();
            $table->string('caption_sw', 500)->nullable();
            $table->string('link', 500)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status', 16)->default('published');
            $table->timestamps();

            $table->index(['slot', 'status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_media_items');
    }
};
