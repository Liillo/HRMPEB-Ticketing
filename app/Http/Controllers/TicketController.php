<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Payment;
use App\Services\MpesaService;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{
    protected $mpesaService;
    protected $ticketService;

    public function __construct(MpesaService $mpesaService, TicketService $ticketService)
    {
        $this->mpesaService = $mpesaService;
        $this->ticketService = $ticketService;
    }

    // Homepage - list events
    public function index()
    {
        $events = \App\Models\Event::where('is_active', true)
            ->orderBy('event_date', 'asc')
            ->get();
        return view('tickets.index', compact('events'));
    }

    // Show booking type selection
    public function bookingType($eventId)
    {
        $event = \App\Models\Event::findOrFail($eventId);
        return view('tickets.booking-type', compact('event'));
    }

    // Show individual booking form
    public function individualBooking($eventId)
    {
        $event = \App\Models\Event::findOrFail($eventId);
        return view('tickets.individual-booking', compact('event'));
    }

    // Show corporate booking form
    public function corporateBooking($eventId)
    {
        $event = \App\Models\Event::findOrFail($eventId);
        return view('tickets.corporate-booking', compact('event'));
    }

    // Store individual booking in SESSION
    public function storeIndividual(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
        ]);

        session([
            'booking_data' => [
                'event_id' => $request->event_id,
                'type' => 'individual',
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
            ]
        ]);

        return redirect()->route('payment.create');
    }

    // Store corporate booking in SESSION
    public function storeCorporate(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'company_name' => 'required|string|max:255',
            'company_email' => 'required|email|max:255',
            'company_phone' => 'required|string|max:20',
            'number_of_attendees' => 'required|integer|min:1|max:8',
        ]);

        session([
            'booking_data' => [
                'event_id' => $request->event_id,
                'type' => 'corporate',
                'company_name' => $request->company_name,
                'company_email' => $request->company_email,
                'company_phone' => $request->company_phone,
                'number_of_attendees' => $request->number_of_attendees,
            ]
        ]);

        return redirect()->route('payment.create');
    }

    // Create ticket and show payment page
    public function createPayment()
    {
        $bookingData = session('booking_data');
        
        if (!$bookingData) {
            return redirect()->route('home')->with('error', 'No booking data found. Please start again.');
        }

        $event = \App\Models\Event::findOrFail($bookingData['event_id']);

        // Create ticket NOW
        if ($bookingData['type'] === 'individual') {
            $ticket = Ticket::create([
                'event_id' => $event->id,
                'type' => 'individual',
                'name' => $bookingData['name'],
                'email' => $bookingData['email'],
                'phone' => $bookingData['phone'],
                'number_of_attendees' => 1,
                'amount' => $event->individual_price,
                'status' => 'pending',
                'max_scans' => 1,
            ]);
        } else {
            $ticket = Ticket::create([
                'event_id' => $event->id,
                'type' => 'corporate',
                'company_name' => $bookingData['company_name'],
                'company_email' => $bookingData['company_email'],
                'company_phone' => $bookingData['company_phone'],
                'number_of_attendees' => $bookingData['number_of_attendees'],
                'amount' => $event->corporate_price,
                'status' => 'pending',
                'max_scans' => $bookingData['number_of_attendees'],
            ]);
        }

        session()->forget('booking_data');

        return view('tickets.payment', compact('ticket'));
    }

    // Show payment page (for existing ticket)
    public function payment($uuid)
    {
        $ticket = Ticket::where('uuid', $uuid)->firstOrFail();
        return view('tickets.payment', compact('ticket'));
    }

    public function pendingPaymentForm()
    {
        return view('tickets.pending-payment');
    }

    public function pendingPayment(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
        ]);

        $pendingTicket = Ticket::where('status', 'pending')
            ->where(function ($query) use ($data) {
                $query->where('email', $data['email'])
                    ->orWhere('company_email', $data['email']);
            })
            ->where(function ($query) use ($data) {
                $query->where('phone', $data['phone'])
                    ->orWhere('company_phone', $data['phone']);
            })
            ->latest('updated_at')
            ->first();

        if ($pendingTicket) {
            return redirect()
                ->route('payment', $pendingTicket->uuid)
                ->with('success', 'Pending ticket found. Continue your payment.');
        }

        $paidTicket = Ticket::where('status', 'paid')
            ->where(function ($query) use ($data) {
                $query->where('email', $data['email'])
                    ->orWhere('company_email', $data['email']);
            })
            ->where(function ($query) use ($data) {
                $query->where('phone', $data['phone'])
                    ->orWhere('company_phone', $data['phone']);
            })
            ->latest('updated_at')
            ->first();

        if ($paidTicket) {
            return redirect()
                ->route('ticket.show', $paidTicket->uuid)
                ->with('success', 'Your ticket is already paid.');
        }

        return back()
            ->withInput()
            ->with('error', 'No pending ticket found for that email and phone number.');
    }

    // Initiate M-Pesa payment
    public function initiatePayment(Request $request, $uuid)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
        ]);

        $ticket = Ticket::where('uuid', $uuid)->firstOrFail();

        if ($ticket->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'This ticket has already been paid for.'
            ]);
        }

        try {
            Log::info('Initiating M-Pesa Payment', [
                'ticket_uuid' => $ticket->uuid,
                'amount' => $ticket->amount,
                'phone' => $request->phone
            ]);

            $response = $this->mpesaService->stkPush(
                
                $request ->phone,
                $ticket->amount,
                $ticket->uuid,
                'Ticket Payment - ' . $ticket->event->name
            );



            Log::info('M-Pesa STK Push Response', ['response' => $response]);

            if (isset($response['ResponseCode']) && $response['ResponseCode'] == '0') {
                Payment::create([
                    'ticket_id' => $ticket->id,
                    'checkout_request_id' => $response['CheckoutRequestID'],
                    'merchant_request_id' => $response['MerchantRequestID'],
                    'phone_number' => $request->phone,
                    'amount' => $ticket->amount,
                    'status' => 'pending',
                ]);

                Log::info('Payment Record Created', [
                    'ticket_id' => $ticket->id,
                    'checkout_request_id' => $response['CheckoutRequestID']
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment request sent. Please check your phone.'
                ]);
            } else {
                Log::error('M-Pesa STK Push Failed', ['response' => $response]);
                
                return response()->json([
                    'success' => false,
                    'message' => $response['errorMessage'] ?? 'Payment initiation failed. Please try again.'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Payment Initiation Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again.'
            ]);
        }
    }

    // Show waiting page
    public function waiting($uuid)
    {
        $ticket = Ticket::where('uuid', $uuid)->firstOrFail();
        return view('tickets.waiting', compact('ticket'));
    }

    // Check payment status (AJAX endpoint)
    public function checkPaymentStatus($uuid)
    {
        try {
            $ticket = Ticket::where('uuid', $uuid)->with('payment')->firstOrFail();
            $payment = $ticket->payment;
            
            Log::info('Checking Payment Status', [
                'ticket_uuid' => $uuid,
                'ticket_status' => $ticket->status,
                'payment_status' => $payment ? $payment->status : 'no_payment'
            ]);

            if ($ticket->status === 'paid') {
                Log::info('Payment Confirmed - Ticket Paid', ['ticket_uuid' => $uuid]);
                
                return response()->json([
                    'status' => 'paid',
                    'redirect' => route('payment.success', $ticket->uuid)
                ]);
            }

            if ($ticket->status === 'failed') {
                Log::info('Payment Failed', ['ticket_uuid' => $uuid]);
                
                return response()->json([
                    'status' => 'failed',
                    'redirect' => route('payment', $ticket->uuid)
                ]);
            }

            // Fallback: if callback is delayed/missed, query M-Pesa directly.
            if ($payment && $payment->status === 'pending' && $payment->checkout_request_id) {
                try {
                    $queryResponse = $this->mpesaService->queryTransaction($payment->checkout_request_id);

                    Log::info('M-Pesa Query Transaction Response', [
                        'ticket_uuid' => $uuid,
                        'checkout_request_id' => $payment->checkout_request_id,
                        'response' => $queryResponse
                    ]);

                    $resultCode = (string) ($queryResponse['ResultCode'] ?? '');
                    $resultDesc = $queryResponse['ResultDesc'] ?? 'No description';

                    if ($resultCode === '0') {
                        $payment->update([
                            'status' => 'success',
                            'response_description' => $resultDesc,
                        ]);

                        $ticket->update(['status' => 'paid']);

                        return response()->json([
                            'status' => 'paid',
                            'redirect' => route('payment.success', $ticket->uuid)
                        ]);
                    }

                    if (in_array($resultCode, ['1032', '1037', '2001'], true)) {
                        $payment->update([
                            'status' => 'failed',
                            'response_description' => $resultDesc,
                        ]);

                        $ticket->update(['status' => 'failed']);

                        return response()->json([
                            'status' => 'failed',
                            'redirect' => route('payment', $ticket->uuid)
                        ]);
                    }
                } catch (\Exception $queryException) {
                    Log::warning('M-Pesa Query Transaction Failed', [
                        'ticket_uuid' => $uuid,
                        'checkout_request_id' => $payment->checkout_request_id,
                        'error' => $queryException->getMessage()
                    ]);
                }
            }

            return response()->json([
                'status' => 'pending',
                'message' => 'Payment is being processed...'
            ]);

        } catch (\Exception $e) {
            Log::error('Error Checking Payment Status', [
                'ticket_uuid' => $uuid,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error checking payment status'
            ], 500);
        }
    }

    // Show success page
    public function success($uuid)
    {
        $ticket = Ticket::where('uuid', $uuid)->firstOrFail();

        if ($ticket->status !== 'paid') {
            return redirect()->route('payment', $ticket->uuid)
                ->with('error', 'Payment not completed yet.');
        }

        $qrPath = storage_path('app/public/qrcodes/' . $ticket->uuid . '.svg');
        if (!file_exists($qrPath)) {
            $this->ticketService->generateQrCode($ticket);
        }

        return view('tickets.success', compact('ticket'));
    }

    // Show ticket details
    public function show($uuid)
    {
        $ticket = Ticket::where('uuid', $uuid)->with('event')->firstOrFail();

        if ($ticket->status !== 'paid') {
            return redirect()->route('home')->with('error', 'This ticket has not been paid for.');
        }

        $qrPath = storage_path('app/public/qrcodes/' . $ticket->uuid . '.svg');
        if (!file_exists($qrPath)) {
            $this->ticketService->generateQrCode($ticket);
        }

        return view('tickets.show', compact('ticket'));
    }

    // Download ticket PDF
    public function download($uuid)
    {
        $ticket = Ticket::where('uuid', $uuid)->with('event')->firstOrFail();

        if ($ticket->status !== 'paid') {
            abort(403, 'This ticket has not been paid for.');
        }

        $pdf = $this->ticketService->generateTicketPdf($ticket);
        return $pdf->download('ticket-' . $ticket->uuid . '.pdf');
    }

    public function viewValidation($uuid)
    {
        $ticket = Ticket::where('uuid', $uuid)->with('event')->firstOrFail();
        return view('tickets.validate', compact('ticket'));
    }

    public function retrieveForm()
    {
        return view('tickets.retrieve');
    }

    public function retrieveTicket(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'resend_email' => 'nullable|boolean',
        ]);

        $phone = trim($data['phone']);

        $ticket = Ticket::with('event')
            ->where('status', 'paid')
            ->where(function ($query) use ($data) {
                $query->where('email', $data['email'])
                    ->orWhere('company_email', $data['email']);
            })
            ->where(function ($query) use ($phone) {
                $query->where('phone', $phone)
                    ->orWhere('company_phone', $phone);
            })
            ->latest('updated_at')
            ->first();

        if (!$ticket) {
            return back()
                ->withInput()
                ->with('error', 'No paid ticket matched the provided email and phone number.');
        }

        if ((bool) $request->boolean('resend_email')) {
            try {
                $this->ticketService->sendTicketEmail($ticket);
            } catch (\Throwable $e) {
                Log::error('Failed to resend ticket email from customer retrieve flow', [
                    'ticket_id' => $ticket->id,
                    'ticket_uuid' => $ticket->uuid,
                    'error' => $e->getMessage(),
                ]);

                return back()
                    ->withInput()
                    ->with('error', 'Ticket found, but email resend failed. Please try again.');
            }
        }

        return redirect()
            ->route('ticket.show', $ticket->uuid)
            ->with('hide_ticket_sent_notice', true)
            ->with('success', $request->boolean('resend_email')
                ? 'Ticket found. We have resent the ticket email.'
                : 'Ticket found successfully.');
    }
}
