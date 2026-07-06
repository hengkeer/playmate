<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\EventMessage;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyEventsController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $upcoming = Event::whereHas('participants', fn($q) => $q->where('user_id', $user->id)->where('status', 'approved'))
            ->where('start_time', '>=', now())
            ->where('status', '!=', 'cancelled')
            ->with(['sport', 'host'])
            ->orderBy('start_time')
            ->get();

        $hosted = $user->hostedEvents()
            ->where('status', '!=', 'cancelled')
            ->with(['sport'])
            ->orderBy('start_time')
            ->get();

        $waiting = Event::whereHas('participants', fn($q) => $q->where('user_id', $user->id)->where('status', 'waiting'))
            ->with(['sport', 'host'])
            ->get();

        $pending = Event::whereHas('participants', fn($q) => $q->where('user_id', $user->id)->where('status', 'pending')->where('is_invite', false))
            ->with(['sport', 'host'])
            ->get();

        $incomingChallenges = Event::whereHas('participants', fn($q) => $q->where('user_id', $user->id)->where('status', 'pending')->where('is_invite', true))
            ->with(['sport', 'host', 'participants'])
            ->get();

        $past = Event::whereHas('participants', fn($q) => $q->where('user_id', $user->id))
            ->where('end_time', '<', now())
            ->with(['sport', 'host'])
            ->orderBy('start_time', 'desc')
            ->limit(10)->get();

        $cancelled = Event::whereHas('participants', fn($q) => $q->where('user_id', $user->id))
            ->where('status', 'cancelled')
            ->with(['sport', 'host'])
            ->limit(5)->get();

        // Count pending join requests for the badge on My Events page
        $pendingRequestsCount = EventParticipant::whereHas('event', fn($q) => $q->where('host_id', $user->id))
            ->where('status', 'pending')
            ->where('is_invite', false)
            ->count();

        $incomingChallengesCount = $incomingChallenges->count();

        return view('my-events', compact('upcoming', 'hosted', 'waiting', 'pending', 'past', 'cancelled', 'pendingRequestsCount', 'incomingChallenges', 'incomingChallengesCount'));
    }

    /**
     * Show all pending join requests across the host's events.
     */
    public function hostRequests()
    {
        $user = Auth::user();

        $pendingRequests = EventParticipant::whereHas('event', fn($q) => $q->where('host_id', $user->id))
            ->where('status', 'pending')
            ->where('is_invite', false)
            ->with(['event.sport', 'user'])
            ->orderBy('joined_at', 'desc')
            ->get();

        return view('my-events-host-requests', compact('pendingRequests'));
    }

    /**
     * Approve a pending join request.
     */
    public function approve(Request $request, EventParticipant $participant)
    {
        $user = Auth::user();
        $event = $participant->event;

        abort_unless($event->host_id === $user->id, 403, 'Only the host can approve requests.');
        abort_unless($participant->status === 'pending', 403, 'This request is not pending.');
        abort_unless(!$participant->is_invite, 403, 'This is a challenge invite — only the invited player can respond.');

        $participant->update(['status' => 'approved', 'slot_number' => $event->participants()->max('slot_number') + 1]);

        // Notify participant via event chat
        EventMessage::create([
            'event_id' => $event->id,
            'user_id'  => $user->id,
            'content'  => "✅ " . $participant->user->name . " has been approved and joined the event!",
            'type'     => 'text',
        ]);

        Notification::create([
            'user_id'    => $participant->user_id,
            'type'       => 'join_approved',
            'title'      => 'You\'re in! ' . $event->title,
            'body'       => $user->name . ' approved your request to join.',
            'action_url' => route('events.show', $event),
        ]);

        return redirect()->route('my-events.host-requests')
            ->with('success', $participant->user->name . "'s request has been approved.");
    }

    /**
     * Reject a pending join request.
     */
    public function reject(Request $request, EventParticipant $participant)
    {
        $user = Auth::user();
        $event = $participant->event;

        abort_unless($event->host_id === $user->id, 403, 'Only the host can reject requests.');
        abort_unless($participant->status === 'pending', 403, 'This request is not pending.');
        abort_unless(!$participant->is_invite, 403, 'This is a challenge invite — only the invited player can respond.');

        $participant->update(['status' => 'rejected']);

        // Notify participant via event chat
        EventMessage::create([
            'event_id' => $event->id,
            'user_id'  => $user->id,
            'content'  => "❌ " . $participant->user->name . "'s join request has been declined.",
            'type'     => 'text',
        ]);

        Notification::create([
            'user_id'    => $participant->user_id,
            'type'       => 'join_rejected',
            'title'      => 'Request declined — ' . $event->title,
            'body'       => $user->name . ' declined your request to join.',
            'action_url' => route('events.index'),
        ]);

        return redirect()->route('my-events.host-requests')
            ->with('success', $participant->user->name . "'s request has been declined.");
    }

    /**
     * Accept a challenge invite (opponent-only action).
     */
    public function acceptChallenge(Request $request, EventParticipant $participant)
    {
        $user = Auth::user();
        $event = $participant->event;

        abort_unless($participant->user_id === $user->id, 403, 'This challenge is not yours to respond to.');
        abort_unless($participant->isChallengeInvite(), 403, 'This challenge is no longer pending.');

        $participant->update([
            'status'      => 'approved',
            'slot_number' => $event->participants()->max('slot_number') + 1,
        ]);

        EventMessage::create([
            'event_id' => $event->id,
            'user_id'  => $user->id,
            'content'  => "✅ " . $user->name . " accepted the challenge!",
            'type'     => 'text',
        ]);

        Notification::create([
            'user_id'    => $event->host_id,
            'type'       => 'challenge_accepted',
            'title'      => $user->name . ' accepted your challenge',
            'body'       => $event->title . ' is on.',
            'action_url' => route('events.show', $event),
        ]);

        return redirect()->route('my-events')->with('success', "You accepted the challenge — you're in!");
    }

    /**
     * Decline a challenge invite (opponent-only action).
     */
    public function declineChallenge(Request $request, EventParticipant $participant)
    {
        $user = Auth::user();
        $event = $participant->event;

        abort_unless($participant->user_id === $user->id, 403, 'This challenge is not yours to respond to.');
        abort_unless($participant->isChallengeInvite(), 403, 'This challenge is no longer pending.');

        $participant->update(['status' => 'rejected']);

        EventMessage::create([
            'event_id' => $event->id,
            'user_id'  => $user->id,
            'content'  => "❌ " . $user->name . " declined the challenge.",
            'type'     => 'text',
        ]);

        Notification::create([
            'user_id'    => $event->host_id,
            'type'       => 'challenge_declined',
            'title'      => $user->name . ' declined your challenge',
            'body'       => $event->title . ($event->is_challenge ? ' (Event cancelled)' : ''),
            'action_url' => route('my-events'),
        ]);

        if ($event->is_challenge) {
            $event->delete();
        }

        return redirect()->route('my-events')->with('success', 'Challenge declined.');
    }
}