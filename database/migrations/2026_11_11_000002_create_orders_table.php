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
            $table->string('order_number')->unique();            // contoh: INV-261111-AB12C (tampil ke pembeli)
            $table->string('access_token', 64)->unique();        // token acak untuk URL pesanan & unduhan
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('product_name');                      // salinan nama saat dibeli
            $table->string('buyer_name');
            $table->string('buyer_email');
            $table->decimal('amount', 12, 2);                    // total yang dibayar
            $table->string('status')->default('pending')->index(); // pending | paid | failed
            $table->string('payment_method')->nullable();        // qris, bank_transfer, gopay, dst.
            $table->string('snap_token')->nullable();            // token Midtrans Snap
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('download_expires_at')->nullable(); // batas berlaku link unduhan (N9)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
