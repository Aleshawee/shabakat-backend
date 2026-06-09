# Sports Predictions — Match-to-Event Flow

Backend: `backend/app/Http/Controllers/Admin/MatchApiController.php`  
Frontend: `admin-panel/src/pages/predictions/Index.vue`

## Date & Timezone
- API is 1 day behind real dates → controller **always adds +1 day** when querying: `$apiDate = date('Ymd', strtotime($selectedDate . ' + 1 day'));`
- `Esd` from API is UTC → converted to **local time (UTC+8)** via manual `gmmktime + 8*3600` calculation, formatted with `gmdate()` to avoid server timezone double-apply
- `match_time` = local hours:minutes, `match_date` = local ISO datetime `Y-m-d\TH:i:s`

## Arabic Translation
`MatchApiController` has a `$arabicDictionary` (~200 entries) that translates:
- **Countries** (England→إنجلترا, Spain→إسبانيا, Saudi Arabia→السعودية, etc.)
- **Leagues** (Premier League→الدوري الإنجليزي الممتاز, Champions League→دوري أبطال أوروبا, etc.)
- **Teams** (Manchester City→مانشستر سيتي, Real Madrid→ريال مدريد, Al Hilal→الهلال, etc.)

The `translate($name)` method is applied to `league.name`, `league.country`, `match.home_team`, and `match.away_team` before returning the JSON response.

## `loadMatches()`
Called on **mount** and on date changes.  
Fetches from `GET /api/admin/v1/predictions/matches` with param `date` only.  
Returns `leagues[]` (each with `matches[]`), `matches[]`, `remaining_requests`.

## `matchToEvent(match, league)`
Pre-fills the create-event modal with:
- `form.title` = `"{home_team} vs {away_team}"`
- `form.home_team`, `form.away_team` from match data (already translated)
- `form.option_a_image`, `form.option_b_image` from `match.home_img` / `match.away_img`
- `form.match_date` = `subDay(match.match_date)` (compensate +1 day API offset)
- `form.prediction_deadline` = `subHour(subDay(match.match_date), 1)` (1h before match)
- `form.event_type = 'match'`, `form.prediction_type = 'winner'`

Sets `editingEvent = null` and opens `showFormModal = true`.

## `addAllToEvents(league)`
Batch-creates events for all **upcoming** matches in a league.  
Same date logic as `matchToEvent`.  
Calls `loadEvents()` on success and shows a count alert.

## Helpers
- `subDay(dt)` — subtracts 1 day from `YYYY-MM-DDTHH:mm`, preserving time; uses `new Date(y, m-1, d)` for safe local date math
- `subHour(dt, n)` — subtracts `n` hours from `YYYY-MM-DDTHH:mm` via `new Date(y, mo, d, hh, mm)`, handles day wrapping
- `formatCardTime("HH:mm")` — converts 24h → 12h with Arabic صباحاً/مساءً (e.g. `"22:00"` → `"10:00 مساءً"`)

## Match Card UI
Redesigned with:
- **Status pill** in top bar (live: red pulse, finished: grey ✅, upcoming: green 📅)
- **Round label** styled as "الجولة X" (grey badge, top-left)
- **Team logos** in circular gradient containers (w-11 h-11) with fallback ⚽ icon
- **Match time** displayed in amber box above VS, formatted as `10:00 مساءً` (12h + Arabic AM/PM via `formatCardTime()`)
- **Score** in gradient-indigo rounded box (finished/live) or **VS** in gradient-emerald rounded box (upcoming)
- **Full-width action button** "إضافة كحدث مسابقة" for upcoming matches only

## Timezone Critical
- All stored `match_date`/`prediction_deadline` values are **numeric Riyadh time (UTC+3)** — NOT UTC
- Frontend extracts time from ISO via regex `T(\d{2}:\d{2})` or UTC methods to bypass browser timezone
- Backend deadline comparisons use `Carbon::now('Asia/Riyadh')` NOT `now()` (which returns UTC)

## Auto-Close
`php artisan sport-events:auto-close` runs every minute via scheduler.
Closes open events where `prediction_deadline < now('Asia/Riyadh')`.

## Reward Flow
1. User predicts → points deducted (PointTransaction: type=`prediction`)
2. Admin closes event manually or auto-close runs
3. Admin enters result (score + winner)
4. On `status=completed` + `winner` set → `processPredictions()` auto-runs:
   - Marks winners (`is_winner=true/false`)
   - Credits winners: `reward_per_winner + points_bet`
   - Refunds losers: `points_bet`
   - Sets `rewards_distributed = true`
5. Re-editing result on completed event re-runs distribution (skips `rewards_distributed` check)

## Edit Fee Logic
| `edit_fee` value | Behavior |
|------------------|----------|
| **0** (default) | Refund old bet + re-deduct new bet |
| **> 0** | Charge fee only, old bet stays, new prediction replaces old |

## API Endpoints — Admin

| Method | Path | Usage |
|--------|------|-------|
| GET | `/api/admin/v1/predictions/matches` | Fetch matches by date (param: `date`) |
| GET | `/api/admin/v1/sport-events/stats` | **← MUST be placed before apiResource** — returns stats JSON |
| GET | `/api/admin/v1/sport-events` | List events (params: `page`, `per_page`, `status`) |
| POST | `/api/admin/v1/sport-events` | Create a single sport event |
| GET | `/api/admin/v1/sport-events/{id}` | Show event with predictions |
| PUT | `/api/admin/v1/sport-events/{id}` | Update event (close, set result, edit fields) |
| DELETE | `/api/admin/v1/sport-events/{id}` | Delete event |
| POST | `/api/admin/v1/sport-events/{id}/distribute` | Manually distribute rewards (only if `rewards_distributed=false`) |
| GET | `/api/admin/v1/predictions/settings` | Get prediction settings |
| PUT | `/api/admin/v1/predictions/settings` | Save prediction settings (includes `edit_fee`) |

## API Endpoints — User

| Method | Path | Usage |
|--------|------|-------|
| GET | `/api/v1/sport-events` | List active events (includes `enabled` flag) |
| GET | `/api/v1/sport-events/{id}` | Show event details |
| POST | `/api/v1/sport-events/{id}/predict` | Place/edit prediction |
| GET | `/api/v1/user/predictions` | My predictions (upcoming/history) |
