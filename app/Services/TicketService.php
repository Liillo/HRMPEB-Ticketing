<?php

namespace App\Services;

use App\Mail\TicketPurchased;
use App\Models\Ticket;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TicketService
{
    public function generateQrCode(Ticket $ticket)
    {
        $qrData = $ticket->uuid;
        
        $qrCode = QrCode::format('svg')
            ->size(300)
            ->margin(1)
            ->errorCorrection('H')
            ->generate($qrData);
        
        $filename = 'qrcodes/' . $ticket->uuid . '.svg';
        Storage::disk('public')->put($filename, $qrCode);

        if ($ticket->qr_code !== $ticket->uuid) {
            $ticket->update(['qr_code' => $ticket->uuid]);
        }
        
        return $filename;
    }

    public function generateTicketPdf(Ticket $ticket)
    {
        $ticket->loadMissing(['event', 'payment']);
        $payment = $this->resolveBookingPayment($ticket);

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('tickets.pdf', compact('ticket', 'payment'));
        $pdf->setPaper('a4', 'portrait');
        
        return $pdf;
    }

    public function sendTicketEmail(Ticket $ticket): bool
    {
        $email = $ticket->email ?: $ticket->company_email;

        if (!$email) {
            Log::warning('Cannot send ticket email: no recipient email', [
                'ticket_id' => $ticket->id,
                'ticket_uuid' => $ticket->uuid,
                'type' => $ticket->type,
            ]);

            return false;
        }

        Mail::to($email)->send(new TicketPurchased($ticket));

        return true;
    }

    public function fulfillPaidTicket(Ticket $ticket): void
    {
        $ticket->loadMissing('event');

        if ($ticket->type === 'corporate' && is_array($ticket->attendee_details) && count($ticket->attendee_details) > 0) {
            $this->fulfillCorporatePaidBooking($ticket);
            return;
        }

        $this->ensureQrExists($ticket);
        $this->sendTicketEmail($ticket);
    }

    private function fulfillCorporatePaidBooking(Ticket $ticket): void
    {
        $attendees = array_values(array_filter((array) $ticket->attendee_details, function ($attendee) {
            return !empty($attendee['name']) && !empty($attendee['email']) && !empty($attendee['phone']);
        }));

        if (count($attendees) === 0) {
            Log::warning('Corporate paid booking has no valid attendees; falling back to single ticket fulfillment.', [
                'ticket_id' => $ticket->id,
                'ticket_uuid' => $ticket->uuid,
            ]);

            $this->ensureQrExists($ticket);
            $this->sendTicketEmail($ticket);
            return;
        }

        $bookingAmount = (float) $ticket->amount;
        $supportsBookingRef = Schema::hasColumn('tickets', 'corporate_booking_ref');
        $bookingRef = $supportsBookingRef ? ($ticket->corporate_booking_ref ?: (string) Str::uuid()) : null;

        if ($supportsBookingRef && $bookingRef) {
            $existingAttendeeTickets = Ticket::where('corporate_booking_ref', $bookingRef)
                ->where('status', 'paid')
                ->count();

            if ($existingAttendeeTickets > 1) {
                $attendeeTickets = Ticket::where('corporate_booking_ref', $bookingRef)
                    ->where('status', 'paid')
                    ->orderBy('id')
                    ->get();

                foreach ($attendeeTickets as $attendeeTicket) {
                    $attendeeTicket->loadMissing('event');
                    $this->ensureQrExists($attendeeTicket);
                    $this->sendTicketEmail($attendeeTicket);
                }

                return;
            }
        }

        $generatedTicketIds = [];
        DB::transaction(function () use ($ticket, $attendees, $bookingAmount, $bookingRef, $supportsBookingRef, &$generatedTicketIds): void {
            $first = $attendees[0];

            $updateData = [
                'type' => 'corporate',
                'name' => $first['name'],
                'email' => $first['email'],
                'phone' => $first['phone'],
                'staff_no' => $first['staff_no'] ?? null,
                'ihrm_no' => $first['ihrm_no'] ?? null,
                'number_of_attendees' => 1,
                'attendee_details' => null,
                'max_scans' => 1,
                'scan_count' => min((int) $ticket->scan_count, 1),
                'amount' => $bookingAmount,
            ];

            if ($supportsBookingRef && $bookingRef) {
                $updateData['corporate_booking_ref'] = $bookingRef;
            }

            $ticket->update($updateData);
            $generatedTicketIds[] = (int) $ticket->id;

            for ($i = 1; $i < count($attendees); $i++) {
                $attendee = $attendees[$i];

                $newTicketData = [
                    'event_id' => $ticket->event_id,
                    'type' => 'corporate',
                    'name' => $attendee['name'],
                    'email' => $attendee['email'],
                    'phone' => $attendee['phone'],
                    'staff_no' => $attendee['staff_no'] ?? null,
                    'ihrm_no' => $attendee['ihrm_no'] ?? null,
                    'company_name' => $ticket->company_name,
                    'company_email' => $ticket->company_email,
                    'company_phone' => $ticket->company_phone,
                    'number_of_attendees' => 1,
                    'amount' => $bookingAmount,
                    'status' => 'paid',
                    'max_scans' => 1,
                    'scan_count' => 0,
                ];

                if ($supportsBookingRef && $bookingRef) {
                    $newTicketData['corporate_booking_ref'] = $bookingRef;
                }

                $newTicket = Ticket::create($newTicketData);
                $generatedTicketIds[] = (int) $newTicket->id;
            }
        });

        $attendeeTickets = Ticket::whereIn('id', $generatedTicketIds)
            ->orderBy('id')
            ->get();

        foreach ($attendeeTickets as $attendeeTicket) {
            $attendeeTicket->loadMissing('event');
            $this->ensureQrExists($attendeeTicket);
            $this->sendTicketEmail($attendeeTicket);
        }
    }

    private function ensureQrExists(Ticket $ticket): void
    {
        $qrFile = 'qrcodes/' . $ticket->uuid . '.svg';
        if (!Storage::disk('public')->exists($qrFile) || !$ticket->qr_code) {
            $this->generateQrCode($ticket);
        }
    }

    private function resolveBookingPayment(Ticket $ticket)
    {
        if ($ticket->payment) {
            return $ticket->payment;
        }

        if (!Schema::hasColumn('tickets', 'corporate_booking_ref') || !$ticket->corporate_booking_ref) {
            return null;
        }

        $paymentTicket = Ticket::with('payment')
            ->where('corporate_booking_ref', $ticket->corporate_booking_ref)
            ->whereHas('payment')
            ->first();

        return $paymentTicket?->payment;
    }
}
