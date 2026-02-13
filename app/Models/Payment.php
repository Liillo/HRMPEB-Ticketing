<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'ticket_id',
        'checkout_request_id',
        'merchant_request_id',
        'mpesa_receipt',
        'phone_number',
        'amount',
        'status',
        'response_description',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
