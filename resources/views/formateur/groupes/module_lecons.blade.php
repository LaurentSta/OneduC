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

  {{-- LISTE SECTIONS + LEÇONS --}}
  <main class="space-y-6">

    @forelse($sections as $section)
      <section class="bg-white rounded-[20px] shadow-md p-6 border border-gray-100">

        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
          <div class="min-w-0">
            <p class="font-raleway text-lg text-bleuone font-bold truncate">
              {{ $section->section_title ?? $section->title ?? 'Section' }}
            </p>
            @if(!empty($section->description))
              <p class="text-sm text-gray-600 mt-1 font-lisible">{{ $section->description }}</p>
            @endif
          </div>

          <div class="text-sm text-gray-500 font-varela">
            {{ $section->lectures->count() }} leçon(s)
          </div>
        </div>

        <div class="mt-4 overflow-x-auto">
          <table class="min-w-full text-sm text-left text-gray-800 font-lisible">
            <thead class="bg-gray-50 text-xs text-gray-600 uppercase font-varela">
              <tr>
                <th class="px-4 py-3">Leçon</th>
                <th class="px-4 py-3">Diapositives</th>
                <th class="px-4 py-3">Questions</th>
                <th class="px-4 py-3">État</th>
                <th class="px-4 py-3 text-right">Cheminement</th>
              </tr>
            </thead>

            <tbody>
  @forelse($section->lectures as $lecture)
    @php
      $row = $rows[$lecture->id] ?? null;
      $enabled = $row ? (bool) $row->is_enabled : true;

      $slides = (int) ($lecture->slide_count ?? 0);
      $questions = (int) ($lecture->question_count ?? $lecture->quiz_questions_per_attempt ?? 0);
    @endphp

    <tr class="border-t hover:bg-gray-50 {{ $enabled ? '' : 'opacity-60' }}">
      <td class="px-4 py-3 font-medium">
        {{ $lecture->lecture_title ?? $lecture->title ?? 'Leçon' }}
      </td>

      <td class="px-4 py-3">{{ $slides }}</td>
      <td class="px-4 py-3">{{ $questions }}</td>

      <td class="px-4 py-3">
        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-varela
          {{ $enabled ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
          {{ $enabled ? 'Active' : 'Inactive' }}
        </span>
      </td>

      <td class="px-4 py-3 text-right">
        <div class="flex items-center justify-end gap-3">

          
          @php
            $isFormateur = request()->routeIs('formateur.*');
            $quizStartRoute = $isFormateur ? 'formateur.formations.quiz.start' : 'stagiaire.formations.quiz.start';
          @endphp

          

          {{-- Interrupteur Activer/Désactiver (grand, cliquable) --}}
          <form method="POST"
                action="{{ route('formateur.groupes.modules.lecons.toggle', ['group' => $group->id, 'module' => $module->id, 'lecture' => $lecture->id]) }}">
            @csrf

            <button type="submit"
              class="relative inline-flex items-center h-9 w-28 rounded-full transition-colors duration-200
                     focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orangeone
                     {{ $enabled ? 'bg-green-500 hover:bg-green-600' : 'bg-gray-300 hover:bg-gray-400' }}"
              aria-label="{{ $enabled ? 'Désactiver la leçon' : 'Activer la leçon' }}">

              <span class="inline-block h-7 w-7 transform rounded-full bg-white shadow transition-transform duration-200
                           {{ $enabled ? 'translate-x-20' : 'translate-x-1' }}"></span>

              <span class="absolute inset-0 flex items-center justify-center text-xs font-semibold text-white">
                {{ $enabled ? 'Active' : 'Inactive' }}
              </span>
            </button>
          </form>

          {{-- Monter --}}
          <form method="POST"
                action="{{ route('formateur.groupes.modules.lecons.move.up', ['group' => $group->id, 'module' => $module->id, 'lecture' => $lecture->id]) }}">
            @csrf
            <button type="submit"
                    class="text-sm text-bleuone hover:underline px-2 py-1"
                    aria-label="Monter la leçon">
              Monter
            </button>
          </form>

          {{-- Descendre --}}
          <form method="POST"
                action="{{ route('formateur.groupes.modules.lecons.move.down', ['group' => $group->id, 'module' => $module->id, 'lecture' => $lecture->id]) }}">
            @csrf
            <button type="submit"
                    class="text-sm text-bleuone hover:underline px-2 py-1"
                    aria-label="Descendre la leçon">
              Descendre
            </button>
          </form>

        </div>
      </td>
    </tr>
  @empty
    <tr class="border-t">
      <td colspan="5" class="px-4 py-4 text-gray-500">
        Aucune leçon dans cette section.
      </td>
    </tr>
  @endforelse
</tbody>



          </table>
        </div>

      </section>
    @empty
      <div class="bg-white rounded-[20px] shadow-md p-6">
        <p class="text-gray-500 font-lisible">Aucune section trouvée pour ce module.</p>
      </div>
    @endforelse

  </main>
</div>
@endsection
