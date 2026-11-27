<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'price', 'category', 'preset_count', 'supported_apps',
        'file_format', 'file_size', 'image_url', 'install_guide', 'download_url',
        'is_active', 'is_featured', 'is_new',
    ];

    protected $casts = [
        'price'       => 'decimal:2',
        'is_active'   => 'boolean',
        'is_featured' => 'boolean',
        'is_new'      => 'boolean',
    ];

    /* ---------- Relasi ---------- */

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }

    /* ---------- Scopes ---------- */

    /** Hanya preset berstatus aktif (F3). */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Filter gaya preset; diabaikan jika kosong. */
    public function scopeCategory(Builder $query, ?string $category): Builder
    {
        return $query->when($category, fn ($q) => $q->where('category', $category));
    }

    /** Cari kata kunci pada nama, deskripsi, atau gaya; diabaikan jika kosong. */
    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        return $query->when($keyword, function ($q) use ($keyword) {
            $q->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%")
                  ->orWhere('category', 'like', "%{$keyword}%");
            });
        });
    }

    /* ---------- Accessor ---------- */

    /** 49000 -> "Rp 49.000" */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    /** Foto sampul: image_url, foto "sesudah" pertama, lalu satu fallback tetap. */
    public function getImageAttribute(): string
    {
        $url = $this->image_url ?: optional($this->images->first())->after_url;

        if (! $url) {
            return 'https://picsum.photos/id/1018/600/400';
        }

        return Str::startsWith($url, ['http://', 'https://']) ? $url : asset($url);
    }

    /** Data ringkas untuk fitur bandingkan produk (dibaca compare.js dari atribut data-product). */
    public function compareData(): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'category'     => $this->category,
            'price'        => $this->formatted_price,
            'price_raw'    => (int) $this->price,
            'preset_count' => (int) $this->preset_count,
            'apps'         => $this->supported_apps ?: '-',
            'format'       => $this->file_format ?: '-',
            'size'         => $this->file_size ?: '-',
            'image'        => $this->image,
            'url'          => route('catalog.show', $this->id),
            'buy_url'      => route('checkout.create', $this->id),
        ];
    }

    /** Pesan WhatsApp untuk memesan preset ini (alternatif checkout). */
    public function whatsappOrderMessage(): string
    {
        return implode("\n", [
            'Halo Kak, saya ingin memesan preset foto berikut:',
            "- Nama Preset: {$this->name}",
            "- Harga: {$this->formatted_price}",
            '- Link Produk: ' . route('catalog.show', $this->id),
            'Mohon info selanjutnya...',
        ]);
    }
}
