<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Port extends Model
{
    /**
     * Kolom yang boleh diisi.
     */
    protected $fillable = [
        'port_name',
        'country',
        'country_code',
        'city',
        'region',
        'latitude',
        'longitude',
        'status',
        'capacity',
        'congestion_level',
        'risk_level',
        'notes',
    ];

    /**
     * Tipe data kolom.
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }
}