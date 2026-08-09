<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('write_locale', 10)->default('sw')->after('social');
            $table->string('translate_locale', 10)->default('en')->after('write_locale');
            $table->text('deepl_api_key')->nullable()->after('translate_locale');
            $table->string('deepl_api_plan', 10)->default('pro')->after('deepl_api_key');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'write_locale',
                'translate_locale',
                'deepl_api_key',
                'deepl_api_plan',
            ]);
        });
    }
};
