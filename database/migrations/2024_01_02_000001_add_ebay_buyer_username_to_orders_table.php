<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // eBay-Käufer-Username für Account-Deletion-Benachrichtigungen
            $table->string('ebay_buyer_username')->nullable()->after('ebay_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('ebay_buyer_username');
        });
    }
};
