@extends('formateur.dashboard')

@section('formateur')
{{-- /home/laurents/Oneduc_Dev/resources/views/formateur/groupes/module_lecons.blade.php --}}

<div class="max-w-[1285px] mx-auto px-8 space-y-6">

  {{-- EN-TÊTE --}}
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full">
    <div class="grid grid-cols-12 gap-6 items-center">

      <div class="col-span-12 md:col-span-9">
        <x-typography variant="titre">Personnaliser les leçons</x-typography>

        <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
          Définissez le cheminement pédagogique du groupe pour ce module.
        </x-typography>

        <x-typography>
          Groupe : <span class="font-semibold">{{ $group->name }}</span>
          — Module : <span class="font-semibold">{{ $module->module_title }}</span>
        </x-typography>

        <div class="mt-5 flex flex-col gap-3 md:flex-row md:items-center">
          <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('formateur.groupes.edit', $group->id) }}"
               class="btn-oneduc-blue !px-4 !py-2 !border-2 inline-flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19l-7-7 7-7" />
              </svg>
              Retour au groupe
            </a>

            <form method="POST"
                  action="{{ route('formateur.groupes.modules.lecons.reset', ['group' => $group->id, 'module' => $module->id]) }}"
                  onsubmit="return confirm('Réinitialiser le cheminement de ce groupe pour ce module ?');">
              @csrf
              <button type="submit" class="btn-oneduc-blue !px-4 !py-2 !border-2 inline-flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 4v6h6M20 20v-6h-6M20 10a8 8 0 00-14.9-4M4 14a8 8 0 0014.9 4" />
                </svg>
                Réinitialiser
              </button>
            </form>
          </div>

          <div class="flex flex-wrap items-center gap-3 md:ml-auto">
            @if(!empty($officialPreviewUrl))
              <a href="{{ $officialPreviewUrl }}"
                 target="_blank"
                 rel="noopener"
                 class="inline-flex items-center gap-2 rounded-lg border-2 border-bleuone bg-white px-4 py-2 text-sm font-semibold text-bleuone transition hover:bg-bleuone hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                Voir le parcours officiel
              </a>
            @else
              <button type="button"
                      disabled
                      class="inline-flex cursor-not-allowed items-center gap-2 rounded-lg border-2 border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                Voir le parcours officiel
              </button>
            @endif

            @if(!empty($groupPreviewUrl))
              <a href="{{ $groupPreviewUrl }}"
                 target="_blank"
                 rel="noopener"
                 class="inline-flex items-center gap-2 rounded-lg border-2 border-bleuone bg-white px-4 py-2 text-sm font-semibold text-bleuone transition hover:bg-bleuone hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Voir le parcours du groupe
              </a>
            @else
              <button type="button"
                      disabled
                      class="inline-flex cursor-not-allowed items-center gap-2 rounded-lg border-2 border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Voir le parcours du groupe
              </button>
            @endif
          </div>
        </div>
        <p class="mt-2 text-xs text-gray-500">
          Les aperçus s'ouvrent dans un nouvel onglet.
        </p>
      </div>

      <div class="col-span-12 md:col-span-3 flex justify-center md:justify-end">
        <img src="{{ asset('images/svg/Modules.svg') }}"
             alt="Illustration des modules de formation"
             class="max-w-[220px] h-auto">
      </div>

    </div>
  </header>

  @php
    $sectionsForFlow = $sections->values()->map(function ($section) use ($rows, $group, $module) {
      return [
        'id' => (int) $section->id,
        'title' => (string) ($section->section_title ?? $section->title ?? 'Section'),
        'description' => (string) ($section->description ?? ''),
        'lectures' => collect($section->lectures ?? [])
          ->values()
          ->map(function ($lecture, $lectureIndex) use ($rows, $group, $module) {
            $row = $rows[$lecture->id] ?? null;
            $enabled = $row ? (bool) $row->is_enabled : true;
            $questionsCount = (bool) ($lecture->quiz_enabled ?? false)
              ? (int) ($lecture->quiz_questions_per_attempt ?? 0)
              : (int) ($lecture->question_count ?? 0);

            return [
              'id' => (int) $lecture->id,
              'title' => (string) ($lecture->lecture_title ?? $lecture->title ?? 'Leçon'),
              'position' => (int) ($row->position ?? $lecture->position ?? ($lectureIndex + 1)),
              'is_enabled' => $enabled,
              'slides' => (int) ($lecture->slide_count ?? 0),
              'questions' => $questionsCount,
              'toggle_url' => route('formateur.groupes.modules.lecons.toggle', [
                'group' => $group->id,
                'module' => $module->id,
                'lecture' => $lecture->id,
              ], false),
              'move_up_url' => route('formateur.groupes.modules.lecons.move.up', [
                'group' => $group->id,
                'module' => $module->id,
                'lecture' => $lecture->id,
              ], false),
              'move_down_url' => route('formateur.groupes.modules.lecons.move.down', [
                'group' => $group->id,
                'module' => $module->id,
                'lecture' => $lecture->id,
              ], false),
            ];
          })
          ->values(),
      ];
    })->values();
  @endphp

  <main class="space-y-6">
    <div
      data-group-lesson-flow
      data-sections='@json($sectionsForFlow)'
      data-csrf-token="{{ csrf_token() }}"
      class="space-y-6"
    ></div>

    <noscript>
      <div class="bg-white rounded-[20px] shadow-md p-6 border border-orange-100">
        <p class="text-sm text-gray-700">
          Activez JavaScript pour afficher la vue React Flow des leçons et gérer le parcours pédagogique.
        </p>
      </div>
    </noscript>
  </main>
</div>
@endsection
