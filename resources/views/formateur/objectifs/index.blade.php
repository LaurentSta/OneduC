@extends('formateur.dashboard')

@section('formateur')

<div class="max-w-[1285px] mx-auto px-8">
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full mb-6">
    <div class="grid grid-cols-12 gap-6 items-center">
      <div class="col-span-12 md:col-span-8">
        <x-typography variant="titre">Je cherche un objectif</x-typography>
        <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
          Recherchez les objectifs existants pour eviter les doublons.
        </x-typography>
        <x-typography>
          La recherche fonctionne par mots-cles sur les objectifs de lecon, la lecon, le chapitre et le module.
        </x-typography>

        <nav class="text-sm font-varela text-gray-600 mt-2" aria-label="Fil d'Ariane">
          <ol class="inline-flex items-center space-x-1">
            <li class="flex items-center">
              <a href="{{ route('formateur.dashboard') }}" class="text-orangeone hover:underline flex items-center">
                <span class="sr-only">Accueil</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 9.75L12 3l9 6.75V19a2 2 0 01-2 2h-4a1 1 0 01-1-1v-5H10v5a1 1 0 01-1 1H5a2 2 0 01-2-2V9.75z"/>
                </svg>
              </a>
              <span class="mx-2 text-gray-400" aria-hidden="true">/</span>
            </li>
            <li class="text-gray-400">Recherche objectifs</li>
          </ol>
        </nav>
      </div>

      <div class="col-span-12 md:col-span-4 flex justify-center md:justify-end">
        <img src="{{ asset('images/svg/Modules.svg') }}"
             alt="Illustration recherche d'objectifs"
             class="max-w-[260px] h-auto"
             loading="lazy">
      </div>
    </div>
  </header>

  <main class="space-y-8">
    <div class="flex flex-wrap items-center justify-between gap-4 px-6 pt-4 pb-0">
      <div class="inline-flex items-center gap-2 rounded-full border border-bleuone/20 bg-white px-4 py-2 text-sm font-varela text-gray-700">
        <span>Objectifs trouves :</span>
        <span class="font-bold text-bleuone">{{ $objectives->total() }}</span>
      </div>

      @if($search !== '')
        <p class="text-sm text-gray-600 font-varela">
          Recherche active :
          <span class="text-orangeone font-semibold">{{ $search }}</span>
        </p>
      @endif
    </div>

    <form method="GET" action="{{ route('formateur.objectifs.index') }}" class="flex flex-wrap items-end gap-3 -mt-1">
      <div class="w-full md:w-5/12">
        <label for="search" class="sr-only">Recherche d'objectif</label>
        <input
          id="search"
          name="search"
          type="text"
          value="{{ $search }}"
          placeholder="Ex. excel formule tableau"
          class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orangeone focus:border-orangeone text-sm font-lisible">
      </div>

      <div class="w-full md:w-4/12">
        <label for="module_id" class="sr-only">Filtrer par module</label>
        <select
          id="module_id"
          name="module_id"
          class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orangeone focus:border-orangeone text-sm font-lisible bg-white">
          <option value="0">Tous les modules</option>
          @foreach($modules as $m)
            @php $moduleLabel = $m->module_title ?: ($m->module_name ?: ('Module #' . $m->id)); @endphp
            <option value="{{ $m->id }}" @selected($moduleId === (int) $m->id)>{{ $moduleLabel }}</option>
          @endforeach
        </select>
      </div>

      <button type="submit" class="btn-oneduc">Filtrer</button>

      @if($search !== '' || $moduleId > 0)
        <a href="{{ route('formateur.objectifs.index') }}"
           class="btn-oneduc bg-white text-gray-700 border border-gray-300 hover:border-orangeone hover:text-orangeone">
          Reinitialiser
        </a>
      @endif
    </form>

    <p class="text-sm text-gray-500 font-lisible -mt-4">
      Tape un ou plusieurs mots. Exemple : <span class="font-medium text-gray-700">excel formule</span> retrouve les objectifs contenant ces mots, meme s'ils ne sont pas colles ensemble.
    </p>

    <div class="overflow-x-auto bg-white shadow-md rounded-[20px] border-2 border-bleuone/20">
      <table class="min-w-full bg-white text-sm text-left text-gray-800 font-lisible">
        <thead class="bg-bleuone uppercase text-xs text-white font-varela sticky top-0 z-10">
          <tr>
            <th class="px-6 py-3">#</th>
            <th class="px-6 py-3">Objectif</th>
            <th class="px-6 py-3">Competences</th>
            <th class="px-6 py-3">Lecon</th>
            <th class="px-6 py-3">Chapitre</th>
            <th class="px-6 py-3">Module</th>
            <th class="px-6 py-3">Actions</th>
          </tr>
        </thead>

        <tbody>
          @forelse($objectives as $index => $objective)
            @php
              $lecture = $objective->lecture;
              $section = $lecture?->section;
              $module = $lecture?->module;
              $moduleLabel = $module?->module_title ?: ($module?->module_name ?: null);
            @endphp
            <tr class="border-t {{ $index % 2 === 0 ? 'bg-white' : 'bg-orangeone/8' }} hover:bg-orangeone/15 transition-colors">
              <td class="px-6 py-4 font-medium">{{ $objectives->firstItem() + $index }}</td>

              <td class="px-6 py-4">
                <p class="font-semibold text-gray-900">{{ $objective->title }}</p>
                @if(!empty($objective->description))
                  <p class="text-xs text-gray-600 mt-1">{{ \Illuminate\Support\Str::limit($objective->description, 110) }}</p>
                @endif
              </td>

              <td class="px-6 py-4">
                @forelse($objective->competencies as $competency)
                  <span class="inline-flex items-center rounded-full bg-gray-100 text-gray-700 px-2 py-1 text-xs mr-1 mb-1">
                    {{ $competency->code ? $competency->code . ' - ' : '' }}{{ $competency->label }}
                  </span>
                @empty
                  <span class="text-gray-400">—</span>
                @endforelse
              </td>

              <td class="px-6 py-4">{{ $lecture?->lecture_title ?: '—' }}</td>
              <td class="px-6 py-4">{{ $section?->section_title ?: '—' }}</td>
              <td class="px-6 py-4">{{ $moduleLabel ?: '—' }}</td>

              <td class="px-6 py-4">
                <div class="flex flex-wrap gap-2">
                  @if($lecture && $section && $module)
                    <a
                      href="{{ route('formateur.formations.lecture', ['module' => $module->id, 'section' => $section->id, 'lecture' => $lecture->id]) }}"
                      class="btn-oneduc px-3 py-1 text-xs text-white bg-orangeone border-orangeone hover:bg-white hover:text-orangeone">
                      Ouvrir
                    </a>
                  @endif
                  <button
                    type="button"
                    data-copy-objective="{{ $objective->title }}"
                    class="btn-oneduc px-3 py-1 text-xs bg-white text-gray-700 border border-gray-300 hover:border-orangeone hover:text-orangeone">
                    Copier
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-6 py-6 text-center text-gray-500">
                Aucun objectif trouve avec ces filtres.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div>
      {{ $objectives->links('pagination::tailwind') }}
    </div>
  </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const buttons = document.querySelectorAll('[data-copy-objective]');

  const copyText = async (text) => {
    if (navigator.clipboard && window.isSecureContext) {
      await navigator.clipboard.writeText(text);
      return;
    }

    const input = document.createElement('textarea');
    input.value = text;
    input.setAttribute('readonly', '');
    input.style.position = 'absolute';
    input.style.left = '-9999px';
    document.body.appendChild(input);
    input.select();
    document.execCommand('copy');
    document.body.removeChild(input);
  };

  buttons.forEach((button) => {
    button.addEventListener('click', async function () {
      const value = (button.getAttribute('data-copy-objective') || '').trim();
      if (!value) return;

      const previousLabel = button.textContent;

      try {
        await copyText(value);
        button.textContent = 'Copie';
      } catch (error) {
        button.textContent = 'Echec';
      }

      setTimeout(() => {
        button.textContent = previousLabel;
      }, 1200);
    });
  });
});
</script>
@endsection
