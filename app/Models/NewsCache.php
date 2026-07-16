<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsCache extends Model
{
    protected $table = 'news_cache';

    protected $fillable = [
        'country_code',
        'country_name',
        'title',
        'description',
        'url',
        'source_name',
        'category',
        'sentiment',
        'positive_words',
        'negative_words',
        'news_risk',
        'published_at',
        'fetched_at',
        'raw_data',
    ];

    protected $casts = [
        'positive_words' => 'integer',
        'negative_words' => 'integer',
        'news_risk' => 'integer',
        'published_at' => 'datetime',
        'fetched_at' => 'datetime',
        'raw_data' => 'array',
    ];
}