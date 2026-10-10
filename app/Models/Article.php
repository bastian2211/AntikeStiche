<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Article extends Model
{
    protected $fillable = [
        'sku', 'title', 'title_en', 'description', 'description_en',
        'category_id', 'price', 'price_eur',
        'shipping_cost_de', 'shipping_cost_eu', 'shipping_cost_world',
        'condition', 'stock', 'status', 'primary_image',
        'ebay_item_id', 'ebay_sync_status', 'ebay_last_sync_at',
        'ebay_updated_at', 'ebay_active', 'view_count', 'sold_at',
    ];

    protected $casts = [
        'price'            => 'decimal:2',
        'price_eur'        => 'decimal:2',
        'shipping_cost_de' => 'decimal:2',
        'shipping_cost_eu' => 'decimal:2',
        'shipping_cost_world' => 'decimal:2',
        'ebay_active'      => 'boolean',
        'ebay_last_sync_at' => 'datetime',
        'ebay_updated_at'  => 'datetime',
        'sold_at'          => 'datetime',
    ];

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('stock', '>', 0);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->whereFullText(['title', 'description'], $term)
            ->orWhere('sku', 'like', "%{$term}%");
    }

    // ── Relationships ─────────────────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ArticleImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): ?ArticleImage
    {
        return $this->images()->where('is_primary', true)->first()
            ?? $this->images()->first();
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function isAvailable(): bool
    {
        return $this->status === 'active' && $this->stock > 0;
    }

    public function markAsSold(): void
    {
        $this->update([
            'status'  => 'sold',
            'stock'   => 0,
            'sold_at' => now(),
        ]);
    }

    public function getShippingCostForCountry(string $countryCode): float
    {
        if ($countryCode === 'DE') {
            return (float) $this->shipping_cost_de;
        }
        $euCountries = ['AT','BE','BG','CY','CZ','DK','EE','ES','FI','FR',
                        'GR','HR','HU','IE','IT','LT','LU','LV','MT','NL',
                        'PL','PT','RO','SE','SI','SK'];
        if (in_array($countryCode, $euCountries, true)) {
            return (float) $this->shipping_cost_eu;
        }
        return (float) $this->shipping_cost_world;
    }

    public static function generateSku(): string
    {
        do {
            $sku = 'ANT-' . strtoupper(Str::random(8));
        } while (static::where('sku', $sku)->exists());
        return $sku;
    }

    // ── Accessors ─────────────────────────────────────────────────────────

    public function getTitleLocalizedAttribute(): string
    {
        $locale = app()->getLocale();
        return ($locale === 'en' && $this->title_en) ? $this->title_en : $this->title;
    }

    public function getDescriptionLocalizedAttribute(): string
    {
        $locale = app()->getLocale();
        return ($locale === 'en' && $this->description_en) ? $this->description_en : $this->description;
    }
}
