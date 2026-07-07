<?php

use App\Models\ConsommationIA;
use App\Models\User;

it('lets a trainer see only their own AI token consumption', function () {
    $formateurA = User::factory()->create(['role' => 'formateur']);
    $formateurB = User::factory()->create(['role' => 'formateur']);

    ConsommationIA::create(['formateur_id' => $formateurA->id, 'type' => 'chat', 'model' => 'mistral-large-latest', 'prompt_tokens' => 60, 'completion_tokens' => 40, 'total_tokens' => 100]);
    ConsommationIA::create(['formateur_id' => $formateurA->id, 'type' => 'chat', 'model' => 'mistral-large-latest', 'prompt_tokens' => 30, 'completion_tokens' => 20, 'total_tokens' => 50]);
    ConsommationIA::create(['formateur_id' => $formateurB->id, 'type' => 'chat', 'model' => 'mistral-large-latest', 'prompt_tokens' => 200, 'completion_tokens' => 100, 'total_tokens' => 300]);

    $response = $this->actingAs($formateurA)->get(route('formateur.modules.builder.consommation-ia'));

    $response->assertOk();
    $response->assertSee('150'); // total tokens de formateurA (100 + 50)
    $response->assertSee('90 tokens envoyés'); // prompt_tokens de formateurA (60 + 30)
    $response->assertSee('60 tokens générés'); // completion_tokens de formateurA (40 + 20)
});

it('shows every trainer\'s AI token consumption to admins', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $formateurA = User::factory()->create(['role' => 'formateur', 'prenom' => 'Alice', 'name' => 'Martin']);
    $formateurB = User::factory()->create(['role' => 'formateur', 'prenom' => 'Bob', 'name' => 'Durand']);

    ConsommationIA::create(['formateur_id' => $formateurA->id, 'type' => 'chat', 'model' => 'mistral-large-latest', 'prompt_tokens' => 60, 'completion_tokens' => 40, 'total_tokens' => 100]);
    ConsommationIA::create(['formateur_id' => $formateurA->id, 'type' => 'chat', 'model' => 'mistral-large-latest', 'prompt_tokens' => 30, 'completion_tokens' => 20, 'total_tokens' => 50]);
    ConsommationIA::create(['formateur_id' => $formateurB->id, 'type' => 'chat', 'model' => 'mistral-large-latest', 'prompt_tokens' => 200, 'completion_tokens' => 100, 'total_tokens' => 300]);

    $response = $this->actingAs($admin)->get(route('admin.pilotage.consommation-ia'));

    $response->assertOk();
    $response->assertSee('Alice Martin');
    $response->assertSee('Bob Durand');
    $response->assertSee('150');
    $response->assertSee('300');
});
