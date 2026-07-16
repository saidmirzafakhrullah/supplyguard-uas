<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    /**
     * Kolom yang boleh diisi.
     */
    protected $fillable = [
        'title',
        'category',
        'summary',
        'content',
        'source',
        'author',
        'status',
        'sentiment',
        'risk_level',
        'published_at',
    ];

    /**
     * Tipe data kolom.
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }
}