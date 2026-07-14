<?php

namespace App\Domains\Outils\TriCartes\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use stdClass;

class DepotTriCartes
{
    private const ALPHABET_CODE = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public function estInstalle(): bool
    {
        return Schema::hasTable('card_sort_sessions')
            && Schema::hasTable('card_sort_categories')
            && Schema::hasTable('card_sort_cards')
            && Schema::hasTable('card_sort_attempts')
            && Schema::hasTable('card_sort_placements');
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
        return DB::table('card_sort_sessions as sessions')
            ->join('groups', 'groups.id', '=', 'sessions.group_id')
            ->select(['sessions.*', 'groups.name as group_name'])
            ->selectSub(function ($query): void {
                $query->from('card_sort_cards')
                    ->selectRaw('count(*)')
                    ->whereColumn('card_sort_cards.card_sort_session_id', 'sessions.id');
            }, 'cards_count')
            ->selectSub(function ($query): void {
                $query->from('card_sort_attempts')
                    ->selectRaw('count(*)')
                    ->whereColumn('card_sort_attempts.card_sort_session_id', 'sessions.id');
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
        $session = DB::table('card_sort_sessions as sessions')
            ->join('groups', 'groups.id', '=', 'sessions.group_id')
            ->select(['sessions.*', 'groups.name as group_name'])
            ->where('sessions.id', $sessionId)
            ->first();

        return $session ? $this->preparerSession($session) : null;
    }

    public function trouverParCode(string $code): ?stdClass
    {
        $session = DB::table('card_sort_sessions as sessions')
            ->join('groups', 'groups.id', '=', 'sessions.group_id')
            ->select(['sessions.*', 'groups.name as group_name'])
            ->where('sessions.access_code', strtoupper(trim($code)))
            ->first();

        return $session ? $this->preparerSession($session) : null;
    }

    public function creer(array $donnees): int
    {
        $maintenant = now();

        return DB::table('card_sort_sessions')->insertGetId([
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
        $session = DB::table('card_sort_sessions')->where('id', $sessionId)->first();
        abort_unless($session, 404);

        $actif = ! (bool) $session->is_active;
        DB::table('card_sort_sessions')->where('id', $sessionId)->update([
            'is_active' => $actif,
            'opened_at' => $actif ? now() : $session->opened_at,
            'closed_at' => $actif ? null : now(),
            'updated_at' => now(),
        ]);
    }

    public function supprimer(int $sessionId): void
    {
        DB::table('card_sort_sessions')->where('id', $sessionId)->delete();
    }

    // --- Catégories ---

    public function categories(int $sessionId): Collection
    {
        return DB::table('card_sort_categories')
            ->where('card_sort_session_id', $sessionId)
            ->orderBy('position')
            ->get();
    }

    public function trouverCategorie(int $categorieId): ?stdClass
    {
        return DB::table('card_sort_categories')->where('id', $categorieId)->first();
    }

    public function ajouterCategorie(int $sessionId, string $label): int
    {
        $maintenant = now();
        $position = ((int) DB::table('card_sort_categories')->where('card_sort_session_id', $sessionId)->max('position')) + 1;

        return DB::table('card_sort_categories')->insertGetId([
            'card_sort_session_id' => $sessionId,
            'label' => $label,
            'position' => $position,
            'created_at' => $maintenant,
            'updated_at' => $maintenant,
        ]);
    }

    public function modifierCategorie(int $categorieId, string $label): void
    {
        DB::table('card_sort_categories')->where('id', $categorieId)->update([
            'label' => $label,
            'updated_at' => now(),
        ]);
    }

    public function supprimerCategorie(int $categorieId): void
    {
        DB::table('card_sort_categories')->where('id', $categorieId)->delete();
    }

    // --- Cartes ---

    public function cartes(int $sessionId): Collection
    {
        return DB::table('card_sort_cards')
            ->where('card_sort_session_id', $sessionId)
            ->orderBy('position')
            ->get();
    }

    public function trouverCarte(int $carteId): ?stdClass
    {
        return DB::table('card_sort_cards')->where('id', $carteId)->first();
    }

    public function ajouterCarte(int $sessionId, array $donnees): int
    {
        $maintenant = now();
        $position = ((int) DB::table('card_sort_cards')->where('card_sort_session_id', $sessionId)->max('position')) + 1;

        return DB::table('card_sort_cards')->insertGetId([
            'card_sort_session_id' => $sessionId,
            'correct_category_id' => $donnees['correct_category_id'],
            'text' => $donnees['text'] ?? null,
            'image_path' => $donnees['image_path'] ?? null,
            'position' => $position,
            'created_at' => $maintenant,
            'updated_at' => $maintenant,
        ]);
    }

    public function modifierCarte(int $carteId, array $donnees): void
    {
        $update = ['updated_at' => now()];

        foreach (['correct_category_id', 'text', 'image_path'] as $champ) {
            if (array_key_exists($champ, $donnees)) {
                $update[$champ] = $donnees[$champ];
            }
        }

        DB::table('card_sort_cards')->where('id', $carteId)->update($update);
    }

    public function supprimerCarte(int $carteId): void
    {
        DB::table('card_sort_cards')->where('id', $carteId)->delete();
    }

    public function deplacerCarte(int $carteId, string $direction): void
    {
        DB::transaction(function () use ($carteId, $direction): void {
            $carte = DB::table('card_sort_cards')->where('id', $carteId)->lockForUpdate()->first();
            abort_unless($carte, 404);

            $voisine = $direction === 'up'
                ? DB::table('card_sort_cards')
                    ->where('card_sort_session_id', $carte->card_sort_session_id)
                    ->where('position', '<', $carte->position)
                    ->orderByDesc('position')
                    ->lockForUpdate()
                    ->first()
                : DB::table('card_sort_cards')
                    ->where('card_sort_session_id', $carte->card_sort_session_id)
                    ->where('position', '>', $carte->position)
                    ->orderBy('position')
                    ->lockForUpdate()
                    ->first();

            if (! $voisine) {
                return;
            }

            DB::table('card_sort_cards')->where('id', $carte->id)->update(['position' => $voisine->position, 'updated_at' => now()]);
            DB::table('card_sort_cards')->where('id', $voisine->id)->update(['position' => $carte->position, 'updated_at' => now()]);
        });
    }

    // --- Tentatives stagiaire ---

    /**
     * @param  array<int, int>  $placements  card_id => category_id
     * @return array{score: int, total: int, details: array<int, array{card_id: int, category_id: int, is_correct: bool}>}
     */
    public function soumettre(int $sessionId, int $userId, array $placements): array
    {
        return DB::transaction(function () use ($sessionId, $userId, $placements): array {
            $cartes = DB::table('card_sort_cards')->where('card_sort_session_id', $sessionId)->get();
            abort_if($cartes->isEmpty(), 422, "Cette activité ne contient pas encore de carte.");

            $categoriesCorrectes = $cartes->pluck('correct_category_id', 'id')->map(fn ($id): int => (int) $id)->all();
            $resultat = JeuTriCartes::corriger($categoriesCorrectes, $placements);

            $identite = ['card_sort_session_id' => $sessionId, 'user_id' => $userId];
            $tentative = DB::table('card_sort_attempts')->where($identite);

            if ($tentative->exists()) {
                $attemptId = (int) DB::table('card_sort_attempts')->where($identite)->value('id');
                DB::table('card_sort_attempts')->where('id', $attemptId)->update([
                    'score' => $resultat['score'],
                    'total' => $resultat['total'],
                    'submitted_at' => now(),
                    'updated_at' => now(),
                ]);
                DB::table('card_sort_placements')->where('card_sort_attempt_id', $attemptId)->delete();
            } else {
                $attemptId = DB::table('card_sort_attempts')->insertGetId($identite + [
                    'score' => $resultat['score'],
                    'total' => $resultat['total'],
                    'submitted_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $maintenant = now();
            $lignes = array_map(fn (array $detail): array => [
                'card_sort_attempt_id' => $attemptId,
                'card_sort_card_id' => $detail['card_id'],
                'category_id' => $detail['category_id'],
                'is_correct' => $detail['is_correct'],
                'created_at' => $maintenant,
                'updated_at' => $maintenant,
            ], $resultat['details']);

            if ($lignes !== []) {
                DB::table('card_sort_placements')->insert($lignes);
            }

            return $resultat;
        });
    }

    public function tentativePourStagiaire(int $sessionId, int $userId): ?stdClass
    {
        $tentative = DB::table('card_sort_attempts')
            ->where('card_sort_session_id', $sessionId)
            ->where('user_id', $userId)
            ->first();

        if (! $tentative) {
            return null;
        }

        $tentative->score = (int) $tentative->score;
        $tentative->total = (int) $tentative->total;
        $tentative->placements = DB::table('card_sort_placements')
            ->where('card_sort_attempt_id', $tentative->id)
            ->get()
            ->keyBy('card_sort_card_id');

        return $tentative;
    }

    public function resultatsPourFormateur(int $sessionId): Collection
    {
        return DB::table('card_sort_attempts as attempts')
            ->join('users', 'users.id', '=', 'attempts.user_id')
            ->select(['attempts.id', 'attempts.score', 'attempts.total', 'attempts.submitted_at', 'users.name as user_name'])
            ->where('attempts.card_sort_session_id', $sessionId)
            ->orderByDesc('attempts.submitted_at')
            ->get()
            ->map(function (stdClass $tentative): stdClass {
                $tentative->score = (int) $tentative->score;
                $tentative->total = (int) $tentative->total;

                return $tentative;
            });
    }

    /** @return array<string, mixed> */
    public function etat(int $sessionId): array
    {
        return [
            'results' => $this->resultatsPourFormateur($sessionId)->map(fn (stdClass $tentative): array => [
                'name' => $tentative->user_name,
                'score' => $tentative->score,
                'total' => $tentative->total,
            ])->all(),
            'updated_at' => now()->toIso8601String(),
        ];
    }

    public function resumePourStagiaire(int $groupId, int $userId): ?stdClass
    {
        $sessions = DB::table('card_sort_sessions')
            ->where('group_id', $groupId)
            ->orderByDesc('created_at')
            ->get();

        if ($sessions->isEmpty()) {
            return null;
        }

        $ids = $sessions->pluck('id');
        $participees = DB::table('card_sort_attempts')
            ->whereIn('card_sort_session_id', $ids)
            ->where('user_id', $userId)
            ->distinct()
            ->count('card_sort_session_id');

        $active = $sessions->first(fn (stdClass $session): bool => (bool) $session->is_active);

        return (object) [
            'key' => 'tri_cartes',
            'label' => 'Cartes à trier',
            'sessions' => $sessions->count(),
            'participated' => $participees,
            'trackable' => true,
            'last_used' => $sessions->max('opened_at') ?? $sessions->max('created_at'),
            'active_code' => $active?->access_code,
            'icon_path' => 'M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z',
        ];
    }

    private function genererCodeAcces(): string
    {
        do {
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $code .= self::ALPHABET_CODE[random_int(0, strlen(self::ALPHABET_CODE) - 1)];
            }
        } while (DB::table('card_sort_sessions')->where('access_code', $code)->exists());

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

        if (property_exists($session, 'attempts_count')) {
            $session->attempts_count = (int) $session->attempts_count;
        }

        return $session;
    }
}
