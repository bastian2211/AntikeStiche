<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ebay_sync_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('article_id')->nullable();
            $table->string('ebay_item_id')->nullable();
            $table->enum('action', ['create', 'update', 'end_listing', 'stock_update', 'price_update']);
            $table->enum('direction', ['shop_to_ebay', 'ebay_to_shop']);
            $table->enum('status', ['success', 'error', 'pending']);
            $table->text('message')->nullable();
            $table->json('payload')->nullable()->comment('Gesendete/empfangene Daten');
            $table->timestamps();

            $table->foreign('article_id')->references('id')->on('articles')->nullOnDelete();
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ebay_sync_log');
    }
};
