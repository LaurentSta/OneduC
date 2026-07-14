<?php

namespace App\Domains\Outils\CartesRetourner\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use stdClass;

class DepotCartesRetourner
{
    private const ALPHABET_CODE = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public function estInstalle(): bool
    {
        return Schema::hasTable('flashcard_sessions') && Schema::hasTable('flashcard_cards');
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
        return DB::table('flashcard_sessions as sessions')
            ->join('groups', 'groups.id', '=', 'sessions.group_id')
            ->select(['sessions.*', 'groups.name as group_name'])
            ->selectSub(function ($query): void {
                $query->from('flashcard_cards')
                    ->selectRaw('count(*)')
                    ->whereColumn('flashcard_cards.flashcard_session_id', 'sessions.id');
            }, 'cards_count')
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
        $session = DB::table('flashcard_sessions as sessions')
            ->join('groups', 'groups.id', '=', 'sessions.group_id')
            ->select(['sessions.*', 'groups.name as group_name'])
            ->where('sessions.id', $sessionId)
            ->first();

        return $session ? $this->preparerSession($session) : null;
    }

    public function trouverParCode(string $code): ?stdClass
    {
        $session = DB::table('flashcard_sessions as sessions')
            ->join('groups', 'groups.id', '=', 'sessions.group_id')
            ->select(['sessions.*', 'groups.name as group_name'])
            ->where('sessions.access_code', strtoupper(trim($code)))
            ->first();

        return $session ? $this->preparerSession($session) : null;
    }

    public function creer(array $donnees): int
    {
        $maintenant = now();

        return DB::table('flashcard_sessions')->insertGetId([
            'formateur_id' => $donnees['formateur_id'],
            'group_id' => $donnees['group_id'],
            'title' => $donnees['title'],
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
        $session = DB::table('flashcard_sessions')->where('id', $sessionId)->first();
        abort_unless($session, 404);

        $actif = ! (bool) $session->is_active;
        DB::table('flashcard_sessions')->where('id', $sessionId)->update([
            'is_active' => $actif,
            'opened_at' => $actif ? now() : $session->opened_at,
            'closed_at' => $actif ? null : now(),
            'updated_at' => now(),
        ]);
    }

    public function supprimer(int $sessionId): void
    {
        DB::table('flashcard_sessions')->where('id', $sessionId)->delete();
    }

    public function cartes(int $sessionId): Collection
    {
        return DB::table('flashcard_cards')
            ->where('flashcard_session_id', $sessionId)
            ->orderBy('position')
            ->get();
    }

    public function trouverCarte(int $carteId): ?stdClass
    {
        return DB::table('flashcard_cards')->where('id', $carteId)->first();
    }

    public function ajouterCarte(int $sessionId, array $donnees): int
    {
        $maintenant = now();
        $position = ((int) DB::table('flashcard_cards')->where('flashcard_session_id', $sessionId)->max('position')) + 1;

        return DB::table('flashcard_cards')->insertGetId([
            'flashcard_session_id' => $sessionId,
            'position' => $position,
            'recto_text' => $donnees['recto_text'] ?? null,
            'recto_image_path' => $donnees['recto_image_path'] ?? null,
            'verso_text' => $donnees['verso_text'] ?? null,
            'verso_image_path' => $donnees['verso_image_path'] ?? null,
            'created_at' => $maintenant,
            'updated_at' => $maintenant,
        ]);
    }

    public function modifierCarte(int $carteId, array $donnees): void
    {
        $update = ['updated_at' => now()];

        foreach (['recto_text', 'recto_image_path', 'verso_text', 'verso_image_path'] as $champ) {
            if (array_key_exists($champ, $donnees)) {
                $update[$champ] = $donnees[$champ];
            }
        }

        DB::table('flashcard_cards')->where('id', $carteId)->update($update);
    }

    public function supprimerCarte(int $carteId): void
    {
        DB::table('flashcard_cards')->where('id', $carteId)->delete();
    }

    public function deplacerCarte(int $carteId, string $direction): void
    {
        DB::transaction(function () use ($carteId, $direction): void {
            $carte = DB::table('flashcard_cards')->where('id', $carteId)->lockForUpdate()->first();
            abort_unless($carte, 404);

            $voisine = $direction === 'up'
                ? DB::table('flashcard_cards')
                    ->where('flashcard_session_id', $carte->flashcard_session_id)
                    ->where('position', '<', $carte->position)
                    ->orderByDesc('position')
                    ->lockForUpdate()
                    ->first()
                : DB::table('flashcard_cards')
                    ->where('flashcard_session_id', $carte->flashcard_session_id)
                    ->where('position', '>', $carte->position)
                    ->orderBy('position')
                    ->lockForUpdate()
                    ->first();

            if (! $voisine) {
                return;
            }

            DB::table('flashcard_cards')->where('id', $carte->id)->update(['position' => $voisine->position, 'updated_at' => now()]);
            DB::table('flashcard_cards')->where('id', $voisine->id)->update(['position' => $carte->position, 'updated_at' => now()]);
        });
    }

    public function resumePourStagiaire(int $groupId, int $userId): ?stdClass
    {
        $sessions = DB::table('flashcard_sessions')
            ->where('group_id', $groupId)
            ->orderByDesc('created_at')
            ->get();

        if ($sessions->isEmpty()) {
            return null;
        }

        $active = $sessions->first(fn (stdClass $session): bool => (bool) $session->is_active);

        return (object) [
            'key' => 'cartes_retourner',
            'label' => 'Cartes à retourner',
            'sessions' => $sessions->count(),
            'participated' => null,
            'trackable' => false,
            'last_used' => $sessions->max('opened_at') ?? $sessions->max('created_at'),
            'active_code' => $active?->access_code,
            'icon_path' => 'M4 4h7v7H4V4zm9 0h7v7h-7V4zM4 13h7v7H4v-7zm9 0h7v7h-7v-7z',
        ];
    }

    private function genererCodeAcces(): string
    {
        do {
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $code .= self::ALPHABET_CODE[random_int(0, strlen(self::ALPHABET_CODE) - 1)];
            }
        } while (DB::table('flashcard_sessions')->where('access_code', $code)->exists());

        return $code;
    }

    private function preparerSession(stdClass $session): stdClass
    {
        $session->id = (int) $session->id;
        $session->formateur_id = (int) $session->formateur_id;
        $session->group_id = (int) $session->group_id;
        $session->is_active = (bool) $session->is_active;

        if (property_exists($session, 'cards_count')) {
            $session->cards_count = (int) $session->cards_count;
        }

        return $session;
    }
}
