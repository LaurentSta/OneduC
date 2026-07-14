<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GroupeController extends Controller
{
    public function AllGroupe(): View
    {
        $groupes = Group::query()
            ->with('instructor:id,prenom,name,email,status')
            ->withCount('students')
            ->orderBy('name')
            ->get();

        return view('admin.backend.groupes.groupes', compact('groupes'));
    }

    public function AddGroupe(): View
    {
        [$formateurs, $stagiaires] = $this->utilisateursDisponibles();

        return view('admin.backend.groupes.add_groupe', compact('formateurs', 'stagiaires'));
    }

    public function StoreGroupe(Request $request): RedirectResponse
    {
        $donnees = $request->validate($this->reglesValidation());

        DB::transaction(function () use ($donnees): void {
            $groupe = Group::query()->create([
                'name' => $donnees['name'],
                'description' => $donnees['description'] ?? null,
                'instructor_id' => $donnees['formateur_id'],
            ]);

            $this->synchroniserStagiaires($groupe, $donnees['stagiaires'] ?? []);
        });

        return redirect()
            ->route('admin.groupes')
            ->with('success', 'Le groupe a été créé avec succès.');
    }

    public function EditGroupe(int $id): View
    {
        $groupe = Group::query()
            ->with('students:id,prenom,name,email,status')
            ->findOrFail($id);
        [$formateurs, $stagiaires] = $this->utilisateursDisponibles();

        return view('admin.backend.groupes.edit_groupe', compact('groupe', 'formateurs', 'stagiaires'));
    }

    public function UpdateGroupe(Request $request, int $id): RedirectResponse
    {
        $groupe = Group::query()->findOrFail($id);
        $donnees = $request->validate($this->reglesValidation($groupe));

        DB::transaction(function () use ($donnees, $groupe): void {
            $groupe->update([
                'name' => $donnees['name'],
                'description' => $donnees['description'] ?? null,
                'instructor_id' => $donnees['formateur_id'],
            ]);

            $this->synchroniserStagiaires($groupe, $donnees['stagiaires'] ?? []);
        });

        return redirect()
            ->route('admin.groupes')
            ->with('success', 'Le groupe a été mis à jour avec succès.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $groupe = Group::query()->findOrFail($id);
        $groupe->delete();

        return redirect()
            ->route('admin.groupes')
            ->with('success', 'Le groupe a été supprimé.');
    }

    /**
     * @return array{0: \Illuminate\Database\Eloquent\Collection<int, User>, 1: \Illuminate\Database\Eloquent\Collection<int, User>}
     */
    private function utilisateursDisponibles(): array
    {
        $formateurs = User::query()
            ->where('role', 'formateur')
            ->orderBy('name')
            ->orderBy('prenom')
            ->get(['id', 'prenom', 'name', 'email', 'status']);

        $stagiaires = User::query()
            ->where('role', 'stagiaire')
            ->orderBy('name')
            ->orderBy('prenom')
            ->get(['id', 'prenom', 'name', 'email', 'status']);

        return [$formateurs, $stagiaires];
    }

    /**
     * @return array<string, mixed>
     */
    private function reglesValidation(?Group $groupe = null): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('groups', 'name')->ignore($groupe?->id),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'formateur_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')
                    ->where(fn ($query) => $query
                        ->where('role', 'formateur')
                        ->whereNull('deleted_at')),
            ],
            'stagiaires' => ['nullable', 'array'],
            'stagiaires.*' => [
                'integer',
                'distinct',
                Rule::exists('users', 'id')
                    ->where(fn ($query) => $query
                        ->where('role', 'stagiaire')
                        ->whereNull('deleted_at')),
            ],
        ];
    }

    /**
     * @param  array<int|string, mixed>  $stagiaireIds
     */
    private function synchroniserStagiaires(Group $groupe, array $stagiaireIds): void
    {
        $affectations = collect($stagiaireIds)
            ->mapWithKeys(fn ($stagiaireId): array => [
                (int) $stagiaireId => ['role_in_group' => 'stagiaire'],
            ])
            ->all();

        $groupe->students()->sync($affectations);
    }
}
