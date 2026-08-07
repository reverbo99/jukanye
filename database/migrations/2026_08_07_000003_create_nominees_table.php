<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nominees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('award_category_id')->constrained('award_categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('country')->nullable();
            $table->string('photo')->nullable();
            $table->text('bio_en')->nullable();
            $table->text('bio_sw')->nullable();
            $table->json('links')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status')->default('draft')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nominees');
    }
};
