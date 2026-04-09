<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Mail\StagiaireGroupInvitation;
use App\Models\Group;
use App\Models\GroupModuleLecture;
use App\Models\Module;
use App\Models\ModuleLecture;
use App\Models\ModuleSection;
use App\Models\User;
use App\Notifications\GroupCoTrainerAddedNotification;
use App\Services\CodeGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class GroupeController extends Controller
{
    /**
     * Liste des groupes du formateur connecté.
     */
    public function index()
    {
        $groupes = Group::query()
            ->accessibleByTrainer((int) auth()->id())
            ->with(['modules', 'students', 'observers', 'instructor', 'coFormateurs'])
            ->orderBy('name')
            ->get();

        return view('formateur.groupes.index', compact('groupes'));
    }

    /**
     * Formulaire de création (wizard).
     */
    public function create()
    {
        $modules = Module::active()
            ->with([
                'sections.lectures:id,module_id,section_id,duration,question_count,quiz_enabled,quiz_questions_per_attempt',
            ])
            ->orderBy('module_title')
            ->get();

        $initialCoFormateurs = $this->resolveCoTrainerPayloadFromIds(old('co_formateurs', []));
        $canManageCoFormateurs = true;

        return view('formateur.groupes.create', compact('modules', 'initialCoFormateurs', 'canManageCoFormateurs'));
    }

    /**
     * Crée un groupe avec ses stagiaires et ses modules ordonnés.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom' => ['required', 'string', 'max:150', Rule::unique('groups', 'name')],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'password' => ['required', 'string', 'min:8'],
            'modules' => ['required', 'array', 'min:1'],
            'modules.*' => [Rule::exists('modules', 'id')->where('status', 1)],
            'module_positions' => ['nullable', 'array'],
            'module_positions.*' => ['nullable', 'integer', 'min:1'],
            'stagiaires' => ['nullable', 'array'],
            'stagiaires.*.email' => ['nullable', 'email', 'distinct'],
            'stagiaires.*.prenom' => ['nullable', 'string', 'max:255'],
            'stagiaires.*.nom' => ['nullable', 'string', 'max:255'],
            'co_formateurs' => ['nullable', 'array'],
            'co_formateurs.*' => [
                'integer',
                'distinct',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query
                        ->where('role', 'formateur')
                        ->where('status', 1)
                        ->where('id', '<>', (int) auth()->id());
                }),
            ],
        ], $this->groupValidationMessages(), $this->groupValidationAttributes());

        $temporaryPassword = (string) $request->password;
        $coTrainerIds = $this->sanitizeCoTrainerIds($request->input('co_formateurs', []), (int) auth()->id());

        try {
            DB::transaction(function () use ($request, $temporaryPassword, $coTrainerIds): void {
                $group = Group::create([
                    'name' => trim((string) $request->nom),
                    'description' => $request->description,
                    'is_active' => $request->boolean('is_active'),
                    'start_date' => $request->input('start_date') ?: null,
                    'end_date' => $request->input('end_date') ?: null,
                    'temporary_password' => $temporaryPassword,
                    'instructor_id' => auth()->id(),
                ]);

                $this->syncCoFormateurs($group, $coTrainerIds, auth()->user());

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
                        if (blank($user->code_acces)) {
                            $user->code_acces = CodeGeneratorService::generateUniqueAccessCode();
                        }
                        $user->save();
                    } else {
                        $user = User::create([
                            'prenom' => $s['prenom'] ?? null,
                            'name' => $s['nom'] ?? null,
                            'email' => $email,
                            'password' => Hash::make($temporaryPassword),
                            'role' => 'stagiaire',
                            'formateur_id' => auth()->id(),
                            'status' => 1,
                            'code_acces' => CodeGeneratorService::generateUniqueAccessCode(),
                        ]);
                    }

                    $alreadyInGroup = DB::table('group_user')
                        ->where('group_id', $group->id)
                        ->where('user_id', $user->id)
                        ->where('role_in_group', 'stagiaire')
                        ->exists();

                    $group->students()->syncWithoutDetaching([
                        $user->id => ['role_in_group' => 'stagiaire'],
                    ]);

                    if (! $alreadyInGroup) {
                        DB::afterCommit(function () use ($group, $user): void {
                            $this->sendInvitationEmailToStagiaire($group, $user);
                        });
                    }
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
        } catch (QueryException $e) {
            if ((string) $e->getCode() === '23000' && str_contains($e->getMessage(), 'groups_name_unique')) {
                throw ValidationException::withMessages([
                    'nom' => 'Ce nom de groupe existe déjà. Merci de choisir un autre nom.',
                ]);
            }

            throw $e;
        }

        return redirect()
            ->route('formateur.groupes.index')
            ->with('success', 'Groupe et stagiaires enregistrés avec succès.');
    }

    /**
     * Formulaire d’édition.
     */
    public function edit($id)
    {
        $group = Group::query()
            ->accessibleByTrainer((int) auth()->id())
            ->where('id', $id)
            ->with(['modules', 'students', 'observers', 'coFormateurs', 'instructor'])
            ->firstOrFail();

        $modules = Module::active()
            ->with([
                'sections.lectures:id,module_id,section_id,duration,question_count,quiz_enabled,quiz_questions_per_attempt',
            ])
            ->orderBy('module_title')
            ->get();

        $canManageCoFormateurs = $group->canManageCoFormateurs(auth()->user());
        $initialCoFormateurs = $canManageCoFormateurs && old('co_formateurs')
            ? $this->resolveCoTrainerPayloadFromIds(old('co_formateurs', []))
            : $group->coFormateurs
                ->sortBy('email')
                ->values()
                ->map(fn (User $trainer) => [
                    'id' => (int) $trainer->id,
                    'email' => (string) $trainer->email,
                ]);

        return view('formateur.groupes.edit', compact('group', 'modules', 'initialCoFormateurs', 'canManageCoFormateurs'));
    }

    /**
     * Met à jour le groupe, l'ordre des modules et les stagiaires.
     */
    public function update(Request $request, $id)
    {
        $group = Group::query()
            ->accessibleByTrainer((int) auth()->id())
            ->where('id', $id)
            ->with(['students', 'coFormateurs'])
            ->firstOrFail();
        $canManageCoFormateurs = $group->canManageCoFormateurs(auth()->user());

        $request->validate([
            'nom' => [
                'required',
                'string',
                'max:150',
                Rule::unique('groups', 'name')->ignore($id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'password' => ['nullable', 'string', 'min:8'],
            'modules' => ['required', 'array', 'min:1'],
            'modules.*' => [Rule::exists('modules', 'id')->where('status', 1)],
            'module_positions' => ['nullable', 'array'],
            'stagiaires' => ['nullable', 'array'],
            'stagiaires.*.email' => ['nullable', 'email', 'distinct'],
            'stagiaires.*.prenom' => ['nullable', 'string', 'max:255'],
            'stagiaires.*.nom' => ['nullable', 'string', 'max:255'],
            'remove_students' => ['nullable', 'array'],
            'remove_students.*' => ['integer', Rule::exists('users', 'id')],
            'co_formateurs' => ['nullable', 'array'],
            'co_formateurs.*' => [
                'integer',
                'distinct',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query
                        ->where('role', 'formateur')
                        ->where('status', 1)
                        ->where('id', '<>', (int) auth()->id());
                }),
            ],
        ], $this->groupValidationMessages(), $this->groupValidationAttributes());

        $temporaryPasswordInput = trim((string) $request->input('password', ''));
        $effectiveTemporaryPassword = $temporaryPasswordInput !== ''
            ? $temporaryPasswordInput
            : (string) ($group->temporary_password ?? '');

        $hasStagiairePayload = collect($request->input('stagiaires', []))->contains(function ($s): bool {
            $prenom = trim((string) ($s['prenom'] ?? ''));
            $nom = trim((string) ($s['nom'] ?? ''));
            $email = trim((string) ($s['email'] ?? ''));

            return $prenom !== '' || $nom !== '' || $email !== '';
        });

        if ($hasStagiairePayload && $effectiveTemporaryPassword === '') {
            throw ValidationException::withMessages([
                'password' => 'Veuillez renseigner un code d\'accès provisoire (au moins 8 caractères) avant d\'ajouter un nouveau stagiaire.',
            ]);
        }

        $groupPayload = [
            'name' => $request->nom,
            'description' => $request->description,
            'start_date' => $request->input('start_date') ?: null,
            'end_date' => $request->input('end_date') ?: null,
        ];

        static $hasIsActiveColumn = null;
        $hasIsActiveColumn ??= Schema::hasColumn('groups', 'is_active');
        if ($hasIsActiveColumn) {
            $groupPayload['is_active'] = $request->boolean('is_active');
        }

        // Ne pas écraser le code provisoire existant quand l'utilisateur modifie seulement la description.
        if ($temporaryPasswordInput !== '') {
            $groupPayload['temporary_password'] = $temporaryPasswordInput;
        }

        $group->update($groupPayload);

        if ($canManageCoFormateurs) {
            $coTrainerIds = $this->sanitizeCoTrainerIds($request->input('co_formateurs', []), (int) auth()->id());
            $this->syncCoFormateurs($group, $coTrainerIds, auth()->user());
        }

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
                    if (blank($user->code_acces)) {
                        $user->code_acces = CodeGeneratorService::generateUniqueAccessCode();
                    }
                    $user->save();
                } else {
                    $user = User::create([
                        'prenom' => $s['prenom'] ?? null,
                        'name' => $s['nom'] ?? null,
                        'email' => $email,
                        'password' => Hash::make($effectiveTemporaryPassword),
                        'role' => 'stagiaire',
                        'formateur_id' => auth()->id(),
                        'status' => 1,
                        'code_acces' => CodeGeneratorService::generateUniqueAccessCode(),
                    ]);
                }

                $alreadyInGroup = DB::table('group_user')
                    ->where('group_id', $group->id)
                    ->where('user_id', $user->id)
                    ->where('role_in_group', 'stagiaire')
                    ->exists();

                $group->students()->syncWithoutDetaching([
                    $user->id => ['role_in_group' => 'stagiaire'],
                ]);

                if (! $alreadyInGroup) {
                    DB::afterCommit(function () use ($group, $user): void {
                        $this->sendInvitationEmailToStagiaire($group, $user);
                    });
                }
            }
        }

        return redirect()
            ->route('formateur.groupes.index')
            ->with('success', 'Groupe modifié avec succès.');
    }

    private function sendInvitationEmailToStagiaire(Group $group, User $user): void
    {
        $loginUrl = route('stagiaire.code.form');

        try {
            Mail::to($user->email)->send(
                new StagiaireGroupInvitation($user, $group, $loginUrl)
            );
        } catch (\Throwable $e) {
            Log::warning('Invitation stagiaire non envoyee (erreur SMTP).', [
                'group_id' => $group->id,
                'stagiaire_id' => $user->id,
                'stagiaire_email' => $user->email,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Suppression d’un groupe et nettoyage des associations.
     */
    public function destroy($id)
    {
        $group = Group::query()
            ->accessibleByTrainer((int) auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        abort_unless($group->isOwnedBy(auth()->user()), 403);

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
        $moduleLectureIds = ModuleLecture::query()
            ->where('module_id', $module->id)
            ->orderBy('section_id')
            ->orderBy('position')
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($moduleLectureIds->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($group, $module, $moduleLectureIds): void {
            $existingRows = GroupModuleLecture::query()
                ->where('group_id', $group->id)
                ->where('module_id', $module->id)
                ->lockForUpdate()
                ->get(['lecture_id', 'position']);

            if ($existingRows->isEmpty()) {
                $pos = 1;
                $payload = $moduleLectureIds->map(function ($lectureId) use ($group, $module, &$pos) {
                    return [
                        'group_id' => $group->id,
                        'module_id' => $module->id,
                        'lecture_id' => (int) $lectureId,
                        'position' => $pos++,
                        'is_enabled' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })->all();

                GroupModuleLecture::query()->insert($payload);
                return;
            }

            $existingLectureIds = $existingRows
                ->pluck('lecture_id')
                ->map(fn ($id) => (int) $id);

            $missingLectureIds = $moduleLectureIds->diff($existingLectureIds)->values();
            if ($missingLectureIds->isEmpty()) {
                return;
            }

            $pos = ((int) $existingRows->max('position')) + 1;
            $payload = $missingLectureIds->map(function ($lectureId) use ($group, $module, &$pos) {
                return [
                    'group_id' => $group->id,
                    'module_id' => $module->id,
                    'lecture_id' => (int) $lectureId,
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
            ->accessibleByTrainer((int) $formateurId)
            ->where('id', $groupId)
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

        // Parcours officiel: premier chapitre/leçon selon l'ordre natif du module.
        $officialFirstLecture = ModuleLecture::query()
            ->where('module_id', $module->id)
            ->orderBy('position')
            ->orderBy('id')
            ->first(['id', 'section_id']);

        // Parcours groupe: première leçon active selon la personnalisation du groupe.
        $groupFirstSectionId = 0;
        $groupFirstLectureId = 0;
        foreach ($sections as $section) {
            foreach (($section->lectures ?? collect()) as $lecture) {
                $row = $rows[$lecture->id] ?? null;
                $enabled = $row ? (bool) $row->is_enabled : true;

                if (! $enabled) {
                    continue;
                }

                $groupFirstSectionId = (int) $section->id;
                $groupFirstLectureId = (int) $lecture->id;
                break 2;
            }
        }

        if (! $groupFirstSectionId || ! $groupFirstLectureId) {
            $fallbackSection = $sections->first();
            $fallbackLecture = $fallbackSection?->lectures?->first();
            if ($fallbackSection && $fallbackLecture) {
                $groupFirstSectionId = (int) $fallbackSection->id;
                $groupFirstLectureId = (int) $fallbackLecture->id;
            }
        }

        $officialPreviewUrl = null;
        if ($officialFirstLecture) {
            $officialPreviewUrl = route('formateur.formations.section', [
                'module' => $module->id,
                'section' => (int) $officialFirstLecture->section_id,
                'mode' => 'officiel',
            ]);
        }

        $groupPreviewUrl = null;
        if ($groupFirstSectionId && $groupFirstLectureId) {
            $groupPreviewUrl = route('formateur.formations.section', [
                'module' => $module->id,
                'section' => $groupFirstSectionId,
                'mode' => 'groupe',
                'group_id' => $group->id,
            ]);
        }

        return view('formateur.groupes.module_lecons', compact(
            'group',
            'module',
            'sections',
            'rows',
            'officialPreviewUrl',
            'groupPreviewUrl',
        ));
    }

    /**
     * Active/désactive une leçon pour un groupe donné.
     */
    public function toggleModuleLesson($groupId, $moduleId, $lectureId)
    {
        $group = Group::query()
            ->accessibleByTrainer((int) auth()->id())
            ->where('id', $groupId)
            ->firstOrFail();
        $module = Module::findOrFail($moduleId);

        abort_unless($group->modules()->where('modules.id', $module->id)->exists(), 403);

        $this->ensureCustomization($group, $module);

        $row = GroupModuleLecture::where('group_id', $group->id)
            ->where('module_id', $module->id)
            ->where('lecture_id', $lectureId)
            ->first();

        if (! $row) {
            $lectureExistsInModule = ModuleLecture::query()
                ->where('id', $lectureId)
                ->where('module_id', $module->id)
                ->exists();

            abort_unless($lectureExistsInModule, 404);

            $nextPosition = ((int) GroupModuleLecture::query()
                ->where('group_id', $group->id)
                ->where('module_id', $module->id)
                ->max('position')) + 1;

            $row = GroupModuleLecture::query()->create([
                'group_id' => $group->id,
                'module_id' => $module->id,
                'lecture_id' => (int) $lectureId,
                'position' => max(1, $nextPosition),
                'is_enabled' => true,
            ]);
        }

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
        $group = Group::query()
            ->accessibleByTrainer((int) auth()->id())
            ->where('id', $groupId)
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
        $group = Group::query()
            ->accessibleByTrainer((int) auth()->id())
            ->where('id', $groupId)
            ->firstOrFail();
        $module = Module::findOrFail($moduleId);

        abort_unless($group->modules()->where('modules.id', $module->id)->exists(), 403);

        GroupModuleLecture::where('group_id', $group->id)
            ->where('module_id', $module->id)
            ->delete();

        return back()->with('success', 'Personnalisation réinitialisée.');
    }

    public function searchCoFormateurs(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));
        if (mb_strlen($term) < 3) {
            return response()->json(['items' => []]);
        }

        $groupId = (int) $request->query('group_id', 0);
        if ($groupId > 0) {
            $group = Group::query()
                ->accessibleByTrainer((int) auth()->id())
                ->where('id', $groupId)
                ->firstOrFail();

            abort_unless($group->canManageCoFormateurs(auth()->user()), 403);
        }

        $excludeIds = collect($request->query('exclude', []))
            ->map(fn ($value) => (int) $value)
            ->filter(fn ($value) => $value > 0)
            ->push((int) auth()->id())
            ->unique()
            ->values();

        $items = User::query()
            ->where('role', 'formateur')
            ->where('status', 1)
            ->whereNotNull('email')
            ->where('email', 'like', mb_strtolower($term) . '%')
            ->when($excludeIds->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $excludeIds->all()))
            ->orderBy('email')
            ->limit(10)
            ->get(['id', 'email'])
            ->map(fn (User $trainer) => [
                'id' => (int) $trainer->id,
                'email' => (string) $trainer->email,
            ])
            ->values();

        return response()->json(['items' => $items]);
    }

    private function groupValidationMessages(): array
    {
        return [
            'nom.required' => 'Veuillez renseigner le nom du groupe.',
            'nom.unique' => 'Ce nom de groupe existe déjà. Merci de choisir un autre nom.',
            'password.required' => 'Veuillez renseigner un code d\'accès provisoire.',
            'password.min' => 'Le code d\'accès provisoire doit contenir au moins 8 caractères.',
            'modules.required' => 'Veuillez sélectionner au moins un module.',
            'modules.min' => 'Veuillez sélectionner au moins un module.',
            'modules.*.exists' => 'Un des modules sélectionnés est invalide.',
            'stagiaires.*.email.distinct' => 'Chaque stagiaire doit avoir une adresse e-mail différente.',
            'end_date.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de démarrage.',
        ];
    }

    private function groupValidationAttributes(): array
    {
        return [
            'nom' => 'nom du groupe',
            'description' => 'description du groupe',
            'start_date' => 'date de démarrage',
            'end_date' => 'date de fin',
            'is_active' => 'statut du groupe',
            'password' => 'code d\'accès provisoire',
            'modules' => 'modules du parcours',
            'modules.*' => 'module du parcours',
            'module_positions' => 'ordre des modules',
            'module_positions.*' => 'position du module',
            'stagiaires' => 'liste des stagiaires',
            'stagiaires.*.prenom' => 'prénom du stagiaire',
            'stagiaires.*.nom' => 'nom du stagiaire',
            'stagiaires.*.email' => 'adresse e-mail du stagiaire',
            'remove_students' => 'stagiaires à retirer',
            'remove_students.*' => 'stagiaire à retirer',
            'co_formateurs' => 'co-formateurs',
            'co_formateurs.*' => 'co-formateur',
        ];
    }

    private function sanitizeCoTrainerIds(array $rawIds, int $currentTrainerId)
    {
        $candidateIds = collect($rawIds)
            ->map(fn ($value) => (int) $value)
            ->filter(fn (int $value) => $value > 0 && $value !== $currentTrainerId)
            ->unique()
            ->values();

        if ($candidateIds->isEmpty()) {
            return collect();
        }

        return User::query()
            ->whereIn('id', $candidateIds->all())
            ->where('role', 'formateur')
            ->where('status', 1)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();
    }

    private function resolveCoTrainerPayloadFromIds(array $rawIds)
    {
        $ids = collect($rawIds)
            ->map(fn ($value) => (int) $value)
            ->filter(fn (int $value) => $value > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return User::query()
            ->whereIn('id', $ids->all())
            ->where('role', 'formateur')
            ->where('status', 1)
            ->orderBy('email')
            ->get(['id', 'email'])
            ->map(fn (User $trainer) => [
                'id' => (int) $trainer->id,
                'email' => (string) $trainer->email,
            ])
            ->values();
    }

    private function syncCoFormateurs(Group $group, $coTrainerIds, ?User $actor = null): void
    {
        $targetIds = collect($coTrainerIds)
            ->map(fn ($value) => (int) $value)
            ->filter(fn (int $value) => $value > 0)
            ->unique()
            ->values();

        $currentIds = $group->coFormateurs()
            ->pluck('users.id')
            ->map(fn ($value) => (int) $value)
            ->values();

        $toDetach = $currentIds->diff($targetIds)->values();
        $toAttach = $targetIds->diff($currentIds)->values();

        if ($toDetach->isNotEmpty()) {
            DB::table('group_user')
                ->where('group_id', $group->id)
                ->whereIn('user_id', $toDetach->all())
                ->where('role_in_group', 'formateur')
                ->delete();
        }

        if ($toAttach->isNotEmpty()) {
            $now = now();

            foreach ($toAttach as $trainerId) {
                DB::table('group_user')->updateOrInsert(
                    [
                        'group_id' => $group->id,
                        'user_id' => $trainerId,
                    ],
                    [
                        'role_in_group' => 'formateur',
                        'updated_at' => $now,
                        'created_at' => $now,
                    ],
                );
            }
        }

        if ($toAttach->isNotEmpty() && $actor instanceof User && Schema::hasTable('notifications')) {
            DB::afterCommit(function () use ($group, $toAttach, $actor): void {
                User::query()
                    ->whereIn('id', $toAttach->all())
                    ->where('role', 'formateur')
                    ->get()
                    ->each(fn (User $trainer) => $trainer->notify(new GroupCoTrainerAddedNotification($group, $actor)));
            });
        }
    }
}
