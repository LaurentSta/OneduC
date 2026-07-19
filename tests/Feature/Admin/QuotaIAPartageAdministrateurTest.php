<?php

use App\Domains\ModulesFormateur\Support\LimiteurBudgetTokensIA;
use App\Domains\ModulesFormateur\Support\LimiteurGenerationIA;
use App\Models\ConsommationIA;
use App\Models\User;
use App\Services\ConsommationIADashboardService;
use Illuminate\Support\Str;

it('partage les budgets et limites IA entre administrateurs sans inclure les formateurs', function () {
    config([
        'services.mistral.admin_monthly_token_limit' => 1000,
        'services.mistral.admin_daily_generation_limit' => 2,
    ]);

    $adminA = User::factory()->create(['role' => 'admin']);
    $adminB = User::factory()->create(['role' => 'admin']);
    $formateur = User::factory()->create(['role' => 'formateur']);

    ConsommationIA::query()->create([
        'formateur_id' => $adminA->id,
        'type' => 'structure',
        'model' => 'mistral-large-latest',
        'prompt_tokens' => 60,
        'completion_tokens' => 40,
        'total_tokens' => 100,
    ]);
    ConsommationIA::query()->create([
        'formateur_id' => $adminB->id,
        'type' => 'structure',
        'model' => 'mistral-large-latest',
        'prompt_tokens' => 120,
        'completion_tokens' => 80,
        'total_tokens' => 200,
    ]);
    ConsommationIA::query()->create([
        'formateur_id' => $formateur->id,
        'type' => 'structure',
        'model' => 'mistral-large-latest',
        'prompt_tokens' => 500,
        'completion_tokens' => 400,
        'total_tokens' => 900,
    ]);

    $budget = app(LimiteurBudgetTokensIA::class);
    $resume = app(ConsommationIADashboardService::class)->resumePourFormateur($adminA->id);

    expect($budget->tokensConsommesCeMois($adminA->id))->toBe(300)
        ->and($budget->tokensConsommesCeMois($adminB->id))->toBe(300)
        ->and($resume['totaux']['appels'])->toBe(2)
        ->and($resume['totaux']['total_tokens'])->toBe(300)
        ->and($resume['budget']['consomme_ce_mois'])->toBe(300)
        ->and($resume['historique']->total())->toBe(2);

    $limiteurQuotidien = app(LimiteurGenerationIA::class);
    $typeGeneration = 'quota-admin-partage-'.Str::uuid();
    $limiteurQuotidien->enregistrerTentative($adminA->id, $typeGeneration);

    expect($limiteurQuotidien->tentativesRestantes($adminB->id, $typeGeneration))->toBe(1);

    $limiteurQuotidien->enregistrerTentative($adminB->id, $typeGeneration);

    expect($limiteurQuotidien->tropDeTentatives($adminA->id, $typeGeneration))->toBeTrue();
});
