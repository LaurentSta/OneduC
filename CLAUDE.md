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

### Naming convention

For new business-logic classes (`app/Domains/**`, Actions, Support classes, and similar Oneduc-specific code), use French names when possible — e.g. `CreerModule`, `ModifierLecon`, `AccesModule` rather than `CreateModule`, `UpdateLesson`, `ModuleAccess`. Keep English for framework-mandated names (`Controller`, `Model`, `Request`, `Middleware` suffixes, Laravel conventions) and third-party-facing code. When renaming existing files to follow this, confirm the exact names with the user first (naming is subjective and worth a quick check).

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

## Wiki sync

This project documents itself in `docs/wiki/` (mirrored to the GitHub Wiki). Whenever you make a meaningful code change — new feature, refactor, rename, bug fix that changes behavior — update the relevant `docs/wiki/*.md` page(s) in the same session, and say in your summary which pages you touched. Don't wait to be asked.

Pushing those wiki changes to `origin` or syncing the GitHub Wiki still requires explicit confirmation each time, per the git safety rules below — this instruction only covers keeping the local `docs/wiki` files current.

## Git workflow

`main` is a protected branch on GitHub — direct pushes are rejected (`GH013`, "Changes must be made through a pull request"). Any change meant for `main` needs its own `feature/*` branch pushed to `origin`, with a PR opened from that branch. `gh` is not authenticated on this machine, so PRs can't be created or merged from the CLI — push the branch, hand the user the compare/PR link, and let them create and merge it on GitHub.

---

## Behavioral guidelines (Karpathy-inspired)

**Tradeoff:** These guidelines bias toward caution over speed. For trivial tasks, use judgment.

### 1. Think Before Coding

**Don't assume. Don't hide confusion. Surface tradeoffs.**

Before implementing:
- State your assumptions explicitly. If uncertain, ask.
- If multiple interpretations exist, present them - don't pick silently.
- If a simpler approach exists, say so. Push back when warranted.
- If something is unclear, stop. Name what's confusing. Ask.

### 2. Simplicity First

**Minimum code that solves the problem. Nothing speculative.**

- No features beyond what was asked.
- No abstractions for single-use code.
- No "flexibility" or "configurability" that wasn't requested.
- No error handling for impossible scenarios.
- If you write 200 lines and it could be 50, rewrite it.

Ask yourself: "Would a senior engineer say this is overcomplicated?" If yes, simplify.

### 3. Surgical Changes

**Touch only what you must. Clean up only your own mess.**

When editing existing code:
- Don't "improve" adjacent code, comments, or formatting.
- Don't refactor things that aren't broken.
- Match existing style, even if you'd do it differently.
- If you notice unrelated dead code, mention it - don't delete it.

When your changes create orphans:
- Remove imports/variables/functions that YOUR changes made unused.
- Don't remove pre-existing dead code unless asked.

The test: Every changed line should trace directly to the user's request.

### 4. Goal-Driven Execution

**Define success criteria. Loop until verified.**

Transform tasks into verifiable goals:
- "Add validation" → "Write tests for invalid inputs, then make them pass"
- "Fix the bug" → "Write a test that reproduces it, then make it pass"
- "Refactor X" → "Ensure tests pass before and after"

For multi-step tasks, state a brief plan:
```
1. [Step] → verify: [check]
2. [Step] → verify: [check]
3. [Step] → verify: [check]
```

Strong success criteria let you loop independently. Weak criteria ("make it work") require constant clarification.

---

**These guidelines are working if:** fewer unnecessary changes in diffs, fewer rewrites due to overcomplication, and clarifying questions come before implementation rather than after mistakes.
