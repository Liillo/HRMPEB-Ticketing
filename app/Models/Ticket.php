<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function scans(): HasMany
    {
        return $this->hasMany(Scan::class);
    }

    public function latestScan(): HasOne
    {
        return $this->hasOne(Scan::class)->latestOfMany('scanned_at');
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
