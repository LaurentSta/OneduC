<?php

namespace App\Notifications;

use App\Models\PilotTask;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PilotageTaskNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly PilotTask $task,
        private readonly string $eventType,
        private readonly ?User $actor,
        private readonly array $context = [],
        private readonly array $channels = ['database'],
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $title = $this->notificationTitle();
        $message = $this->notificationMessage();

        return (new MailMessage)
            ->subject('[Oneduc Pilotage] ' . $title)
            ->line($message)
            ->action('Ouvrir la tache', url(route('admin.pilotage.tasks.edit', $this->task->id, false)));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->task->loadMissing('project');

        return [
            'event' => $this->eventType,
            'title' => $this->notificationTitle(),
            'message' => $this->notificationMessage(),
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'task_status' => $this->task->status,
            'task_priority' => $this->task->priority,
            'project_id' => $this->task->project_id,
            'project_name' => $this->task->project?->name,
            'url' => route('admin.pilotage.tasks.edit', $this->task->id),
            'context' => $this->context,
            'actor' => $this->actor?->username ?: $this->actor?->name,
            'created_at' => now()->toIso8601String(),
        ];
    }

    private function notificationTitle(): string
    {
        return match ($this->eventType) {
            'created' => 'Nouvelle tache',
            'updated' => 'Tache mise a jour',
            'assigned' => 'Tache assignee',
            'status_changed' => 'Statut de tache modifie',
            'comment_added' => 'Nouveau commentaire',
            default => 'Notification pilotage',
        };
    }

    private function notificationMessage(): string
    {
        $actor = $this->actor?->username ?: $this->actor?->name ?: 'Un utilisateur';

        return match ($this->eventType) {
            'created' => $actor . ' a cree la tache "' . $this->task->title . '".',
            'updated' => $actor . ' a modifie la tache "' . $this->task->title . '".',
            'assigned' => 'Vous etes responsable de "' . $this->task->title . '".',
            'status_changed' => $actor . ' a passe "' . $this->task->title . '" au statut "' . $this->task->statusLabel() . '".',
            'comment_added' => $actor . ' a commente "' . $this->task->title . '".',
            default => 'Mise a jour sur "' . $this->task->title . '".',
        };
    }
}

