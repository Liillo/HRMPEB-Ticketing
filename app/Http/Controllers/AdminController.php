<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Scan;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


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

        $recent_tickets = Ticket::with('payment')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recent_tickets'));
    }

    public function tickets(Request $request)
    {
        $query = Ticket::with('payment', 'scans');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('company_email', 'like', "%{$search}%")
                  ->orWhere('uuid', 'like', "%{$search}%")
                  ->orWhereHas('payments', function ($paymentQuery) use ($search) {
                      $paymentQuery->where('mpesa_receipt', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(20);

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

        $tickets = Ticket::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orWhere('phone', 'like', "%{$query}%")
            ->orWhere('company_name', 'like', "%{$query}%")
            ->orWhere('company_email', 'like', "%{$query}%")
            ->orWhere('uuid', 'like', "%{$query}%")
            ->with('payment')
            ->get();

        return view('admin.search-results', compact('tickets', 'query'));
    }
}
