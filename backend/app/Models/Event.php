<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'id',
        'title',
        'date',
        'time',
        'description',
        'status'
    ];
    
    // Disable auto-incrementing since we use string IDs from the frontend
    public $incrementing = false;
    protected $keyType = 'string';
}
