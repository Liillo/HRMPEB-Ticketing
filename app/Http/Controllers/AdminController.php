<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Scan;
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
            'total_revenue' => Ticket::where('status', 'paid')->sum('amount'),
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

        return view('admin.dashboard', compact('stats', 'recent_scans', 'recent_paid_tickets'));
    }

    public function tickets(Request $request)
    {
        $search = $request->search;
        $status = $request->status ?? 'all';
        $type = $request->type ?? 'all';
        $scanFilter = $request->scan ?? 'all';

        $tickets = Ticket::with(['event', 'payment', 'latestScan.admin'])
            ->when($search, function ($query, $search) {
                $likeSearch = '%' . $search . '%';

                return $query->where(function ($q) use ($search, $likeSearch) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%')
                        ->orWhere('company_name', 'like', '%' . $search . '%')
                        ->orWhere('company_email', 'like', '%' . $search . '%')
                        ->orWhere('uuid', 'like', '%' . $search . '%')
                        ->orWhereHas('payment', function ($paymentQuery) use ($search) {
                            $paymentQuery->where('mpesa_receipt', 'like', '%' . $search . '%');
                        })
                        ->orWhereRaw("JSON_SEARCH(attendee_details, 'one', ?) IS NOT NULL", [$likeSearch]);
                });
            })
            ->when($status !== 'all', function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->when($type !== 'all', function ($query) use ($type) {
                return $query->where('type', $type);
            })
            ->when($scanFilter === 'scanned', function ($query) {
                return $query->where('scan_count', '>', 0);
            })
            ->when($scanFilter === 'not_scanned', function ($query) {
                return $query->where('scan_count', '=', 0);
            })
            ->latest()
            ->paginate(20);

        return view('admin.tickets', compact('tickets'));
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

    public function validation()
    {
        return view('admin.validation');
    }

    public function scanTicket(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
            'attendee_index' => 'nullable|integer|min:0',
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

        if ($ticket->type === 'corporate' && is_array($ticket->attendee_details) && count($ticket->attendee_details) > 0) {
            $attendees = array_values($ticket->attendee_details);

            if (!$request->has('attendee_index')) {
                $remainingScans = max(0, $ticket->max_scans - $ticket->scan_count);

                return response()->json([
                    'success' => true,
                    'requires_attendee_selection' => true,
                    'message' => 'Select the attendee entering the event.',
                    'ticket' => [
                        'type' => $ticket->type,
                        'name' => $ticket->company_name,
                        'scan_count' => $ticket->scan_count,
                        'max_scans' => $ticket->max_scans,
                        'remaining_scans' => $remainingScans,
                        'attendees' => array_map(function ($attendee, $index) {
                            return [
                                'index' => $index,
                                'name' => $attendee['name'] ?? ('Attendee ' . ($index + 1)),
                                'email' => $attendee['email'] ?? null,
                                'phone' => $attendee['phone'] ?? null,
                                'checked_in' => (bool) ($attendee['checked_in'] ?? false),
                            ];
                        }, $attendees, array_keys($attendees)),
                    ],
                ]);
            }

            $attendeeIndex = (int) $request->attendee_index;

            if (!array_key_exists($attendeeIndex, $attendees)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected attendee is invalid.',
                ], 422);
            }

            if ((bool) ($attendees[$attendeeIndex]['checked_in'] ?? false)) {
                return response()->json([
                    'success' => false,
                    'message' => ($attendees[$attendeeIndex]['name'] ?? 'This attendee') . ' is already checked in.',
                ], 400);
            }

            if (!$ticket->canBeScanned()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Corporate ticket has reached maximum scans (' . $ticket->max_scans . ')',
                ], 400);
            }

            $attendees[$attendeeIndex]['checked_in'] = true;
            $attendees[$attendeeIndex]['checked_in_at'] = now()->toDateTimeString();
            $attendees[$attendeeIndex]['checked_in_by_admin_id'] = Auth::id();
            $ticket->attendee_details = $attendees;
            $ticket->save();

            Scan::create([
                'ticket_id' => $ticket->id,
                'admin_id' => Auth::id(),
                'scanned_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $ticket->incrementScan();
            $ticket->refresh();

            $remainingScans = max(0, $ticket->max_scans - $ticket->scan_count);

            return response()->json([
                'success' => true,
                'message' => ($attendees[$attendeeIndex]['name'] ?? 'Attendee') . ' checked in successfully',
                'ticket' => [
                    'type' => $ticket->type,
                    'name' => $ticket->company_name,
                    'scan_count' => $ticket->scan_count,
                    'max_scans' => $ticket->max_scans,
                    'remaining_scans' => $remainingScans,
                ],
            ]);
        }

        if (!$ticket->canBeScanned()) {
            if ($ticket->type === 'corporate') {
                return response()->json([
                    'success' => false,
                    'message' => 'Corporate ticket has reached maximum scans (' . $ticket->max_scans . ')',
                ], 400);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Ticket has already been scanned',
                ], 400);
            }
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
                'name' => $ticket->type === 'corporate' ? $ticket->company_name : $ticket->name,
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
            ->orWhere('company_name', 'like', "%{$query}%")
            ->orWhere('company_email', 'like', "%{$query}%")
            ->orWhere('uuid', 'like', "%{$query}%")
            ->orWhereRaw("JSON_SEARCH(attendee_details, 'one', ?) IS NOT NULL", [$likeQuery])
            ->with('payment')
            ->get();

        return view('admin.search-results', compact('tickets', 'query'));
    }
}

