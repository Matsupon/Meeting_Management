<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'id',
        'user_id',
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
        'recurrence_rule',
        'recurrence_end',
    ];

    protected $casts = [
        'day_overrides' => 'array',
        'recurrence_end' => 'date',
    ];

    
    // Disable auto-incrementing since we use string IDs from the frontend
    public $incrementing = false;
    protected $keyType = 'string';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
