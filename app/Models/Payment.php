<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const METHOD_MPESA = 'mpesa';
    public const METHOD_CHEQUE = 'cheque';

    protected $fillable = [
        'ticket_id',
        'method',
        'checkout_request_id',
        'merchant_request_id',
        'mpesa_receipt',
        'cheque_number',
        'bank_name',
        'cheque_date',
        'payer_name',
        'phone_number',
        'amount',
        'status',
        'response_description',
        'approved_by',
        'rejected_by',
    ];

    protected $casts = [
        'cheque_date' => 'date',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}
