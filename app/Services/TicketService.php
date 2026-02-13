<?php

namespace App\Services;

use App\Models\Ticket;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

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
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('tickets.pdf', compact('ticket'));
        $pdf->setPaper('a4', 'portrait');
        
        return $pdf;
    }
}
