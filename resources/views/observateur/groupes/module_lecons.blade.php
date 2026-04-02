@extends('observateur.dashboard')

@section('observateur')
<div class="max-w-[1285px] mx-auto px-8 space-y-6">
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full">
    <div class="grid grid-cols-12 gap-6 items-center">
      <div class="col-span-12 md:col-span-9">
        <x-typography variant="titre">Parcours pédagogique en lecture seule</x-typography>
        <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
          Consultez l’ordre et l’activation des leçons sans modification possible.
        </x-typography>
        <x-typography>
          Groupe : <span class="font-semibold">{{ $group->name }}</span>
          — Module : <span class="font-semibold">{{ $module->module_title }}</span>
        </x-typography>

        <div class="mt-5 flex flex-col gap-3 md:flex-row md:items-center">
          <a href="{{ route('observateur.groupes.index') }}"
             class="btn-oneduc-blue !px-4 !py-2 !text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19l-7-7 7-7" />
            </svg>
            Retour aux groupes
          </a>

          <div class="flex flex-wrap items-center gap-3 md:ml-auto">
            @if(!empty($officialPreviewUrl))
              <a href="{{ $officialPreviewUrl }}" target="_blank" rel="noopener"
                 class="btn-oneduc-outline !px-4 !py-2 !text-sm">
                <x-icons.eye-iconify class="h-4 w-4" />
                Voir le parcours officiel
              </a>
            @endif

            @if(!empty($groupPreviewUrl))
              <a href="{{ $groupPreviewUrl }}" target="_blank" rel="noopener"
                 class="btn-oneduc-outline !px-4 !py-2 !text-sm">
                <x-icons.eye-iconify class="h-4 w-4" />
                Voir le parcours du groupe
              </a>
            @endif
          </div>
        </div>

        <p class="mt-2 text-xs text-gray-500">Les aperçus s’ouvrent dans un nouvel onglet. Aucune action d’édition n’est disponible.</p>
      </div>

      <div class="col-span-12 md:col-span-3 flex justify-center md:justify-end">
        <img src="{{ asset('images/svg/Modules.svg') }}" alt="Illustration des modules de formation" class="max-w-[220px] h-auto">
      </div>
    </div>
  </header>

  @php
    $sectionsForFlow = $sections->values()->map(function ($section) use ($rows) {
      return [
        'id' => (int) $section->id,
        'title' => (string) ($section->section_title ?? $section->title ?? 'Section'),
        'description' => (string) ($section->description ?? ''),
        'lectures' => collect($section->lectures ?? [])
          ->values()
          ->map(function ($lecture, $lectureIndex) use ($rows) {
            $row = $rows[$lecture->id] ?? null;

            return [
              'id' => (int) $lecture->id,
              'title' => (string) ($lecture->lecture_title ?? $lecture->title ?? 'Leçon'),
              'position' => (int) ($row->position ?? $lecture->position ?? ($lectureIndex + 1)),
              'is_enabled' => $row ? (bool) $row->is_enabled : true,
              'slides' => (int) ($lecture->slide_count ?? 0),
              'questions' => (int) ($lecture->question_count ?? $lecture->quiz_questions_per_attempt ?? 0),
              'toggle_url' => '',
              'move_up_url' => '',
              'move_down_url' => '',
            ];
          })
          ->values(),
      ];
    })->values();
  @endphp

  <main class="space-y-6">
    <div
      data-group-lesson-flow
      data-read-only="1"
      data-sections='@json($sectionsForFlow)'
      data-csrf-token="{{ csrf_token() }}"
      class="space-y-6"
    ></div>
  </main>
</div>
@endsection
