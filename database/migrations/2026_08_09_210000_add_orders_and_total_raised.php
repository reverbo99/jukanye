<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('site_settings', 'total_raised')) {
                $table->unsignedBigInteger('total_raised')->default(0)->after('donate_body_sw');
            }
            if (! Schema::hasColumn('site_settings', 'raised_currency')) {
                $table->string('raised_currency', 10)->default('TZS')->after('total_raised');
            }
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 32); // donation|ticket
            $table->foreignId('ticket_tier_id')->nullable()->constrained('ticket_tiers')->nullOnDelete();
            $table->unsignedBigInteger('amount');
            $table->string('currency', 10)->default('TZS');
            $table->string('status', 32)->default('pending'); // pending|paid|failed
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable()->index();
            $table->string('customer_phone')->nullable();
            $table->string('reference')->unique();
            $table->string('provider', 40)->default('flutterwave');
            $table->string('provider_txn_id')->nullable()->index();
            $table->string('payment_link')->nullable();
            $table->string('qr_payload')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');

        Schema::table('site_settings', function (Blueprint $table) {
            if (Schema::hasColumn('site_settings', 'raised_currency')) {
                $table->dropColumn('raised_currency');
            }
            if (Schema::hasColumn('site_settings', 'total_raised')) {
                $table->dropColumn('total_raised');
            }
        });
    }
};
