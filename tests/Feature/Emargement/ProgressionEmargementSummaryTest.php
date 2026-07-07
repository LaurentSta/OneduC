<?php

use App\Domains\Outils\Emargement\Actions\CreerSeance;
use App\Domains\Outils\Emargement\Actions\OuvrirSeance;
use App\Domains\Outils\Emargement\Actions\SignerPresence;
use App\Domains\Outils\Emargement\Support\SignatureImage;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

const PROGRESSION_TINY_PNG_BASE64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';

function createProgressionEmargementUser(string $role): User
{
    return User::query()->create([
        'prenom' => ucfirst($role),
        'name' => ucfirst($role).' ProgressionEmargement',
        'username' => $role.'_progression_emargement_'.uniqid(),
        'email' => $role.'.progression.emargement.'.uniqid().'@example.test',
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => true,
        'password_changed_at' => now(),
        'total_site_time' => 0,
    ]);
}

function createProgressionEmargementGroup(User $formateur, array $students = []): Group
{
    $group = Group::query()->create([
        'name' => 'Groupe progression emargement '.uniqid(),
        'description' => 'Groupe de test',
        'instructor_id' => $formateur->id,
    ]);

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

it('shows the emargement summary on the stagiaire progression page without touching presence summary', function () {
    Storage::fake('local');

    $formateur = createProgressionEmargementUser('formateur');
    $stagiaire = createProgressionEmargementUser('stagiaire');
    $group = createProgressionEmargementGroup($formateur, [$stagiaire]);

    $seance1 = (new CreerSeance)->execute($group, ['date' => now()->subDay()->toDateString(), 'creneau' => 'matin'], $formateur);
    $seance1 = (new OuvrirSeance)->execute($seance1);
    $presence1 = $seance1->presences()->where('user_id', $stagiaire->id)->firstOrFail();
    (new SignerPresence(new SignatureImage))->execute($presence1, $stagiaire, 'data:image/png;base64,'.PROGRESSION_TINY_PNG_BASE64);

    // Second seance left unsigned (en_attente)
    (new CreerSeance)->execute($group, ['date' => now()->toDateString(), 'creneau' => 'apres_midi'], $formateur);

    $response = $this->actingAs($formateur)->get(route('formateur.progressions.stagiaire', [
        'user' => $stagiaire->id,
        'group_id' => $group->id,
    ]));

    $response->assertOk();
    $response->assertSeeText('Assiduité et présence');
    $response->assertSeeText('Émargements signés');

    $emargementSummary = $response->viewData('emargementSummary');
    expect($emargementSummary['signed'])->toBe(1);
    expect($emargementSummary['total'])->toBe(2);

    // presenceSummary (online activity) must be unaffected by emargement data
    $presenceSummary = $response->viewData('presenceSummary');
    expect($presenceSummary['active_days_count'])->toBe(0);
});

it('does not show the emargement block when the stagiaire has no seances', function () {
    $formateur = createProgressionEmargementUser('formateur');
    $stagiaire = createProgressionEmargementUser('stagiaire');
    $group = createProgressionEmargementGroup($formateur, [$stagiaire]);

    $response = $this->actingAs($formateur)->get(route('formateur.progressions.stagiaire', [
        'user' => $stagiaire->id,
        'group_id' => $group->id,
    ]));

    $response->assertOk();
    $response->assertDontSeeText('Émargements signés');
});
