<?php

namespace App\Domains\Outils\Pendu\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use stdClass;

class DepotPendu
{
    private const ALPHABET_CODE = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public function estInstalle(): bool
    {
        return Schema::hasTable('hangman_sessions') && Schema::hasTable('hangman_guesses');
    }

    public function groupesPourFormateur(int $formateurId): Collection
    {
        return DB::table('groups')
            ->select(['groups.id', 'groups.name'])
            ->selectSub(function ($query): void {
                $query->from('group_user')
                    ->selectRaw('count(*)')
                    ->whereColumn('group_user.group_id', 'groups.id')
                    ->where('group_user.role_in_group', 'stagiaire');
            }, 'students_count')
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
            ->orderBy('groups.name')
            ->get();
    }

    public function sessionsPourFormateur(int $formateurId, int $limite = 20): Collection
    {
        return DB::table('hangman_sessions as sessions')
            ->join('groups', 'groups.id', '=', 'sessions.group_id')
            ->select(['sessions.*', 'groups.name as group_name'])
            ->selectSub(function ($query): void {
                $query->from('hangman_guesses')
                    ->selectRaw('count(*)')
                    ->whereColumn('hangman_guesses.hangman_session_id', 'sessions.id');
            }, 'guesses_count')
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
            ->latest('sessions.created_at')
            ->limit($limite)
            ->get()
            ->map(fn (stdClass $session): stdClass => $this->preparerSession($session));
    }

    public function trouver(int $sessionId): ?stdClass
    {
        $session = DB::table('hangman_sessions as sessions')
            ->join('groups', 'groups.id', '=', 'sessions.group_id')
            ->select(['sessions.*', 'groups.name as group_name'])
            ->where('sessions.id', $sessionId)
            ->first();

        return $session ? $this->preparerSession($session) : null;
    }

    public function trouverParCode(string $code): ?stdClass
    {
        $session = DB::table('hangman_sessions as sessions')
            ->join('groups', 'groups.id', '=', 'sessions.group_id')
            ->select(['sessions.*', 'groups.name as group_name'])
            ->where('sessions.access_code', strtoupper(trim($code)))
            ->first();

        return $session ? $this->preparerSession($session) : null;
    }

    public function creer(array $donnees): int
    {
        $maintenant = now();

        return DB::table('hangman_sessions')->insertGetId([
            'formateur_id' => $donnees['formateur_id'],
            'group_id' => $donnees['group_id'],
            'title' => $donnees['title'],
            'word' => $donnees['word'],
            'max_attempts' => 6,
            'guessed_letters' => json_encode([], JSON_THROW_ON_ERROR),
            'status' => JeuPendu::STATUS_EN_COURS,
            'access_code' => $this->genererCodeAcces(),
            'is_active' => true,
            'opened_at' => $maintenant,
            'closed_at' => null,
            'created_at' => $maintenant,
            'updated_at' => $maintenant,
        ]);
    }

    public function basculer(int $sessionId): void
    {
        $session = DB::table('hangman_sessions')->where('id', $sessionId)->first();
        abort_unless($session, 404);

        $actif = ! (bool) $session->is_active;
        DB::table('hangman_sessions')->where('id', $sessionId)->update([
            'is_active' => $actif,
            'opened_at' => $actif ? now() : $session->opened_at,
            'closed_at' => $actif ? null : now(),
            'updated_at' => now(),
        ]);
    }

    public function supprimer(int $sessionId): void
    {
        DB::table('hangman_sessions')->where('id', $sessionId)->delete();
    }

