<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $fillable = [
        'base_currency', 'currency_code', 'rate', 'rate_date', 'source',
    ];

    protected $casts = [
        'rate' => 'decimal:8',
        'rate_date' => 'date',
    ];
}
