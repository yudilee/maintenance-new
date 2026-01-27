<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    protected $guarded = ['id'];
    
    protected $casts = [
        'sdp_stock' => 'float',
        'in_stock' => 'float',
        'rented' => 'float',
        'in_service' => 'float',
        'summary_json' => 'array',
    ];
}