    /** @return array{ok: bool, message: ?string} */
    public function proposerLettre(int $sessionId, int $userId, string $lettre): array
    {
        return DB::transaction(function () use ($sessionId, $userId, $lettre): array {
            $session = DB::table('hangman_sessions')->where('id', $sessionId)->lockForUpdate()->first();
            abort_unless($session, 404);
            $session = $this->preparerSession($session);

            if (! $session->is_active || $session->status !== JeuPendu::STATUS_EN_COURS) {
                return ['ok' => false, 'message' => 'Cette partie n\'est plus en cours.'];
            }

            $lettreNormalisee = JeuPendu::normaliserLettre($lettre);
            if ($lettreNormalisee === '' || ! ctype_alpha($lettreNormalisee)) {
                return ['ok' => false, 'message' => 'Merci de proposer une seule lettre.'];
            }

            $lettresProposees = JeuPendu::lettresProposees($session);
            if (in_array($lettreNormalisee, $lettresProposees, true)) {
                return ['ok' => false, 'message' => 'Cette lettre a déjà été proposée.'];
            }

            $correcte = in_array($lettreNormalisee, JeuPendu::lettresDuMot($session->word), true);
            DB::table('hangman_guesses')->insert([
                'hangman_session_id' => $sessionId,
                'user_id' => $userId,
                'letter' => $lettreNormalisee,
                'correct' => $correcte,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $lettresProposees[] = $lettreNormalisee;
            $statut = JeuPendu::statutApresProposition($session, $lettresProposees);

            DB::table('hangman_sessions')->where('id', $sessionId)->update([
                'guessed_letters' => json_encode($lettresProposees, JSON_THROW_ON_ERROR),
                'status' => $statut,
                'closed_at' => $statut === JeuPendu::STATUS_EN_COURS ? null : now(),
                'updated_at' => now(),
            ]);

            return ['ok' => true, 'message' => null];
        });
    }

    /** @return array<string, mixed> */
    public function etat(stdClass $session): array
    {
        $participants = DB::table('hangman_guesses')
            ->where('hangman_session_id', $session->id)
            ->distinct()
            ->count('user_id');

        return JeuPendu::etat($session, $participants);
    }

    public function resumePourStagiaire(int $groupId, int $userId): ?stdClass
    {
        $sessions = DB::table('hangman_sessions')
            ->where('group_id', $groupId)
            ->orderByDesc('created_at')
            ->get();

        if ($sessions->isEmpty()) {
            return null;
        }

        $ids = $sessions->pluck('id');
        $participees = DB::table('hangman_guesses')
            ->whereIn('hangman_session_id', $ids)
            ->where('user_id', $userId)
            ->distinct()
            ->count('hangman_session_id');

        $active = $sessions->first(fn (stdClass $session): bool => (bool) $session->is_active && $session->status === JeuPendu::STATUS_EN_COURS);

        return (object) [
            'key' => 'hangman',
            'label' => 'Jeu du pendu',
            'sessions' => $sessions->count(),
            'participated' => $participees,
            'trackable' => true,
            'last_used' => $sessions->max('opened_at') ?? $sessions->max('created_at'),
            'active_code' => $active?->access_code,
            'icon_path' => 'M11 4a2 2 0 114 0v1a1 1 0 001 1h3a2 2 0 012 2v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a2 2 0 01-2 2h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a2 2 0 01-2-2v-3a1 1 0 011-1h1a2 2 0 100-4H6a1 1 0 01-1-1V7a2 2 0 012-2h3a1 1 0 001-1V4z',
        ];
    }

    private function genererCodeAcces(): string
    {
        do {
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $code .= self::ALPHABET_CODE[random_int(0, strlen(self::ALPHABET_CODE) - 1)];
            }
        } while (DB::table('hangman_sessions')->where('access_code', $code)->exists());

        return $code;
    }

    private function preparerSession(stdClass $session): stdClass
    {
        $session->id = (int) $session->id;
        $session->formateur_id = (int) $session->formateur_id;
        $session->group_id = (int) $session->group_id;
        $session->max_attempts = (int) $session->max_attempts;
        $session->is_active = (bool) $session->is_active;
        $session->guessed_letters = is_array($session->guessed_letters)
            ? $session->guessed_letters
            : (json_decode((string) ($session->guessed_letters ?? '[]'), true) ?: []);

        if (property_exists($session, 'guesses_count')) {
            $session->guesses_count = (int) $session->guesses_count;
        }

        return $session;
    }
}
