<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiskScore extends Model
{
    protected $fillable = [
        'country_code',
        'country_name',
        'weather_risk',
        'inflation_risk',
        'currency_risk',
        'news_risk',
        'port_risk',
        'total_risk',
        'category',
        'recommendation',
        'score_date',
        'source',
    ];

    protected $casts = [
        'weather_risk' => 'integer',
        'inflation_risk' => 'integer',
        'currency_risk' => 'integer',
        'news_risk' => 'integer',
        'port_risk' => 'integer',
        'total_risk' => 'decimal:2',
        'score_date' => 'date',
    ];
}