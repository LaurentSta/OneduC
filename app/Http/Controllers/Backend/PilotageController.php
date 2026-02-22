<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ActivityJournalEntry;
use App\Models\Module;
use App\Models\PilotNotificationPreference;
use App\Models\PilotProject;
use App\Models\PilotSubscription;
use App\Models\PilotTask;
use App\Models\PilotTaskComment;
use App\Models\User;
use App\Support\PilotageNotifier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PilotageController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'search' => trim((string) $request->string('search')),
            'status' => (string) $request->input('status', ''),
            'task_type' => (string) $request->input('task_type', ''),
            'module_id' => (string) $request->input('module_id', ''),
            'responsible_id' => (string) $request->input('responsible_id', ''),
            'priority' => (string) $request->input('priority', ''),
            'due_filter' => (string) $request->input('due_filter', ''),
        ];

        $baseQuery = PilotTask::query()
            ->with(['project:id,name', 'responsible:id,username,name,prenom', 'module:id,module_title,module_name', 'creator:id,username,name'])
            ->withCount('comments');
        $this->applyTaskFilters($baseQuery, $filters);

        $tasks = (clone $baseQuery)
            ->orderByRaw("case priority when 'high' then 0 when 'normal' then 1 else 2 end")
            ->orderByRaw("case when due_date is null then 1 else 0 end")
            ->orderBy('due_date')
            ->orderByDesc('updated_at')
            ->paginate(25)
            ->withQueryString();

        $kanbanTasks = (clone $baseQuery)
            ->orderBy('position')
            ->orderBy('id')
            ->limit(400)
            ->get()
            ->groupBy('status');

        $statusCounterQuery = PilotTask::query();
        $this->applyTaskFilters($statusCounterQuery, Arr::except($filters, ['status']));
        $statusCounts = array_fill_keys(array_keys(PilotTask::STATUSES), 0);
        $statusCounterQuery
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->each(function ($total, $status) use (&$statusCounts) {
                if (array_key_exists($status, $statusCounts)) {
                    $statusCounts[$status] = (int) $total;
                }
            });

        $projects = PilotProject::query()
            ->with(['module:id,module_title,module_name', 'creator:id,username,name'])
            ->withCount('tasks')
            ->withCount(['tasks as tasks_done_count' => fn ($query) => $query->where('status', 'done')])
            ->orderByDesc('updated_at')
            ->get();

        $modules = Module::query()
            ->select('id', 'module_title', 'module_name')
            ->orderBy('module_title')
            ->orderBy('module_name')
            ->get();

        $users = User::query()
            ->where('status', true)
            ->select('id', 'username', 'name', 'prenom', 'email')
            ->orderBy('username')
            ->orderBy('name')
            ->get();

        $projectSubscribers = PilotSubscription::query()
            ->whereNull('task_id')
            ->get()
            ->groupBy('project_id')
            ->map(fn ($rows) => $rows->pluck('user_id')->map(fn ($id) => (int) $id)->all());
        $projectMailSubscribers = PilotSubscription::query()
            ->whereNull('task_id')
            ->where('notify_mail', true)
            ->get()
            ->groupBy('project_id')
            ->map(fn ($rows) => $rows->pluck('user_id')->map(fn ($id) => (int) $id)->all());

        return view('admin.backend.pilotage.index', [
            'tasks' => $tasks,
            'kanbanTasks' => $kanbanTasks,
            'statusCounts' => $statusCounts,
            'statuses' => PilotTask::STATUSES,
            'priorities' => PilotTask::PRIORITIES,
            'types' => PilotTask::TYPES,
            'projects' => $projects,
            'modules' => $modules,
            'users' => $users,
            'filters' => $filters,
            'projectSubscribers' => $projectSubscribers,
            'projectMailSubscribers' => $projectMailSubscribers,
        ]);
    }

    public function storeProject(Request $request)
    {
        $validated = $this->validateProject($request);

        $project = PilotProject::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'module_id' => $validated['module_id'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        $this->syncProjectSubscriptions(
            $project,
            $validated['subscribers'] ?? [],
            $validated['mail_subscribers'] ?? []
        );

        return redirect()
            ->route('admin.pilotage.index')
            ->with('success', 'Projet de pilotage cree.');
    }

    public function updateProject(Request $request, PilotProject $project)
    {
        $validated = $this->validateProject($request);

        $project->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'module_id' => $validated['module_id'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
        ]);

        $this->syncProjectSubscriptions(
            $project,
            $validated['subscribers'] ?? [],
            $validated['mail_subscribers'] ?? []
        );

        return redirect()
            ->route('admin.pilotage.index')
            ->with('success', 'Projet de pilotage mis a jour.');
    }

    public function destroyProject(PilotProject $project)
    {
        $project->delete();

        return redirect()
            ->route('admin.pilotage.index')
            ->with('success', 'Projet supprime.');
    }

    public function storeTask(Request $request)
    {
        $validated = $this->validateTask($request);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('pilotage/attachments', 'public');
        }

        $status = $validated['status'];

        $task = PilotTask::create([
            'project_id' => $validated['project_id'] ?? null,
            'module_id' => $validated['module_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $status,
            'priority' => $validated['priority'],
            'due_date' => $validated['due_date'] ?? null,
            'responsible_id' => $validated['responsible_id'] ?? null,
            'task_type' => $validated['task_type'],
            'internal_url' => $this->normalizeInternalUrl($validated['internal_url'] ?? null),
            'attachment_path' => $attachmentPath,
            'position' => $this->nextPosition($status),
            'created_by' => $request->user()->id,
        ]);

        $this->syncTaskSubscriptions(
            $task,
            $validated['subscribers'] ?? [],
            $validated['mail_subscribers'] ?? []
        );

        PilotageNotifier::notifyTaskEvent($task, 'created', $request->user());
        if (!empty($validated['responsible_id'])) {
            PilotageNotifier::notifyTaskEvent($task, 'assigned', $request->user());
        }

        return redirect()
            ->route('admin.pilotage.index')
            ->with('success', 'Tache creee.');
    }

    public function editTask(PilotTask $task)
    {
        $task->load([
            'project:id,name',
            'module:id,module_title,module_name',
            'responsible:id,username,name,prenom',
            'comments.user:id,username,name,prenom',
            'subscriptions:user_id,task_id,notify_mail',
        ]);

        $projects = PilotProject::query()->select('id', 'name')->orderBy('name')->get();
        $modules = Module::query()->select('id', 'module_title', 'module_name')->orderBy('module_title')->orderBy('module_name')->get();
        $users = User::query()
            ->where('status', true)
            ->select('id', 'username', 'name', 'prenom', 'email')
            ->orderBy('username')
            ->orderBy('name')
            ->get();

        $selectedSubscribers = $task->subscriptions->pluck('user_id')->map(fn ($id) => (int) $id)->all();
        $selectedMailSubscribers = $task->subscriptions
            ->where('notify_mail', true)
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return view('admin.backend.pilotage.task_edit', [
            'task' => $task,
            'projects' => $projects,
            'modules' => $modules,
            'users' => $users,
            'statuses' => PilotTask::STATUSES,
            'priorities' => PilotTask::PRIORITIES,
            'types' => PilotTask::TYPES,
            'selectedSubscribers' => $selectedSubscribers,
            'selectedMailSubscribers' => $selectedMailSubscribers,
        ]);
    }

    public function updateTask(Request $request, PilotTask $task)
    {
        $validated = $this->validateTask($request, true);

        $oldStatus = $task->status;
        $oldResponsibleId = $task->responsible_id;

        $updates = [
            'project_id' => $validated['project_id'] ?? null,
            'module_id' => $validated['module_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'due_date' => $validated['due_date'] ?? null,
            'responsible_id' => $validated['responsible_id'] ?? null,
            'task_type' => $validated['task_type'],
            'internal_url' => $this->normalizeInternalUrl($validated['internal_url'] ?? null),
        ];

        if ($request->boolean('remove_attachment') && $task->attachment_path) {
            Storage::disk('public')->delete($task->attachment_path);
            $updates['attachment_path'] = null;
        }

        if ($request->hasFile('attachment')) {
            if ($task->attachment_path) {
                Storage::disk('public')->delete($task->attachment_path);
            }
            $updates['attachment_path'] = $request->file('attachment')->store('pilotage/attachments', 'public');
        }

        if ($oldStatus !== $validated['status']) {
            $updates['position'] = $this->nextPosition($validated['status']);
        }

        $task->update($updates);

        $this->syncTaskSubscriptions(
            $task,
            $validated['subscribers'] ?? [],
            $validated['mail_subscribers'] ?? []
        );

        if ($oldStatus !== $task->status) {
            PilotageNotifier::notifyTaskEvent($task, 'status_changed', $request->user(), [
                'old_status' => $oldStatus,
                'new_status' => $task->status,
            ]);
        }

        if ($oldResponsibleId !== $task->responsible_id && !empty($task->responsible_id)) {
            PilotageNotifier::notifyTaskEvent($task, 'assigned', $request->user(), [
                'old_responsible_id' => $oldResponsibleId,
                'new_responsible_id' => $task->responsible_id,
            ]);
        }

        PilotageNotifier::notifyTaskEvent($task, 'updated', $request->user());

        return redirect()
            ->route('admin.pilotage.tasks.edit', $task)
            ->with('success', 'Tache mise a jour.');
    }

    public function destroyTask(PilotTask $task)
    {
        if ($task->attachment_path) {
            Storage::disk('public')->delete($task->attachment_path);
        }

        $task->delete();

        return redirect()
            ->route('admin.pilotage.index')
            ->with('success', 'Tache supprimee.');
    }

    public function moveTask(Request $request, PilotTask $task)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(PilotTask::STATUSES))],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        $oldStatus = $task->status;
        $newStatus = $validated['status'];
        $targetPosition = $validated['position'] ?? null;

        DB::transaction(function () use ($task, $oldStatus, $newStatus, $targetPosition) {
            $taskIds = PilotTask::query()
                ->where('status', $newStatus)
                ->whereKeyNot($task->id)
                ->orderBy('position')
                ->orderBy('id')
                ->pluck('id')
                ->values()
                ->all();

            $insertPosition = $targetPosition;
            if ($insertPosition === null || $insertPosition > count($taskIds)) {
                $insertPosition = count($taskIds);
            }

            array_splice($taskIds, $insertPosition, 0, [$task->id]);

            foreach ($taskIds as $position => $taskId) {
                PilotTask::query()
                    ->whereKey($taskId)
                    ->update([
                        'status' => $newStatus,
                        'position' => $position,
                    ]);
            }

            if ($oldStatus !== $newStatus) {
                $this->normalizePositions($oldStatus);
            }
        });

        $task->refresh();

        if ($oldStatus !== $task->status) {
            PilotageNotifier::notifyTaskEvent($task, 'status_changed', $request->user(), [
                'old_status' => $oldStatus,
                'new_status' => $task->status,
            ]);
        }

        return response()->json([
            'ok' => true,
            'task_id' => $task->id,
            'status' => $task->status,
        ]);
    }

    public function storeComment(Request $request, PilotTask $task)
    {
        $validated = $request->validate([
            'comment' => ['required', 'string', 'min:2', 'max:3000'],
        ]);

        PilotTaskComment::create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'body' => $validated['comment'],
        ]);

        PilotageNotifier::notifyTaskEvent($task, 'comment_added', $request->user());

        return back()->with('success', 'Commentaire ajoute.');
    }

    public function journal(Request $request)
    {
        $query = ActivityJournalEntry::query()
            ->with('user:id,username,name,prenom')
            ->latest('created_at');

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function (Builder $builder) use ($search) {
                $builder
                    ->where('action', 'like', '%' . $search . '%')
                    ->orWhere('route_name', 'like', '%' . $search . '%')
                    ->orWhere('url', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('action')) {
            $query->where('action', $request->string('action'));
        }

        if ($request->filled('route_name')) {
            $query->where('route_name', $request->string('route_name'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', (int) $request->input('user_id'));
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->date('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->date('to_date'));
        }

        $entries = $query->paginate(40)->withQueryString();

        $actions = ActivityJournalEntry::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $users = User::query()
            ->where('role', 'admin')
            ->select('id', 'username', 'name', 'prenom')
            ->orderBy('username')
            ->orderBy('name')
            ->get();

        return view('admin.backend.pilotage.journal', [
            'entries' => $entries,
            'actions' => $actions,
            'users' => $users,
            'filters' => $request->only([
                'search',
                'action',
                'route_name',
                'user_id',
                'from_date',
                'to_date',
            ]),
        ]);
    }

    public function notifications(Request $request)
    {
        $preference = PilotNotificationPreference::firstOrCreate(
            ['user_id' => $request->user()->id],
            [
                'in_app_enabled' => true,
                'email_enabled' => false,
                'frequency' => 'immediate',
                'event_types' => PilotNotificationPreference::defaultEventTypes(),
            ]
        );

        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.backend.pilotage.notifications', [
            'notifications' => $notifications,
            'preference' => $preference,
            'eventTypes' => PilotTask::EVENT_TYPES,
            'frequencies' => PilotNotificationPreference::FREQUENCIES,
        ]);
    }

    public function markNotificationRead(Request $request, string $notificationId)
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $notificationId)
            ->firstOrFail();

        if (!$notification->read_at) {
            $notification->markAsRead();
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back();
    }

    public function markAllNotificationsRead(Request $request)
    {
        /** @var \Illuminate\Support\Collection<int, DatabaseNotification> $notifications */
        $notifications = $request->user()->unreadNotifications;
        $notifications->markAsRead();

        return back()->with('success', 'Toutes les notifications ont ete marquees comme lues.');
    }

    public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'in_app_enabled' => ['nullable', 'boolean'],
            'email_enabled' => ['nullable', 'boolean'],
            'frequency' => ['required', Rule::in(array_keys(PilotNotificationPreference::FREQUENCIES))],
            'event_types' => ['nullable', 'array'],
            'event_types.*' => ['string', Rule::in(array_keys(PilotTask::EVENT_TYPES))],
        ]);

        $preference = PilotNotificationPreference::firstOrCreate(
            ['user_id' => $request->user()->id],
            [
                'in_app_enabled' => true,
                'email_enabled' => false,
                'frequency' => 'immediate',
                'event_types' => PilotNotificationPreference::defaultEventTypes(),
            ]
        );

        $preference->update([
            'in_app_enabled' => (bool) ($validated['in_app_enabled'] ?? false),
            'email_enabled' => (bool) ($validated['email_enabled'] ?? false),
            'frequency' => $validated['frequency'],
            'event_types' => array_values(array_unique($validated['event_types'] ?? [])),
        ]);

        return back()->with('success', 'Preferences de notification enregistrees.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateProject(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'module_id' => ['nullable', 'integer', 'exists:modules,id'],
            'due_date' => ['nullable', 'date'],
            'subscribers' => ['nullable', 'array'],
            'subscribers.*' => ['integer', 'exists:users,id'],
            'mail_subscribers' => ['nullable', 'array'],
            'mail_subscribers.*' => ['integer', 'exists:users,id'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateTask(Request $request, bool $isUpdate = false): array
    {
        $rules = [
            'project_id' => ['nullable', 'integer', 'exists:pilot_projects,id'],
            'module_id' => ['nullable', 'integer', 'exists:modules,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(array_keys(PilotTask::STATUSES))],
            'priority' => ['required', Rule::in(array_keys(PilotTask::PRIORITIES))],
            'due_date' => ['nullable', 'date'],
            'responsible_id' => ['nullable', 'integer', 'exists:users,id'],
            'task_type' => ['required', Rule::in(array_keys(PilotTask::TYPES))],
            'internal_url' => ['nullable', 'string', 'max:2048'],
            'subscribers' => ['nullable', 'array'],
            'subscribers.*' => ['integer', 'exists:users,id'],
            'mail_subscribers' => ['nullable', 'array'],
            'mail_subscribers.*' => ['integer', 'exists:users,id'],
        ];

        if ($isUpdate) {
            $rules['attachment'] = ['nullable', 'file', 'max:12288'];
            $rules['remove_attachment'] = ['nullable', 'boolean'];
        } else {
            $rules['attachment'] = ['nullable', 'file', 'max:12288'];
        }

        return $request->validate($rules);
    }

    private function applyTaskFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $builder) use ($search) {
                $builder
                    ->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if (!empty($filters['status']) && isset(PilotTask::STATUSES[$filters['status']])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['task_type']) && isset(PilotTask::TYPES[$filters['task_type']])) {
            $query->where('task_type', $filters['task_type']);
        }

        if (!empty($filters['module_id']) && ctype_digit((string) $filters['module_id'])) {
            $query->where('module_id', (int) $filters['module_id']);
        }

        if (!empty($filters['responsible_id']) && ctype_digit((string) $filters['responsible_id'])) {
            $query->where('responsible_id', (int) $filters['responsible_id']);
        }

        if (!empty($filters['priority']) && isset(PilotTask::PRIORITIES[$filters['priority']])) {
            $query->where('priority', $filters['priority']);
        }

        if (!empty($filters['due_filter'])) {
            $today = now()->startOfDay();
            $endOfWeek = now()->endOfWeek();
            $todayDate = $today->toDateString();
            $endOfWeekDate = $endOfWeek->toDateString();

            match ($filters['due_filter']) {
                'overdue' => $query->whereDate('due_date', '<', $todayDate)->where('status', '!=', 'done'),
                'today' => $query->whereDate('due_date', '=', $todayDate),
                'this_week' => $query->whereDate('due_date', '>=', $todayDate)->whereDate('due_date', '<=', $endOfWeekDate),
                'no_due' => $query->whereNull('due_date'),
                default => null,
            };
        }
    }

    private function normalizeInternalUrl(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        $trimmed = trim($url);
        if ($trimmed === '') {
            return null;
        }

        if (str_starts_with($trimmed, 'http://') || str_starts_with($trimmed, 'https://') || str_starts_with($trimmed, '/')) {
            return $trimmed;
        }

        return '/' . ltrim($trimmed, '/');
    }

    private function nextPosition(string $status): int
    {
        return (int) PilotTask::query()->where('status', $status)->max('position') + 1;
    }

    private function normalizePositions(string $status): void
    {
        $ids = PilotTask::query()
            ->where('status', $status)
            ->orderBy('position')
            ->orderBy('id')
            ->pluck('id')
            ->values()
            ->all();

        foreach ($ids as $position => $taskId) {
            PilotTask::query()->whereKey($taskId)->update(['position' => $position]);
        }
    }

    /**
     * @param array<int, int|string> $subscriberIds
     * @param array<int, int|string> $mailSubscriberIds
     */
    private function syncProjectSubscriptions(PilotProject $project, array $subscriberIds, array $mailSubscriberIds): void
    {
        $subscriberIds = collect($subscriberIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();
        $mailSubscriberIds = collect($mailSubscriberIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        PilotSubscription::query()
            ->where('project_id', $project->id)
            ->whereNull('task_id')
            ->delete();

        foreach ($subscriberIds as $userId) {
            PilotSubscription::create([
                'user_id' => $userId,
                'project_id' => $project->id,
                'task_id' => null,
                'notify_in_app' => true,
                'notify_mail' => $mailSubscriberIds->contains($userId),
                'frequency' => 'immediate',
            ]);
        }
    }

    /**
     * @param array<int, int|string> $subscriberIds
     * @param array<int, int|string> $mailSubscriberIds
     */
    private function syncTaskSubscriptions(PilotTask $task, array $subscriberIds, array $mailSubscriberIds): void
    {
        $subscriberIds = collect($subscriberIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();
        $mailSubscriberIds = collect($mailSubscriberIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        PilotSubscription::query()
            ->where('task_id', $task->id)
            ->delete();

        foreach ($subscriberIds as $userId) {
            PilotSubscription::create([
                'user_id' => $userId,
                'project_id' => $task->project_id,
                'task_id' => $task->id,
                'notify_in_app' => true,
                'notify_mail' => $mailSubscriberIds->contains($userId),
                'frequency' => 'immediate',
            ]);
        }
    }
}
