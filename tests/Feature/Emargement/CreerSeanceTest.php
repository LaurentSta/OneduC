<?php

use App\Domains\Outils\Emargement\Actions\CreerSeance;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

function createEmargementUser(string $role): User
{
    return User::query()->create([
        'prenom' => ucfirst($role),
        'name' => ucfirst($role).' Emargement',
        'username' => $role.'_emargement_'.uniqid(),
        'email' => $role.'.emargement.'.uniqid().'@example.test',
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => true,
        'password_changed_at' => now(),
    ]);
}

function createEmargementGroup(User $formateur, array $students = [], array $attributes = []): Group
{
    $group = Group::query()->create(array_merge([
        'name' => 'Groupe emargement '.uniqid(),
        'description' => 'Groupe de test pour l\'émargement',
        'temporary_password' => 'temp-password',
        'instructor_id' => $formateur->id,
    ], $attributes));

    foreach ($students as $student) {
        DB::table('group_user')->insert([
            'group_id' => $group->id,
            'user_id' => $student->id,
            'role_in_group' => 'stagiaire',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    return $group;
}

it('creates a seance and snapshots the current roster of the group', function () {
    $formateur = createEmargementUser('formateur');
    $stagiaire1 = createEmargementUser('stagiaire');
    $stagiaire2 = createEmargementUser('stagiaire');
    $group = createEmargementGroup($formateur, [$stagiaire1, $stagiaire2]);

    $seance = (new CreerSeance)->execute($group, [
        'date' => now()->addDay()->toDateString(),
        'creneau' => 'matin',
    ], $formateur);

    expect($seance->group_id)->toBe($group->id);
    expect($seance->statut)->toBe('planifiee');
    expect($seance->presences)->toHaveCount(2);
    expect($seance->presences->pluck('statut')->unique()->all())->toBe(['en_attente']);
    expect($seance->presences->pluck('user_id')->sort()->values()->all())
        ->toBe(collect([$stagiaire1->id, $stagiaire2->id])->sort()->values()->all());
});

it('does not allow two seances on the same group, date and creneau', function () {
    $formateur = createEmargementUser('formateur');
    $group = createEmargementGroup($formateur);
    $date = now()->addDay()->toDateString();

    (new CreerSeance)->execute($group, ['date' => $date, 'creneau' => 'matin'], $formateur);

    expect(fn () => (new CreerSeance)->execute($group, ['date' => $date, 'creneau' => 'matin'], $formateur))
        ->toThrow(\Illuminate\Database\QueryException::class);
});
