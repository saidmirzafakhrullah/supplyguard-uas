<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SentimentWord extends Model
{
    /**
     * Kolom yang boleh diisi.
     */
    protected $fillable = [
        'word',
        'type',
        'category',
        'weight',
        'meaning',
        'status',
    ];

    /**
     * Tipe data kolom.
     */
    protected function casts(): array
    {
        return [
            'weight' => 'integer',
        ];
    }
}