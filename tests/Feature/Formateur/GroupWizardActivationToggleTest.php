<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

function createFormateurForGroupWizardTest(): User
{
    return User::query()->create([
        'prenom' => 'Formateur',
        'name' => 'Wizard',
        'username' => 'formateur_wizard_' . uniqid(),
        'email' => 'formateur.wizard.' . uniqid() . '@example.test',
        'password' => Hash::make('password'),
        'role' => 'formateur',
        'status' => true,
        'password_changed_at' => now(),
    ]);
}

it('shows the activation toggle in the first pane of the group creation wizard', function () {
    $formateur = createFormateurForGroupWizardTest();

    $response = $this->actingAs($formateur)
        ->get(route('formateur.groupes.create'));

    $response->assertOk();
    $response->assertSee('Activer le groupe');
    $response->assertSee('name="is_active"', false);
});
