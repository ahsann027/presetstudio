<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'before_url', 'after_url', 'caption', 'sort_order'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getBeforeSrcAttribute(): string
    {
        return self::resolve($this->before_url);
    }

    public function getAfterSrcAttribute(): string
    {
        return self::resolve($this->after_url);
    }

    private static function resolve(string $url): string
    {
        return Str::startsWith($url, ['http://', 'https://']) ? $url : asset($url);
    }
}
