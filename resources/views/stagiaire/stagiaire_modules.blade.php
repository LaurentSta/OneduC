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
    @php
      $stagiaireFlowModules = $modules->values()->map(function ($module, $index) {
          return [
              'id' => (int) $module->id,
              'title' => (string) ($module->module_title ?? ''),
              'order' => $index + 1,
              'status' => (string) ($module->progression_status ?? 'not_started'),
              'progress' => (int) ($module->progression_percent ?? 0),
              'estimated_duration_label' => $module->getFormattedDurationForUser(auth()->id()),
              'detail_url' => route('stagiaire.module.detail', $module->id),
          ];
      });
    @endphp

    <div
      data-stagiaire-module-flow
      data-modules='@json($stagiaireFlowModules)'
    ></div>

    @if($modules->isEmpty())
      <div class="text-center py-20 bg-white rounded-[20px] w-full shadow-inner">
        <p class="text-gray-500 font-lisible">Aucun module ne vous a encore été attribué.</p>
      </div>
    @endif

  </main>
</div>
@endsection
