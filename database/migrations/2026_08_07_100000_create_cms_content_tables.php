<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('tagline_en')->nullable();
            $table->string('tagline_sw')->nullable();
            $table->string('date_label')->nullable();
            $table->string('location_label')->nullable();
            $table->dateTime('festival_starts_at')->nullable();
            $table->dateTime('countdown_at')->nullable();
            $table->string('donate_embed_url')->nullable();
            $table->text('donate_body_en')->nullable();
            $table->text('donate_body_sw')->nullable();
            $table->text('about_intro_en')->nullable();
            $table->text('about_intro_sw')->nullable();
            $table->text('download_text_en')->nullable();
            $table->text('download_text_sw')->nullable();
            $table->json('footer_contact')->nullable();
            $table->json('social')->nullable();
            $table->timestamps();
        });

        Schema::create('sponsors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo')->nullable();
            $table->string('url')->nullable();
            $table->string('tier')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status')->default('draft')->index();
            $table->timestamps();
        });

        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('role_en')->nullable();
            $table->string('role_sw')->nullable();
            $table->string('photo')->nullable();
            $table->text('bio_en')->nullable();
            $table->text('bio_sw')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status')->default('draft')->index();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name_en');
            $table->string('name_sw');
            $table->unsignedInteger('price')->default(0);
            $table->string('currency', 10)->default('TZS');
            $table->string('tagline_en')->nullable();
            $table->string('tagline_sw')->nullable();
            $table->text('description_en')->nullable();
            $table->text('description_sw')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status')->default('draft')->index();
            $table->timestamps();
        });

        Schema::create('home_sections', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // objective|activity|audience|cta
            $table->string('title_en');
            $table->string('title_sw');
            $table->text('body_en')->nullable();
            $table->text('body_sw')->nullable();
            $table->string('link')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status')->default('draft')->index();
            $table->timestamps();
        });

        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index(); // speaker|artist|hero|friend|exhibition
            $table->string('name');
            $table->string('subtitle_en')->nullable();
            $table->string('subtitle_sw')->nullable();
            $table->string('photo')->nullable();
            $table->text('bio_en')->nullable();
            $table->text('bio_sw')->nullable();
            $table->json('links')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status')->default('draft')->index();
            $table->timestamps();
        });

        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('form')->index(); // register|contact
            $table->string('email')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->string('name_en');
            $table->string('name_sw');
            $table->unsignedInteger('from_price')->default(0);
            $table->string('currency', 10)->default('TZS');
            $table->string('duration_en')->nullable();
            $table->string('duration_sw')->nullable();
            $table->string('image')->nullable();
            $table->text('description_en')->nullable();
            $table->text('description_sw')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status')->default('draft')->index();
            $table->timestamps();
        });

        Schema::create('ticket_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name_en');
            $table->string('name_sw');
            $table->unsignedInteger('price')->default(0);
            $table->string('currency', 10)->default('TZS');
            $table->text('description_en')->nullable();
            $table->text('description_sw')->nullable();
            $table->json('includes')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status')->default('draft')->index();
            $table->timestamps();
        });

        Schema::create('map_places', function (Blueprint $table) {
            $table->id();
            $table->string('name_en');
            $table->string('name_sw');
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->text('description_en')->nullable();
            $table->text('description_sw')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status')->default('draft')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('map_places');
        Schema::dropIfExists('ticket_tiers');
        Schema::dropIfExists('tours');
        Schema::dropIfExists('form_submissions');
        Schema::dropIfExists('people');
        Schema::dropIfExists('home_sections');
        Schema::dropIfExists('products');
        Schema::dropIfExists('team_members');
        Schema::dropIfExists('sponsors');
        Schema::dropIfExists('site_settings');
    }
};
