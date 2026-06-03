<?php

use App\Mail\ModuleQuestionnaireSubmitted;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

function createSidebarProgressFormateur(): User
{
    return User::query()->create([
        'prenom' => 'Lina',
        'name' => 'Formatrice',
        'username' => 'lina.formatrice',
        'email' => 'lina.formatrice@example.test',
        'password' => Hash::make('password'),
        'role' => 'formateur',
        'status' => true,
    ]);
}

function recordSidebarProgress(User $formateur, string $lesson, string $activity): void
{
    recordModuleTwoProgress(
        $formateur,
        'mettre-en-place-un-parcours-coherent',
        $lesson,
        $activity
    );
}

function recordModuleTwoProgress(
    User $formateur,
    string $chapter,
    string $lesson,
    string $activity,
    string $activityType = 'guided_group_creation'
): void
{
    $now = now();

    DB::table('trainer_path_activity_attempts')->insert([
        'user_id' => $formateur->id,
        'module_key' => 'organiser-ses-parcours',
        'chapter_key' => $chapter,
        'lesson_key' => $lesson,
        'activity_key' => $activity,
        'activity_type' => $activityType,
        'total_items' => 1,
        'correct_items' => 1,
        'is_success' => true,
        'submitted_answer' => json_encode([]),
        'expected_answer' => json_encode([]),
        'wrong_items' => json_encode([]),
        'submitted_at' => $now,
        'created_at' => $now,
        'updated_at' => $now,
    ]);
}

function sidebarLessonPartRoute(string $lesson, string $part): string
{
    return route('formateur.parcours.lessons.part', [
        'module' => 'organiser-ses-parcours',
        'chapter' => 'mettre-en-place-un-parcours-coherent',
        'lesson' => $lesson,
        'part' => $part,
    ]);
}

function moduleTwoQuestionnairePayload(): array
{
    return [
        'submission_uuid' => 'd1bf4fe5-d025-4ed1-bb58-32e1a72be5ab',
        'closed_items' => collect(range(1, 17))
            ->map(fn (int $itemNumber): array => [
                'item_number' => $itemNumber,
                'value' => $itemNumber === 17 ? 'NA' : (($itemNumber - 1) % 5) + 1,
            ])
            ->all(),
        'open_questions' => [
            [
                'item_number' => 18,
                'text' => 'Les simulateurs et les consignes.',
            ],
            [
                'item_number' => 19,
                'text' => 'Préciser le vocabulaire utilisé.',
            ],
        ],
    ];
}

it('keeps the final chapter in progress until cases and bilan are completed', function () {
    $formateur = createSidebarProgressFormateur();

    recordSidebarProgress($formateur, 'associer-le-bon-parcours-au-bon-contexte', 'ajustement-groupe-finalise');

    $marcValidation = $this
        ->actingAs($formateur)
        ->get(sidebarLessonPartRoute('traiter-les-cas-particuliers', 'validation'));

    $marcValidation->assertOk();
    $marcValidation->assertSee('1/3 étapes validées');

    $casesFinalisation = $this
        ->actingAs($formateur)
        ->get(sidebarLessonPartRoute('traiter-les-cas-particuliers', 'modifier-contenu-finalisation'));

    $casesFinalisation->assertOk();
    $casesFinalisation->assertSee('2/3 étapes validées');

    $bilanFinal = $this
        ->actingAs($formateur)
        ->get(sidebarLessonPartRoute('bilan-module-2', 'resultat-final'));

    $bilanFinal->assertOk();
    $bilanFinal->assertSee('3/3 étapes validées');
    $bilanFinal->assertSee('Chapitre validé');
    $bilanFinal->assertSee('Bilan et ouverture validés');
});

it('adds a student inside the group adjustment simulator without writing to the database', function () {
    $formateur = createSidebarProgressFormateur();
    $finalisationUrl = sidebarLessonPartRoute('associer-le-bon-parcours-au-bon-contexte', 'ajustement-groupe-finalisation');

    $form = $this
        ->actingAs($formateur)
        ->get(sidebarLessonPartRoute('associer-le-bon-parcours-au-bon-contexte', 'ajouter-stagiaire'));

    $form->assertOk();
    $form->assertSee('action="' . $finalisationUrl . '" method="GET"', false);
    $form->assertDontSee(route('formateur.stagiaires.store'), false);

    $finalisation = $this
        ->actingAs($formateur)
        ->get($finalisationUrl . '?' . http_build_query([
            'prenom' => 'Sophie',
            'name' => 'Durand',
            'email' => 'sophie.durand@example.test',
            'group_id' => 1,
        ]));

    $finalisation->assertOk();
    $finalisation->assertSee('Sophie');
    $finalisation->assertSee('Durand');
    $finalisation->assertSee('sophie.durand@example.test');
    $finalisation->assertSee('Hygiène alimentaire 2026 - promo 1');
    $this->assertDatabaseMissing('users', ['email' => 'sophie.durand@example.test']);
});

