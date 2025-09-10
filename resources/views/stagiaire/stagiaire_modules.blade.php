@extends('stagiaire.master')

@section('content')

{{-- Wrapper unique --}}
<div class="max-w-[1285px] mx-auto px-8">

  {{-- 🧩 EN-TÊTE DE PAGE STAGIAIRE – Mes formations --}}
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full mb-6">
    <div class="grid grid-cols-12 gap-6 items-center">

      {{-- Texte (9) --}}
      <div class="col-span-12 md:col-span-8">
        <p class="font-raleway text-titre text-bleuone leading-tight mb-4">Mes formations</p>
        <p class="font-varela text-sous-titre text-orangeone leading-snug mb-3">
          Accédez à vos contenus et suivez votre progression.
        </p>
        <p class="font-lisible text-lg text-gray-800 leading-loose mb-6">
          Chaque module regroupe plusieurs sections. Vous pouvez reprendre une leçon à tout moment.
        </p>

        {{-- Fil d’Ariane --}}
        <nav class="text-sm font-varela text-gray-600 mt-2" aria-label="Fil d'Ariane">
          <ol class="inline-flex items-center space-x-1">
            <li class="flex items-center">
              <a href="{{ route('stagiaire.dashboard') }}" class="text-orangeone hover:underline flex items-center">
                <span class="sr-only">Accueil</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 9.75L12 3l9 6.75V19a2 2 0 01-2 2h-4a1 1 0 01-1-1v-5H10v5a1 1 0 01-1 1H5a2 2 0 01-2-2V9.75z"/>
                </svg>
              </a>
              <span class="mx-2 text-gray-400" aria-hidden="true">/</span>
            </li>
            <li class="text-gray-400">Mes modules</li>
          </ol>
        </nav>
      </div>

      {{-- Image (3) --}}
      <div class="col-span-12 md:col-span-4 flex justify-center md:justify-end">
        <img src="{{ asset('images/svg/FormationStagiaire.svg') }}"
             alt="Illustration des modules de formation"
             class="max-w-[400px] h-auto">
      </div>

    </div>
  </header>

  {{-- 📋 CONTENU PRINCIPAL --}}
  <main class="space-y-8">

    <section aria-labelledby="liste-modules">
      <h2 id="liste-modules" class="sr-only">Liste des modules</h2>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($modules as $module)
          <article class="bg-white shadow-md rounded-[20px] overflow-hidden flex flex-col">
            {{-- Image du module --}}
            @if($module->module_image)
              <img
                src="{{ asset('storage/' . $module->module_image) }}"
                alt="Image du module {{ $module->module_title }}"
                class="w-full h-40 object-cover"
                loading="lazy">
            @endif

            <div class="px-6 pt-5 pb-6 flex flex-col flex-1 justify-between">
              <div>
                <h3 class="text-lg font-varela text-bleuone mb-1">{{ $module->module_title }}</h3>
                <p class="text-sm text-gray-600 font-lisible mb-3">
                  {{ \Illuminate\Support\Str::limit($module->description, 100) }}
                </p>

                {{-- Statut et progression --}}
                @php
                  $status = $module->progression_status ?? 'not_started';
                  $percentage = (int)($module->progression_percent ?? 0);

                  $badgeText = [
                    'completed'   => 'Terminé',
                    'in_progress' => 'En cours',
                    'not_started' => 'Non commencé',
                  ][$status] ?? 'Indéfini';

                  $badgeColor = [
                    'completed'   => 'bg-vertone text-white',
                    'in_progress' => 'bg-orangeone text-white',
                    'not_started' => 'bg-gray-200 text-gray-700',
                  ][$status] ?? 'bg-gray-2 00 text-gray-700';
                @endphp

                <div class="flex items-center justify-between mt-2">
                  <span class="text-xs font-varela px-3 py-1 rounded-full {{ $badgeColor }}">
                    {{ $badgeText }}
                  </span>
                  <span class="text-xs text-gray-500 font-lisible" aria-live="polite">
                    {{ $percentage }}%
                  </span>
                </div>

                <div class="w-full bg-gray-200 h-2 rounded mt-2" role="progressbar"
                     aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"
                     aria-label="Progression du module {{ $module->module_title }}">
                  <div class="h-2 rounded bg-vertone transition-all duration-200" style="width: {{ $percentage }}%"></div>
                </div>
              </div>

              <div class="mt-4">
                <a href="{{ route('stagiaire.module.detail', $module->id) }}"
                   class="btn-oneduc w-full text-center">
                  Voir le module
                </a>
              </div>
            </div>
          </article>
        @empty
          <div class="col-span-3 text-gray-500 font-lisible text-center">
            Aucun module ne vous a encore été attribué.
          </div>
        @endforelse
      </div>
    </section>

  </main>
</div>
@endsection
