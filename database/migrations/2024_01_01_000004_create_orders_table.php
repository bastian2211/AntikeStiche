<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->enum('source', ['shop', 'ebay'])->default('shop');
            $table->string('ebay_order_id')->nullable()->unique();
            // Käufer
            $table->string('buyer_name');
            $table->string('buyer_email');
            $table->string('buyer_phone')->nullable();
            // Lieferadresse
            $table->string('shipping_name');
            $table->string('shipping_street');
            $table->string('shipping_city');
            $table->string('shipping_zip');
            $table->string('shipping_country', 2)->comment('ISO 3166-1 alpha-2');
            $table->string('shipping_state')->nullable();
            // Beträge
            $table->decimal('subtotal', 10, 2);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('currency', 3)->default('EUR');
            $table->decimal('vat_rate', 5, 2)->default(0)->comment('MwSt-Satz des Landes');
            $table->decimal('vat_amount', 10, 2)->default(0);
            // Zahlung
            $table->enum('payment_method', ['paypal', 'stripe', 'bank_transfer'])->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->string('payment_reference')->nullable()->comment('PayPal/Stripe Transaktions-ID');
            $table->timestamp('paid_at')->nullable();
            // Status
            $table->enum('status', ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])->default('pending');
            $table->string('tracking_number')->nullable();
            $table->string('shipping_provider')->nullable()->comment('DHL, Deutsche Post etc.');
            $table->text('notes')->nullable()->comment('Interne Notizen');
            $table->text('compliance_hints')->nullable()->comment('Länder-spezifische Hinweise beim Bearbeiten');
            $table->timestamps();

            $table->index(['status', 'payment_status']);
            $table->index(['shipping_country']);
            $table->index(['source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
