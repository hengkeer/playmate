# Routes

> PlayMate — Routes Reference
> File: `routes/web.php`
> Last updated: July 2026

---

## 1. Public Routes (No Auth Required)

| Method | URI | Controller@Method | View |
|---|---|---|---|
| GET | `/` | `DashboardController@index` | `welcome.blade.php` — Landing page |
| GET | `/events` | `EventController@index` | `events/index` — Event grid |
| GET | `/venues` | `VenueController@index` | `venues/index` |
| GET | `/venues/search` | `VenueController@search` | JSON autocomplete (no view) |
| GET | `/venues/{venue}` | `VenueController@show` | `venues/show` |
| GET | `/players/{user}` | `ConnectionController@playerProfile` | `players/profile` |
| GET | `/login` | — | `auth/login` |
| POST | `/login` | — | Auth attempt |
| POST | `/logout` | — | Destroy session |
| GET | `/register` | — | `auth/register` |
| POST | `/register` | — | Create user + login |

---

## 2. Auth-Protected Routes

### Dashboard
| Method | URI | Controller@Method |
|---|---|---|
| GET | `/dashboard` | `DashboardController@index` |
| GET | `/matchmaking` | `MatchmakingController@index` |

### Events
| Method | URI | Controller@Method |
|---|---|---|
| GET | `/events/create` | `EventController@create` |
| POST | `/events` | `EventController@store` |
| GET | `/events/{event}` | `EventController@show` |
| POST | `/events/{event}/join` | `EventController@join` |
| DELETE | `/events/{event}/leave` | `EventController@leave` |
| DELETE | `/events/{event}/cancel` | `EventController@cancel` |
| GET | `/events/challenge/{user}` | `EventController@challenge` |

### My Events
| Method | URI | Controller@Method | Notes |
|---|---|---|---|
| GET | `/my-events` | `MyEventsController@index` | Tabs: Upcoming, Hosted, Waiting, Pending, Incoming Challenges, Past, Cancelled |
| GET | `/my-events/host-requests` | `MyEventsController@hostRequests` | Pending join request queue |
| POST | `/my-events/host-requests/{participant}/approve` | `MyEventsController@approve` | Host approves join request |
| POST | `/my-events/host-requests/{participant}/reject` | `MyEventsController@reject` | Host rejects join request |
| POST | `/my-events/challenges/{participant}/accept` | `MyEventsController@acceptChallenge` | Opponent accepts challenge invite |
| POST | `/my-events/challenges/{participant}/decline` | `MyEventsController@declineChallenge` | Opponent declines challenge invite |

### Notifications
| Method | URI | Controller@Method | Notes |
|---|---|---|---|
| GET | `/notifications/{notification}/open` | `NotificationController@open` | Mark as read + redirect to `action_url` |
| POST | `/notifications/read-all` | `NotificationController@readAll` | Mark all unread as read |

### Group Chat (per event)
| Method | URI | Controller@Method |
|---|---|---|
| GET | `/events/{event}/chat` | `ChatController@show` |
| POST | `/events/{event}/chat` | `ChatController@send` |
| DELETE | `/events/{event}/chat/{message}` | `ChatController@destroy` |

### Connections / Private Chat
| Method | URI | Controller@Method |
|---|---|---|
| GET | `/connections` | `ConnectionController@index` |
| POST | `/connections` | `ConnectionController@store` |
| PATCH | `/connections/{connection}` | `ConnectionController@update` |
| GET | `/connections/{connection}/chat` | `ConnectionController@chat` |
| POST | `/connections/{connection}/chat` | `ConnectionController@sendMessage` |

### Reviews
| Method | URI | Controller@Method |
|---|---|---|
| GET | `/reviews/create` | `ReviewController@create` |
| POST | `/reviews` | `ReviewController@store` |

### Profile
| Method | URI | Controller@Method |
|---|---|---|
| GET | `/profile` | `ProfileController@index` |
| POST | `/profile` | `ProfileController@update` |

### Legacy
| Method | URI | Controller@Method |
|---|---|---|
| GET | `/field-availability` | `FieldAvailabilityController@index` |

### AI Assistant (RAG Chatbot — June 2026)
| Method | URI | Controller@Method | Notes |
|---|---|---|---|
| GET | `/chat` | `ChatAssistantController@show` | Full-page chat |
| GET | `/chat/sessions` | `ChatAssistantController@index` | JSON list sidebar |
| POST | `/chat/sessions` | `ChatAssistantController@store` | Create session |
| GET | `/chat/sessions/{session}/messages` | `ChatAssistantController@messages` | JSON messages |
| POST | `/chat/sessions/{session}/messages` | `ChatAssistantController@send` | Send + Gemini reply |
| DELETE | `/chat/sessions/{session}` | `ChatAssistantController@destroy` | Delete session |

---

## 3. Filament Admin Panel

| URL | Notes |
|---|---|
| `/admin` | Requires authentication |

All resources auto-discovered via `discoverResources(in: app_path('Filament/Resources'))`.