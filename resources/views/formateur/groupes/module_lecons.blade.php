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

        <div class="mt-5 flex flex-wrap gap-3">
          <a href="{{ route('formateur.groupes.edit', $group->id) }}"
             class="btn-oneduc-blue !px-4 !py-2 !border-2">
            Retour au groupe
          </a>
          
          <form method="POST"
                action="{{ route('formateur.groupes.modules.lecons.reset', ['group' => $group->id, 'module' => $module->id]) }}"
                onsubmit="return confirm('Réinitialiser le cheminement de ce groupe pour ce module ?');">
            @csrf
            <button type="submit" class="btn-oneduc-blue !px-4 !py-2 !border-2">
              Réinitialiser
            </button>
          </form>

        </div>
      </div>
@php
  // Cherche la 1ère leçon ACTIVE (selon le tableau d’activation du groupe)
  $firstSection = null;
  $firstLecture = null;

  foreach ($sections as $sec) {
    foreach (($sec->lectures ?? collect()) as $lec) {

      // $rows doit être votre tableau indexé par lecture_id (GroupModuleLecture)
      $row = $rows[$lec->id] ?? null;

      // Par défaut : si pas de ligne en base, on considère la leçon active
      $enabled = $row ? (bool) $row->is_enabled : true;

      if ($enabled) {
        $firstSection = $sec;
        $firstLecture = $lec;
        break 2;
      }
    }
  }

  // Fallback : si tout est désactivé, on prend quand même la première leçon
  if (!$firstSection || !$firstLecture) {
    $firstSection = $sections->first();
    $firstLecture = $firstSection?->lectures?->first();
  }
@endphp

@if($firstSection && $firstLecture)
  {{-- Officiel --}}
<a href="{{ route('formateur.formations.lecture', [
      'module'  => $module->id,
      'section' => $firstSection->id,
      'lecture' => $firstLecture->id,
]) }}?mode=officiel"
   target="_blank" rel="noopener"
   class="btn-oneduc-blue !px-4 !py-2 !border-2">
  Voir le parcours officiel
</a>

{{-- Groupe (sans include_hidden) --}}
<a href="{{ route('formateur.formations.lecture', [
      'module'  => $module->id,
      'section' => $firstSection->id,
      'lecture' => $firstLecture->id,
]) }}?mode=groupe&group_id={{ $group->id }}"
   target="_blank" rel="noopener"
   class="btn-oneduc-blue !px-4 !py-2 !border-2">
  Voir le parcours du groupe
</a>

@endif


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

            return [
              'id' => (int) $lecture->id,
              'title' => (string) ($lecture->lecture_title ?? $lecture->title ?? 'Leçon'),
              'position' => (int) ($row->position ?? $lecture->position ?? ($lectureIndex + 1)),
              'is_enabled' => $enabled,
              'slides' => (int) ($lecture->slide_count ?? 0),
              'questions' => (int) ($lecture->question_count ?? $lecture->quiz_questions_per_attempt ?? 0),
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
