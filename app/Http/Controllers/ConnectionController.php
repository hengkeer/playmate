<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\ConnectionMessage;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ConnectionController extends Controller
{
    /**
     * Connections dashboard: incoming, sent, accepted.
     */
    public function index()
    {
        $user = Auth::user();

        $incoming = Connection::with('requester.userSports.sport')
            ->where('receiver_id', $user->id)
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->get();

        $sent = Connection::with('receiver.userSports.sport')
            ->where('requester_id', $user->id)
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->get();

        $connections = Connection::with(['requester.userSports.sport', 'receiver.userSports.sport'])
            ->where(function ($q) use ($user) {
                $q->where('requester_id', $user->id)
                  ->orWhere('receiver_id', $user->id);
            })
            ->where('status', 'accepted')
            ->orderByDesc('updated_at')
            ->get();

        return view('connections.index', compact('incoming', 'sent', 'connections'));
    }

    /**
     * Send a connection request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = Auth::user();
        $receiverId = (int) $validated['user_id'];

        if ($receiverId === $user->id) {
            return back()->withErrors(['user_id' => 'You cannot connect with yourself.']);
        }

        // Check if connection already exists (any status)
        $exists = Connection::where(function ($q) use ($user, $receiverId) {
            $q->where('requester_id', $user->id)->where('receiver_id', $receiverId)
              ->orWhere('requester_id', $receiverId)->where('receiver_id', $user->id);
        })->exists();

        if ($exists) {
            return back()->withErrors(['user_id' => 'Connection already exists.']);
        }

        Connection::create([
            'requester_id' => $user->id,
            'receiver_id'  => $receiverId,
            'status'       => 'pending',
        ]);

        Notification::create([
            'user_id'    => $receiverId,
            'type'       => 'connection_request',
            'title'      => $user->name . ' wants to connect',
            'body'       => 'Review this request in your network.',
            'action_url' => route('connections.index'),
        ]);

        return back()->with('success', 'Connection request sent!');
    }

    /**
     * Accept / decline / block a connection.
     */
    public function update(Request $request, Connection $connection)
    {
        $user = Auth::user();

        // Only receiver can respond
        if ($connection->receiver_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'action' => 'required|in:accept,decline,block',
        ]);

        $statusMap = [
            'accept' => 'accepted',
            'decline' => 'declined',
            'block'  => 'blocked',
        ];

        $connection->update(['status' => $statusMap[$validated['action']]]);

        if ($validated['action'] === 'accept') {
            Notification::create([
                'user_id'    => $connection->requester_id,
                'type'       => 'connection_accepted',
                'title'      => $user->name . ' accepted your connection request',
                'body'       => 'You can now chat with them from your network.',
                'action_url' => route('connections.index'),
            ]);
        }

        return back()->with('success', "Connection {$statusMap[$validated['action']]}.");
    }

    /**
     * Public player profile.
     */
    public function playerProfile(User $user)
    {
        $user->load(['userSports.sport', 'reviewsReceived.reviewer', 'joinedEvents.sport']);

        return view('players.profile', compact('user'));
    }

    // ─── Private Chat ─────────────────────────────────────────────────────────

    public function chat(Connection $connection)
    {
        $user = Auth::user();

        if (!$connection->involvesUser($user) || !$connection->isAccepted()) {
            abort(403);
        }

        $connection->load(['messages.sender', 'requester.userSports.sport', 'receiver.userSports.sport']);

        return view('connections.chat', compact('connection'));
    }

    public function sendMessage(Request $request, Connection $connection)
    {
        $user = Auth::user();

        if (!$connection->involvesUser($user) || !$connection->isAccepted()) {
            abort(403);
        }

        $validated = $request->validate([
            'message' => 'required_without:file|string|max:1000',
            'file'    => 'nullable|file|max:5120', // 5MB max
        ]);

        $fileUrl = null;
        $fileName = null;
        $type = 'text';

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $type = $file->getClientMimeType();
            $type = (str_starts_with($type, 'image/')) ? 'image' : 'file';
            $path = $file->store('chat-files', 'public');
            $fileUrl = Storage::url($path);
            $fileName = $file->getClientOriginalName();
            $message = $request->input('message', $fileName);
        } else {
            $message = $validated['message'];
        }

        ConnectionMessage::create([
            'connection_id' => $connection->id,
            'sender_id'     => $user->id,
            'message'       => $message,
            'type'          => $type,
            'file_url'      => $fileUrl,
            'file_name'     => $fileName,
        ]);

        $connection->touch();

        return back();
    }
}
