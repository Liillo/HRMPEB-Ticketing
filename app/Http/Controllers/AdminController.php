<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Scan;
use App\Models\Payment;
use App\Models\Event;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class AdminController extends Controller
{
    protected $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->middleware('auth')->except(['showLogin', 'login']);
        $this->middleware('admin')->except(['showLogin', 'login']);
        $this->ticketService = $ticketService;
    }

    public function showLogin(Request $request)
    {
        if (Auth::check()) {
            if (Auth::user()->is_admin) {
                return redirect()->route('admin.dashboard');
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            if (Auth::user()->is_admin) {
                return redirect()->intended(route('admin.dashboard'));
            }
            
            Auth::logout();
            return back()->withErrors([
                'email' => 'You are not authorized to access this area.',
            ]);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('admin.login');
    }

    public function dashboard()
    {
        $stats = [
            'total_tickets' => Ticket::count(),
            'paid_tickets' => Ticket::where('status', 'paid')->count(),
            'pending_tickets' => Ticket::where('status', 'pending')->count(),
            'failed_tickets' => Ticket::where('status', 'failed')->count(),
            'total_scans' => Scan::count(),
            'total_revenue' => Payment::where('status', 'success')->sum('amount'),
            'pending_cheque_payments' => Payment::where('method', Payment::METHOD_CHEQUE)
                ->where('status', 'pending')
                ->count(),
        ];

        $recent_scans = Scan::with(['ticket', 'admin'])
            ->orderByDesc('scanned_at')
            ->orderByDesc('id')
            ->take(5)
            ->get();

        $recent_paid_tickets = Ticket::with(['payment', 'latestScan.admin'])
            ->where('status', 'paid')
            ->withMax('payment', 'updated_at')
            ->orderByDesc('payment_max_updated_at')
            ->orderByDesc('id')
            ->take(5)
            ->get();

        $pending_cheque_payments = Payment::with(['ticket.event'])
            ->where('method', Payment::METHOD_CHEQUE)
            ->where('status', 'pending')
            ->latest('updated_at')
            ->take(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'recent_scans', 'recent_paid_tickets', 'pending_cheque_payments'));
    }

    public function tickets(Request $request)
    {
        $search = $request->search;
        $status = $request->status ?? 'all';
        $type = $request->type ?? 'all';
        $scanFilter = $request->scan ?? 'all';
        $eventId = $request->filled('event_id') ? (int) $request->event_id : null;

        $tickets = Ticket::with(['event', 'payment', 'latestScan.admin'])
            ->when($search, function ($query, $search) {
                $likeSearch = '%' . $search . '%';

                return $query->where(function ($q) use ($search, $likeSearch) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%')
                        ->orWhere('staff_no', 'like', '%' . $search . '%')
                        ->orWhere('ihrm_no', 'like', '%' . $search . '%')
                        ->orWhere('company_name', 'like', '%' . $search . '%')
                        ->orWhere('company_email', 'like', '%' . $search . '%')
                        ->orWhere('uuid', 'like', '%' . $search . '%')
                        ->orWhere('corporate_booking_ref', 'like', '%' . $search . '%')
                        ->orWhereHas('payment', function ($paymentQuery) use ($search) {
                            $paymentQuery->where('mpesa_receipt', 'like', '%' . $search . '%')
                                ->orWhere('cheque_number', 'like', '%' . $search . '%');
                        })
                        ->orWhere('attendee_details', 'like', $likeSearch);
                });
            })
            ->when($status !== 'all', function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->when($type !== 'all', function ($query) use ($type) {
                return $query->where('type', $type);
            })
            ->when($eventId, function ($query) use ($eventId) {
                return $query->where('event_id', $eventId);
            })
            ->when($scanFilter === 'scanned', function ($query) {
                return $query->where('scan_count', '>', 0);
            })
            ->when($scanFilter === 'not_scanned', function ($query) {
                return $query->where('scan_count', '=', 0);
            })
            ->latest()
            ->paginate(20);

        $events = Event::orderBy('name')->get(['id', 'name']);

        return view('admin.tickets', compact('tickets', 'events'));
    }

    public function ticketDetail($id)
    {
        $ticket = Ticket::with(['payment', 'scans.admin'])->findOrFail($id);
        return view('admin.ticket-detail', compact('ticket'));
    }

    public function downloadTicket($id)
    {
        $ticket = Ticket::findOrFail($id);
        
        if ($ticket->status !== 'paid') {
            return back()->with('error', 'Cannot download unpaid ticket');
        }

        $pdf = $this->ticketService->generateTicketPdf($ticket);
        
        return $pdf->download('ticket-' . $ticket->uuid . '.pdf');
    }

    public function resendTicket($id)
    {
        $ticket = Ticket::with('event')->findOrFail($id);

        if ($ticket->status !== 'paid') {
            return back()->with('error', 'Cannot resend an unpaid ticket.');
        }

        try {
            $sent = $this->ticketService->sendTicketEmail($ticket);

            if (!$sent) {
                return back()->with('error', 'Ticket email could not be sent: missing recipient email.');
            }

            return back()->with('success', 'Ticket email resent successfully.');
        } catch (\Throwable $e) {
            Log::error('Admin ticket resend failed', [
                'ticket_id' => $ticket->id,
                'ticket_uuid' => $ticket->uuid,
                'admin_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to resend ticket email. Please try again.');
        }
    }

    public function approveChequePayment(Payment $payment)
    {
        if ($payment->method !== Payment::METHOD_CHEQUE) {
            return back()->with('error', 'Only cheque payments can be approved from this action.');
        }

        if ($payment->status !== 'pending') {
            return back()->with('error', 'This cheque payment has already been processed.');
        }

        $ticket = Ticket::with('event')->findOrFail($payment->ticket_id);

        $payment->update([
            'status' => 'success',
            'response_description' => 'Cheque payment approved by admin.',
        ]);

        $ticket->update(['status' => 'paid']);
        $this->ticketService->fulfillPaidTicket($ticket);

        return back()->with('success', 'Cheque payment approved and ticket fulfilled.');
    }

    public function rejectChequePayment(Request $request, Payment $payment)
    {
        if ($payment->method !== Payment::METHOD_CHEQUE) {
            return back()->with('error', 'Only cheque payments can be rejected from this action.');
        }

        if ($payment->status !== 'pending') {
            return back()->with('error', 'This cheque payment has already been processed.');
        }

        $data = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $ticket = Ticket::findOrFail($payment->ticket_id);

        $payment->update([
            'status' => 'failed',
            'response_description' => trim((string) ($data['reason'] ?? 'Cheque payment rejected by admin.')),
        ]);

        $ticket->update(['status' => 'failed']);

        return back()->with('success', 'Cheque payment rejected.');
    }

    public function validation()
    {
        return view('admin.validation');
    }

    public function scanTicket(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
        ]);

        $qrCode = trim($request->qr_code);

        // If scanner provides a full URL, extract the trailing UUID.
        if (str_contains($qrCode, '/')) {
            $qrCode = rtrim($qrCode, '/');
            $qrCode = substr($qrCode, strrpos($qrCode, '/') + 1);
        }

        $ticket = Ticket::where('qr_code', $qrCode)
            ->orWhere('uuid', $qrCode)
            ->first();

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid ticket',
            ], 404);
        }

        if ($ticket->status !== 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Ticket is not valid. Status: ' . ucfirst($ticket->status),
            ], 400);
        }

        if (!$ticket->canBeScanned()) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket has already been scanned',
            ], 400);
        }

        Scan::create([
            'ticket_id' => $ticket->id,
            'admin_id' => Auth::id(),
            'scanned_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $ticket->incrementScan();

        $remainingScans = $ticket->max_scans - $ticket->scan_count;

        return response()->json([
            'success' => true,
            'message' => 'Ticket validated successfully',
            'ticket' => [
                'type' => $ticket->type,
                'name' => $ticket->name,
                'scan_count' => $ticket->scan_count,
                'max_scans' => $ticket->max_scans,
                'remaining_scans' => $remainingScans,
            ],
        ]);
    }

    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:3',
        ]);

        $query = $request->query;
        $likeQuery = '%' . $query . '%';

        $tickets = Ticket::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orWhere('phone', 'like', "%{$query}%")
            ->orWhere('staff_no', 'like', "%{$query}%")
            ->orWhere('ihrm_no', 'like', "%{$query}%")
            ->orWhere('company_name', 'like', "%{$query}%")
            ->orWhere('company_email', 'like', "%{$query}%")
            ->orWhere('uuid', 'like', "%{$query}%")
            ->orWhere('corporate_booking_ref', 'like', "%{$query}%")
            ->orWhereHas('payment', function ($paymentQuery) use ($query) {
                $paymentQuery->where('mpesa_receipt', 'like', "%{$query}%")
                    ->orWhere('cheque_number', 'like', "%{$query}%");
            })
            ->orWhere('attendee_details', 'like', $likeQuery)
            ->with('payment')
            ->get();

        return view('admin.search-results', compact('tickets', 'query'));
    }

}

