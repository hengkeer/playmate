<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    public function show(Event $event)
    {
        $user = Auth::user();

        if (!$event->canAccessChat($user)) {
            abort(403, 'You must be a participant to access this chat.');
        }

        $event->load(['sport', 'host', 'approvedParticipants.user']);

        $messages = $event->messages()->with('user')->get();

        return view('chat', compact('event', 'messages'));
    }

    public function send(Event $event, Request $request)
    {
        $user = Auth::user();

        if (!$event->canAccessChat($user)) {
            abort(403);
        }

        $type = $request->input('type', 'text');
        $content = $request->input('content');
        $fileUrl = null;
        $fileName = null;

        if ($type === 'image' || $type === 'file') {
            $request->validate([
                'file' => 'required|file|max:10240', // 10MB max
            ]);

            $path = $request->file('file')->store('chat-files', 'public');
            $fileUrl = Storage::url($path);
            $fileName = $request->file('file')->getClientOriginalName();
            $content = $request->input('content', '');
        } else {
            $request->validate([
                'content' => 'required|string|max:2000',
            ]);
        }

        EventMessage::create([
            'event_id'  => $event->id,
            'user_id'   => $user->id,
            'content'  => $content,
            'type'      => $type,
            'file_url'  => $fileUrl,
            'file_name' => $fileName,
        ]);

        // Update last_active_at
        $user->update(['last_active_at' => now()]);

        return back();
    }

    public function destroy(Event $event, EventMessage $message)
    {
        $user = Auth::user();

        if (!$message->isOwnedBy($user)) {
            abort(403);
        }

        if ($message->file_url) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $message->file_url));
        }

        $message->delete();

        return back();
    }
}