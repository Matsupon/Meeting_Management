<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'id',
        'title',
        'date',
        'end_date',
        'time',
        'location',
        'classification',
        'description',
        'status',
        'color',
        'day_overrides',
    ];

    protected $casts = [
        'day_overrides' => 'array',
    ];
    
    // Disable auto-incrementing since we use string IDs from the frontend
    public $incrementing = false;
    protected $keyType = 'string';
}
