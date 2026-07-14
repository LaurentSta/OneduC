<?php

namespace App\Domains\Outils\Carrousel\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use stdClass;

class DepotCarrousel
{
    private const ALPHABET_CODE = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public function estInstalle(): bool
    {
        return Schema::hasTable('carousel_sessions') && Schema::hasTable('carousel_slides');
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
        return DB::table('carousel_sessions as sessions')
            ->join('groups', 'groups.id', '=', 'sessions.group_id')
            ->select(['sessions.*', 'groups.name as group_name'])
            ->selectSub(function ($query): void {
                $query->from('carousel_slides')
                    ->selectRaw('count(*)')
                    ->whereColumn('carousel_slides.carousel_session_id', 'sessions.id');
            }, 'slides_count')
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
        $session = DB::table('carousel_sessions as sessions')
            ->join('groups', 'groups.id', '=', 'sessions.group_id')
            ->select(['sessions.*', 'groups.name as group_name'])
            ->where('sessions.id', $sessionId)
            ->first();

        return $session ? $this->preparerSession($session) : null;
    }

    public function trouverParCode(string $code): ?stdClass
    {
        $session = DB::table('carousel_sessions as sessions')
            ->join('groups', 'groups.id', '=', 'sessions.group_id')
            ->select(['sessions.*', 'groups.name as group_name'])
            ->where('sessions.access_code', strtoupper(trim($code)))
            ->first();

        return $session ? $this->preparerSession($session) : null;
    }

    public function creer(array $donnees): int
    {
        $maintenant = now();

        return DB::table('carousel_sessions')->insertGetId([
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
        $session = DB::table('carousel_sessions')->where('id', $sessionId)->first();
        abort_unless($session, 404);

        $actif = ! (bool) $session->is_active;
        DB::table('carousel_sessions')->where('id', $sessionId)->update([
            'is_active' => $actif,
            'opened_at' => $actif ? now() : $session->opened_at,
            'closed_at' => $actif ? null : now(),
            'updated_at' => now(),
        ]);
    }

    public function supprimer(int $sessionId): void
    {
        DB::table('carousel_sessions')->where('id', $sessionId)->delete();
    }

    public function slides(int $sessionId): Collection
    {
        return DB::table('carousel_slides')
            ->where('carousel_session_id', $sessionId)
            ->orderBy('position')
            ->get();
    }

    public function trouverSlide(int $slideId): ?stdClass
    {
        return DB::table('carousel_slides')->where('id', $slideId)->first();
    }

    public function ajouterSlide(int $sessionId, array $donnees): int
    {
        $maintenant = now();
        $position = ((int) DB::table('carousel_slides')->where('carousel_session_id', $sessionId)->max('position')) + 1;

        return DB::table('carousel_slides')->insertGetId([
            'carousel_session_id' => $sessionId,
            'position' => $position,
            'text' => $donnees['text'] ?? null,
            'image_path' => $donnees['image_path'] ?? null,
            'created_at' => $maintenant,
            'updated_at' => $maintenant,
        ]);
    }

    public function modifierSlide(int $slideId, array $donnees): void
    {
        $update = ['updated_at' => now()];

        if (array_key_exists('text', $donnees)) {
            $update['text'] = $donnees['text'];
        }

        if (array_key_exists('image_path', $donnees)) {
            $update['image_path'] = $donnees['image_path'];
        }

        DB::table('carousel_slides')->where('id', $slideId)->update($update);
    }

    public function supprimerSlide(int $slideId): void
    {
        DB::table('carousel_slides')->where('id', $slideId)->delete();
    }

    public function deplacerSlide(int $slideId, string $direction): void
    {
        DB::transaction(function () use ($slideId, $direction): void {
            $slide = DB::table('carousel_slides')->where('id', $slideId)->lockForUpdate()->first();
            abort_unless($slide, 404);

            $voisin = $direction === 'up'
                ? DB::table('carousel_slides')
                    ->where('carousel_session_id', $slide->carousel_session_id)
                    ->where('position', '<', $slide->position)
                    ->orderByDesc('position')
                    ->lockForUpdate()
                    ->first()
                : DB::table('carousel_slides')
                    ->where('carousel_session_id', $slide->carousel_session_id)
                    ->where('position', '>', $slide->position)
                    ->orderBy('position')
                    ->lockForUpdate()
                    ->first();

            if (! $voisin) {
                return;
            }

            DB::table('carousel_slides')->where('id', $slide->id)->update(['position' => $voisin->position, 'updated_at' => now()]);
            DB::table('carousel_slides')->where('id', $voisin->id)->update(['position' => $slide->position, 'updated_at' => now()]);
        });
    }

    public function resumePourStagiaire(int $groupId, int $userId): ?stdClass
    {
        $sessions = DB::table('carousel_sessions')
            ->where('group_id', $groupId)
            ->orderByDesc('created_at')
            ->get();

        if ($sessions->isEmpty()) {
            return null;
        }

        $active = $sessions->first(fn (stdClass $session): bool => (bool) $session->is_active);

        return (object) [
            'key' => 'carrousel',
            'label' => 'Carrousel',
            'sessions' => $sessions->count(),
            'participated' => null,
            'trackable' => false,
            'last_used' => $sessions->max('opened_at') ?? $sessions->max('created_at'),
            'active_code' => $active?->access_code,
            'icon_path' => 'M4 6h16M4 6a2 2 0 00-2 2v8a2 2 0 002 2h16a2 2 0 002-2V8a2 2 0 00-2-2M4 6l4 4m12-4l-4 4m-4-2v8',
        ];
    }

    private function genererCodeAcces(): string
    {
        do {
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $code .= self::ALPHABET_CODE[random_int(0, strlen(self::ALPHABET_CODE) - 1)];
            }
        } while (DB::table('carousel_sessions')->where('access_code', $code)->exists());

        return $code;
    }

    private function preparerSession(stdClass $session): stdClass
    {
        $session->id = (int) $session->id;
        $session->formateur_id = (int) $session->formateur_id;
        $session->group_id = (int) $session->group_id;
        $session->is_active = (bool) $session->is_active;

        if (property_exists($session, 'slides_count')) {
            $session->slides_count = (int) $session->slides_count;
        }

        return $session;
    }
}
