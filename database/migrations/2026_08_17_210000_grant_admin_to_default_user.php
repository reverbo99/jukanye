<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'is_admin')) {
            return;
        }

        DB::table('users')
            ->where('email', 'admin@jukanye.com')
            ->update(['is_admin' => true]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'is_admin')) {
            return;
        }

        DB::table('users')
            ->where('email', 'admin@jukanye.com')
            ->update(['is_admin' => false]);
    }
};
