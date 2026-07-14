<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use App\Services\CodeGeneratorService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UtilisateurController extends Controller
{
    private const ROLES_GERES = ['formateur', 'stagiaire'];

    private const PAGINATIONS_AUTORISEES = [20, 50, 100];

    public function index(Request $request)
    {
        $role = in_array($request->string('role')->toString(), self::ROLES_GERES, true)
            ? $request->string('role')->toString()
            : null;
        $statut = in_array($request->string('statut')->toString(), ['actif', 'inactif'], true)
            ? $request->string('statut')->toString()
            : null;
        $rattachement = in_array($request->string('rattachement')->toString(), ['avec_groupe', 'sans_groupe'], true)
            ? $request->string('rattachement')->toString()
            : null;
        $tri = in_array($request->string('tri')->toString(), ['recent', 'nom', 'ancien'], true)
            ? $request->string('tri')->toString()
            : 'recent';
        $recherche = trim($request->string('recherche')->toString());
        $parPage = (int) $request->integer('par_page', 20);

        if (! in_array($parPage, self::PAGINATIONS_AUTORISEES, true)) {
            $parPage = 20;
        }

        $requete = User::query()
            ->whereIn('role', self::ROLES_GERES)
            ->with([
                'formateur:id,prenom,name',
                'groupesStagiaire:id,name',
            ])
            ->withCount([
                'stagiaires',
                'groupesEncadres',
                'groupesFormateur',
                'groupesStagiaire',
            ]);

        if ($role) {
            $requete->where('role', $role);
        }

        if ($statut) {
            $requete->where('status', $statut === 'actif');
        }

        if ($recherche !== '') {
            $motif = '%'.addcslashes($recherche, '%_\\').'%';

            $requete->where(function (Builder $rechercheQuery) use ($motif): void {
                $rechercheQuery
                    ->where('prenom', 'like', $motif)
                    ->orWhere('name', 'like', $motif)
                    ->orWhere('username', 'like', $motif)
                    ->orWhere('email', 'like', $motif)
                    ->orWhere('societe', 'like', $motif);
            });
        }

        if ($rattachement) {
            $avecGroupe = $rattachement === 'avec_groupe';

            $requete->where(function (Builder $rattachementQuery) use ($avecGroupe): void {
                $rattachementQuery
                    ->where(function (Builder $stagiaireQuery) use ($avecGroupe): void {
                        $stagiaireQuery->where('role', 'stagiaire');

                        $avecGroupe
                            ? $stagiaireQuery->whereHas('groupesStagiaire')
                            : $stagiaireQuery->whereDoesntHave('groupesStagiaire');
                    })
                    ->orWhere(function (Builder $formateurQuery) use ($avecGroupe): void {
                        $formateurQuery->where('role', 'formateur');

                        if ($avecGroupe) {
                            $formateurQuery->where(function (Builder $groupeQuery): void {
                                $groupeQuery
                                    ->whereHas('groupesEncadres')
                                    ->orWhereHas('groupesFormateur');
                            });

                            return;
                        }

                        $formateurQuery
                            ->whereDoesntHave('groupesEncadres')
                            ->whereDoesntHave('groupesFormateur');
                    });
            });
        }

        match ($tri) {
            'nom' => $requete->orderBy('name')->orderBy('prenom'),
            'ancien' => $requete->oldest(),
            default => $requete->latest(),
        };

        $utilisateurs = $requete
            ->paginate($parPage)
            ->withQueryString();

        $baseUtilisateurs = User::query()->whereIn('role', self::ROLES_GERES);
        $statistiques = [
            'total' => (clone $baseUtilisateurs)->count(),
            'actifs' => (clone $baseUtilisateurs)->where('status', true)->count(),
            'inactifs' => (clone $baseUtilisateurs)->where('status', false)->count(),
            'formateurs' => (clone $baseUtilisateurs)->where('role', 'formateur')->count(),
            'stagiaires' => (clone $baseUtilisateurs)->where('role', 'stagiaire')->count(),
            'sans_groupe' => User::query()
                ->where('role', 'stagiaire')
                ->whereDoesntHave('groupesStagiaire')
                ->count(),
        ];

        $filtres = compact('role', 'statut', 'rattachement', 'tri', 'recherche', 'parPage');

        return view('admin.backend.utilisateurs.index', compact(
            'utilisateurs',
            'statistiques',
            'filtres'
        ));
    }

    public function create(Request $request)
    {
        $roleSelectionne = in_array($request->string('role')->toString(), self::ROLES_GERES, true)
            ? $request->string('role')->toString()
            : 'formateur';

        return view('admin.backend.utilisateurs.creer', [
            'roleSelectionne' => $roleSelectionne,
            'formateurs' => $this->formateursDisponibles(),
            'groupes' => $this->groupesDisponibles(),
        ]);
    }

    public function store(Request $request)
    {
        $this->normaliserChamps($request);
        $donnees = $request->validate($this->reglesCreation());

        $utilisateur = DB::transaction(function () use ($request, $donnees): User {
            $role = $donnees['role'];
            $estFormateur = $role === 'formateur';
            $groupesSelectionnes = collect($donnees['group_ids'] ?? [])->map(fn ($id): int => (int) $id);
            $formateurId = $estFormateur
                ? null
                : $this->resoudreFormateurId($donnees['formateur_id'] ?? null, $groupesSelectionnes->all());

            $utilisateur = User::query()->create([
                'prenom' => $donnees['prenom'],
                'name' => $donnees['name'],
                'username' => $donnees['username'] ?? null,
                'email' => $donnees['email'],
                'password' => Hash::make($donnees['password']),
                'phone' => $donnees['phone'] ?? null,
                'address' => $donnees['address'] ?? null,
                'societe' => $estFormateur ? ($donnees['societe'] ?? null) : null,
                'role' => $role,
                'status' => $request->boolean('status'),
                'formateur_id' => $formateurId,
                'code_acces' => $estFormateur
                    ? null
                    : ($donnees['code_acces'] ?? CodeGeneratorService::generateUniqueAccessCode()),
                'adhesion_status' => $estFormateur ? $donnees['adhesion_status'] : 'pending',
                'adhesion_valid_until' => $estFormateur
                    ? $this->dateAdhesion($donnees['adhesion_status'], $donnees['adhesion_valid_until'] ?? null)
                    : null,
                'adhesion_verified_at' => $estFormateur && $donnees['adhesion_status'] === 'active' ? now() : null,
                'adhesion_verified_by' => $estFormateur && $donnees['adhesion_status'] === 'active' ? auth()->id() : null,
            ]);

            if (! $estFormateur) {
                $this->synchroniserGroupesStagiaire($utilisateur, $groupesSelectionnes->all());
            }

            return $utilisateur;
        });

        return redirect()
            ->route('admin.utilisateurs.edit', $utilisateur)
            ->with('success', 'Le compte a été créé avec succès.');
    }

    public function edit(User $utilisateur)
    {
        $this->verifierRoleGere($utilisateur);
        $utilisateur->load(['groupesStagiaire:id,name', 'formateur:id,prenom,name']);

        return view('admin.backend.utilisateurs.modifier', [
            'utilisateur' => $utilisateur,
            'formateurs' => $this->formateursDisponibles(),
            'groupes' => $this->groupesDisponibles(),
            'groupesSelectionnes' => $utilisateur->groupesStagiaire->pluck('id')->map(fn ($id): int => (int) $id)->all(),
        ]);
    }

    public function update(Request $request, User $utilisateur)
    {
        $this->verifierRoleGere($utilisateur);
        $this->normaliserChamps($request);
        $donnees = $request->validate($this->reglesModification($utilisateur));

        DB::transaction(function () use ($request, $utilisateur, $donnees): void {
            $estFormateur = $utilisateur->role === 'formateur';
            $groupesSelectionnes = collect($donnees['group_ids'] ?? [])->map(fn ($id): int => (int) $id);

            $utilisateur->fill([
                'prenom' => $donnees['prenom'],
                'name' => $donnees['name'],
                'username' => $donnees['username'] ?? null,
                'email' => $donnees['email'],
                'phone' => $donnees['phone'] ?? null,
                'address' => $donnees['address'] ?? null,
                'status' => $request->boolean('status'),
            ]);

            if ($request->filled('password')) {
                $utilisateur->password = Hash::make($donnees['password']);
                $utilisateur->password_changed_at = null;
            }

            if ($estFormateur) {
                $utilisateur->societe = $donnees['societe'] ?? null;
                $utilisateur->adhesion_status = $donnees['adhesion_status'];
                $utilisateur->adhesion_valid_until = $this->dateAdhesion(
                    $donnees['adhesion_status'],
                    $donnees['adhesion_valid_until'] ?? null
                );
                $utilisateur->adhesion_verified_at = $donnees['adhesion_status'] === 'active' ? now() : null;
                $utilisateur->adhesion_verified_by = $donnees['adhesion_status'] === 'active' ? auth()->id() : null;
            } else {
                $utilisateur->formateur_id = $this->resoudreFormateurId(
                    $donnees['formateur_id'] ?? null,
                    $groupesSelectionnes->all()
                );
                $utilisateur->code_acces = $donnees['code_acces'] ?? CodeGeneratorService::generateUniqueAccessCode();
            }

            $utilisateur->save();

            if (! $estFormateur) {
                $this->synchroniserGroupesStagiaire($utilisateur, $groupesSelectionnes->all());
            }
        });

        return redirect()
            ->route('admin.utilisateurs.edit', $utilisateur)
            ->with('success', 'Le compte a été mis à jour.');
    }

    public function mettreAJourStatut(Request $request, User $utilisateur)
    {
        $this->verifierRoleGere($utilisateur);
        $donnees = $request->validate([
            'status' => ['required', 'boolean'],
        ]);

        $utilisateur->update(['status' => (bool) $donnees['status']]);
        $message = $utilisateur->status
            ? 'Le compte a été activé.'
            : 'Le compte a été désactivé.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => $utilisateur->status,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function reglesCreation(): array
    {
        return array_merge($this->reglesCommunes(), [
            'role' => ['required', Rule::in(self::ROLES_GERES)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
            'code_acces' => ['nullable', 'alpha_num', 'size:6', Rule::unique('users', 'code_acces')],
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function reglesModification(User $utilisateur): array
    {
        return array_merge($this->reglesCommunes(), [
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($utilisateur->id)],
            'password' => ['nullable', 'string', 'min:12', 'confirmed'],
            'code_acces' => [
                'nullable',
                'alpha_num',
                'size:6',
                Rule::unique('users', 'code_acces')->ignore($utilisateur->id),
            ],
            'adhesion_status' => [
                Rule::requiredIf($utilisateur->role === 'formateur'),
                'nullable',
                Rule::in(['pending', 'active', 'expired']),
            ],
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function reglesCommunes(): array
    {
        return [
            'prenom' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'societe' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'boolean'],
            'formateur_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('role', 'formateur')
                    ->whereNull('deleted_at')),
            ],
            'group_ids' => ['nullable', 'array'],
            'group_ids.*' => ['integer', Rule::exists('groups', 'id')],
            'adhesion_status' => ['required_if:role,formateur', 'nullable', Rule::in(['pending', 'active', 'expired'])],
            'adhesion_valid_until' => ['nullable', 'date'],
        ];
    }

    private function normaliserChamps(Request $request): void
    {
        $request->merge([
            'prenom' => trim((string) $request->input('prenom')),
            'name' => trim((string) $request->input('name')),
            'username' => $request->filled('username') ? trim((string) $request->input('username')) : null,
            'email' => strtolower(trim((string) $request->input('email'))),
            'code_acces' => $request->filled('code_acces')
                ? strtoupper(trim((string) $request->input('code_acces')))
                : null,
        ]);
    }

    private function verifierRoleGere(User $utilisateur): void
    {
        abort_unless(in_array($utilisateur->role, self::ROLES_GERES, true), 404);
    }

    /**
     * @param  array<int, int>  $groupesIds
     */
    private function synchroniserGroupesStagiaire(User $stagiaire, array $groupesIds): void
    {
        DB::table('group_user')
            ->where('user_id', $stagiaire->id)
            ->where('role_in_group', 'stagiaire')
            ->delete();

        foreach (array_unique($groupesIds) as $groupeId) {
            DB::table('group_user')->updateOrInsert(
                [
                    'group_id' => $groupeId,
                    'user_id' => $stagiaire->id,
                ],
                [
                    'role_in_group' => 'stagiaire',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * @param  array<int, int>  $groupesIds
     */
    private function resoudreFormateurId(mixed $formateurId, array $groupesIds): ?int
    {
        if ($formateurId) {
            return (int) $formateurId;
        }

        if ($groupesIds === []) {
            return null;
        }

        $instructeurId = Group::query()
            ->whereKey($groupesIds[0])
            ->value('instructor_id');

        return $instructeurId ? (int) $instructeurId : null;
    }

    private function dateAdhesion(string $statut, mixed $date): ?string
    {
        if ($statut === 'pending') {
            return null;
        }

        if ($statut === 'active' && ! $date) {
            return now()->addYear()->toDateString();
        }

        return $date ?: null;
    }

    private function formateursDisponibles()
    {
        return User::query()
            ->where('role', 'formateur')
            ->orderBy('name')
            ->orderBy('prenom')
            ->get(['id', 'prenom', 'name', 'status']);
    }

    private function groupesDisponibles()
    {
        return Group::query()
            ->with('instructor:id,prenom,name')
            ->orderBy('name')
            ->get(['id', 'name', 'instructor_id', 'is_active']);
    }
}
