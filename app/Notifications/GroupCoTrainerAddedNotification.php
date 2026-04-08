<?php

namespace App\Notifications;

use App\Models\Group;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GroupCoTrainerAddedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Group $group,
        private readonly User $primaryTrainer,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $primaryTrainerName = trim((string) (($this->primaryTrainer->prenom ?? '') . ' ' . ($this->primaryTrainer->name ?? '')));
        $primaryTrainerName = $primaryTrainerName !== '' ? $primaryTrainerName : 'Le formateur principal';

        return [
            'event' => 'group_co_trainer_added',
            'title' => 'Ajout comme co-formateur',
            'message' => sprintf(
                '%s vous a ajouté comme co-formateur du groupe "%s".',
                $primaryTrainerName,
                $this->group->name
            ),
            'group_id' => $this->group->id,
            'group_name' => $this->group->name,
            'primary_trainer_id' => $this->primaryTrainer->id,
            'primary_trainer_name' => $primaryTrainerName,
            'url' => route('formateur.groupes.edit', $this->group->id),
            'created_at' => now()->toIso8601String(),
        ];
    }
}
