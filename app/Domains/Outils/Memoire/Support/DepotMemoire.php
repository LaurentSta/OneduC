<?php

namespace App\Domains\Outils\Memoire\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use stdClass;

class DepotMemoire
{
    private const ALPHABET_CODE = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public function estInstalle(): bool
    {
        return Schema::hasTable('memory_sessions') && Schema::hasTable('memory_attempts');
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
        return DB::table('memory_sessions as sessions')
            ->join('groups', 'groups.id', '=', 'sessions.group_id')
            ->select(['sessions.*', 'groups.name as group_name'])
            ->selectSub(function ($query): void {
                $query->from('memory_attempts')
                    ->selectRaw('count(*)')
                    ->whereColumn('memory_attempts.memory_session_id', 'sessions.id');
            }, 'attempts_count')
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
        $session = DB::table('memory_sessions as sessions')
            ->join('groups', 'groups.id', '=', 'sessions.group_id')
            ->select(['sessions.*', 'groups.name as group_name'])
            ->where('sessions.id', $sessionId)
            ->first();

        return $session ? $this->preparerSession($session) : null;
    }

    public function trouverParCode(string $code): ?stdClass
    {
        $session = DB::table('memory_sessions as sessions')
            ->join('groups', 'groups.id', '=', 'sessions.group_id')
            ->select(['sessions.*', 'groups.name as group_name'])
            ->where('sessions.access_code', strtoupper(trim($code)))
            ->first();

        return $session ? $this->preparerSession($session) : null;
    }

    public function creer(array $donnees): int
    {
        $maintenant = now();

        return DB::table('memory_sessions')->insertGetId([
            'formateur_id' => $donnees['formateur_id'],
            'group_id' => $donnees['group_id'],
            'title' => $donnees['title'],
            'pairs' => json_encode($donnees['pairs'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
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
        $session = DB::table('memory_sessions')->where('id', $sessionId)->first();
        abort_unless($session, 404);

        $actif = ! (bool) $session->is_active;
        DB::table('memory_sessions')->where('id', $sessionId)->update([
            'is_active' => $actif,
            'opened_at' => $actif ? now() : $session->opened_at,
            'closed_at' => $actif ? null : now(),
            'updated_at' => now(),
        ]);
    }

    public function supprimer(int $sessionId): void
    {
        DB::table('memory_sessions')->where('id', $sessionId)->delete();
    }

    public function enregistrerTentative(int $sessionId, int $userId, int $coups, int $duree): void
    {
        $session = $this->trouver($sessionId);
        abort_unless($session, 404);

        $nombrePaires = count($session->pairs);
        abort_if($coups < $nombrePaires, 422, 'Le nombre de coups est incohérent avec cette partie.');

        $identite = ['memory_session_id' => $sessionId, 'user_id' => $userId];
        $resultat = [
            'moves' => $coups,
            'errors' => JeuMemoire::calculerErreurs($coups, $nombrePaires),
            'duration_seconds' => $duree,
            'updated_at' => now(),
        ];

        $tentative = DB::table('memory_attempts')->where($identite);
        if ($tentative->exists()) {
            $tentative->update($resultat);

            return;
        }

        DB::table('memory_attempts')->insert($identite + $resultat + ['created_at' => now()]);
    }

    public function tentativeUtilisateur(int $sessionId, int $userId): ?stdClass
    {
        return DB::table('memory_attempts')
            ->where('memory_session_id', $sessionId)
            ->where('user_id', $userId)
            ->first();
    }

    public function classement(int $sessionId): Collection
    {
        return DB::table('memory_attempts as attempts')
            ->join('users', 'users.id', '=', 'attempts.user_id')
            ->select([
                'attempts.id',
                'attempts.moves',
                'attempts.errors',
                'attempts.duration_seconds',
                'attempts.updated_at',
                'users.name as user_name',
            ])
            ->where('attempts.memory_session_id', $sessionId)
            ->orderBy('attempts.errors')
            ->orderBy('attempts.duration_seconds')
            ->get()
            ->map(function (stdClass $tentative): stdClass {
                $tentative->moves = (int) $tentative->moves;
                $tentative->errors = (int) $tentative->errors;
                $tentative->duration_seconds = (int) $tentative->duration_seconds;

                return $tentative;
            });
    }

    /** @return array<string, mixed> */
    public function etat(int $sessionId): array
    {
        return [
            'leaderboard' => $this->classement($sessionId)->map(fn (stdClass $tentative): array => [
                'name' => $tentative->user_name,
                'errors' => $tentative->errors,
                'moves' => $tentative->moves,
                'duration_seconds' => $tentative->duration_seconds,
            ])->all(),
            'updated_at' => now()->toIso8601String(),
        ];
    }

    public function resumePourStagiaire(int $groupId, int $userId): ?stdClass
    {
        $sessions = DB::table('memory_sessions')
            ->where('group_id', $groupId)
            ->orderByDesc('created_at')
            ->get();

        if ($sessions->isEmpty()) {
            return null;
        }

        $ids = $sessions->pluck('id');
        $participees = DB::table('memory_attempts')
            ->whereIn('memory_session_id', $ids)
            ->where('user_id', $userId)
            ->distinct()
            ->count('memory_session_id');

        $active = $sessions->first(fn (stdClass $session): bool => (bool) $session->is_active);

        return (object) [
            'key' => 'memory',
            'label' => 'Jeu de mémoire',
            'sessions' => $sessions->count(),
            'participated' => $participees,
            'trackable' => true,
            'last_used' => $sessions->max('opened_at') ?? $sessions->max('created_at'),
            'active_code' => $active?->access_code,
            'icon_path' => 'M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18M3 9v10a2 2 0 002 2h4m10-12v10a2 2 0 01-2 2h-4',
        ];
    }

    private function genererCodeAcces(): string
    {
        do {
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $code .= self::ALPHABET_CODE[random_int(0, strlen(self::ALPHABET_CODE) - 1)];
            }
        } while (DB::table('memory_sessions')->where('access_code', $code)->exists());

        return $code;
    }

    private function preparerSession(stdClass $session): stdClass
    {
        $session->id = (int) $session->id;
        $session->formateur_id = (int) $session->formateur_id;
        $session->group_id = (int) $session->group_id;
        $session->is_active = (bool) $session->is_active;
        $session->pairs = is_array($session->pairs)
            ? $session->pairs
            : (json_decode((string) $session->pairs, true) ?: []);

        if (property_exists($session, 'attempts_count')) {
            $session->attempts_count = (int) $session->attempts_count;
        }

        return $session;
    }
}
