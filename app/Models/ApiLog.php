<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_name',
        'endpoint',
        'method',
        'feature',
        'status',
        'status_code',
        'response_time',
        'description',
        'error_message',
        'requested_at',
    ];

    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'response_time' => 'integer',
            'requested_at' => 'datetime',
        ];
    }
}