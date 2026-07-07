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

const EXPORT_TINY_PNG_BASE64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';

function createEmargementExportUser(string $role): User
{
    return User::query()->create([
        'prenom' => ucfirst($role),
        'name' => ucfirst($role).' EmargementExport',
        'username' => $role.'_emargement_export_'.uniqid(),
        'email' => $role.'.emargement.export.'.uniqid().'@example.test',
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => true,
        'password_changed_at' => now(),
    ]);
}

function createEmargementExportGroup(User $formateur, array $students = []): Group
{
    $group = Group::query()->create([
        'name' => 'Groupe emargement export '.uniqid(),
        'description' => 'Groupe de test',
        'temporary_password' => 'temp-password',
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

it('exports a pdf with a signed presence embedded', function () {
    Storage::fake('local');

    $formateur = createEmargementExportUser('formateur');
    $stagiaire = createEmargementExportUser('stagiaire');
    $group = createEmargementExportGroup($formateur, [$stagiaire]);
    $seance = (new CreerSeance)->execute($group, ['date' => now()->toDateString(), 'creneau' => 'matin'], $formateur);
    $seance = (new OuvrirSeance)->execute($seance);
    $presence = $seance->presences()->where('user_id', $stagiaire->id)->firstOrFail();
    (new SignerPresence(new SignatureImage))->execute($presence, $stagiaire, 'data:image/png;base64,'.EXPORT_TINY_PNG_BASE64);

    $response = $this->actingAs($formateur)->get(
        route('formateur.groupes.emargement.export-pdf', [$group->id, $seance->id])
    );

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
});

it('forbids a formateur outside the group from exporting the pdf', function () {
    $owner = createEmargementExportUser('formateur');
    $intrus = createEmargementExportUser('formateur');
    $group = createEmargementExportGroup($owner);
    $seance = (new CreerSeance)->execute($group, ['date' => now()->toDateString(), 'creneau' => 'matin'], $owner);

    $response = $this->actingAs($intrus)->get(
        route('formateur.groupes.emargement.export-pdf', [$group->id, $seance->id])
    );

    $response->assertNotFound();
});
