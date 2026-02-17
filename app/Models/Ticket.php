<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id', 'uuid', 'type',
        'name', 'email', 'phone',
        'company_name', 'company_email', 'company_phone',
        'number_of_attendees', 'amount', 'status',
        'max_scans', 'scan_count', 'qr_code',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($ticket) {
            if (!$ticket->uuid) {
                $ticket->uuid = (string) Str::uuid();
            }
        });
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function scans()
    {
        return $this->hasMany(Scan::class);
    }
}