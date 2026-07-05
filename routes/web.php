<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\ConnectionController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\MatchmakingController;
use App\Http\Controllers\MyEventsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VenueController;
use App\Http\Controllers\ChatAssistantController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('welcome'))->name('home');

// Guest-accessible public pages
Route::get('/events',      [EventController::class, 'index'])->name('events.index');
Route::get('/venues',           [VenueController::class, 'index'])->name('venues.index');
Route::get('/venues/search',    [VenueController::class, 'search'])->name('venues.search');
Route::get('/venues/{venue}',   [VenueController::class, 'show'])->name('venues.show');
Route::get('/players/{user}', [ConnectionController::class, 'playerProfile'])->name('player.profile');

// Auth
Route::get('/login', fn() => view('auth.login'))->name('login');
Route::post('/login', function () {
    $creds = request()->only('email', 'password');
    if (auth()->attempt($creds)) return redirect()->intended('/dashboard');
    return back()->withErrors(['email' => 'Invalid credentials']);
})->name('login.post');

Route::get('/register', fn() => view('auth.register'))->name('register');
Route::post('/register', function () {
    $data = request()->validate([
        'name'       => 'required|string|max:255',
        'email'      => 'required|email|unique:users',
        'password'   => 'required|min:8|confirmed',
        'play_style' => 'nullable|in:casual,competitive',
    ]);
    $user = \App\Models\User::create($data);
    auth()->login($user);
    return redirect('/dashboard');
})->name('register.post');

Route::post('/logout', function () {
    auth()->logout();
    return redirect('/');
})->name('logout');

// Authenticated
Route::middleware('auth')->group(function () {
    Route::get('/dashboard',  [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/matchmaking', [MatchmakingController::class, 'index'])->name('matchmaking');

    // Events
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events',        [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}',         [EventController::class, 'show'])->name('events.show');
    Route::post('/events/{event}/join',   [EventController::class, 'join'])->name('events.join');
    Route::delete('/events/{event}/leave',  [EventController::class, 'leave'])->name('events.leave');
    Route::delete('/events/{event}/cancel', [EventController::class, 'cancel'])->name('events.cancel');

    // Challenge: pre-fill event form with selected opponent
    Route::get('/events/challenge/{user}', [EventController::class, 'challenge'])->name('events.challenge');

    // My Events
    Route::get('/my-events', [MyEventsController::class, 'index'])->name('my-events');

    // Host: Event Approval (pending join requests)
    Route::get('/my-events/host-requests',               [MyEventsController::class, 'hostRequests'])->name('my-events.host-requests');
    Route::post('/my-events/host-requests/{participant}/approve', [MyEventsController::class, 'approve'])->name('host-requests.approve');
    Route::post('/my-events/host-requests/{participant}/reject',   [MyEventsController::class, 'reject'])->name('host-requests.reject');

    // Challenge invites: opponent accepts/declines their own invite
    Route::post('/my-events/challenges/{participant}/accept',  [MyEventsController::class, 'acceptChallenge'])->name('challenges.accept');
    Route::post('/my-events/challenges/{participant}/decline', [MyEventsController::class, 'declineChallenge'])->name('challenges.decline');

    // Notifications (bell dropdown)
    Route::get ('/notifications/{notification}/open', [NotificationController::class, 'open'])->name('notifications.open');
    Route::post('/notifications/read-all',            [NotificationController::class, 'readAll'])->name('notifications.read-all');

    // Group Chat
    Route::get('/events/{event}/chat',       [ChatController::class, 'show'])->name('chat');
    Route::post('/events/{event}/chat',       [ChatController::class, 'send'])->name('chat.send');
    Route::delete('/events/{event}/chat/{message}', [ChatController::class, 'destroy'])->name('chat.destroy');

    // Connections / Social
    Route::get('/connections',                    [ConnectionController::class, 'index'])->name('connections.index');
    Route::post('/connections',                   [ConnectionController::class, 'store'])->name('connections.store');
    Route::patch('/connections/{connection}',    [ConnectionController::class, 'update'])->name('connections.update');
    Route::get('/connections/{connection}/chat',  [ConnectionController::class, 'chat'])->name('connections.chat');
    Route::post('/connections/{connection}/chat',[ConnectionController::class, 'sendMessage'])->name('connections.sendMessage');

    // Reviews
    Route::get('/reviews/create', [ReviewController::class, 'create'])->name('reviews.create');
    Route::post('/reviews',       [ReviewController::class, 'store'])->name('reviews.store');

    // Profile
    Route::get('/profile',   [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile',  [ProfileController::class, 'update'])->name('profile.update');

    // AI Assistant (RAG chatbot) — floating widget only, no standalone page
    Route::get   ('/chat/sessions',                          [ChatAssistantController::class, 'index'])->name('chat.sessions.index');
    Route::post  ('/chat/sessions',                          [ChatAssistantController::class, 'store'])->name('chat.sessions.store');
    Route::get   ('/chat/sessions/{session}/messages',       [ChatAssistantController::class, 'messages'])->name('chat.sessions.messages');
    Route::post  ('/chat/sessions/{session}/messages',       [ChatAssistantController::class, 'send'])->name('chat.sessions.send');
    Route::delete('/chat/sessions/{session}',                [ChatAssistantController::class, 'destroy'])->name('chat.sessions.destroy');
});
