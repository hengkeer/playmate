<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role',
        'photo_url', 'bio', 'gender', 'age_range',
        'home_address', 'latitude', 'longitude',
        'play_style', 'last_active_at', 'total_events_joined',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'latitude'          => 'float',
            'longitude'         => 'float',
            'last_active_at'    => 'datetime',
        ];
    }

    // ─── Relationships ─────────────────────────────────────────────────────��──

    public function userSports()
    {
        return $this->hasMany(UserSport::class);
    }

    public function sports()
    {
        return $this->belongsToMany(Sport::class, 'user_sports')
            ->withPivot('skill_value', 'skill_number', 'play_style')
            ->withTimestamps();
    }

    public function hostedEvents()
    {
        return $this->hasMany(Event::class, 'host_id');
    }

    public function eventParticipations()
    {
        return $this->hasMany(EventParticipant::class);
    }

    public function joinedEvents()
    {
        return $this->belongsToMany(Event::class, 'event_participants')
            ->withPivot('status', 'slot_number', 'joined_at')
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(EventMessage::class);
    }

    /**
     * Overrides Notifiable's polymorphic notifications() with our own simple, user-scoped table.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class)->orderByDesc('created_at');
    }

    public function unreadNotifications()
    {
        return $this->notifications()->unread();
    }

    public function matches()
    {
        return $this->hasMany(GameMatch::class);
    }

    // ─── Connections ─────────────────────────────────────────────────────────

    public function sentConnections()
    {
        return $this->hasMany(Connection::class, 'requester_id');
    }

    public function receivedConnections()
    {
        return $this->hasMany(Connection::class, 'receiver_id');
    }

    public function acceptedConnections()
    {
        return Connection::where('status', 'accepted')
            ->where(function ($q) {
                $q->where('requester_id', $this->id)
                  ->orWhere('receiver_id', $this->id);
            });
    }

    // ─── Reviews ─────────────────────────────────────────────────────────────

    public function reviewsReceived()
    {
        return $this->hasMany(UserReview::class, 'reviewed_user_id');
    }

    public function reviewsGiven()
    {
        return $this->hasMany(UserReview::class, 'reviewer_id');
    }

    public function getAverageRatingAttribute(): ?float
    {
        $avg = $this->reviewsReceived()->avg('rating');
        return $avg ? round($avg, 1) : null;
    }

    public function getTotalReviewsAttribute(): int
    {
        return $this->reviewsReceived()->count();
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Get skill number for a specific sport.
     */
    public function getSkillForSport(int $sportId): ?int
    {
        return $this->userSports->where('sport_id', $sportId)->first()?->skill_number;
    }

    /**
     * Calculate distance in km from another user.
     */
    public function distanceFromUser(User $other): ?float
    {
        if (!$this->latitude || !$this->longitude || !$other->latitude || !$other->longitude) {
            return null;
        }

        $R = 6371;
        $dLat = deg2rad($other->latitude - $this->latitude);
        $dLng = deg2rad($other->longitude - $this->longitude);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($this->latitude)) * cos(deg2rad($other->latitude))
            * sin($dLng / 2) ** 2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * Check if user has played a sport recently.
     */
    public function isRecentlyActive(int $days = 7): bool
    {
        return $this->last_active_at
            && $this->last_active_at->diffInDays(now()) <= $days;
    }

    public function avatarInitial(): string
    {
        return strtoupper(mb_substr($this->name, 0, 1, 'UTF-8'));
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * FilamentUser interface — gate admin panel to admins only.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
    }
}