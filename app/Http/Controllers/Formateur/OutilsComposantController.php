<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\ComponentFinderSession;
use App\Models\Group;
use App\Services\CodeGeneratorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class OutilsComposantController extends Controller
{
    public function index(): View
    {
        $formateurId = (int) auth()->id();

        $groups = Group::query()
            ->accessibleByTrainer($formateurId)
            ->withCount('students')
            ->orderBy('name')
            ->get(['id', 'name']);

        $sessions = ComponentFinderSession::query()
            ->whereHas('group', fn ($query) => $query->accessibleByTrainer($formateurId))
            ->with('group:id,name')
            ->withCount('attempts')
            ->latest()
            ->limit(20)
            ->get();

        return view('formateur.outils.composants_index', compact('groups', 'sessions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $formateurId = (int) auth()->id();

        $data = $request->validate([
            'group_id' => ['required', 'exists:groups,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'zones' => ['required', 'json'],
        ]);

        $group = Group::query()
            ->accessibleByTrainer($formateurId)
            ->findOrFail((int) $data['group_id']);

        $allowedShapes = ['square', 'circle', 'oval', 'triangle', 'rectangle'];

        $zones = collect(json_decode($data['zones'], true) ?: [])
            ->map(function ($zone) use ($allowedShapes): array {
                $shape = (string) ($zone['shape'] ?? 'rectangle');
                $x = max(0, min(100, (float) ($zone['x'] ?? 0)));
                $y = max(0, min(100, (float) ($zone['y'] ?? 0)));
                $w = max(0, min(100 - $x, (float) ($zone['w'] ?? 0)));
                $h = max(0, min(100 - $y, (float) ($zone['h'] ?? 0)));

                return [
                    'label' => trim((string) ($zone['label'] ?? '')),
                    'shape' => in_array($shape, $allowedShapes, true) ? $shape : 'rectangle',
                    'x' => $x,
                    'y' => $y,
                    'w' => $w,
                    'h' => $h,
                ];
            })
            ->filter(fn (array $zone): bool => $zone['label'] !== '' && $zone['w'] > 0 && $zone['h'] > 0)
            ->values();

        abort_if($zones->count() < 2, 422, 'Ajoutez au moins deux composants à trouver sur l\'image.');
        abort_if($zones->count() > 15, 422, 'Vous ne pouvez pas définir plus de 15 composants.');

        $imagePath = $request->file('image')->store('component_finder', 'public');

        $session = ComponentFinderSession::query()->create([
            'formateur_id' => $formateurId,
            'group_id' => $group->id,
            'title' => trim((string) ($data['title'] ?? '')) ?: 'Trouve le composant',
            'image_path' => $imagePath,
            'zones' => $zones->all(),
            'access_code' => CodeGeneratorService::generateUniqueCode(ComponentFinderSession::class),
            'is_active' => true,
            'opened_at' => now(),
            'closed_at' => null,
        ]);

        return redirect()->route('formateur.composants.show', $session);
    }

    public function show(ComponentFinderSession $componentFinderSession): View
    {
        $this->assertAccess($componentFinderSession);
        $componentFinderSession->load(['group', 'attempts.user']);

        $leaderboard = $componentFinderSession->attempts
            ->sortBy([
                ['score', 'desc'],
                ['duration_seconds', 'asc'],
            ])
            ->values();

        $zoneStats = collect($componentFinderSession->zones)
            ->map(function (array $zone) use ($componentFinderSession): array {
                $attemptsWithZone = $componentFinderSession->attempts->filter(
                    fn ($attempt) => collect($attempt->details ?? [])->firstWhere('label', $zone['label']) !== null
                );

                $correctCount = $attemptsWithZone->filter(
                    fn ($attempt) => (bool) (collect($attempt->details ?? [])->firstWhere('label', $zone['label'])['correct'] ?? false)
                )->count();

                $total = $attemptsWithZone->count();

                return [
                    'label' => $zone['label'],
                    'correct' => $correctCount,
                    'total' => $total,
                    'percent' => $total > 0 ? (int) round(($correctCount / $total) * 100) : 0,
                ];
            })
            ->values();

        return view('formateur.outils.composants_show', [
            'session' => $componentFinderSession,
            'joinUrl' => route('composants.join.code', $componentFinderSession->access_code),
            'leaderboard' => $leaderboard,
            'zoneStats' => $zoneStats,
        ]);
    }

    public function toggle(ComponentFinderSession $componentFinderSession): RedirectResponse
    {
        $this->assertAccess($componentFinderSession);

        $newStatus = ! $componentFinderSession->is_active;

        $componentFinderSession->update([
            'is_active' => $newStatus,
            'opened_at' => $newStatus ? now() : $componentFinderSession->opened_at,
            'closed_at' => $newStatus ? null : now(),
        ]);

        return back()->with(
            'success',
            $newStatus ? 'Le jeu est maintenant ouvert.' : 'Le jeu est maintenant fermé.'
        );
    }

    public function destroy(ComponentFinderSession $componentFinderSession): RedirectResponse
    {
        $this->assertAccess($componentFinderSession);

        Storage::disk('public')->delete($componentFinderSession->image_path);
        $componentFinderSession->delete();

        return redirect()
            ->route('formateur.composants.index')
            ->with('success', 'Le jeu a été supprimé.');
    }

    private function assertAccess(ComponentFinderSession $componentFinderSession): void
    {
        abort_unless(
            $componentFinderSession->group()
                ->accessibleByTrainer((int) auth()->id())
                ->exists(),
            403
        );
    }
}
