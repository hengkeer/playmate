# Roadmap — Next Steps

> PlayMate — Planned Improvements
> Last updated: June 2026

---

## Shipped (June 2026)

| # | Item | Notes |
|---|---|---|
| ✅ | **AI Assistant (RAG chatbot)** | Login-gated, Gemini 2.0 Flash + `gemini-embedding-001`, top-5 cosine sim from `docs/`, floating widget + `/chat` page. See `docs/features.md` §16 |

---

## High Priority

| # | Item | Notes |
|---|---|---|
| 1 | **Responsive nav** | Add hamburger menu for mobile view |
| 2 | **Event filtering** | Search + sport + date filters on `/events` browse page |
| 3 | **Profile completeness indicator** | Show % completeness on dashboard |
| 4 | **PlayMate branding** | Custom logo and brand assets |

---

## Medium Priority

| # | Item | Notes |
|---|---|---|
| 5 | **Event recommendations** | Recommend events based on user's sport/skill/location |
| 6 | **Cache matchmaking results** | Short TTL cache on `getRecommendations()` |
| 7 | **Notification system** | Alert users for incoming connection requests |

---

## Low Priority

| # | Item | Notes |
|---|---|---|
| 8 | **Add tests** | No test files exist yet |
| 9 | **Soft deletes** | Add `deleted_at` to models |
| 10 | **Activity log** | Track joins/matches for analytics |
| 11 | **Connection request expiry** | Auto-decline stale pending requests |
| 12 | **ReviewResource** | Filament resource for managing user reviews in admin |