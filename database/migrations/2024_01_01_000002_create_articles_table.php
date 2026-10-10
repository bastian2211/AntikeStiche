<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique()->comment('Eindeutige Artikelnummer');
            $table->string('title');
            $table->string('title_en')->nullable();
            $table->text('description');
            $table->text('description_en')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('price_eur', 10, 2)->nullable()->comment('Aktueller EUR-Preis');
            $table->decimal('shipping_cost_de', 10, 2)->default(0);
            $table->decimal('shipping_cost_eu', 10, 2)->default(0);
            $table->decimal('shipping_cost_world', 10, 2)->default(0);
            $table->string('condition')->default('Gebraucht')->comment('Zustand des Artikels');
            $table->integer('stock')->default(1)->comment('Bei Einzelstücken immer 1');
            $table->enum('status', ['active', 'sold', 'reserved', 'inactive'])->default('active');
            // eBay-Sync
            $table->string('ebay_item_id')->nullable()->unique();
            $table->enum('ebay_sync_status', ['synced', 'pending', 'error', 'not_listed'])->default('not_listed');
            $table->timestamp('ebay_last_sync_at')->nullable();
            $table->timestamp('ebay_updated_at')->nullable();
            $table->boolean('ebay_active')->default(false);
            // Timestamps & Tracking
            $table->string('primary_image')->nullable()->comment('Dateiname des Hauptbildes');
            $table->integer('view_count')->default(0);
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            $table->index(['status', 'category_id']);
            $table->index(['ebay_item_id']);
            $table->index(['ebay_sync_status']);
            $table->fullText(['title', 'description']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
