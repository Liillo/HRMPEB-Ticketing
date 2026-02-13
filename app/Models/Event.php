<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $fillable = [
        'name',
        'description',
        'event_date',
        'location',
        'individual_price',
        'corporate_price',
        'max_corporate_attendees',
        'is_active',
    ];

    protected $casts = [
        'event_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function paidTickets(): HasMany
    {
        return $this->hasMany(Ticket::class)->where('status', 'paid');
    }

    public function scannedTicketsCount(): int
    {
        return $this->tickets()->where('scan_count', '>', 0)->count();
    }

    public function unscannedTicketsCount(): int
    {
        return $this->paidTickets()->where('scan_count', 0)->count();
    }
}
