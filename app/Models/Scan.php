<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Scan extends Model
{
    protected $fillable = [
        'ticket_id',
        'admin_id',
        'scanned_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
