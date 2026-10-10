<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleImage extends Model
{
    protected $fillable = ['article_id', 'filename', 'alt_text', 'sort_order', 'is_primary'];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/articles/' . $this->filename);
    }

    public function getThumbnailUrlAttribute(): string
    {
        return asset('storage/articles/thumbs/' . $this->filename);
    }
}
