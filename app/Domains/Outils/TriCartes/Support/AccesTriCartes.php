<?php

namespace App\Domains\Outils\TriCartes\Support;

use Illuminate\Support\Facades\DB;

class AccesTriCartes
{
    public function formateurPeutGererGroupe(int $groupId, int $formateurId): bool
    {
        return DB::table('groups')
            ->where('groups.id', $groupId)
            ->whereNull('groups.deleted_at')
            ->where(function ($query) use ($formateurId): void {
                $query->where('groups.instructor_id', $formateurId)
                    ->orWhereExists(function ($pivot) use ($formateurId): void {
                        $pivot->selectRaw('1')
                            ->from('group_user')
                            ->whereColumn('group_user.group_id', 'groups.id')
                            ->where('group_user.user_id', $formateurId)
                            ->where('group_user.role_in_group', 'formateur');
                    });
            })
            ->exists();
    }

    public function estStagiaireDuGroupe(int $groupId, int $userId): bool
    {
        return DB::table('group_user')
            ->join('groups', 'groups.id', '=', 'group_user.group_id')
            ->where('group_user.group_id', $groupId)
            ->where('group_user.user_id', $userId)
            ->where('group_user.role_in_group', 'stagiaire')
            ->whereNull('groups.deleted_at')
            ->exists();
    }

    public function peutVoir(int $groupId, int $userId): bool
    {
        return $this->estStagiaireDuGroupe($groupId, $userId)
            || $this->formateurPeutGererGroupe($groupId, $userId);
    }

    public function verifierFormateur(int $groupId, int $formateurId): void
    {
        abort_unless($this->formateurPeutGererGroupe($groupId, $formateurId), 403);
    }

    public function verifierStagiaire(int $groupId, int $userId): void
    {
        abort_unless($this->estStagiaireDuGroupe($groupId, $userId), 403);
    }

    public function verifierVue(int $groupId, int $userId): void
    {
        abort_unless($this->peutVoir($groupId, $userId), 403);
    }
}