it('shows a green overview border once every module step is completed', function () {
    $formateur = createSidebarProgressFormateur();

    recordModuleTwoProgress($formateur, 'preparer-les-contenus', 'retrouver-les-espaces-de-preparation', 'classer-les-elements', 'sorting');
    recordModuleTwoProgress($formateur, 'preparer-les-contenus', 'distinguer-contenu-ressource-et-structure', 'preparer-informations-utiles', 'essential_sorting');
    recordModuleTwoProgress($formateur, 'structurer-la-progression', 'creation-groupe-de-formation', 'creation-groupe-finalisee');
    recordModuleTwoProgress($formateur, 'structurer-la-progression', 'creation-parcours', 'creation-parcours-finalisee');
    recordSidebarProgress($formateur, 'associer-le-bon-parcours-au-bon-contexte', 'ajustement-groupe-finalise');
    recordSidebarProgress($formateur, 'traiter-les-cas-particuliers', 'cas-particuliers-finalises');

    $beforeBilan = $this
        ->actingAs($formateur)
        ->get(sidebarLessonPartRoute('bilan-module-2', 'bilan'));

    $beforeBilan->assertOk();
    $beforeBilan->assertDontSee('border-t-0 border-vertone bg-white', false);

    $completedModule = $this
        ->actingAs($formateur)
        ->get(sidebarLessonPartRoute('bilan-module-2', 'resultat-final'));

    $completedModule->assertOk();
    $completedModule->assertSee('border-vertone bg-teal-50/70', false);
    $completedModule->assertSee('border-t-0 border-vertone bg-white', false);
    $completedModule->assertSee('Validé');
});

it('offers the module two usability questionnaire after the final overview', function () {
    $formateur = createSidebarProgressFormateur();
    $questionnaireUrl = sidebarLessonPartRoute('bilan-module-2', 'questionnaire');

    $overview = $this
        ->actingAs($formateur)
        ->get(sidebarLessonPartRoute('bilan-module-2', 'resultat-final'));

    $overview->assertOk();
    $overview->assertSee('Répondre au questionnaire');
    $overview->assertSee($questionnaireUrl, false);

    $questionnaire = $this
        ->actingAs($formateur)
        ->get($questionnaireUrl);

    $questionnaire->assertOk();
    $questionnaire->assertSee('Votre avis sur le module 2');
    $questionnaire->assertSee('Mettre en place un environnement de formation');
    $questionnaire->assertSee('Effort cognitif perçu');
    $questionnaire->assertSee('Suivre le module m’a fatigué.');
    $questionnaire->assertSee('Le retour après chaque activité m’a aidé à savoir si j’avais réussi.');
    $questionnaire->assertSee('Qu’est-ce qui vous a le plus aidé dans ce module ?');
    $questionnaire->assertSee('Qu’est-ce qui mériterait d’être clarifié ou amélioré ?');
    $questionnaire->assertSee('Envoyer mes réponses');
    $questionnaire->assertSee('aria-label="NA — Non applicable"', false);
    $questionnaire->assertSee('const questionnaireSubmitUrl =', false);
    $questionnaire->assertSee('submission_uuid: submissionUuid', false);
    $questionnaire->assertSee('fetch(questionnaireSubmitUrl', false);

    foreach (range(1, 17) as $itemNumber) {
        $questionnaire->assertSee('name="item_' . $itemNumber . '"', false);
    }
});

it('emails the completed module two questionnaire to Oneduc', function () {
    Mail::fake();

    $formateur = createSidebarProgressFormateur();
    $token = 'csrf-module-two-questionnaire-complete';
    $payload = moduleTwoQuestionnairePayload();
    $payload['_token'] = $token;

    $response = $this
        ->withSession(['_token' => $token])
        ->withHeader('X-CSRF-TOKEN', $token)
        ->actingAs($formateur)
        ->postJson(route('formateur.parcours.questionnaire.submit', ['module' => 'organiser-ses-parcours']), $payload);

    $response
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Vos réponses ont bien été enregistrées et envoyées à l’équipe Onéduc.',
        ]);

    $this->assertDatabaseHas('trainer_module_questionnaire_submissions', [
        'submission_uuid' => 'd1bf4fe5-d025-4ed1-bb58-32e1a72be5ab',
        'user_id' => $formateur->id,
        'module_number' => 2,
        'module_key' => 'organiser-ses-parcours',
        'questionnaire_key' => 'utilisabilite-percue',
        'questionnaire_version' => 1,
    ]);

    Mail::assertSent(ModuleQuestionnaireSubmitted::class, function (ModuleQuestionnaireSubmitted $mail): bool {
        return $mail->hasTo('contact@oneduc.fr')
            && $mail->questionnaire['trainer']['email'] === 'lina.formatrice@example.test'
            && $mail->questionnaire['closed_items'][6]['reversed'] === true
            && $mail->questionnaire['closed_items'][16]['answer_label'] === 'Non applicable'
            && $mail->questionnaire['open_questions'][0]['text'] === 'Les simulateurs et les consignes.'
            && str_contains($mail->render(), 'item inversé à recoder lors de l’analyse');
    });

    expect(DB::table('trainer_module_questionnaire_submissions')->value('emailed_at'))->not->toBeNull();

    $retry = $this
        ->withSession(['_token' => $token])
        ->withHeader('X-CSRF-TOKEN', $token)
        ->actingAs($formateur)
        ->postJson(route('formateur.parcours.questionnaire.submit', ['module' => 'organiser-ses-parcours']), $payload);

    $retry->assertOk();

    expect(DB::table('trainer_module_questionnaire_submissions')->count())->toBe(1);
    Mail::assertSent(ModuleQuestionnaireSubmitted::class, 1);
});

it('rejects an incomplete module two questionnaire without sending an email', function () {
    Mail::fake();

    $formateur = createSidebarProgressFormateur();
    $token = 'csrf-module-two-questionnaire-incomplete';
    $payload = moduleTwoQuestionnairePayload();
    array_pop($payload['closed_items']);
    $payload['_token'] = $token;

    $response = $this
        ->withSession(['_token' => $token])
        ->withHeader('X-CSRF-TOKEN', $token)
        ->actingAs($formateur)
        ->postJson(route('formateur.parcours.questionnaire.submit', ['module' => 'organiser-ses-parcours']), $payload);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors('closed_items');

    Mail::assertNothingSent();
});
