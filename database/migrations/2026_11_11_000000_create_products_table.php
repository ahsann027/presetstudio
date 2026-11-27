<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->default(0);        // harga dalam Rupiah
            $table->string('category')->index();                // gaya preset: Cerah, Moody, Vintage, dst.
            $table->unsignedSmallInteger('preset_count')->default(1); // jumlah preset dalam paket
            $table->string('supported_apps')->nullable();       // contoh: Lightroom Mobile & Desktop
            $table->string('file_format')->nullable();          // contoh: DNG, XMP
            $table->string('file_size')->nullable();            // contoh: 18 MB
            $table->string('image_url')->nullable();            // foto sampul (URL atau path di public/)
            $table->text('install_guide')->nullable();          // panduan khusus; kosong = pakai panduan umum
            $table->string('download_url')->nullable();         // link file (Drive/dll) atau path di storage/app
            $table->boolean('is_active')->default(true);        // false = disembunyikan dari katalog
            $table->boolean('is_featured')->default(false);     // label "Terlaris"
            $table->boolean('is_new')->default(false);          // label "Baru"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
