<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\Payment;
use App\Services\MpesaService;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

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
        $events = Event::where('is_active', true)
            ->orderBy('event_date', 'asc')
            ->get();
        return view('tickets.index', compact('events'));
    }

    // Show booking type selection
    public function bookingType($eventId)
    {
        $event = Event::findOrFail($eventId);

        if ($event->isSoldOut()) {
            return redirect()->route('home')->with('error', 'This event is sold out.');
        }

        return view('tickets.booking-type', compact('event'));
    }

    // Show individual booking form
    public function individualBooking($eventId)
    {
        $event = Event::findOrFail($eventId);

        if ($event->isSoldOut()) {
            return redirect()->route('home')->with('error', 'This event is sold out.');
        }

        return view('tickets.individual-booking', compact('event'));
    }

    // Show corporate booking form
    public function corporateBooking($eventId)
    {
        $event = Event::findOrFail($eventId);

        if ($event->isSoldOut()) {
            return redirect()->route('home')->with('error', 'This event is sold out.');
        }

        if ($event->isCorporateSoldOut()) {
            return redirect()
                ->route('booking.type', $event->id)
                ->with('error', $this->buildCorporateCapacityErrorMessage($event));
        }

        return view('tickets.corporate-booking', compact('event'));
    }

    // Store individual booking in SESSION
    public function storeIndividual(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'staff_no' => 'nullable|string|max:100',
            'ihrm_no' => 'nullable|string|max:100',
        ]);

        $event = Event::findOrFail((int) $validated['event_id']);
        if (!$event->hasCapacityFor(1)) {
            return back()
                ->withInput()
                ->with('error', $this->buildCapacityErrorMessage($event, 1));
        }

        session([
            'booking_data' => [
                'event_id' => $validated['event_id'],
                'type' => 'individual',
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'staff_no' => $validated['staff_no'] ?? null,
                'ihrm_no' => $validated['ihrm_no'] ?? null,
            ]
        ]);

        return redirect()->route('payment.create');
    }

    // Store corporate booking in SESSION
    public function storeCorporate(Request $request)
    {
        $baseValidated = $request->validate([
            'event_id' => 'required|exists:events,id',
        ]);

        $event = Event::findOrFail((int) $baseValidated['event_id']);

        if (!$event->hasCorporateTableCapacity()) {
            return back()
                ->withInput()
                ->with('error', $this->buildCorporateCapacityErrorMessage($event));
        }

        $remainingSlots = $event->remainingCapacity();

        if ($remainingSlots !== null && $remainingSlots <= 0) {
            return back()
                ->withInput()
                ->with('error', 'This event is sold out.');
        }

        $maxAttendeesAllowed = $remainingSlots === null
            ? 10
            : min(10, $remainingSlots);

        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'company_name' => 'required|string|max:255',
            'company_email' => 'required|email|max:255',
            'company_phone' => 'required|string|max:20',
            'number_of_attendees' => 'required|integer|min:1|max:' . $maxAttendeesAllowed,
            'attendee_names' => 'required|array',
            'attendee_names.*' => 'required|string|max:255',
            'attendee_emails' => 'required|array',
            'attendee_emails.*' => 'required|email|max:255|distinct',
            'attendee_phones' => 'required|array',
            'attendee_phones.*' => 'required|string|max:20',
            'attendee_staff_nos' => 'nullable|array',
            'attendee_staff_nos.*' => 'nullable|string|max:100',
            'attendee_ihrm_nos' => 'nullable|array',
            'attendee_ihrm_nos.*' => 'nullable|string|max:100',
        ]);

        $attendeeCount = (int) $validated['number_of_attendees'];
        $names = $validated['attendee_names'] ?? [];
        $emails = $validated['attendee_emails'] ?? [];
        $phones = $validated['attendee_phones'] ?? [];
        $staffNos = array_values($validated['attendee_staff_nos'] ?? []);
        $ihrmNos = array_values($validated['attendee_ihrm_nos'] ?? []);

        if (count($staffNos) === 0) {
            $staffNos = array_fill(0, $attendeeCount, null);
        }

        if (count($ihrmNos) === 0) {
            $ihrmNos = array_fill(0, $attendeeCount, null);
        }

        if (
            count($names) !== $attendeeCount
            || count($emails) !== $attendeeCount
            || count($phones) !== $attendeeCount
            || count($staffNos) !== $attendeeCount
            || count($ihrmNos) !== $attendeeCount
        ) {
            return back()
                ->withInput()
                ->withErrors(['number_of_attendees' => 'Attendee details count must match selected number of attendees.']);
        }

        if (!$event->hasCapacityFor($attendeeCount)) {
            return back()
                ->withInput()
                ->with('error', $this->buildCapacityErrorMessage($event, $attendeeCount));
        }

        $attendees = [];
        for ($i = 0; $i < $attendeeCount; $i++) {
            $attendees[] = [
                'name' => trim((string) $names[$i]),
                'email' => trim((string) $emails[$i]),
                'phone' => trim((string) $phones[$i]),
                'staff_no' => trim((string) ($staffNos[$i] ?? '')) ?: null,
                'ihrm_no' => trim((string) ($ihrmNos[$i] ?? '')) ?: null,
            ];
        }

        session([
            'booking_data' => [
                'event_id' => $validated['event_id'],
                'type' => 'corporate',
                'company_name' => $validated['company_name'],
                'company_email' => $validated['company_email'],
                'company_phone' => $validated['company_phone'],
                'number_of_attendees' => $validated['number_of_attendees'],
                'attendees' => $attendees,
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

        $event = Event::findOrFail($bookingData['event_id']);
        $requestedAttendees = $this->getRequestedAttendeesFromBookingData($bookingData);

        if (!$event->hasCapacityFor($requestedAttendees)) {
            session()->forget('booking_data');

            return redirect()
                ->route('home')
                ->with('error', $this->buildCapacityErrorMessage($event, $requestedAttendees));
        }

        if (($bookingData['type'] ?? 'individual') === 'corporate' && !$event->hasCorporateTableCapacity()) {
            session()->forget('booking_data');

            return redirect()
                ->route('home')
                ->with('error', $this->buildCorporateCapacityErrorMessage($event));
        }

        // Create ticket NOW
        if ($bookingData['type'] === 'individual') {
            $ticket = Ticket::create([
                'event_id' => $event->id,
                'type' => 'individual',
                'name' => $bookingData['name'],
                'email' => $bookingData['email'],
                'phone' => $bookingData['phone'],
                'staff_no' => $bookingData['staff_no'] ?? null,
                'ihrm_no' => $bookingData['ihrm_no'] ?? null,
                'number_of_attendees' => 1,
                'amount' => $event->individual_price,
                'status' => 'pending',
                'max_scans' => 1,
            ]);
        } else {
            $bookingRef = (string) Str::uuid();
            $ticketData = [
                'event_id' => $event->id,
                'type' => 'corporate',
                'company_name' => $bookingData['company_name'],
                'company_email' => $bookingData['company_email'],
                'company_phone' => $bookingData['company_phone'],
                'number_of_attendees' => $bookingData['number_of_attendees'],
                'attendee_details' => $bookingData['attendees'] ?? [],
                'amount' => $event->corporate_price,
                'status' => 'pending',
                'max_scans' => 1,
            ];

            if (Schema::hasColumn('tickets', 'corporate_booking_ref')) {
                $ticketData['corporate_booking_ref'] = $bookingRef;
            }

            $ticket = Ticket::create($ticketData);
        }

        session()->forget('booking_data');

        return view('tickets.payment', compact('ticket'));
    }

    // Show payment page (for existing ticket)
    public function payment($uuid)
    {
        $ticket = Ticket::where('uuid', $uuid)->firstOrFail();

        if ($this->isPendingTicketExpired($ticket)) {
            $this->deleteExpiredPendingTicket($ticket);

            return redirect()
                ->route('payment.pending.form')
                ->with('error', 'This pending payment has expired after 24 hours. Please start the booking process again.');
        }

        return view('tickets.payment', compact('ticket'));
    }

    public function pendingPaymentForm()
    {
        $this->purgeExpiredPendingTickets();

        return view('tickets.pending-payment');
    }

    public function pendingPayment(Request $request)
    {
        $this->purgeExpiredPendingTickets();

        $data = $request->validate([
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
        ]);

        $pendingTicket = Ticket::where('status', 'pending')
            ->where('created_at', '>=', now()->subHours(24))
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

    // Initiate payment (M-Pesa or cheque)
    public function initiatePayment(Request $request, $uuid)
    {
        $validated = $request->validate([
            'method' => 'nullable|in:' . Payment::METHOD_MPESA . ',' . Payment::METHOD_CHEQUE,
            'phone' => 'nullable|string|max:20',
            'cheque_number' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:255',
            'cheque_date' => 'nullable|date',
            'payer_name' => 'nullable|string|max:255',
        ]);

        $ticket = Ticket::where('uuid', $uuid)->firstOrFail();

        if ($this->isPendingTicketExpired($ticket)) {
            $this->deleteExpiredPendingTicket($ticket);

            return response()->json([
                'success' => false,
                'message' => 'This pending payment expired after 24 hours. Please start the booking process again.'
            ], 410);
        }

        if ($ticket->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'This ticket has already been paid for.'
            ]);
        }

        $method = (string) ($validated['method'] ?? Payment::METHOD_MPESA);

        $event = Event::find($ticket->event_id);
        $hasAttendeeCapacity = $event && $event->hasCapacityFor((int) $ticket->number_of_attendees, true, (int) $ticket->id);
        $hasCorporateTableCapacity = $event
            && ($ticket->type !== 'corporate' || $event->hasCorporateTableCapacity(true, (int) $ticket->id));

        if (!$event || !$hasAttendeeCapacity || !$hasCorporateTableCapacity) {
            $message = 'This event is sold out.';

            if ($event) {
                $message = !$hasCorporateTableCapacity
                    ? $this->buildCorporateCapacityErrorMessage($event, true, (int) $ticket->id)
                    : $this->buildCapacityErrorMessage($event, (int) $ticket->number_of_attendees, true, (int) $ticket->id);
            }

            return response()->json([
                'success' => false,
                'message' => $message
            ], 409);
        }

        if ($method === Payment::METHOD_CHEQUE) {
            $chequeData = validator($validated, [
                'cheque_number' => 'required|string|max:100',
                'bank_name' => 'required|string|max:255',
                'cheque_date' => 'required|date',
                'payer_name' => 'required|string|max:255',
            ])->validate();

            Payment::updateOrCreate([
                'ticket_id' => $ticket->id,
            ], [
                'ticket_id' => $ticket->id,
                'method' => Payment::METHOD_CHEQUE,
                'checkout_request_id' => 'CHEQUE-' . (string) Str::uuid(),
                'merchant_request_id' => null,
                'mpesa_receipt' => null,
                'phone_number' => $ticket->phone ?: $ticket->company_phone,
                'amount' => $ticket->amount,
                'status' => 'pending',
                'cheque_number' => trim((string) $chequeData['cheque_number']),
                'bank_name' => trim((string) $chequeData['bank_name']),
                'cheque_date' => $chequeData['cheque_date'],
                'payer_name' => trim((string) $chequeData['payer_name']),
                'response_description' => 'Cheque submitted and awaiting admin verification.',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cheque details submitted. Payment is pending verification.',
                'redirect' => route('payment', $ticket->uuid),
            ]);
        }

        $mpesaPhone = trim((string) ($validated['phone'] ?? ''));
        if ($mpesaPhone === '') {
            return response()->json([
                'success' => false,
                'message' => 'M-Pesa phone number is required.'
            ], 422);
        }

        try {
            Log::info('Initiating M-Pesa Payment', [
                'ticket_uuid' => $ticket->uuid,
                'amount' => $ticket->amount,
                'phone' => $mpesaPhone
            ]);

            $response = $this->mpesaService->stkPush(
                $mpesaPhone,
                $ticket->amount,
                $ticket->uuid,
                'Ticket Payment - ' . $ticket->event->name
            );

            Log::info('M-Pesa STK Push Response', ['response' => $response]);

            if (isset($response['ResponseCode']) && $response['ResponseCode'] == '0') {
                Payment::updateOrCreate([
                    'ticket_id' => $ticket->id,
                ], [
                    'ticket_id' => $ticket->id,
                    'method' => Payment::METHOD_MPESA,
                    'checkout_request_id' => $response['CheckoutRequestID'],
                    'merchant_request_id' => $response['MerchantRequestID'],
                    'mpesa_receipt' => null,
                    'cheque_number' => null,
                    'bank_name' => null,
                    'cheque_date' => null,
                    'payer_name' => null,
                    'phone_number' => $mpesaPhone,
                    'amount' => $ticket->amount,
                    'status' => 'pending',
                ]);

                Log::info('Payment Record Created', [
                    'ticket_id' => $ticket->id,
                    'checkout_request_id' => $response['CheckoutRequestID']
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment request sent. Please check your phone.',
                    'redirect' => route('payment.waiting', $ticket->uuid),
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

        if ($this->isPendingTicketExpired($ticket)) {
            $this->deleteExpiredPendingTicket($ticket);

            return redirect()
                ->route('payment.pending.form')
                ->with('error', 'This pending payment has expired after 24 hours. Please start the booking process again.');
        }

        return view('tickets.waiting', compact('ticket'));
    }

    // Check payment status (AJAX endpoint)
    public function checkPaymentStatus($uuid)
    {
        try {
            $ticket = Ticket::where('uuid', $uuid)->with('payment')->firstOrFail();

            if ($this->isPendingTicketExpired($ticket)) {
                $this->deleteExpiredPendingTicket($ticket);

                return response()->json([
                    'status' => 'expired',
                    'message' => 'This pending payment expired after 24 hours. Please start the booking process again.',
                    'redirect' => route('payment.pending.form')
                ], 410);
            }

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
                    'redirect' => route('ticket.show', ['uuid' => $ticket->uuid, 'paid' => 1])
                ]);
            }

            if ($ticket->status === 'failed') {
                Log::info('Payment Failed', ['ticket_uuid' => $uuid]);
                
                return response()->json([
                    'status' => 'failed',
                    'redirect' => route('payment', $ticket->uuid)
                ]);
            }

            if ($payment && $payment->method === Payment::METHOD_CHEQUE && $payment->status === 'pending') {
                return response()->json([
                    'status' => 'pending',
                    'message' => 'Cheque payment submitted and awaiting admin verification.'
                ]);
            }

            // Fallback: if callback is delayed/missed, query M-Pesa directly.
            if (
                $payment
                && $payment->method === Payment::METHOD_MPESA
                && $payment->status === 'pending'
                && $payment->checkout_request_id
            ) {
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
                        $event = Event::find($ticket->event_id);
                        $hasAttendeeCapacity = $event && $event->hasCapacityFor((int) $ticket->number_of_attendees, true, (int) $ticket->id);
                        $hasCorporateTableCapacity = $event
                            && ($ticket->type !== 'corporate' || $event->hasCorporateTableCapacity(true, (int) $ticket->id));

                        if (!$event || !$hasAttendeeCapacity || !$hasCorporateTableCapacity) {
                            $message = 'This event is sold out.';

                            if ($event) {
                                $message = !$hasCorporateTableCapacity
                                    ? $this->buildCorporateCapacityErrorMessage($event, true, (int) $ticket->id)
                                    : $this->buildCapacityErrorMessage($event, (int) $ticket->number_of_attendees, true, (int) $ticket->id);
                            }

                            $payment->update([
                                'status' => 'failed',
                                'response_description' => $message,
                            ]);

                            $ticket->update(['status' => 'failed']);

                            return response()->json([
                                'status' => 'failed',
                                'redirect' => route('payment', $ticket->uuid),
                                'message' => $message,
                            ]);
                        }

                        $extractedReceipt = $this->extractMpesaReceiptFromQueryResponse($queryResponse);

                        $paymentUpdate = [
                            'status' => 'success',
                            'response_description' => $resultDesc,
                        ];

                        if ($extractedReceipt) {
                            $paymentUpdate['mpesa_receipt'] = $extractedReceipt;
                        }

                        $payment->update($paymentUpdate);

                        $ticket->update(['status' => 'paid']);
                        $this->ticketService->fulfillPaidTicket($ticket);

                        return response()->json([
                            'status' => 'paid',
                            'redirect' => route('ticket.show', ['uuid' => $ticket->uuid, 'paid' => 1])
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

    // Show ticket details
    public function show(Request $request, $uuid)
    {
        $ticket = Ticket::where('uuid', $uuid)->with(['event', 'payment'])->firstOrFail();

        if ($ticket->status !== 'paid') {
            return redirect()->route('home')->with('error', 'This ticket has not been paid for.');
        }

        $qrPath = storage_path('app/public/qrcodes/' . $ticket->uuid . '.svg');
        if (!file_exists($qrPath)) {
            $this->ticketService->generateQrCode($ticket);
        }

        $bookingTickets = $this->getBookingTickets($ticket);
        $transactionPayment = $this->getBookingPayment($ticket);
        $paymentJustCompleted = (bool) $request->boolean('paid');

        return view('tickets.show', compact('ticket', 'bookingTickets', 'transactionPayment', 'paymentJustCompleted'));
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
            'mpesa_receipt' => 'required|string|max:50',
            'phone' => 'required|string|max:20',
            'resend_email' => 'nullable|boolean',
        ]);

        $receipt = strtoupper(str_replace(' ', '', trim($data['mpesa_receipt'])));
        $phone = trim($data['phone']);

        $payment = Payment::with('ticket')
            ->where('status', 'success')
            ->whereNotNull('mpesa_receipt')
            ->whereRaw('UPPER(REPLACE(mpesa_receipt, " ", "")) = ?', [$receipt])
            ->latest('updated_at')
            ->first();

        if (!$payment || !$payment->ticket) {
            return back()
                ->withInput()
                ->with('error', 'No successful payment matched the provided M-Pesa receipt code.');
        }

        $sourceTicket = $payment->ticket;

        $ticket = Ticket::with('event')
            ->where('status', 'paid')
            ->where('phone', $phone)
            ->where(function ($query) use ($sourceTicket) {
                if ($sourceTicket->corporate_booking_ref) {
                    $query->where('corporate_booking_ref', $sourceTicket->corporate_booking_ref);
                } else {
                    $query->where('id', $sourceTicket->id);
                }
            })
            ->latest('updated_at')
            ->first();

        if (!$ticket) {
            return back()
                ->withInput()
                ->with('error', 'No paid ticket matched that M-Pesa receipt code and phone number.');
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

        $showRouteParams = ['uuid' => $ticket->uuid];
        if ($ticket->corporate_booking_ref) {
            $showRouteParams['single'] = 1;
        }

        return redirect()
            ->route('ticket.show', $showRouteParams)
            ->with('hide_ticket_sent_notice', true)
            ->with('success', $request->boolean('resend_email')
                ? 'Ticket found. We have resent the ticket email.'
                : 'Ticket found successfully.');
    }

    private function isPendingTicketExpired(Ticket $ticket): bool
    {
        return $ticket->status === 'pending'
            && $ticket->created_at !== null
            && $ticket->created_at->lt(now()->subHours(24));
    }

    private function deleteExpiredPendingTicket(Ticket $ticket): void
    {
        if (!$this->isPendingTicketExpired($ticket)) {
            return;
        }

        Log::info('Deleting expired pending ticket', [
            'ticket_id' => $ticket->id,
            'ticket_uuid' => $ticket->uuid,
            'created_at' => $ticket->created_at?->toDateTimeString(),
        ]);

        $ticket->delete();
    }

    private function purgeExpiredPendingTickets(): void
    {
        $deleted = Ticket::where('status', 'pending')
            ->where('created_at', '<', now()->subHours(24))
            ->delete();

        if ($deleted > 0) {
            Log::info('Purged expired pending tickets', ['count' => $deleted]);
        }
    }

    private function getBookingTickets(Ticket $ticket)
    {
        if (!$ticket->corporate_booking_ref) {
            return collect([$ticket]);
        }

        return Ticket::where('corporate_booking_ref', $ticket->corporate_booking_ref)
            ->where('status', 'paid')
            ->orderBy('name')
            ->get();
    }

    private function getBookingPayment(Ticket $ticket): ?Payment
    {
        if ($ticket->payment) {
            return $ticket->payment;
        }

        if (!$ticket->corporate_booking_ref) {
            return null;
        }

        $paymentTicket = Ticket::with('payment')
            ->where('corporate_booking_ref', $ticket->corporate_booking_ref)
            ->whereHas('payment')
            ->first();

        return $paymentTicket?->payment;
    }

    private function extractMpesaReceiptFromQueryResponse(array $queryResponse): ?string
    {
        if (!empty($queryResponse['MpesaReceiptNumber'])) {
            return (string) $queryResponse['MpesaReceiptNumber'];
        }

        if (!empty($queryResponse['mpesa_receipt'])) {
            return (string) $queryResponse['mpesa_receipt'];
        }

        $metadataItems = $queryResponse['CallbackMetadata']['Item'] ?? [];
        foreach ($metadataItems as $item) {
            if (($item['Name'] ?? null) === 'MpesaReceiptNumber' && !empty($item['Value'])) {
                return (string) $item['Value'];
            }
        }

        $resultParams = $queryResponse['ResultParameters']['ResultParameter'] ?? [];
        foreach ($resultParams as $param) {
            if (($param['Key'] ?? null) === 'MpesaReceiptNumber' && !empty($param['Value'])) {
                return (string) $param['Value'];
            }
        }

        return null;
    }

    private function getRequestedAttendeesFromBookingData(array $bookingData): int
    {
        if (($bookingData['type'] ?? 'individual') === 'corporate') {
            return max(1, (int) ($bookingData['number_of_attendees'] ?? 1));
        }

        return 1;
    }

    private function buildCapacityErrorMessage(Event $event, int $requestedAttendees, bool $includePending = true, ?int $ignoreTicketId = null): string
    {
        $remaining = $event->remainingCapacity($includePending, $ignoreTicketId);

        if ($remaining === null) {
            return 'No available capacity for this booking.';
        }

        if ($remaining <= 0) {
            return 'This event is sold out.';
        }

        if ($requestedAttendees > $remaining) {
            return 'Only ' . $remaining . ' slot' . ($remaining === 1 ? ' is' : 's are') . ' left for this event.';
        }

        return 'No available capacity for this booking.';
    }

    private function buildCorporateCapacityErrorMessage(Event $event, bool $includePending = true, ?int $ignoreTicketId = null): string
    {
        $remaining = $event->remainingCorporateTables($includePending, $ignoreTicketId);

        if ($remaining === null) {
            return 'No corporate booking tables are available for this event.';
        }

        if ($remaining <= 0) {
            return 'Corporate booking is sold out for this event.';
        }

        return 'Only ' . $remaining . ' corporate table' . ($remaining === 1 ? ' is' : 's are') . ' left for this event.';
    }
}
