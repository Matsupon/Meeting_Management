<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventAudit extends Model
{
    protected $fillable = ['event_id', 'user_id', 'action', 'old_values', 'new_values'];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
