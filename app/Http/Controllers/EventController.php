<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\Notification;
use App\Models\Sport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $sportId = $request->get('sport_id');
        $date = $request->get('date');

        $query = Event::with(['sport', 'host', 'users'])
            ->where('status', '!=', 'cancelled')
            ->where('start_time', '>=', now());

        if ($sportId) {
            $query->where('sport_id', $sportId);
        }
        if ($date) {
            $query->whereDate('start_time', $date);
        }

        $events = $query->orderBy('start_time')->paginate(12);
        $sports = Sport::all();

        return view('events.index', compact('events', 'sports'));
    }

    public function create()
    {
        $sports  = Sport::all();
        $venue   = null;

        if ($venueId = request('venue_id')) {
            $venue = \App\Models\Venue::find($venueId);
        }

        return view('events.create', compact('sports', 'venue'));
    }

    /**
     * Pre-fill event creation form with a specific opponent.
     * Used by the Challenger Discovered reveal flow.
     */
    public function challenge(\App\Models\User $user)
    {
        $sports = Sport::all();
        $opponent = $user->load('sports');
        return view('host-game', compact('sports', 'opponent'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'             => 'required|string|max:255',
            'sport_id'          => 'nullable|exists:sports,id',
            'description'       => 'nullable|string|max:2000',
            'venue_name'        => 'required|string|max:255',
            'latitude'          => 'nullable|numeric|between:-90,90',
            'longitude'         => 'nullable|numeric|between:-180,180',
            'start_time'        => 'required|date|after:now',
            'end_time'          => 'required|date|after:start_time',
            'max_slots'         => 'nullable|integer|min:2|max:50',
            'price'             => 'nullable|numeric|min:0',
            'payment_info'      => 'nullable|string|max:500',
            'visibility'        => 'in:public,private',
            'match_type'        => 'in:singles,doubles',
            'approval_required' => 'boolean',
        ]);

        $event = Event::create([
            'host_id'           => Auth::id(),
            'sport_id'          => $validated['sport_id'] ?? null,
            'title'             => $validated['title'],
            'description'       => $validated['description'] ?? null,
            'venue_name'        => $validated['venue_name'],
            'latitude'          => $validated['latitude'] ?? null,
            'longitude'         => $validated['longitude'] ?? null,
            'start_time'        => $validated['start_time'],
            'end_time'          => $validated['end_time'],
            'max_slots'         => $validated['max_slots'] ?? 4,
            'price'             => $validated['price'] ?? null,
            'payment_info'      => $validated['payment_info'] ?? null,
            'visibility'         => $validated['visibility'] ?? 'public',
            'match_type'         => $validated['match_type'] ?? 'singles',
            'approval_required' => $validated['approval_required'] ?? false,
            'status'            => 'upcoming',
        ]);

        EventParticipant::create([
            'event_id'  => $event->id,
            'user_id'   => Auth::id(),
            'status'    => 'approved',
            'slot_number' => 1,
            'joined_at' => now(),
        ]);

        // If this event was created via Challenge flow, pre-invite the opponent
        if ($request->filled('opponent_id')) {
            EventParticipant::create([
                'event_id'   => $event->id,
                'user_id'    => $request->input('opponent_id'),
                'status'     => 'pending',
                'is_invite'  => true,
                'slot_number' => 2,
                'joined_at'  => now(),
            ]);

            Notification::create([
                'user_id'    => $request->input('opponent_id'),
                'type'       => 'challenge_invite',
                'title'      => Auth::user()->name . ' challenged you to a match',
                'body'       => $event->title . ' — respond from your schedule.',
                'action_url' => route('my-events') . '#incoming-challenges',
            ]);
        }

        return redirect()->route('events.show', $event)
            ->with('success', 'Event created. You are the host.');
    }

    public function show(Event $event)
    {
        $event->load(['sport', 'host', 'approvedParticipants.user']);

        return view('events.show', compact('event'));
    }

    public function join(Event $event)
    {
        $user = Auth::user();

        $exists = EventParticipant::where('event_id', $event->id)
            ->where('user_id', $user->id)->exists();

        if ($exists) {
            return back()->with('error', 'You have already joined this event.');
        }

        if ($event->isFull() && !$event->approval_required) {
            // Add to waiting list
            EventParticipant::create([
                'event_id' => $event->id,
                'user_id'  => $user->id,
                'status'   => 'waiting',
                'joined_at' => now(),
            ]);
            return back()->with('info', 'Event is full. You have been added to the waiting list.');
        }

        $status = $event->approval_required ? 'pending' : 'approved';

        EventParticipant::create([
            'event_id'    => $event->id,
            'user_id'     => $user->id,
            'status'      => $status,
            'slot_number' => $event->approvedParticipants()->count() + 1,
            'joined_at'   => now(),
        ]);

        $user->increment('total_events_joined');

        if ($status === 'pending') {
            Notification::create([
                'user_id'    => $event->host_id,
                'type'       => 'join_request',
                'title'      => $user->name . ' wants to join ' . $event->title,
                'body'       => 'Review this request in your approval queue.',
                'action_url' => route('my-events.host-requests'),
            ]);
        }

        $msg = $status === 'pending'
            ? 'Request sent. Waiting for host approval.'
            : 'You have joined the event!';

        return back()->with('success', $msg);
    }

    public function leave(Event $event)
    {
        $user = Auth::user();

        if ($event->isHostedBy($user)) {
            return back()->with('error', 'Host cannot leave the event. Cancel it instead.');
        }

        EventParticipant::where('event_id', $event->id)
            ->where('user_id', $user->id)->delete();

        return redirect()->route('my-events')->with('success', 'You left the event.');
    }

    public function cancel(Event $event)
    {
        $user = Auth::user();

        if (!$event->isHostedBy($user)) {
            abort(403);
        }

        $event->update(['status' => 'cancelled']);

        return redirect()->route('events.index')->with('success', 'Event cancelled.');
    }
}