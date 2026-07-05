<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Mark a single notification as read, then redirect to its target.
     */
    public function open(Notification $notification)
    {
        abort_unless($notification->user_id === Auth::id(), 403);

        $notification->markAsRead();

        return $notification->action_url
            ? redirect($notification->action_url)
            : back();
    }

    /**
     * Mark all of the current user's notifications as read.
     */
    public function readAll()
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);

        return back();
    }
}
