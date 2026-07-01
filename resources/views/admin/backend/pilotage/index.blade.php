@extends('admin.admin_dashboard')

@section('admin')
@php
  $statusBadge = [
    'todo' => 'bg-slate-100 text-slate-700',
    'in_progress' => 'bg-blue-100 text-blue-700',
    'in_validation' => 'bg-amber-100 text-amber-700',
    'done' => 'bg-emerald-100 text-emerald-700',
    'blocked' => 'bg-red-100 text-red-700',
  ];
  $priorityBadge = [
    'low' => 'bg-gray-100 text-gray-700',
    'normal' => 'bg-indigo-100 text-indigo-700',
    'high' => 'bg-red-100 text-red-700',
  ];
  $dueFilters = [
    '' => 'Toutes les echeances',
    'overdue' => 'En retard',
    'today' => "Aujourd'hui",
    'this_week' => 'Cette semaine',
    'no_due' => 'Sans echeance',
  ];
@endphp

<div class="mx-auto w-full max-w-[1500px] space-y-8">
  <div class="flex flex-wrap items-start justify-between gap-4">
    <div>
      <h1 class="text-3xl font-semibold text-bleuone">Tableau de pilotage</h1>
      <p class="mt-1 text-sm text-gray-600">Suivi des taches, decisions et blocages.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      <a href="{{ route('admin.pilotage.qualite-parcours-formateur') }}" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Qualité parcours</a>
      <a href="{{ route('admin.pilotage.journal') }}" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Journal</a>
      <a href="{{ route('admin.pilotage.notifications.index') }}" class="rounded-md bg-[#004461] px-3 py-2 text-sm font-medium text-white hover:bg-[#00364d]">Centre notifications</a>
    </div>
  </div>

  @if (session('success'))
    <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
  @endif
  @if (session('error'))
    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
  @endif
  @if ($errors->any())
    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
      <p class="font-semibold">Verifications a corriger :</p>
      <ul class="mt-1 list-disc pl-5">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
    @foreach($statuses as $statusKey => $statusLabel)
      <article class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <p class="text-sm font-medium text-gray-600">{{ $statusLabel }}</p>
        <p class="mt-1 text-2xl font-bold text-gray-900">{{ $statusCounts[$statusKey] ?? 0 }}</p>
      </article>
    @endforeach
  </section>

  <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
    <h2 class="text-lg font-semibold text-gray-900">Filtres</h2>
    <form method="GET" action="{{ route('admin.pilotage.index') }}" class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-7">
      <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Recherche tache..."
             class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">

      <select name="status" class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">
        <option value="">Tous statuts</option>
        @foreach($statuses as $key => $label)
          <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
        @endforeach
      </select>

      <select name="task_type" class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">
        <option value="">Tous types</option>
        @foreach($types as $key => $label)
          <option value="{{ $key }}" @selected(($filters['task_type'] ?? '') === $key)>{{ $label }}</option>
        @endforeach
      </select>

      <select name="module_id" class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">
        <option value="">Tous modules</option>
        @foreach($modules as $module)
          @php $moduleLabel = $module->module_title ?: $module->module_name ?: ('Module #' . $module->id); @endphp
          <option value="{{ $module->id }}" @selected((string)($filters['module_id'] ?? '') === (string)$module->id)>{{ $moduleLabel }}</option>
        @endforeach
      </select>

      <select name="responsible_id" class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">
        <option value="">Tous responsables</option>
        @foreach($users as $user)
          @php
            $userLabel = $user->username ?: trim(($user->prenom ?? '') . ' ' . ($user->name ?? ''));
            $userLabel = $userLabel ?: ('Utilisateur #' . $user->id);
          @endphp
          <option value="{{ $user->id }}" @selected((string)($filters['responsible_id'] ?? '') === (string)$user->id)>{{ $userLabel }}</option>
        @endforeach
      </select>

      <select name="priority" class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">
        <option value="">Toutes priorites</option>
        @foreach($priorities as $key => $label)
          <option value="{{ $key }}" @selected(($filters['priority'] ?? '') === $key)>{{ $label }}</option>
        @endforeach
      </select>

      <div class="flex gap-2">
        <select name="due_filter" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">
          @foreach($dueFilters as $key => $label)
            <option value="{{ $key }}" @selected(($filters['due_filter'] ?? '') === $key)>{{ $label }}</option>
          @endforeach
        </select>
      </div>

      <div class="md:col-span-2 xl:col-span-7 flex flex-wrap items-center gap-2">
        <button type="submit" class="rounded-md bg-[#004461] px-4 py-2 text-sm font-medium text-white hover:bg-[#00364d]">Filtrer</button>
        <a href="{{ route('admin.pilotage.index') }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Reinitialiser</a>
      </div>
    </form>
  </section>

  <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
    <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
      <h2 class="text-lg font-semibold text-gray-900">Nouveau projet</h2>
      <form method="POST" action="{{ route('admin.pilotage.projects.store') }}" class="mt-4 space-y-3">
        @csrf
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Nom du projet</label>
          <input type="text" name="name" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">
        </div>
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Module</label>
            <select name="module_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">
              <option value="">Aucun module</option>
              @foreach($modules as $module)
                @php $moduleLabel = $module->module_title ?: $module->module_name ?: ('Module #' . $module->id); @endphp
                <option value="{{ $module->id }}">{{ $moduleLabel }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Echeance</label>
            <input type="date" name="due_date" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">
          </div>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Description</label>
          <textarea name="description" rows="3" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none"></textarea>
        </div>
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Abonnes (interface)</label>
            <select name="subscribers[]" multiple class="h-32 w-full rounded-md border border-gray-300 px-2 py-2 text-sm focus:border-orangeone focus:outline-none">
              @foreach($users as $user)
                @php $userLabel = $user->username ?: trim(($user->prenom ?? '') . ' ' . ($user->name ?? '')) ?: ('Utilisateur #' . $user->id); @endphp
                <option value="{{ $user->id }}">{{ $userLabel }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Abonnes (mail)</label>
            <select name="mail_subscribers[]" multiple class="h-32 w-full rounded-md border border-gray-300 px-2 py-2 text-sm focus:border-orangeone focus:outline-none">
              @foreach($users as $user)
                @php $userLabel = $user->username ?: trim(($user->prenom ?? '') . ' ' . ($user->name ?? '')) ?: ('Utilisateur #' . $user->id); @endphp
                <option value="{{ $user->id }}">{{ $userLabel }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <button type="submit" class="rounded-md bg-orangeone px-4 py-2 text-sm font-medium text-white hover:bg-orange-600">Creer projet</button>
      </form>
    </article>

    <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
      <h2 class="text-lg font-semibold text-gray-900">Nouvelle tache</h2>
      <form method="POST" action="{{ route('admin.pilotage.tasks.store') }}" enctype="multipart/form-data" class="mt-4 space-y-3">
        @csrf
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Titre</label>
          <input type="text" name="title" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Description</label>
          <textarea name="description" rows="3" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none"></textarea>
        </div>
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Projet</label>
            <select name="project_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">
              <option value="">Aucun projet</option>
              @foreach($projects as $project)
                <option value="{{ $project->id }}">{{ $project->name }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Module</label>
            <select name="module_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">
              <option value="">Aucun module</option>
              @foreach($modules as $module)
                @php $moduleLabel = $module->module_title ?: $module->module_name ?: ('Module #' . $module->id); @endphp
                <option value="{{ $module->id }}">{{ $moduleLabel }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Type</label>
            <select name="task_type" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">
              @foreach($types as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Statut</label>
            <select name="status" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">
              @foreach($statuses as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Priorite</label>
            <select name="priority" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">
              @foreach($priorities as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Responsable</label>
            <select name="responsible_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">
              <option value="">Aucun responsable</option>
              @foreach($users as $user)
                @php $userLabel = $user->username ?: trim(($user->prenom ?? '') . ' ' . ($user->name ?? '')) ?: ('Utilisateur #' . $user->id); @endphp
                <option value="{{ $user->id }}">{{ $userLabel }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Echeance</label>
            <input type="date" name="due_date" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">
          </div>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Lien interne OneDuc (URL)</label>
          <input type="text" name="internal_url" placeholder="/admin/modules/edit/12" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Piece jointe (facultatif)</label>
          <input type="file" name="attachment" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">
        </div>
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Abonnes (interface)</label>
            <select name="subscribers[]" multiple class="h-32 w-full rounded-md border border-gray-300 px-2 py-2 text-sm focus:border-orangeone focus:outline-none">
              @foreach($users as $user)
                @php $userLabel = $user->username ?: trim(($user->prenom ?? '') . ' ' . ($user->name ?? '')) ?: ('Utilisateur #' . $user->id); @endphp
                <option value="{{ $user->id }}">{{ $userLabel }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Abonnes (mail)</label>
            <select name="mail_subscribers[]" multiple class="h-32 w-full rounded-md border border-gray-300 px-2 py-2 text-sm focus:border-orangeone focus:outline-none">
              @foreach($users as $user)
                @php $userLabel = $user->username ?: trim(($user->prenom ?? '') . ' ' . ($user->name ?? '')) ?: ('Utilisateur #' . $user->id); @endphp
                <option value="{{ $user->id }}">{{ $userLabel }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <button type="submit" class="rounded-md bg-orangeone px-4 py-2 text-sm font-medium text-white hover:bg-orange-600">Creer tache</button>
      </form>
    </article>
  </section>

  <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
    <div class="mb-4 flex items-center justify-between">
      <h2 class="text-lg font-semibold text-gray-900">Projets</h2>
      <span class="text-xs text-gray-500">{{ $projects->count() }} projet(s)</span>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50 text-gray-700">
          <tr>
            <th class="px-3 py-2 text-left font-semibold">Projet</th>
            <th class="px-3 py-2 text-left font-semibold">Module</th>
            <th class="px-3 py-2 text-left font-semibold">Echeance</th>
            <th class="px-3 py-2 text-left font-semibold">Avancement</th>
            <th class="px-3 py-2 text-left font-semibold">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse($projects as $project)
            @php
              $moduleLabel = $project->module?->module_title ?: $project->module?->module_name ?: '-';
              $totalTasks = (int) $project->tasks_count;
              $doneTasks = (int) $project->tasks_done_count;
              $progress = $totalTasks > 0 ? (int) round(($doneTasks / $totalTasks) * 100) : 0;
              $subscribers = $projectSubscribers[$project->id] ?? [];
              $mailSubscribers = $projectMailSubscribers[$project->id] ?? [];
            @endphp
            <tr>
              <td class="px-3 py-3 align-top">
                <p class="font-semibold text-gray-900">{{ $project->name }}</p>
                <p class="mt-1 text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($project->description, 90) }}</p>
              </td>
              <td class="px-3 py-3 align-top">{{ $moduleLabel }}</td>
              <td class="px-3 py-3 align-top">{{ optional($project->due_date)->format('d/m/Y') ?: '-' }}</td>
              <td class="px-3 py-3 align-top">
                <div class="h-2 w-40 rounded-full bg-gray-200">
                  <div class="h-2 rounded-full bg-[#004461]" style="width: {{ $progress }}%;"></div>
                </div>
                <p class="mt-1 text-xs text-gray-600">{{ $doneTasks }}/{{ $totalTasks }} ({{ $progress }}%)</p>
              </td>
              <td class="px-3 py-3 align-top">
                <details>
                  <summary class="cursor-pointer text-xs font-medium text-blue-700">Modifier</summary>
                  <form method="POST" action="{{ route('admin.pilotage.projects.update', $project) }}" class="mt-2 space-y-2 rounded-md border border-gray-200 bg-gray-50 p-3">
                    @csrf
                    @method('PUT')
                    <input type="text" name="name" value="{{ $project->name }}" class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs">
                    <textarea name="description" rows="2" class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs">{{ $project->description }}</textarea>
                    <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
                      <select name="module_id" class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs">
                        <option value="">Aucun module</option>
                        @foreach($modules as $module)
                          @php $moduleLabel = $module->module_title ?: $module->module_name ?: ('Module #' . $module->id); @endphp
                          <option value="{{ $module->id }}" @selected((int)$project->module_id === (int)$module->id)>{{ $moduleLabel }}</option>
                        @endforeach
                      </select>
                      <input type="date" name="due_date" value="{{ optional($project->due_date)->format('Y-m-d') }}" class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs">
                    </div>
                    <select name="subscribers[]" multiple class="h-24 w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs">
                      @foreach($users as $user)
                        @php $userLabel = $user->username ?: trim(($user->prenom ?? '') . ' ' . ($user->name ?? '')) ?: ('Utilisateur #' . $user->id); @endphp
                        <option value="{{ $user->id }}" @selected(in_array((int) $user->id, $subscribers, true))>{{ $userLabel }}</option>
                      @endforeach
                    </select>
                    <select name="mail_subscribers[]" multiple class="h-24 w-full rounded-md border border-gray-300 px-2 py-1.5 text-xs">
                      @foreach($users as $user)
                        @php $userLabel = $user->username ?: trim(($user->prenom ?? '') . ' ' . ($user->name ?? '')) ?: ('Utilisateur #' . $user->id); @endphp
                        <option value="{{ $user->id }}" @selected(in_array((int) $user->id, $mailSubscribers, true))>{{ $userLabel }}</option>
                      @endforeach
                    </select>
                    <div class="flex items-center gap-2">
                      <button type="submit" class="rounded-md bg-[#004461] px-2.5 py-1.5 text-xs font-medium text-white">Sauver</button>
                    </div>
                  </form>
                  <div class="mt-2">
                    <button type="button" x-data x-on:click="$dispatch('open-modal', 'delete-project-{{ $project->id }}')" class="rounded-md bg-red-600 px-2.5 py-1.5 text-xs font-medium text-white">Supprimer</button>
                  </div>
                  <x-confirm-modal
                    name="delete-project-{{ $project->id }}"
                    title="Supprimer ce projet ?"
                    :action="route('admin.pilotage.projects.destroy', $project)"
                    method="DELETE"
                    confirm-label="Supprimer"
                  />
                </details>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-3 py-4 text-center text-sm text-gray-500">Aucun projet.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>

  <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
      <h2 class="text-lg font-semibold text-gray-900">Liste des taches</h2>
      <span class="text-xs text-gray-500">{{ $tasks->total() }} tache(s)</span>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50 text-gray-700">
          <tr>
            <th class="px-3 py-2 text-left font-semibold">Tache</th>
            <th class="px-3 py-2 text-left font-semibold">Type</th>
            <th class="px-3 py-2 text-left font-semibold">Statut</th>
            <th class="px-3 py-2 text-left font-semibold">Priorite</th>
            <th class="px-3 py-2 text-left font-semibold">Responsable</th>
            <th class="px-3 py-2 text-left font-semibold">Echeance</th>
            <th class="px-3 py-2 text-left font-semibold">Lien</th>
            <th class="px-3 py-2 text-left font-semibold">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse($tasks as $task)
            @php
              $responsibleLabel = $task->responsible?->username ?: trim(($task->responsible?->prenom ?? '') . ' ' . ($task->responsible?->name ?? ''));
              $responsibleLabel = $responsibleLabel ?: '-';
              $isOverdue = $task->due_date && $task->due_date->isPast() && $task->status !== 'done';
            @endphp
            <tr class="align-top">
              <td class="px-3 py-3">
                <a href="{{ route('admin.pilotage.tasks.edit', $task) }}" class="font-semibold text-blue-700 hover:underline">{{ $task->title }}</a>
                <p class="mt-1 text-xs text-gray-500">{{ $task->project?->name ?: 'Sans projet' }}</p>
                @if($task->comments_count > 0)
                  <p class="mt-1 text-[11px] text-gray-400">{{ $task->comments_count }} commentaire(s)</p>
                @endif
              </td>
              <td class="px-3 py-3">{{ $types[$task->task_type] ?? $task->task_type }}</td>
              <td class="px-3 py-3">
                <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $statusBadge[$task->status] ?? 'bg-gray-100 text-gray-700' }}">
                  {{ $statuses[$task->status] ?? $task->status }}
                </span>
              </td>
              <td class="px-3 py-3">
                <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $priorityBadge[$task->priority] ?? 'bg-gray-100 text-gray-700' }}">
                  {{ $priorities[$task->priority] ?? $task->priority }}
                </span>
              </td>
              <td class="px-3 py-3">{{ $responsibleLabel }}</td>
              <td class="px-3 py-3">
                <span class="{{ $isOverdue ? 'text-red-700 font-semibold' : 'text-gray-700' }}">
                  {{ optional($task->due_date)->format('d/m/Y') ?: '-' }}
                </span>
              </td>
              <td class="px-3 py-3">
                @if($task->internal_url)
                  <a href="{{ $task->internal_url }}" target="_blank" class="text-blue-700 hover:underline">Ouvrir</a>
                @else
                  <span class="text-gray-400">-</span>
                @endif
              </td>
              <td class="px-3 py-3">
                <div class="flex flex-wrap items-center gap-2">
                  <a href="{{ route('admin.pilotage.tasks.edit', $task) }}" class="rounded-md border border-gray-300 bg-white px-2 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50">Editer</a>
                  <button type="button" x-data x-on:click="$dispatch('open-modal', 'delete-task-{{ $task->id }}')" class="rounded-md bg-red-600 px-2 py-1 text-xs font-medium text-white hover:bg-red-700">Supprimer</button>
                  <x-confirm-modal
                    name="delete-task-{{ $task->id }}"
                    title="Supprimer cette tache ?"
                    :action="route('admin.pilotage.tasks.destroy', $task)"
                    method="DELETE"
                    confirm-label="Supprimer"
                  />
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="px-3 py-4 text-center text-sm text-gray-500">Aucune tache ne correspond aux filtres.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="mt-4">{{ $tasks->links() }}</div>
  </section>

  <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
    <div class="mb-4 flex items-center justify-between">
      <h2 class="text-lg font-semibold text-gray-900">Kanban</h2>
      <p class="text-xs text-gray-500">Glisser/deposer une carte pour changer son statut.</p>
    </div>

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-5">
      @foreach($statuses as $statusKey => $statusLabel)
        <div class="kanban-column rounded-xl border border-gray-200 bg-gray-50 p-3"
             data-status="{{ $statusKey }}">
          <div class="mb-3 flex items-center justify-between">
            <p class="text-sm font-semibold text-gray-800">{{ $statusLabel }}</p>
            <span class="rounded-full bg-white px-2 py-0.5 text-xs text-gray-500">{{ ($kanbanTasks[$statusKey] ?? collect())->count() }}</span>
          </div>
          <div class="kanban-cards min-h-20 space-y-2">
            @foreach(($kanbanTasks[$statusKey] ?? collect()) as $task)
              @php
                $responsibleLabel = $task->responsible?->username ?: trim(($task->responsible?->prenom ?? '') . ' ' . ($task->responsible?->name ?? ''));
              @endphp
              <article class="pilot-card cursor-grab rounded-lg border border-gray-200 bg-white p-3 shadow-sm"
                       draggable="true"
                       data-task-id="{{ $task->id }}">
                <a href="{{ route('admin.pilotage.tasks.edit', $task) }}" class="line-clamp-2 text-sm font-semibold text-gray-900 hover:text-blue-700">{{ $task->title }}</a>
                <p class="mt-1 text-xs text-gray-500">{{ $task->project?->name ?: 'Sans projet' }}</p>
                <div class="mt-2 flex flex-wrap items-center gap-2">
                  <span class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $priorityBadge[$task->priority] ?? 'bg-gray-100 text-gray-700' }}">
                    {{ $priorities[$task->priority] ?? $task->priority }}
                  </span>
                  @if($responsibleLabel)
                    <span class="text-[11px] text-gray-500">{{ $responsibleLabel }}</span>
                  @endif
                </div>
              </article>
            @endforeach
          </div>
        </div>
      @endforeach
    </div>
  </section>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = @json(csrf_token());
    let draggedCard = null;

    document.querySelectorAll('.pilot-card').forEach((card) => {
      card.addEventListener('dragstart', () => {
        draggedCard = card;
        card.classList.add('opacity-60');
      });

      card.addEventListener('dragend', () => {
        card.classList.remove('opacity-60');
      });
    });

    document.querySelectorAll('.kanban-column').forEach((column) => {
      const cardsContainer = column.querySelector('.kanban-cards');

      column.addEventListener('dragover', (event) => {
        event.preventDefault();
      });

      column.addEventListener('drop', async (event) => {
        event.preventDefault();
        if (!draggedCard || !cardsContainer) {
          return;
        }

        const taskId = draggedCard.dataset.taskId;
        const newStatus = column.dataset.status;
        if (!taskId || !newStatus) {
          return;
        }

        cardsContainer.appendChild(draggedCard);
        const newPosition = Array.from(cardsContainer.querySelectorAll('.pilot-card')).indexOf(draggedCard);
        const moveUrl = @json(route('admin.pilotage.tasks.move', ['task' => '__TASK__'])).replace('__TASK__', taskId);

        try {
          const response = await fetch(moveUrl, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
              'Accept': 'application/json',
            },
            body: JSON.stringify({
              status: newStatus,
              position: newPosition,
            }),
          });

          if (!response.ok) {
            throw new Error('Echec mise a jour kanban');
          }
        } catch (error) {
          window.location.reload();
        }
      });
    });
  });
</script>
@endpush
