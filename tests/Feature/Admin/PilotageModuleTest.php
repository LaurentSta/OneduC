<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createAdminForPilotage(): User
{
    return User::query()->create([
        'prenom' => 'Admin',
        'name' => 'Principal',
        'username' => 'adminpilot',
        'email' => 'adminpilot@example.test',
        'password' => Hash::make('password'),
        'role' => 'admin',
        'status' => true,
    ]);
}

it('shows pilotage dashboard to admin', function () {
    $admin = createAdminForPilotage();

    $response = $this->actingAs($admin)->get(route('admin.pilotage.index'));

    $response->assertOk();
    $response->assertSee('Tableau de pilotage');
});

it('creates a pilotage task and notifies responsible user', function () {
    $admin = createAdminForPilotage();

    $responsible = User::query()->create([
        'prenom' => 'Marie',
        'name' => 'Formateur',
        'username' => 'marie.formateur',
        'email' => 'marie.formateur@example.test',
        'password' => Hash::make('password'),
        'role' => 'formateur',
        'status' => true,
    ]);

    $response = $this->actingAs($admin)->post(route('admin.pilotage.tasks.store'), [
        'title' => 'Valider questionnaire final',
        'description' => 'Verifier les questions et options.',
        'status' => 'todo',
        'priority' => 'high',
        'task_type' => 'questionnaire',
        'responsible_id' => $responsible->id,
        'subscribers' => [$responsible->id],
    ]);

    $response->assertRedirect(route('admin.pilotage.index'));

    $task = DB::table('pilot_tasks')->where('title', 'Valider questionnaire final')->first();
    expect($task)->not->toBeNull();
    expect($task->status)->toBe('todo');
    expect($task->responsible_id)->toBe($responsible->id);

    $notificationCount = DB::table('notifications')
        ->where('notifiable_type', User::class)
        ->where('notifiable_id', $responsible->id)
        ->count();
    expect($notificationCount)->toBeGreaterThan(0);
});

it('moves task between kanban columns and logs admin activity', function () {
    $admin = createAdminForPilotage();

    $taskId = DB::table('pilot_tasks')->insertGetId([
        'title' => 'Corriger score SCORM',
        'status' => 'todo',
        'priority' => 'normal',
        'task_type' => 'scorm',
        'position' => 0,
        'created_by' => $admin->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin)->postJson(route('admin.pilotage.tasks.move', $taskId), [
        'status' => 'blocked',
        'position' => 0,
    ]);

    $response->assertOk()->assertJson(['ok' => true, 'status' => 'blocked']);

    $task = DB::table('pilot_tasks')->where('id', $taskId)->first();
    expect($task->status)->toBe('blocked');

    $journalEntry = DB::table('activity_journal_entries')
        ->where('route_name', 'admin.pilotage.tasks.move')
        ->first();
    expect($journalEntry)->not->toBeNull();
});

