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
        'poster_path',
        'individual_price',
        'corporate_price',
        'max_capacity',
        'max_corporate_tables',
        'max_corporate_attendees',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'is_active' => 'boolean',
        'max_capacity' => 'integer',
        'max_corporate_tables' => 'integer',
        'individual_price' => 'decimal:2',
        'corporate_price' => 'decimal:2',
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
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

    public function paidAttendeesCount(?int $ignoreTicketId = null): int
    {
        $query = $this->tickets()->where('status', 'paid');

        if ($ignoreTicketId !== null) {
            $query->where('id', '!=', $ignoreTicketId);
        }

        return (int) $query->sum('number_of_attendees');
    }

    public function pendingAttendeesCount(?int $ignoreTicketId = null): int
    {
        $query = $this->tickets()
            ->where('status', 'pending');

        if ($ignoreTicketId !== null) {
            $query->where('id', '!=', $ignoreTicketId);
        }

        return (int) $query->sum('number_of_attendees');
    }

    public function remainingCapacity(bool $includePending = true, ?int $ignoreTicketId = null): ?int
    {
        if (!$this->max_capacity) {
            return null;
        }

        $used = $this->paidAttendeesCount($ignoreTicketId);

        if ($includePending) {
            $used += $this->pendingAttendeesCount($ignoreTicketId);
        }

        return max(0, (int) $this->max_capacity - $used);
    }

    public function hasCapacityFor(int $requestedAttendees = 1, bool $includePending = true, ?int $ignoreTicketId = null): bool
    {
        if ($requestedAttendees < 1) {
            return false;
        }

        if (!$this->max_capacity) {
            return true;
        }

        $remaining = $this->remainingCapacity($includePending, $ignoreTicketId);

        return $remaining !== null && $remaining >= $requestedAttendees;
    }

    public function isSoldOut(bool $includePending = true): bool
    {
        if (!$this->max_capacity) {
            return false;
        }

        return !$this->hasCapacityFor(1, $includePending);
    }

    public function paidCorporateTablesCount(?int $ignoreTicketId = null): int
    {
        return $this->corporateTablesCount('paid', $ignoreTicketId);
    }

    public function pendingCorporateTablesCount(?int $ignoreTicketId = null): int
    {
        return $this->corporateTablesCount('pending', $ignoreTicketId);
    }

    public function remainingCorporateTables(bool $includePending = true, ?int $ignoreTicketId = null): ?int
    {
        if (!$this->max_corporate_tables) {
            return null;
        }

        $used = $this->paidCorporateTablesCount($ignoreTicketId);

        if ($includePending) {
            $used += $this->pendingCorporateTablesCount($ignoreTicketId);
        }

        return max(0, (int) $this->max_corporate_tables - $used);
    }

    public function hasCorporateTableCapacity(bool $includePending = true, ?int $ignoreTicketId = null): bool
    {
        if (!$this->max_corporate_tables) {
            return true;
        }

        $remaining = $this->remainingCorporateTables($includePending, $ignoreTicketId);

        return $remaining !== null && $remaining >= 1;
    }

    public function isCorporateSoldOut(bool $includePending = true): bool
    {
        if (!$this->max_corporate_tables) {
            return false;
        }

        return !$this->hasCorporateTableCapacity($includePending);
    }

    private function corporateTablesCount(string $status, ?int $ignoreTicketId = null): int
    {
        $query = $this->tickets()
            ->where('type', 'corporate')
            ->where('status', $status);

        if ($ignoreTicketId !== null) {
            $query->where('id', '!=', $ignoreTicketId);
        }

        return (int) $query
            ->get(['id', 'corporate_booking_ref'])
            ->map(static function ($ticket): string {
                if (!empty($ticket->corporate_booking_ref)) {
                    return (string) $ticket->corporate_booking_ref;
                }

                return 'ticket-' . (string) $ticket->id;
            })
            ->unique()
            ->count();
    }
}
