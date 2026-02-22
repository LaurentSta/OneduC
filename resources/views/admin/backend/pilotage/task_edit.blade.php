@extends('admin.admin_dashboard')

@section('admin')
@php
  $responsibleLabel = $task->responsible?->username ?: trim(($task->responsible?->prenom ?? '') . ' ' . ($task->responsible?->name ?? ''));
@endphp

<div class="mx-auto w-full max-w-5xl space-y-6">
  <div class="flex flex-wrap items-start justify-between gap-3">
    <div>
      <a href="{{ route('admin.pilotage.index') }}" class="text-sm text-blue-700 hover:underline">Retour au pilotage</a>
      <h1 class="mt-1 text-2xl font-semibold text-bleuone">Edition tache #{{ $task->id }}</h1>
      <p class="mt-1 text-sm text-gray-600">{{ $task->title }}</p>
    </div>
    <div class="text-sm text-gray-600">
      <p>Responsable: <span class="font-semibold text-gray-800">{{ $responsibleLabel ?: 'Non assigne' }}</span></p>
      <p>Projet: <span class="font-semibold text-gray-800">{{ $task->project?->name ?: 'Sans projet' }}</span></p>
    </div>
  </div>

  @if (session('success'))
    <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
  @endif
  @if ($errors->any())
    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
      <ul class="list-disc pl-5">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
    <form method="POST" action="{{ route('admin.pilotage.tasks.update', $task) }}" enctype="multipart/form-data" class="space-y-4">
      @csrf
      @method('PUT')

      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Titre</label>
        <input type="text" name="title" value="{{ old('title', $task->title) }}" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">
      </div>

      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Description</label>
        <textarea name="description" rows="4" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">{{ old('description', $task->description) }}</textarea>
      </div>

      <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Projet</label>
          <select name="project_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">
            <option value="">Aucun projet</option>
            @foreach($projects as $project)
              <option value="{{ $project->id }}" @selected((string)old('project_id', $task->project_id) === (string)$project->id)>{{ $project->name }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Module</label>
          <select name="module_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">
            <option value="">Aucun module</option>
            @foreach($modules as $module)
              @php $moduleLabel = $module->module_title ?: $module->module_name ?: ('Module #' . $module->id); @endphp
              <option value="{{ $module->id }}" @selected((string)old('module_id', $task->module_id) === (string)$module->id)>{{ $moduleLabel }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Type</label>
          <select name="task_type" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">
            @foreach($types as $key => $label)
              <option value="{{ $key }}" @selected((string)old('task_type', $task->task_type) === (string)$key)>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Statut</label>
          <select name="status" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">
            @foreach($statuses as $key => $label)
              <option value="{{ $key }}" @selected((string)old('status', $task->status) === (string)$key)>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Priorite</label>
          <select name="priority" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">
            @foreach($priorities as $key => $label)
              <option value="{{ $key }}" @selected((string)old('priority', $task->priority) === (string)$key)>{{ $label }}</option>
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
              <option value="{{ $user->id }}" @selected((string)old('responsible_id', $task->responsible_id) === (string)$user->id)>{{ $userLabel }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Echeance</label>
          <input type="date" name="due_date" value="{{ old('due_date', optional($task->due_date)->format('Y-m-d')) }}" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">
        </div>
      </div>

      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Lien interne</label>
        <input type="text" name="internal_url" value="{{ old('internal_url', $task->internal_url) }}" placeholder="/admin/modules/edit/12" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">
      </div>

      <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Abonnes (interface)</label>
          <select name="subscribers[]" multiple class="h-32 w-full rounded-md border border-gray-300 px-2 py-2 text-sm focus:border-orangeone focus:outline-none">
            @foreach($users as $user)
              @php $userLabel = $user->username ?: trim(($user->prenom ?? '') . ' ' . ($user->name ?? '')) ?: ('Utilisateur #' . $user->id); @endphp
              <option value="{{ $user->id }}" @selected(in_array((int)$user->id, old('subscribers', $selectedSubscribers), true))>{{ $userLabel }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-gray-700">Abonnes (mail)</label>
          <select name="mail_subscribers[]" multiple class="h-32 w-full rounded-md border border-gray-300 px-2 py-2 text-sm focus:border-orangeone focus:outline-none">
            @foreach($users as $user)
              @php $userLabel = $user->username ?: trim(($user->prenom ?? '') . ' ' . ($user->name ?? '')) ?: ('Utilisateur #' . $user->id); @endphp
              <option value="{{ $user->id }}" @selected(in_array((int)$user->id, old('mail_subscribers', $selectedMailSubscribers), true))>{{ $userLabel }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div>
        <label class="mb-1 block text-sm font-medium text-gray-700">Piece jointe</label>
        <input type="file" name="attachment" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">
        @if($task->attachment_path)
          <div class="mt-2 rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm">
            <a href="{{ asset('storage/' . $task->attachment_path) }}" target="_blank" class="text-blue-700 hover:underline">Voir la piece jointe actuelle</a>
            <label class="mt-2 flex items-center gap-2 text-xs text-gray-600">
              <input type="checkbox" name="remove_attachment" value="1">
              Supprimer la piece jointe actuelle
            </label>
          </div>
        @endif
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <button type="submit" class="rounded-md bg-[#004461] px-4 py-2 text-sm font-medium text-white hover:bg-[#00364d]">Enregistrer</button>
        <a href="{{ route('admin.pilotage.index') }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Retour liste</a>
      </div>
    </form>
  </section>

  <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
    <h2 class="text-lg font-semibold text-gray-900">Commentaires</h2>

    <form method="POST" action="{{ route('admin.pilotage.tasks.comments.store', $task) }}" class="mt-4">
      @csrf
      <label class="mb-1 block text-sm font-medium text-gray-700">Ajouter un commentaire</label>
      <textarea name="comment" rows="3" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-orangeone focus:outline-none">{{ old('comment') }}</textarea>
      <button type="submit" class="mt-2 rounded-md bg-orangeone px-4 py-2 text-sm font-medium text-white hover:bg-orange-600">Ajouter</button>
    </form>

    <div class="mt-5 space-y-3">
      @forelse($task->comments as $comment)
        @php $commentAuthor = $comment->user?->username ?: trim(($comment->user?->prenom ?? '') . ' ' . ($comment->user?->name ?? '')); @endphp
        <article class="rounded-lg border border-gray-200 bg-gray-50 p-3">
          <div class="flex items-center justify-between gap-2">
            <p class="text-sm font-semibold text-gray-800">{{ $commentAuthor ?: 'Utilisateur inconnu' }}</p>
            <p class="text-xs text-gray-500">{{ $comment->created_at?->format('d/m/Y H:i') }}</p>
          </div>
          <p class="mt-2 whitespace-pre-line text-sm text-gray-700">{{ $comment->body }}</p>
        </article>
      @empty
        <p class="text-sm text-gray-500">Aucun commentaire pour le moment.</p>
      @endforelse
    </div>
  </section>
</div>
@endsection

