<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PilotTask extends Model
{
    use HasFactory;

    public const STATUSES = [
        'todo' => 'A faire',
        'in_progress' => 'En cours',
        'in_validation' => 'En validation',
        'done' => 'Termine',
        'blocked' => 'Bloque',
    ];

    public const PRIORITIES = [
        'low' => 'Basse',
        'normal' => 'Normale',
        'high' => 'Haute',
    ];

    public const TYPES = [
        'video_content' => 'Contenu video',
        'questionnaire' => 'Questionnaire',
        'scorm' => 'SCORM',
        'interface' => 'Interface',
        'data' => 'Donnees',
        'support' => 'Support',
        'rgpd' => 'RGPD',
    ];

    public const EVENT_TYPES = [
        'created' => 'Creation',
        'updated' => 'Mise a jour',
        'assigned' => 'Assignation',
        'status_changed' => 'Changement de statut',
        'comment_added' => 'Nouveau commentaire',
    ];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
        ];
    }

    public function project()
    {
        return $this->belongsTo(PilotProject::class, 'project_id');
    }

    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function comments()
    {
        return $this->hasMany(PilotTaskComment::class, 'task_id')->latest();
    }

    public function subscriptions()
    {
        return $this->hasMany(PilotSubscription::class, 'task_id');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function priorityLabel(): string
    {
        return self::PRIORITIES[$this->priority] ?? $this->priority;
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->task_type] ?? $this->task_type;
    }
}

