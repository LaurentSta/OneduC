<?php

namespace App\Support;

use App\Models\PilotNotificationPreference;
use App\Models\PilotSubscription;
use App\Models\PilotTask;
use App\Models\User;
use App\Notifications\PilotageTaskNotification;
use Illuminate\Support\Facades\Notification;

class PilotageNotifier
{
    public static function notifyTaskEvent(
        PilotTask $task,
        string $eventType,
        ?User $actor = null,
        array $context = [],
    ): void {
        $task->loadMissing('project', 'responsible');

        $recipientIds = collect();

        if ($task->responsible_id) {
            $recipientIds->push((int) $task->responsible_id);
        }

        if ($task->project?->created_by) {
            $recipientIds->push((int) $task->project->created_by);
        }

        $taskSubscriberIds = PilotSubscription::query()
            ->where('task_id', $task->id)
            ->pluck('user_id');
        $recipientIds = $recipientIds->merge($taskSubscriberIds);

        if ($task->project_id) {
            $projectSubscriberIds = PilotSubscription::query()
                ->where('project_id', $task->project_id)
                ->whereNull('task_id')
                ->pluck('user_id');
            $recipientIds = $recipientIds->merge($projectSubscriberIds);
        }

        $recipientIds = $recipientIds
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($actor) {
            $recipientIds = $recipientIds->reject(fn ($id) => $id === (int) $actor->id)->values();
        }

        if ($recipientIds->isEmpty()) {
            return;
        }

        $users = User::query()->whereIn('id', $recipientIds)->get()->keyBy('id');

        foreach ($recipientIds as $recipientId) {
            $user = $users->get($recipientId);
            if (!$user) {
                continue;
            }

            $channels = self::channelsFor($user, $task, $eventType);
            if ($channels === []) {
                continue;
            }

            Notification::send(
                $user,
                new PilotageTaskNotification($task, $eventType, $actor, $context, $channels)
            );
        }
    }

    /**
     * @return array<int, string>
     */
    private static function channelsFor(User $user, PilotTask $task, string $eventType): array
    {
        $pref = PilotNotificationPreference::firstOrCreate(
            ['user_id' => $user->id],
            [
                'in_app_enabled' => true,
                'email_enabled' => false,
                'frequency' => 'immediate',
                'event_types' => PilotNotificationPreference::defaultEventTypes(),
            ]
        );

        $eventTypes = $pref->event_types ?: PilotNotificationPreference::defaultEventTypes();
        if (!in_array($eventType, $eventTypes, true)) {
            return [];
        }

        if ($pref->frequency !== 'immediate') {
            return [];
        }

        $subscription = PilotSubscription::query()
            ->where('user_id', $user->id)
            ->where(function ($query) use ($task) {
                $query->where('task_id', $task->id);
                if ($task->project_id) {
                    $query->orWhere(function ($q) use ($task) {
                        $q->where('project_id', $task->project_id)->whereNull('task_id');
                    });
                }
            })
            ->orderByRaw('case when task_id is not null then 0 else 1 end')
            ->first();

        $notifyInApp = $pref->in_app_enabled && ($subscription?->notify_in_app ?? true);
        $notifyMail = $pref->email_enabled && ($subscription?->notify_mail ?? false);

        $channels = [];
        if ($notifyInApp) {
            $channels[] = 'database';
        }
        if ($notifyMail) {
            $channels[] = 'mail';
        }

        return $channels;
    }
}

