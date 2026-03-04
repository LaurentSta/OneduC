<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupModuleLecture;
use App\Models\Module;
use App\Models\ModuleLecture;
use App\Models\ModuleSection;
use App\Models\User;
use App\Services\CodeGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GroupeController extends Controller
{
    /**
     * Liste des groupes du formateur connecté.
     */
    public function index()
    {
        $groupes = Group::where('instructor_id', auth()->id())
            ->with(['modules', 'students'])
            ->get();

        return view('formateur.groupes.index', compact('groupes'));
    }

    /**
     * Formulaire de création (wizard).
     */
    public function create()
    {
        $modules = Module::active()->orderBy('module_title')->get();

        return view('formateur.groupes.create', compact('modules'));
    }

    /**
     * Crée un groupe avec ses stagiaires et ses modules ordonnés.
     */
    public function store(Request $request)
    {
        // Validation volontairement minimale pour rester aligné au comportement actuel.
        DB::transaction(function () use ($request): void {
            $group = Group::create([
                'name' => $request->nom,
                'description' => $request->description,
                'instructor_id' => auth()->id(),
            ]);

            foreach ($request->input('stagiaires', []) as $s) {
                if (empty($s['email']) && empty($s['prenom']) && empty($s['nom'])) {
                    continue;
                }

                $email = strtolower(trim((string) ($s['email'] ?? '')));
                if ($email === '') {
                    continue;
                }

                $user = User::withTrashed()->where('email', $email)->first();

                if ($user) {
                    if ($user->trashed()) {
                        $user->restore();
                    }
                    if (! $user->formateur_id) {
                        $user->formateur_id = auth()->id();
                    }
                    $user->prenom = $user->prenom ?: ($s['prenom'] ?? null);
                    $user->name = $user->name ?: ($s['nom'] ?? null);
                    $user->save();
                } else {
                    $user = User::create([
                        'prenom' => $s['prenom'] ?? null,
                        'name' => $s['nom'] ?? null,
                        'email' => $email,
                        'password' => Hash::make((string) $request->password),
                        'role' => 'stagiaire',
                        'formateur_id' => auth()->id(),
                        'status' => 1,
                        'code_acces' => CodeGeneratorService::generateUniqueAccessCode(),
                    ]);
                }

                $group->students()->syncWithoutDetaching([
                    $user->id => ['role_in_group' => 'stagiaire'],
                ]);
            }

            $moduleIds = collect($request->input('modules', []))
                ->map(fn ($value) => (int) $value)
                ->filter()
                ->unique()
                ->values();

            $positions = collect($request->input('module_positions', []))
                ->mapWithKeys(fn ($value, $moduleId) => [(int) $moduleId => (int) $value]);

            // On normalise la position en 1..N pour éviter des trous/inversions côté pivot.
            $orderedModuleIds = $moduleIds
                ->sortBy(fn ($moduleId) => $positions->get($moduleId, PHP_INT_MAX))
                ->values();

            $sync = [];
            foreach ($orderedModuleIds as $index => $moduleId) {
                $sync[$moduleId] = ['position' => $index + 1];
            }

            $group->modules()->sync($sync);
        });

        return redirect()
            ->route('formateur.groupes.index')
            ->with('success', 'Groupe et stagiaires enregistrés avec succès.');
    }

    /**
     * Formulaire d’édition.
     */
    public function edit($id)
    {
        $group = Group::where('id', $id)
            ->where('instructor_id', auth()->id())
            ->with(['modules', 'students'])
            ->firstOrFail();

        $modules = Module::active()->orderBy('module_title')->get();

        return view('formateur.groupes.edit', compact('group', 'modules'));
    }

    /**
     * Met à jour le groupe, l'ordre des modules et les stagiaires.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nom' => [
                'required',
                'string',
                'max:150',
                Rule::unique('groups', 'name')->ignore($id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'modules' => ['required', 'array', 'min:1'],
            'modules.*' => [Rule::exists('modules', 'id')->where('status', 1)],
            'module_positions' => ['nullable', 'array'],
            'stagiaires' => ['nullable', 'array'],
            'stagiaires.*.email' => ['nullable', 'email', 'distinct'],
            'stagiaires.*.prenom' => ['nullable', 'string', 'max:255'],
            'stagiaires.*.nom' => ['nullable', 'string', 'max:255'],
            'remove_students' => ['nullable', 'array'],
            'remove_students.*' => ['integer', Rule::exists('users', 'id')],
        ]);

        $group = Group::where('id', $id)
            ->where('instructor_id', auth()->id())
            ->with('students')
            ->firstOrFail();

        $group->update([
            'name' => $request->nom,
            'description' => $request->description,
        ]);

        $moduleIds = collect($request->input('modules', []))
            ->map(fn ($value) => (int) $value)
            ->filter()
            ->unique()
            ->values();

        $positions = collect($request->input('module_positions', []))
            ->mapWithKeys(fn ($value, $moduleId) => [(int) $moduleId => (int) $value]);

        $orderedModuleIds = $moduleIds
            ->sortBy(fn ($moduleId) => $positions->get($moduleId, PHP_INT_MAX))
            ->values();

        $syncData = [];
        foreach ($orderedModuleIds as $index => $moduleId) {
            $syncData[$moduleId] = ['position' => $index + 1];
        }

        $group->modules()->sync($syncData);

        $removeIds = collect($request->input('remove_students', []))
            ->map(fn ($v) => (int) $v)
            ->filter()
            ->unique();

        if ($removeIds->isNotEmpty()) {
            $currentIds = $group->students->pluck('id')->map(fn ($v) => (int) $v);
            $safeDetach = $removeIds->intersect($currentIds);

            if ($safeDetach->isNotEmpty()) {
                $group->students()->detach($safeDetach->all());
            }
        }

        if ($request->filled('stagiaires')) {
            foreach ($request->stagiaires as $s) {
                if (empty($s['email']) && empty($s['prenom']) && empty($s['nom'])) {
                    continue;
                }

                $email = strtolower(trim($s['email'] ?? ''));
                if ($email === '') {
                    continue;
                }

                $user = User::withTrashed()->where('email', $email)->first();

                if ($user) {
                    if ($user->trashed()) {
                        $user->restore();
                    }
                    if (! $user->formateur_id) {
                        $user->formateur_id = auth()->id();
                    }
                    $user->prenom = $user->prenom ?: ($s['prenom'] ?? null);
                    $user->name = $user->name ?: ($s['nom'] ?? null);
                    $user->save();
                } else {
                    $user = User::create([
                        'prenom' => $s['prenom'] ?? null,
                        'name' => $s['nom'] ?? null,
                        'email' => $email,
                        'password' => Hash::make(Str::random(12)),
                        'role' => 'stagiaire',
                        'formateur_id' => auth()->id(),
                        'status' => 1,
                        'code_acces' => CodeGeneratorService::generateUniqueAccessCode(),
                    ]);
                }

                $group->students()->syncWithoutDetaching([
                    $user->id => ['role_in_group' => 'stagiaire'],
                ]);
            }
        }

        return redirect()
            ->route('formateur.groupes.index')
            ->with('success', 'Groupe modifié avec succès.');
    }

    /**
     * Suppression d’un groupe et nettoyage des associations.
     */
    public function destroy($id)
    {
        $group = Group::where('id', $id)
            ->where('instructor_id', auth()->id())
            ->firstOrFail();

        if (! empty($group->groupe_image) && Storage::disk('public')->exists($group->groupe_image)) {
            Storage::disk('public')->delete($group->groupe_image);
        }

        $group->students()->detach();
        $group->modules()->detach();
        $group->delete();

        return redirect()
            ->route('formateur.groupes.index')
            ->with('success', 'Groupe supprimé avec succès.');
    }

    /**
     * Initialise la personnalisation des leçons d'un module pour un groupe.
     */
    private function ensureCustomization(Group $group, Module $module): void
    {
        $exists = GroupModuleLecture::query()
            ->where('group_id', $group->id)
            ->where('module_id', $module->id)
            ->exists();

        if ($exists) {
            return;
        }

        DB::transaction(function () use ($group, $module): void {
            $existsLocked = GroupModuleLecture::query()
                ->where('group_id', $group->id)
                ->where('module_id', $module->id)
                ->lockForUpdate()
                ->exists();

            if ($existsLocked) {
                return;
            }

            $lectures = ModuleLecture::query()
                ->where('module_id', $module->id)
                ->orderBy('section_id')
                ->orderBy('position')
                ->orderBy('id')
                ->get(['id']);

            if ($lectures->isEmpty()) {
                return;
            }

            // Insertion en masse: plus performant qu'un create() par ligne.
            $pos = 1;
            $payload = $lectures->map(function ($lecture) use ($group, $module, &$pos) {
                return [
                    'group_id' => $group->id,
                    'module_id' => $module->id,
                    'lecture_id' => $lecture->id,
                    'position' => $pos++,
                    'is_enabled' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();

            GroupModuleLecture::query()->insert($payload);
        });
    }

    /**
     * Écran de gestion de l'ordre/activation des leçons par groupe.
     */
    public function editModuleLessons($groupId, $moduleId)
    {
        $formateurId = auth()->id();

        $group = Group::query()
            ->where('id', $groupId)
            ->where('instructor_id', $formateurId)
            ->firstOrFail();

        $module = Module::query()->findOrFail($moduleId);

        abort_unless($group->modules()->where('modules.id', $module->id)->exists(), 403);

        $this->ensureCustomization($group, $module);

        $rows = GroupModuleLecture::query()
            ->where('group_id', $group->id)
            ->where('module_id', $module->id)
            ->orderBy('position')
            ->get(['lecture_id', 'position', 'is_enabled'])
            ->keyBy('lecture_id');

        $sections = ModuleSection::query()
            ->where('module_id', $module->id)
            ->orderBy('id')
            ->with([
                'lectures' => function ($query) use ($module): void {
                    $query->where('module_id', $module->id)
                        ->orderBy('position')
                        ->orderBy('id');
                },
            ])
            ->get();

        // On remplace l'ordre natif par l'ordre propre au groupe.
        $sections->each(function ($section) use ($rows): void {
            $section->setRelation(
                'lectures',
                $section->lectures
                    ->sortBy(fn ($lecture) => (int) ($rows[$lecture->id]->position ?? 999999))
                    ->values()
            );
        });

        return view('formateur.groupes.module_lecons', compact('group', 'module', 'sections', 'rows'));
    }

    /**
     * Active/désactive une leçon pour un groupe donné.
     */
    public function toggleModuleLesson($groupId, $moduleId, $lectureId)
    {
        $group = Group::where('id', $groupId)
            ->where('instructor_id', auth()->id())
            ->firstOrFail();
        $module = Module::findOrFail($moduleId);

        abort_unless($group->modules()->where('modules.id', $module->id)->exists(), 403);

        $this->ensureCustomization($group, $module);

        $row = GroupModuleLecture::where('group_id', $group->id)
            ->where('module_id', $module->id)
            ->where('lecture_id', $lectureId)
            ->firstOrFail();

        $row->update(['is_enabled' => ! (bool) $row->is_enabled]);

        return back()->with('success', 'Leçon mise à jour.');
    }

    public function moveModuleLessonUp($groupId, $moduleId, $lectureId)
    {
        return $this->moveModuleLesson($groupId, $moduleId, $lectureId, -1);
    }

    public function moveModuleLessonDown($groupId, $moduleId, $lectureId)
    {
        return $this->moveModuleLesson($groupId, $moduleId, $lectureId, 1);
    }

    /**
     * Déplace une leçon dans sa section uniquement pour conserver la structure pédagogique.
     */
    private function moveModuleLesson($groupId, $moduleId, $lectureId, int $delta)
    {
        $group = Group::where('id', $groupId)
            ->where('instructor_id', auth()->id())
            ->firstOrFail();
        $module = Module::findOrFail($moduleId);

        abort_unless($group->modules()->where('modules.id', $module->id)->exists(), 403);

        $this->ensureCustomization($group, $module);

        $lecture = ModuleLecture::where('id', $lectureId)
            ->where('module_id', $module->id)
            ->firstOrFail();

        $sectionId = (int) $lecture->section_id;

        DB::transaction(function () use ($group, $module, $lectureId, $delta, $sectionId): void {
            $list = GroupModuleLecture::query()
                ->join('module_lectures', 'module_lectures.id', '=', 'group_module_lectures.lecture_id')
                ->where('group_module_lectures.group_id', $group->id)
                ->where('group_module_lectures.module_id', $module->id)
                ->where('module_lectures.section_id', $sectionId)
                ->orderBy('group_module_lectures.position')
                ->lockForUpdate()
                ->get(['group_module_lectures.*'])
                ->values();

            $idx = $list->search(fn ($row) => (int) $row->lecture_id === (int) $lectureId);
            if ($idx === false) {
                return;
            }

            $swapIdx = $idx + $delta;
            if ($swapIdx < 0 || $swapIdx >= $list->count()) {
                return;
            }

            $a = $list[$idx];
            $b = $list[$swapIdx];

            $tmp = $a->position;
            $a->position = $b->position;
            $b->position = $tmp;

            $a->save();
            $b->save();
        });

        return back()->with('success', 'Ordre mis à jour.');
    }

    /**
     * Supprime la personnalisation pour revenir à l'ordre natif du module.
     */
    public function resetModuleLessons($groupId, $moduleId)
    {
        $group = Group::where('id', $groupId)
            ->where('instructor_id', auth()->id())
            ->firstOrFail();
        $module = Module::findOrFail($moduleId);

        abort_unless($group->modules()->where('modules.id', $module->id)->exists(), 403);

        GroupModuleLecture::where('group_id', $group->id)
            ->where('module_id', $module->id)
            ->delete();

        return back()->with('success', 'Personnalisation réinitialisée.');
    }
}
