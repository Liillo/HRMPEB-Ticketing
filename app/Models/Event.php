<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

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
        'event_date' => 'datetime',
        'is_active' => 'boolean',
        'individual_price' => 'decimal:2',
        'corporate_price' => 'decimal:2',
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function paidTickets()
    {
        return $this->hasMany(Ticket::class)->where('status', 'paid');
    }

    public function scannedTicketsCount()
    {
        return $this->paidTickets()->where('scan_count', '>', 0)->count();
    }

    public function unscannedTicketsCount()
    {
        return $this->paidTickets()->where('scan_count', 0)->count();
    }
}