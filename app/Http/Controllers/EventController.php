<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $events = Event::withCount(['tickets', 'paidTickets'])->latest()->get();
        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        return view('admin.events.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'required|date',
            'location' => 'nullable|string|max:255',
            'individual_price' => 'required|numeric|min:0',
            'corporate_price' => 'required|numeric|min:0',
            'max_corporate_attendees' => 'required|integer|min:1|max:50',
        ]);

        Event::create($request->all());

        return redirect()->route('admin.events.index')
            ->with('success', 'Event created successfully');
    }

    public function show($id)
    {
        $event = Event::with(['tickets.payment', 'tickets.scans'])->findOrFail($id);
        
        $stats = [
            'total_tickets' => $event->tickets()->count(),
            'paid_tickets' => $event->paidTickets()->count(),
            'scanned_tickets' => $event->tickets()->where('scan_count', '>', 0)->count(),
            'unscanned_tickets' => $event->paidTickets()->where('scan_count', 0)->count(),
            'total_revenue' => $event->paidTickets()->sum('amount'),
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
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'required|date',
            'location' => 'nullable|string|max:255',
            'individual_price' => 'required|numeric|min:0',
            'corporate_price' => 'required|numeric|min:0',
            'max_corporate_attendees' => 'required|integer|min:1|max:50',
            'is_active' => 'boolean',
        ]);

        $event = Event::findOrFail($id);
        $event->update($request->all());

        return redirect()->route('admin.events.index')
            ->with('success', 'Event updated successfully');
    }

    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        $event->delete();

        return redirect()->route('admin.events.index')
            ->with('success', 'Event deleted successfully');
    }

    public function toggleStatus($id)
    {
        $event = Event::findOrFail($id);
        $event->is_active = !$event->is_active;
        $event->save();

        return back()->with('success', 'Event status updated');
    }
}
