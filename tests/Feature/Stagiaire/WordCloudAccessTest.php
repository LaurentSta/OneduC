<?php

use App\Models\FormateurMessage;
use App\Models\FormateurParcours;
use App\Models\FormateurParcoursItem;
use App\Models\Group;
use App\Models\User;
use App\Models\WordCloud;
use App\Models\WordCloudEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

function createWordCloudAccessUser(string $role): User
{
    return User::query()->create([
        'prenom' => ucfirst($role),
        'name' => ucfirst($role).' WordCloudAccess',
        'username' => $role.'_wordcloud_access_'.uniqid(),
        'email' => $role.'.wordcloud.access.'.uniqid().'@example.test',
        'password' => Hash::make('password'),
        'role' => $role,
        'status' => true,
        'password_changed_at' => now(),
    ]);
}

function createWordCloudAccessGroup(User $formateur, User $stagiaire, array $attributes = []): Group
{
    $group = Group::query()->create(array_merge([
        'name' => 'Groupe nuage stagiaire '.uniqid(),
        'description' => 'Groupe de test nuage de mots',
        'is_active' => true,
        'temporary_password' => 'password123',
        'instructor_id' => $formateur->id,
    ], $attributes));

    DB::table('group_user')->insert([
        'group_id' => $group->id,
        'user_id' => $stagiaire->id,
        'role_in_group' => 'stagiaire',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return $group;
}

it('shows the active word cloud join button on the stagiaire tools page', function () {
    $formateur = createWordCloudAccessUser('formateur');
    $stagiaire = createWordCloudAccessUser('stagiaire');
    $group = createWordCloudAccessGroup($formateur, $stagiaire);

    $wordCloud = WordCloud::query()->create([
        'group_id' => $group->id,
        'title' => 'Nuage inclusion numerique',
        'question' => 'Quel mot decrit votre besoin ?',
        'questions' => ['Quel mot decrit votre besoin ?'],
        'access_code' => 'NUAGE1',
        'is_active' => true,
        'opened_at' => now(),
    ]);

    $response = $this->actingAs($stagiaire)->get(route('stagiaire.outils'));

    $response->assertOk();
    $response->assertSeeText('Nuage de mots');
    $response->assertSeeText('Participer maintenant');
    $response->assertSee(route('wordcloud.join.code', ['code' => $wordCloud->access_code]), false);
    $response->assertSeeText('Nuage de mots en cours');

    $answerPage = $this->actingAs($stagiaire)->get(route('wordcloud.join.code', ['code' => $wordCloud->access_code]));

    $answerPage->assertOk();
    $answerPage->assertDontSeeText('Nuage de mots en direct');
    $answerPage->assertDontSee('wordcloud2', false);
});

it('exposes the active word cloud in the stagiaire notification status endpoint', function () {
    $formateur = createWordCloudAccessUser('formateur');
    $stagiaire = createWordCloudAccessUser('stagiaire');
    $group = createWordCloudAccessGroup($formateur, $stagiaire);

    WordCloud::query()->create([
        'group_id' => $group->id,
        'title' => 'Nuage prioritaire',
        'question' => 'Un mot ?',
        'questions' => ['Un mot ?'],
        'access_code' => 'NUAGE2',
        'is_active' => true,
        'opened_at' => now(),
    ]);

    $response = $this->actingAs($stagiaire)->getJson(route('stagiaire.wordcloud.notification-status'));

    $response->assertOk()
        ->assertJsonPath('has_active_wordcloud', true)
        ->assertJsonPath('title', 'Nuage prioritaire')
        ->assertJsonPath('access_code', 'NUAGE2')
        ->assertJsonPath('group_name', $group->name)
        ->assertJsonPath('join_url', route('wordcloud.join.code', ['code' => 'NUAGE2']));
});

it('only shows the active word cloud question to the stagiaire', function () {
    $formateur = createWordCloudAccessUser('formateur');
    $stagiaire = createWordCloudAccessUser('stagiaire');
    $group = createWordCloudAccessGroup($formateur, $stagiaire);

    $wordCloud = WordCloud::query()->create([
        'group_id' => $group->id,
        'title' => 'Nuage progressif',
        'question' => 'Premiere question ?',
        'questions' => ['Premiere question ?', 'Deuxieme question ?'],
        'access_code' => 'PROG01',
        'is_active' => true,
        'opened_at' => now(),
    ]);

    $firstPage = $this->actingAs($stagiaire)->get(route('wordcloud.join.code', ['code' => $wordCloud->access_code]));

    $firstPage->assertOk();
    $firstPage->assertSeeText('Question 1 / 2');
    $firstPage->assertSeeText('Premiere question ?');
    $firstPage->assertDontSeeText('Deuxieme question ?');

    $this->actingAs($formateur)->post(route('formateur.nuages.question', $wordCloud), [
        'question_index' => 1,
    ])->assertRedirect(route('formateur.nuages.live', $wordCloud));

    $secondPage = $this->actingAs($stagiaire)->get(route('wordcloud.join.code', ['code' => $wordCloud->access_code]));

    $secondPage->assertOk();
    $secondPage->assertSeeText('Question 2 / 2');
    $secondPage->assertSeeText('Deuxieme question ?');
    $secondPage->assertDontSeeText('Premiere question ?');

    $this->actingAs($stagiaire)
        ->getJson(route('wordcloud.state', ['code' => $wordCloud->access_code]))
        ->assertOk()
        ->assertJsonPath('current_question_index', 1)
        ->assertJsonPath('question', 'Deuxieme question ?');
});

it('rejects answers sent for a word cloud question that is no longer active', function () {
    $formateur = createWordCloudAccessUser('formateur');
    $stagiaire = createWordCloudAccessUser('stagiaire');
    $group = createWordCloudAccessGroup($formateur, $stagiaire);

    $wordCloud = WordCloud::query()->create([
        'group_id' => $group->id,
        'title' => 'Nuage question active',
        'question' => 'Question un ?',
        'questions' => ['Question un ?', 'Question deux ?'],
        'current_question_index' => 1,
        'access_code' => 'ACTIVE',
        'is_active' => true,
        'opened_at' => now(),
    ]);

    $this->actingAs($stagiaire)->post(route('wordcloud.submit', ['code' => $wordCloud->access_code]), [
        'question_index' => 0,
        'answer' => 'Ancienne',
    ])->assertSessionHasErrors('answer');

    expect($wordCloud->entries()->count())->toBe(0);

    $this->actingAs($stagiaire)->post(route('wordcloud.submit', ['code' => $wordCloud->access_code]), [
        'question_index' => 1,
        'answer' => 'Nouvelle',
    ])->assertSessionHasNoErrors();

    expect($wordCloud->entries()->where('question_index', 1)->count())->toBe(1);
});

it('lets the owning formateur close a word cloud from the live cockpit', function () {
    $formateur = createWordCloudAccessUser('formateur');
    $stagiaire = createWordCloudAccessUser('stagiaire');
    $group = createWordCloudAccessGroup($formateur, $stagiaire);

    $wordCloud = WordCloud::query()->create([
        'group_id' => $group->id,
        'title' => 'Nuage a terminer',
        'question' => 'Dernier mot ?',
        'questions' => ['Dernier mot ?'],
        'access_code' => 'CLOSE1',
        'is_active' => true,
        'opened_at' => now(),
    ]);

    $livePage = $this->actingAs($formateur)->get(route('formateur.nuages.live', $wordCloud));

    $livePage->assertOk();
    $livePage->assertSeeText('Action du moment');
    $livePage->assertSeeText('Terminer le nuage');

    $this->actingAs($formateur)
        ->post(route('formateur.nuages.close', $wordCloud))
        ->assertRedirect(route('formateur.nuages.live', $wordCloud));

    $wordCloud->refresh();

    expect($wordCloud->is_active)->toBeFalse()
        ->and($wordCloud->closed_at)->not->toBeNull();

    $this->actingAs($stagiaire)
        ->getJson(route('wordcloud.state', ['code' => $wordCloud->access_code]))
        ->assertOk()
        ->assertJsonPath('active', false);
});

it('ignores active word clouds from inactive groups in the notification status endpoint', function () {
    $formateur = createWordCloudAccessUser('formateur');
    $stagiaire = createWordCloudAccessUser('stagiaire');
    $group = createWordCloudAccessGroup($formateur, $stagiaire, [
        'is_active' => false,
    ]);

    WordCloud::query()->create([
        'group_id' => $group->id,
        'title' => 'Nuage groupe ferme',
        'question' => 'Un mot ?',
        'questions' => ['Un mot ?'],
        'access_code' => 'NUAGE3',
        'is_active' => true,
        'opened_at' => now(),
    ]);

    $response = $this->actingAs($stagiaire)->getJson(route('stagiaire.wordcloud.notification-status'));

    $response->assertOk()
        ->assertJsonPath('has_active_wordcloud', false)
        ->assertJsonPath('title', null)
        ->assertJsonPath('join_url', null);
});

it('offers a parcours word cloud from the stagiaire tools page when no live word cloud is active', function () {
    $formateur = createWordCloudAccessUser('formateur');
    $stagiaire = createWordCloudAccessUser('stagiaire');

    $parcours = FormateurParcours::query()->create([
        'formateur_id' => $formateur->id,
        'title' => 'Parcours avec nuage',
        'description' => 'Parcours test',
    ]);

    $item = FormateurParcoursItem::query()->create([
        'formateur_parcours_id' => $parcours->id,
        'position' => 1,
        'type' => 'wordcloud',
        'wc_title' => 'Nuage du parcours',
        'wc_questions' => ['Quel mot retenez-vous ?'],
        'wc_duration' => 5,
    ]);

    createWordCloudAccessGroup($formateur, $stagiaire, [
        'formateur_parcours_id' => $parcours->id,
    ]);

    $response = $this->actingAs($stagiaire)->get(route('stagiaire.outils'));

    $response->assertOk();
    $response->assertSeeText('Nuage de mots');
    $response->assertSeeText('Participer maintenant');
    $response->assertSee(route('stagiaire.wordcloud.parcours.show', $item), false);

    $wordCloudPage = $this->actingAs($stagiaire)->get(route('stagiaire.wordcloud.parcours.show', $item));

    $wordCloudPage->assertOk();
    $wordCloudPage->assertDontSeeText('Nuage de mots du groupe');
    $wordCloudPage->assertDontSee('wordcloud2', false);
});

it('creates a stagiaire message and unread notification when a formateur launches a word cloud', function () {
    $formateur = createWordCloudAccessUser('formateur');
    $stagiaire = createWordCloudAccessUser('stagiaire');
    $group = createWordCloudAccessGroup($formateur, $stagiaire);

    $response = $this->actingAs($formateur)->post(route('formateur.nuages.store'), [
        'group_id' => $group->id,
        'title' => 'Nuage message stagiaire',
        'questions' => ['Quel mot retenez-vous ?'],
    ]);

    $wordCloud = WordCloud::query()
        ->where('title', 'Nuage message stagiaire')
        ->firstOrFail();
    $message = FormateurMessage::query()
        ->where('stagiaire_id', $stagiaire->id)
        ->firstOrFail();

    $response->assertRedirect(route('formateur.nuages.live', $wordCloud));
    expect($message->subject)->toBe('Nuage de mots disponible')
        ->and($message->body)->toContain('Nuage message stagiaire')
        ->and($message->body)->toContain($wordCloud->access_code)
        ->and($message->sent_as_notification)->toBeTrue()
        ->and($message->sent_as_email)->toBeFalse()
        ->and($stagiaire->unreadNotifications()->count())->toBe(1);

    $notification = $stagiaire->unreadNotifications()->first();
    expect(data_get($notification->data, 'url'))->toBe(route('stagiaire.messages.index'))
        ->and(data_get($notification->data, 'title'))->toBe('Nuage de mots disponible');

    $messagesPage = $this->actingAs($stagiaire)->get(route('stagiaire.messages.index'));

    $messagesPage->assertOk();
    $messagesPage->assertSeeText('Rejoindre le nuage de mots');
    $messagesPage->assertSee(route('wordcloud.join.code', ['code' => $wordCloud->access_code]), false);

    $toolsPage = $this->actingAs($stagiaire)->get(route('stagiaire.outils'));

    $toolsPage->assertOk();
    expect($toolsPage->getContent())->toContain('data-base-count="1"')
        ->and($toolsPage->getContent())->toMatch('/data-bell-badge\s+class="[^"]*bg-orangeone/');
});

it('denies an unauthenticated visitor from joining a word cloud', function () {
    $formateur = createWordCloudAccessUser('formateur');
    $stagiaire = createWordCloudAccessUser('stagiaire');
    $group = createWordCloudAccessGroup($formateur, $stagiaire);

    $wordCloud = WordCloud::query()->create([
        'group_id' => $group->id,
        'title' => 'Nuage prive anonyme',
        'question' => 'Un mot ?',
        'questions' => ['Un mot ?'],
        'access_code' => 'ANON01',
        'is_active' => true,
        'opened_at' => now(),
    ]);

    $this->get(route('wordcloud.join.code', ['code' => $wordCloud->access_code]))
        ->assertRedirect(route('login'));

    $this->post(route('wordcloud.submit', ['code' => $wordCloud->access_code]), [
        'question_index' => 0,
        'answer' => 'Intrus',
    ])->assertRedirect(route('login'));

    expect($wordCloud->entries()->count())->toBe(0);
});

it('denies a stagiaire outside the group from joining a word cloud', function () {
    $formateur = createWordCloudAccessUser('formateur');
    $stagiaire = createWordCloudAccessUser('stagiaire');
    $outsider = createWordCloudAccessUser('stagiaire');
    $group = createWordCloudAccessGroup($formateur, $stagiaire);

    $wordCloud = WordCloud::query()->create([
        'group_id' => $group->id,
        'title' => 'Nuage prive intrus',
        'question' => 'Un mot ?',
        'questions' => ['Un mot ?'],
        'access_code' => 'OUT001',
        'is_active' => true,
        'opened_at' => now(),
    ]);

    $this->actingAs($outsider)
        ->get(route('wordcloud.join.code', ['code' => $wordCloud->access_code]))
        ->assertNotFound();

    $this->actingAs($outsider)
        ->post(route('wordcloud.submit', ['code' => $wordCloud->access_code]), [
            'question_index' => 0,
            'answer' => 'Intrus',
        ])->assertNotFound();

    expect($wordCloud->entries()->count())->toBe(0);
});

it('lets the owning formateur delete a word cloud without deleting the group or stagiaire', function () {
    $formateur = createWordCloudAccessUser('formateur');
    $stagiaire = createWordCloudAccessUser('stagiaire');
    $group = createWordCloudAccessGroup($formateur, $stagiaire);

    $wordCloud = WordCloud::query()->create([
        'group_id' => $group->id,
        'title' => 'Nuage a supprimer',
        'question' => 'Un mot ?',
        'questions' => ['Un mot ?'],
        'access_code' => 'DELWC1',
        'is_active' => true,
        'opened_at' => now(),
    ]);

    $entry = WordCloudEntry::query()->create([
        'word_cloud_id' => $wordCloud->id,
        'question_index' => 0,
        'user_id' => $stagiaire->id,
        'answer' => 'Inclusion',
        'normalized_answer' => 'inclusion',
    ]);

    $response = $this->actingAs($formateur)->delete(route('formateur.nuages.destroy', $wordCloud));

    $response->assertRedirect(route('formateur.nuages.index'));
    $this->assertDatabaseMissing('word_clouds', ['id' => $wordCloud->id]);
    $this->assertDatabaseMissing('word_cloud_entries', ['id' => $entry->id]);
    $this->assertDatabaseHas('groups', ['id' => $group->id]);
    $this->assertDatabaseHas('users', ['id' => $stagiaire->id, 'role' => 'stagiaire']);
});
