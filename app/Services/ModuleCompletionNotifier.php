<?php

namespace App\Services;

use App\Models\Module;
use App\Models\ModuleCompletionNotification;
use App\Models\User;
use App\Notifications\ModuleCompletedNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ModuleCompletionNotifier
{
    public function notify(Module $module, User $stagiaire): array
    {
        $recipients = $this->resolveRecipients($stagiaire);
        $createdFor = [];

        foreach ($recipients as $recipient) {
            $created = ModuleCompletionNotification::query()->firstOrCreate([
                'module_id' => $module->id,
                'stagiaire_id' => $stagiaire->id,
                'recipient_id' => $recipient->id,
            ]);

            if (!$created->wasRecentlyCreated) {
                continue;
            }

            $recipient->notify(new ModuleCompletedNotification(
                module: $module,
                stagiaire: $stagiaire,
                forStagiaire: $recipient->id === $stagiaire->id,
            ));

            $createdFor[] = $recipient->id;
        }

        return [
            'created_for' => $createdFor,
            'created_for_stagiaire' => in_array($stagiaire->id, $createdFor, true),
        ];
    }

    private function resolveRecipients(User $stagiaire): Collection
    {
        $directFormateurIds = [];
        if (!empty($stagiaire->formateur_id)) {
            $directFormateurIds[] = (int) $stagiaire->formateur_id;
        }

        $groupInstructorIds = DB::table('groups')
            ->join('group_user', 'group_user.group_id', '=', 'groups.id')
            ->where('group_user.user_id', $stagiaire->id)
            ->where('group_user.role_in_group', 'stagiaire')
            ->whereNotNull('groups.instructor_id')
            ->pluck('groups.instructor_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $recipientIds = collect(array_merge([$stagiaire->id], $directFormateurIds, $groupInstructorIds))
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        return User::query()
            ->whereIn('id', $recipientIds)
            ->whereIn('role', ['stagiaire', 'formateur'])
            ->get();
    }
}
