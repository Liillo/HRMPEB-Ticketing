<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Ticket extends Model
{
    protected $fillable = [
        'event_id',
        'uuid',
        'type',
        'name',
        'email',
        'phone',
        'company_name',
        'company_email',
        'company_phone',
        'number_of_attendees',
        'amount',
        'status',
        'qr_code',
        'scan_count',
        'max_scans',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($ticket) {
            $ticket->uuid = Str::uuid();
            // Keep qr_code aligned with UUID for scanner consistency.
            $ticket->qr_code = $ticket->uuid;
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function payment(): HasOne
    {
        // Always track the latest payment attempt for status polling.
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function scans(): HasMany
    {
        return $this->hasMany(Scan::class);
    }

    public function canBeScanned(): bool
    {
        return $this->status === 'paid' && $this->scan_count < $this->max_scans;
    }

    public function incrementScan(): void
    {
        $this->increment('scan_count');
    }
}
