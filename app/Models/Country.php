<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = [
        'name',
        'official_name',
        'code',
        'capital',
        'region',
        'subregion',
        'population',
        'currency_code',
        'currency_name',
        'latitude',
        'longitude',
        'flag',
        'landlocked',
        'source',
        'last_synced_at',
    ];

    protected $casts = [
        'population' => 'integer',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'landlocked' => 'boolean',
        'last_synced_at' => 'datetime',
    ];
}