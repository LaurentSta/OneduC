<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Mail\StagiaireGroupInvitation;
use App\Models\FormateurParcours;
use App\Models\Group;
use App\Models\Module;
use App\Models\User;
use App\Notifications\GroupCoTrainerAddedNotification;
use App\Services\CodeGeneratorService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class GroupeController extends Controller
{
    public function index()
    {
        $groupes = Group::query()
            ->accessibleByTrainer((int) auth()->id())
            ->with(['modules', 'students', 'observers', 'instructor', 'coFormateurs'])
            ->orderBy('name')
            ->get();

        return view('formateur.groupes.index', compact('groupes'));
    }

    public function create()
    {
        $modules = Module::query()
            ->assignableAuFormateur((int) auth()->id())
            ->with([
                'sections.lectures:id,module_id,section_id,duration,question_count,quiz_enabled,quiz_questions_per_attempt',
            ])
            ->orderBy('module_title')
            ->get();

        $initialCoFormateurs = $this->resolveCoTrainerPayloadFromIds(old('co_formateurs', []));
        $canManageCoFormateurs = true;

        $mesParcours = FormateurParcours::query()
            ->where('formateur_id', auth()->id())
            ->with(['items' => fn ($q) => $q->orderBy('position')])
            ->orderBy('title')
            ->get();

        return view('formateur.groupes.create', compact('modules', 'initialCoFormateurs', 'canManageCoFormateurs', 'mesParcours'));
    }

    public function store(Request $request)
    {
        $currentTrainerId = (int) auth()->id();
        $request->merge([
            'co_formateurs' => $this->sanitizeCoTrainerIds($request->input('co_formateurs', []), $currentTrainerId)->all(),
        ]);

        $request->validate([
            'nom' => ['required', 'string', 'max:150', Rule::unique('groups', 'name')->withoutTrashed()],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
            'emargement_enabled' => ['nullable', 'boolean'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'password' => ['required', 'string'],
            'formateur_parcours_id' => [
                'nullable', 'integer',
                Rule::exists('formateur_parcours', 'id')->where('formateur_id', auth()->id()),
            ],
            'modules' => ['required', 'array', 'min:1'],
            'modules.*' => [
                Rule::exists('modules', 'id')->where(function ($query) use ($currentTrainerId): void {
                    $this->appliquerFiltreModuleAssignable($query, $currentTrainerId);
                }),
            ],
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
        $coTrainerIds = collect($request->input('co_formateurs', []))
            ->map(fn ($value) => (int) $value)
            ->filter()
            ->unique()
            ->values();

        try {
            DB::transaction(function () use ($request, $temporaryPassword, $coTrainerIds): void {
                $group = Group::create([
                    'name' => trim((string) $request->nom),
                    'description' => $request->description,
                    'is_active' => $request->boolean('is_active'),
                    'emargement_enabled' => $request->boolean('emargement_enabled'),
                    'start_date' => $request->input('start_date') ?: null,
                    'end_date' => $request->input('end_date') ?: null,
                    'temporary_password' => $temporaryPassword,
                    'instructor_id' => auth()->id(),
                    'formateur_parcours_id' => $request->input('formateur_parcours_id') ?: null,
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

    public function edit($id)
    {
        $group = Group::query()
            ->accessibleByTrainer((int) auth()->id())
            ->where('id', $id)
            ->with(['modules', 'students', 'observers', 'coFormateurs', 'instructor'])
            ->firstOrFail();

        $formateurId = (int) auth()->id();
        $modulesConserves = $group->modules->pluck('id')->map(fn ($moduleId) => (int) $moduleId)->all();

        $modules = Module::query()
            ->where(function ($selection) use ($formateurId, $modulesConserves): void {
                $selection
                    ->whereIn('id', $modulesConserves)
                    ->orWhere(function ($assignable) use ($formateurId): void {
                        $assignable->assignableAuFormateur($formateurId);
                    });
            })
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

        $mesParcours = FormateurParcours::query()
            ->where('formateur_id', auth()->id())
            ->with(['items' => fn ($q) => $q->orderBy('position')])
            ->orderBy('title')
            ->get();

        return view('formateur.groupes.edit', compact('group', 'modules', 'initialCoFormateurs', 'canManageCoFormateurs', 'mesParcours'));
    }

    public function update(Request $request, $id)
    {
        $group = Group::query()
            ->accessibleByTrainer((int) auth()->id())
            ->where('id', $id)
            ->with(['students', 'coFormateurs'])
            ->firstOrFail();
        $canManageCoFormateurs = $group->canManageCoFormateurs(auth()->user());
        $currentTrainerId = (int) auth()->id();

        $currentModuleIds = $group->modules()
            ->pluck('modules.id')
            ->map(fn ($id) => (int) $id)
            ->values();

        if (! $request->has('modules')) {
            $currentModulePositions = $group->modules()
                ->pluck('group_module.position', 'modules.id')
                ->mapWithKeys(fn ($position, $moduleId) => [(int) $moduleId => (int) $position])
                ->all();

            $request->merge([
                'modules' => $currentModuleIds->all(),
                'module_positions' => $currentModulePositions,
            ]);
        }

        $request->merge([
            'co_formateurs' => $canManageCoFormateurs
                ? $this->sanitizeCoTrainerIds($request->input('co_formateurs', []), $currentTrainerId)->all()
                : [],
        ]);

        $request->validate([
            'nom' => [
                'required',
                'string',
                'max:150',
                Rule::unique('groups', 'name')->ignore($id)->withoutTrashed(),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
            'emargement_enabled' => ['nullable', 'boolean'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'password' => ['nullable', 'string'],
            'formateur_parcours_id' => [
                'nullable', 'integer',
                Rule::exists('formateur_parcours', 'id')->where('formateur_id', auth()->id()),
            ],
            'modules' => ['required', 'array', 'min:1'],
            'modules.*' => [
                Rule::exists('modules', 'id')->where(function ($query) use ($currentModuleIds, $currentTrainerId): void {
                    $query->where(function ($selection) use ($currentModuleIds, $currentTrainerId): void {
                        $selection->where(function ($assignable) use ($currentTrainerId): void {
                            $this->appliquerFiltreModuleAssignable($assignable, $currentTrainerId);
                        });

                        if ($currentModuleIds->isNotEmpty()) {
                            $selection->orWhereIn('id', $currentModuleIds->all());
                        }
                    });
                }),
            ],
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
                'password' => 'Veuillez renseigner un code d\'accès provisoire avant d\'ajouter un nouveau stagiaire.',
            ]);
        }

        $groupPayload = [
            'name' => $request->nom,
            'description' => $request->description,
            'emargement_enabled' => $request->boolean('emargement_enabled'),
            'start_date' => $request->input('start_date') ?: null,
            'end_date' => $request->input('end_date') ?: null,
            'formateur_parcours_id' => $request->input('formateur_parcours_id') ?: null,
        ];

        static $hasIsActiveColumn = null;
        $hasIsActiveColumn ??= Schema::hasColumn('groups', 'is_active');
        if ($hasIsActiveColumn) {
            $groupPayload['is_active'] = $request->boolean('is_active');
        }

        if ($temporaryPasswordInput !== '') {
            $groupPayload['temporary_password'] = $temporaryPasswordInput;
        }

        $group->update($groupPayload);

        if ($canManageCoFormateurs) {
            $coTrainerIds = collect($request->input('co_formateurs', []))
                ->map(fn ($value) => (int) $value)
                ->filter()
                ->unique()
                ->values();
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

    public function destroy($id)
    {
        $group = Group::query()
            ->accessibleByTrainer((int) auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        abort_unless($group->isOwnedBy(auth()->user()), 403);

        if (! empty($group->groupe_image) && \Illuminate\Support\Facades\Storage::disk('public')->exists($group->groupe_image)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($group->groupe_image);
        }

        $group->students()->detach();
        $group->modules()->detach();
        $group->delete();

        return redirect()
            ->route('formateur.groupes.index')
            ->with('success', 'Groupe supprimé avec succès.');
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
            ->where('email', 'like', mb_strtolower($term).'%')
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

    private function groupValidationMessages(): array
    {
        return [
            'nom.required' => 'Veuillez renseigner le nom du groupe.',
            'nom.unique' => 'Ce nom de groupe existe déjà. Merci de choisir un autre nom.',
            'password.required' => 'Veuillez renseigner un code d\'accès provisoire.',
            'modules.required' => 'Veuillez sélectionner au moins un module.',
            'modules.min' => 'Veuillez sélectionner au moins un module.',
            'modules.*.exists' => 'Un des modules sélectionnés est invalide.',
            'stagiaires.*.email.distinct' => 'Chaque stagiaire doit avoir une adresse e-mail différente.',
            'end_date.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de démarrage.',
        ];
    }

    private function appliquerFiltreModuleAssignable($query, int $formateurId): void
    {
        $query
            ->where('status', 1)
            ->where(function ($visibilite) use ($formateurId): void {
                $visibilite
                    ->where(function ($catalogue): void {
                        $catalogue
                            ->where('is_trainer_authored', false)
                            ->where('publication_state', Module::PUBLICATION_PUBLISHED);
                    })
                    ->orWhere(function ($creationFormateur) use ($formateurId): void {
                        $creationFormateur
                            ->where('is_trainer_authored', true)
                            ->where('formateur_id', $formateurId);
                    });
            });
    }

    private function groupValidationAttributes(): array
    {
        return [
            'nom' => 'nom du groupe',
            'description' => 'description du groupe',
            'start_date' => 'date de démarrage',
            'end_date' => 'date de fin',
            'is_active' => 'statut du groupe',
            'emargement_enabled' => 'activation de l\'émargement',
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

    private function sanitizeCoTrainerIds($rawIds, int $currentTrainerId)
    {
        $candidateIds = collect(is_array($rawIds) ? $rawIds : [$rawIds])
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

    private function resolveCoTrainerPayloadFromIds($rawIds)
    {
        $ids = collect(is_array($rawIds) ? $rawIds : [$rawIds])
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
