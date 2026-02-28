<?php

namespace App\Notifications;

use App\Models\Module;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ModuleCompletedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Module $module,
        private readonly User $stagiaire,
        private readonly bool $forStagiaire,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $title = $this->forStagiaire
            ? 'Module termine'
            : 'Module termine par un stagiaire';

        $message = $this->forStagiaire
            ? sprintf('Vous avez termine le module "%s".', $this->module->module_name)
            : sprintf('%s %s a termine le module "%s".', $this->stagiaire->prenom ?? '', $this->stagiaire->name, $this->module->module_name);

        return [
            'event' => 'module_completed',
            'title' => trim($title),
            'message' => trim(preg_replace('/\s+/', ' ', $message) ?? $message),
            'module_id' => $this->module->id,
            'module_name' => $this->module->module_name,
            'stagiaire_id' => $this->stagiaire->id,
            'stagiaire_name' => trim(($this->stagiaire->prenom ?? '') . ' ' . ($this->stagiaire->name ?? '')),
            'url' => $this->forStagiaire
                ? route('stagiaire.module.fin', ['module' => $this->module->id])
                : route('formateur.progressions.modules', ['module_id' => $this->module->id, 'user_id' => $this->stagiaire->id]),
            'created_at' => now()->toIso8601String(),
        ];
    }
}
