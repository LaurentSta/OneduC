<?php

use App\Domains\ModulesFormateur\Actions\GenererLeconIA;
use App\Domains\ModulesFormateur\Actions\GenererStructureFormationIA;
use App\Domains\ModulesFormateur\Support\LimiteurBudgetTokensIA;
use App\Models\ConsommationIA;
use App\Models\Module;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;

it('sums only the current month\'s tokens for a given trainer', function () {
    config(['services.mistral.monthly_token_limit' => 1000]);

    $formateur = User::factory()->create(['role' => 'formateur']);
    $autre = User::factory()->create(['role' => 'formateur']);

    ConsommationIA::create(['formateur_id' => $formateur->id, 'type' => 'chat', 'model' => 'mistral-large-latest', 'total_tokens' => 400]);
    ConsommationIA::create(['formateur_id' => $formateur->id, 'type' => 'chat', 'model' => 'mistral-large-latest', 'total_tokens' => 300]);
    ConsommationIA::create(['formateur_id' => $autre->id, 'type' => 'chat', 'model' => 'mistral-large-latest', 'total_tokens' => 900]);

    $ancienne = ConsommationIA::create(['formateur_id' => $formateur->id, 'type' => 'chat', 'model' => 'mistral-large-latest', 'total_tokens' => 5000]);
    $ancienne->created_at = Carbon::now()->subMonthNoOverflow();
    $ancienne->save();

    $limiteur = app(LimiteurBudgetTokensIA::class);

    expect($limiteur->tokensConsommesCeMois($formateur->id))->toBe(700);
    expect($limiteur->budgetDepasse($formateur->id))->toBeFalse();

    ConsommationIA::create(['formateur_id' => $formateur->id, 'type' => 'chat', 'model' => 'mistral-large-latest', 'total_tokens' => 300]);

    expect($limiteur->budgetDepasse($formateur->id))->toBeTrue();
});

it('blocks lesson generation once the monthly token budget is reached', function () {
    config(['services.mistral.monthly_token_limit' => 1000]);

    $formateur = User::factory()->create(['role' => 'formateur']);
    ConsommationIA::create(['formateur_id' => $formateur->id, 'type' => 'chat', 'model' => 'mistral-large-latest', 'total_tokens' => 1000]);

    $action = app(GenererLeconIA::class);

    expect(fn () => $action->execute(UploadedFile::fake()->create('doc.txt', 1), new Module, $formateur->id))
        ->toThrow(RuntimeException::class, 'plafond mensuel');
});

it('blocks training structure generation once the monthly token budget is reached', function () {
    config(['services.mistral.monthly_token_limit' => 1000]);

    $formateur = User::factory()->create(['role' => 'formateur']);
    ConsommationIA::create(['formateur_id' => $formateur->id, 'type' => 'chat', 'model' => 'mistral-large-latest', 'total_tokens' => 1000]);

    $action = app(GenererStructureFormationIA::class);

    expect(fn () => $action->execute('Un thème quelconque', null, $formateur->id))
        ->toThrow(RuntimeException::class, 'plafond mensuel');
});
