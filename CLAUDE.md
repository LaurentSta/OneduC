# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Full dev environment (recommended — runs server, queue, logs, and Vite concurrently)
composer dev

# Individual commands
php artisan serve          # Laravel backend only
npm run dev                # Vite frontend hot reload only
npm run build              # Production frontend build
php artisan migrate        # Run pending migrations
php artisan pint           # PHP code formatter (Pint)

# Testing (Pest)
php artisan test                                      # All tests
./vendor/bin/pest tests/Feature/SomeTest.php          # Single file
php artisan test --filter TestName                    # Filter by name
```

## Architecture

**LMS for digital inclusion training** — Laravel 11 (PHP 8.2+) backend, Tailwind CSS v4 + Vite + Alpine.js frontend, MySQL/MariaDB in production.

### Multi-role routing

Each role has its own route file and controller namespace:

| File | Prefix | Middleware | Namespace |
|------|--------|------------|-----------|
| `routes/stagiaire.php` | `/stagiaire` | `auth, role:stagiaire, track.time` | `App\Http\Controllers\Stagiaire\` |
| `routes/formateur.php` | `/formateur` | `auth, role:formateur` | `App\Http\Controllers\Formateur\` |
| `routes/admin.php` | `/admin` | `auth, role:admin` | `App\Http\Controllers\` |
| `routes/observateur.php` | `/observateur` | `auth, role:observateur` | — |

The `force.password.change` middleware (applied as an inner group inside stagiaire/formateur) blocks access until first-login password is set.

### Interactive tool pattern

Every collaborative tool (Word Cloud, Poll, Live Quiz, Question Wall, Whiteboard, Timer, Random Wheel) follows this pattern:

- **DB**: Two tables — a session table (`tool_sessions`) with `group_id`, `formateur_id`, `is_active`, `access_code`, and a responses table (`tool_responses`) with `user_id`, `session_id`
- **Trainer controller** in `app/Http/Controllers/Formateur/` — CRUD + launch/close + JSON results endpoint
- **Trainee controller** in `app/Http/Controllers/Stagiaire/` — show form + submit response
- **Real-time updates**: trainer result pages poll a JSON endpoint every 2–3 seconds via Alpine.js `setInterval` (no WebSockets)
- **Access**: trainees reach tools via their group — controllers verify membership with `$group->students()->where('users.id', auth()->id())->exists()`

Tools are aggregated for the trainee "Outils" dashboard in `StagiaireController::StagiaireOutils()`.

### Group → tool ownership

```
Group (instructor_id, group_user pivot: stagiaire/formateur/observateur)
 └── has many: WordCloud, PollSession, LiveQuizSession, QuestionWall,
               RandomWheelSession, GroupWhiteboard (unique), GroupTimer (unique)
```

Trainers may be the group `instructor` OR a co-trainer via the `group_user` pivot (`role_in_group = 'formateur'`). The `Group::scopeAccessibleByTrainer()` scope covers both cases.

### Learning path (Parcours)

`FormateurParcours` → ordered `FormateurParcoursItem` records (type: `module` | `wordcloud` | `poll` | `activity`). A group has one active parcours via `groups.formateur_parcours_id`. The trainee module list (`StagiaireModules`) renders items in parcours order when a parcours exists.

### SCORM

Dual-path support (legacy + modern) configured in `config/learning_assets.php`. `ScormScore`, `ScormInteraction`, `ScormEvaluationScore` store runtime data. The SCORM JS API wrapper lives in `public/` and communicates via `routes/scorm.php`.

### Services

- `LearningAnalyticsService` — all progress/score aggregations for dashboards
- `QuizService` — quiz attempt logic and scoring
- `CodeGeneratorService` — 6-character alphanumeric access codes for tool sessions

## Design system

Custom Tailwind tokens (defined in `tailwind.config.js`):

| Token | Value | Usage |
|-------|-------|-------|
| `bleuone` | `#004461` | Primary brand / headings |
| `orangeone` | `#E94D2A` | CTAs / highlights |
| `vertone` | `#01c69c` | Success / positive states |
| `font-raleway` | Raleway | Page titles (`text-titre` = 55px) |
| `font-varela` | Varela Round | Subtitles (`text-sous-titre` = 28px) |
| `font-lisible` | OpenDyslexic | Accessible body text |

Reusable button classes: `.btn-oneduc` (orange filled), `.btn-oneduc-outline` (blue outline), `.btn-oneduc-blue` (blue filled).

Card pattern across all dashboards: `bg-white rounded-[20px] shadow-md`.

## Key environment variables

Beyond standard Laravel vars, these are project-specific:

- `HEDGEDOC_BASE_URL` / `HEDGEDOC_NEW_PATH` — self-hosted collaborative pages integration
- `DISCORD_SUPPORT_WEBHOOK_URL` / `DISCORD_SERVER_ID` — support notifications
- `VITE_APP_NAME` — exposed to frontend

Vite HMR host is hardcoded to a dev IP in `vite.config.js` — update it for new dev environments.
