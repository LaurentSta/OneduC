<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ObservateurController extends Controller
{
    public function index(): View
    {
        $observateurs = User::query()
            ->where('role', 'observateur')
            ->with(['groupesObserve.instructor'])
            ->withCount('groupesObserve')
            ->orderBy('name')
            ->get();

        return view('admin.backend.observateur.index', compact('observateurs'));
    }

    public function create(): View
    {
        return view('admin.backend.observateur.create', [
            'observateur' => new User(),
            'groups' => $this->availableGroups(),
            'selectedGroupIds' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'prenom' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'status' => ['nullable', 'boolean'],
            'group_ids' => ['nullable', 'array'],
            'group_ids.*' => ['integer', Rule::exists('groups', 'id')],
        ]);

        $observateur = User::create([
            'prenom' => $validated['prenom'],
            'name' => $validated['name'],
            'username' => $validated['username'] ?? null,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'observateur',
            'status' => (bool) ($validated['status'] ?? true),
        ]);

        $this->syncObservedGroups($observateur, $request->input('group_ids', []));

        return redirect()
            ->route('admin.observateurs.index')
            ->with('success', 'Observateur créé avec succès.');
    }

    public function edit(User $observateur): View
    {
        abort_unless($observateur->role === 'observateur', 404);

        return view('admin.backend.observateur.edit', [
            'observateur' => $observateur->load('groupesObserve'),
            'groups' => $this->availableGroups(),
            'selectedGroupIds' => $observateur->groupesObserve->pluck('id')->map(fn ($id) => (int) $id)->all(),
        ]);
    }

    public function update(Request $request, User $observateur): RedirectResponse
    {
        abort_unless($observateur->role === 'observateur', 404);

        $validated = $request->validate([
            'prenom' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($observateur->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'status' => ['nullable', 'boolean'],
            'group_ids' => ['nullable', 'array'],
            'group_ids.*' => ['integer', Rule::exists('groups', 'id')],
        ]);

        $observateur->fill([
            'prenom' => $validated['prenom'],
            'name' => $validated['name'],
            'username' => $validated['username'] ?? null,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'status' => (bool) ($validated['status'] ?? false),
        ]);

        if (! empty($validated['password'])) {
            $observateur->password = Hash::make($validated['password']);
        }

        $observateur->save();

        $this->syncObservedGroups($observateur, $request->input('group_ids', []));

        return redirect()
            ->route('admin.observateurs.index')
            ->with('success', 'Observateur mis à jour avec succès.');
    }

    public function destroy(User $observateur): RedirectResponse
    {
        if ($observateur->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas vous supprimer.');
        }

        abort_unless($observateur->role === 'observateur', 404);

        DB::table('group_user')
            ->where('user_id', $observateur->id)
            ->where('role_in_group', 'observateur')
            ->delete();

        $observateur->delete();

        return back()->with('success', 'Observateur supprimé.');
    }

    /**
     * @return \Illuminate\Support\Collection<int, Group>
     */
    private function availableGroups()
    {
        return Group::query()
            ->with('instructor:id,prenom,name')
            ->withCount([
                'students as stagiaires_count',
                'modules as modules_count',
            ])
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  array<int|string, mixed>  $groupIds
     */
    private function syncObservedGroups(User $observateur, array $groupIds): void
    {
        $ids = collect($groupIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        $syncData = [];

        foreach ($ids as $groupId) {
            $syncData[$groupId] = [
                'role_in_group' => 'observateur',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $observateur->groupesObserve()->sync($syncData);
    }
}
