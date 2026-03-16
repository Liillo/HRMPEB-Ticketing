<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $events = Event::with(['createdBy', 'updatedBy'])
            ->withCount(['tickets', 'paidTickets'])
            ->withSum([
                'tickets as paid_attendees_count' => function ($query) {
                    $query->where('status', 'paid');
                }
            ], 'number_of_attendees')
            ->withSum([
                'tickets as pending_attendees_count' => function ($query) {
                    $query->where('status', 'pending');
                }
            ], 'number_of_attendees')
            ->orderBy('event_date')
            ->orderByDesc('id')
            ->get();
        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        return view('admin.events.create');
    }

    public function store(Request $request)
    {
        if ($response = $this->ensureHrRole()) {
            return $response;
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'required|date',
            'location' => 'nullable|string|max:255',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'individual_price' => 'required|numeric|min:0',
            'corporate_price' => 'required|numeric|min:0',
            'max_capacity' => 'required|integer|min:1',
            'max_corporate_tables' => 'required|integer|min:1',
        ]);

        $data = $request->only([
            'name',
            'description',
            'event_date',
            'location',
            'poster_path',
            'individual_price',
            'corporate_price',
            'max_capacity',
            'max_corporate_tables',
        ]);
        $data['max_corporate_attendees'] = 10;
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        if ($request->hasFile('poster')) {
            $data['poster_path'] = $request->file('poster')->store('event-posters', 'public');
        }

        Event::create($data);

        return redirect()->route('admin.events.index')
            ->with('success', 'Event created successfully');
    }

    public function show($id)
    {
        $event = Event::with(['createdBy', 'updatedBy', 'tickets.payment', 'tickets.scans'])->findOrFail($id);
        
        $stats = [
            'total_tickets' => $event->tickets()->count(),
            'paid_tickets' => $event->paidTickets()->count(),
            'scanned_tickets' => $event->tickets()->where('scan_count', '>', 0)->count(),
            'unscanned_tickets' => $event->paidTickets()->where('scan_count', 0)->count(),
            'total_revenue' => Payment::where('status', 'success')
                ->whereHas('ticket', function ($query) use ($event) {
                    $query->where('event_id', $event->id);
                })
                ->sum('amount'),
            'max_capacity' => (int) $event->max_capacity,
            'max_corporate_tables' => (int) $event->max_corporate_tables,
            'paid_attendees' => $event->paidAttendeesCount(),
            'pending_attendees' => $event->pendingAttendeesCount(),
            'remaining_capacity' => (int) ($event->remainingCapacity() ?? 0),
            'paid_corporate_tables' => $event->paidCorporateTablesCount(),
            'pending_corporate_tables' => $event->pendingCorporateTablesCount(),
            'remaining_corporate_tables' => (int) ($event->remainingCorporateTables() ?? 0),
            'is_corporate_sold_out' => $event->isCorporateSoldOut(),
            'is_sold_out' => $event->isSoldOut(),
        ];

        return view('admin.events.show', compact('event', 'stats'));
    }

    public function edit($id)
    {
        $event = Event::findOrFail($id);
        return view('admin.events.edit', compact('event'));
    }

    public function update(Request $request, $id)
    {
        if ($response = $this->ensureHrRole()) {
            return $response;
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'required|date',
            'location' => 'nullable|string|max:255',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'individual_price' => 'required|numeric|min:0',
            'corporate_price' => 'required|numeric|min:0',
            'max_capacity' => 'required|integer|min:1',
            'max_corporate_tables' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $event = Event::findOrFail($id);
        $data = $request->only([
            'name',
            'description',
            'event_date',
            'location',
            'poster_path',
            'individual_price',
            'corporate_price',
            'max_capacity',
            'max_corporate_tables',
            'is_active',
        ]);
        $data['max_corporate_attendees'] = 10;
        $data['updated_by'] = Auth::id();

        if ($request->hasFile('poster')) {
            if ($event->poster_path) {
                Storage::disk('public')->delete($event->poster_path);
            }
            $data['poster_path'] = $request->file('poster')->store('event-posters', 'public');
        }

        $event->update($data);

        return redirect()->route('admin.events.index')
            ->with('success', 'Event updated successfully');
    }

    public function destroy($id)
    {
        if ($response = $this->ensureHrRole()) {
            return $response;
        }

        $event = Event::findOrFail($id);
        $event->delete();

        return redirect()->route('admin.events.index')
            ->with('success', 'Event deleted successfully');
    }

    public function toggleStatus($id)
    {
        if ($response = $this->ensureHrRole()) {
            return $response;
        }

        $event = Event::findOrFail($id);
        $event->is_active = !$event->is_active;
        $event->save();

        return back()->with('success', 'Event status updated');
    }

    private function ensureHrRole()
    {
        $user = Auth::user();

        if (!$user || !$user->isHr()) {
            return back()->with('error', 'Only HR admins can manage events.');
        }

        return null;
    }
}
