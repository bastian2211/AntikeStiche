<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'slug', 'title', 'title_en', 'content', 'content_en',
        'meta_title', 'meta_description',
        'is_active', 'show_in_footer', 'sort_order',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'show_in_footer' => 'boolean',
    ];

    public function getContentLocalizedAttribute(): string
    {
        $locale = app()->getLocale();
        return ($locale === 'en' && $this->content_en) ? $this->content_en : $this->content;
    }

    public function getTitleLocalizedAttribute(): string
    {
        $locale = app()->getLocale();
        return ($locale === 'en' && $this->title_en) ? $this->title_en : $this->title;
    }

    public static function getFooterPages()
    {
        return static::where('is_active', true)
            ->where('show_in_footer', true)
            ->orderBy('sort_order')
            ->get();
    }
}
