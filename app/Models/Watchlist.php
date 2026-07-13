<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Watchlist extends Model
{
    /**
     * Kolom yang boleh diisi melalui create atau update.
     */
    protected $fillable = [
        'user_id',
        'country_code',
        'country_name',
    ];

    /**
     * Setiap daftar favorit dimiliki oleh satu pengguna.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}