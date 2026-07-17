@extends('formateur.dashboard')

@section('formateur')

@php
  $items        = collect($selectedItems);
  $moduleItems  = $items->where('type', 'module');
  $wcItems      = $items->where('type', 'wordcloud');
  $pollItems    = $items->where('type', 'poll');

  $statModules   = $moduleItems->count();
  $statLecons    = (int) $moduleItems->sum('lesson_count');
  $statQuestions = (int) $moduleItems->sum('question_count');
  $statWc        = $wcItems->count();
  $statPoll      = $pollItems->count();
  $statEtapes    = $items->count();
@endphp

<div class="max-w-[1285px] mx-auto px-8">

  <header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full mb-6">
    <div class="grid grid-cols-12 gap-6 items-start">

      {{-- Titre + description + breadcrumb --}}
      <div class="col-span-12 lg:col-span-8">
        <x-typography variant="titre">{{ $parcours->title }}</x-typography>
        @if ($parcours->description)
          <p class="text-sm text-gray-500 mt-1 max-w-2xl">{{ $parcours->description }}</p>
        @endif
        <nav class="text-sm font-varela text-gray-600 mt-2" aria-label="Fil d'Ariane">
          <ol class="inline-flex items-center space-x-1">
            <li class="flex items-center">
              <a href="{{ route('formateur.dashboard') }}" class="text-orangeone hover:underline flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9.75L12 3l9 6.75V19a2 2 0 01-2 2h-4a1 1 0 01-1-1v-5H10v5a1 1 0 01-1 1H5a2 2 0 01-2-2V9.75z"/>
                </svg>
              </a>
              <span class="mx-2 text-gray-400">/</span>
            </li>
            <li class="flex items-center">
              <a href="{{ route('formateur.mes-parcours.index') }}" class="text-orangeone hover:underline">Mes parcours</a>
              <span class="mx-2 text-gray-400">/</span>
            </li>
            <li class="text-gray-400">{{ Str::limit($parcours->title, 40) }}</li>
          </ol>
        </nav>

        <a href="{{ route('formateur.mes-parcours.edit', $parcours) }}"
           class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 rounded-[10px] bg-[#E94D2A] text-white font-medium hover:bg-[#cf4121] transition text-sm">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
          </svg>
          Modifier ce parcours
        </a>
      </div>

      {{-- Bandeau stats --}}
      <div class="col-span-12 lg:col-span-4">
        <div class="rounded-[14px] border border-[#004461]/15 bg-[#004461]/5 px-5 py-4 space-y-3">
          <p class="text-xs font-bold uppercase tracking-widest text-[#004461]/60 mb-1">Résumé du parcours</p>

          <div class="grid grid-cols-2 gap-3">
            {{-- Étapes --}}
            <div class="flex items-center gap-3 rounded-[10px] bg-white border border-gray-100 px-3 py-2.5 shadow-sm">
              <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#004461]/10">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#004461]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
              </div>
              <div>
                <div class="text-lg font-bold text-[#004461] leading-none">{{ $statEtapes }}</div>
                <div class="text-[11px] text-gray-500 mt-0.5">Étape{{ $statEtapes > 1 ? 's' : '' }}</div>
              </div>
            </div>

            {{-- Modules --}}
            <div class="flex items-center gap-3 rounded-[10px] bg-white border border-gray-100 px-3 py-2.5 shadow-sm">
              <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-orangeone/10">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-orangeone" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
              </div>
              <div>
                <div class="text-lg font-bold text-orangeone leading-none">{{ $statModules }}</div>
                <div class="text-[11px] text-gray-500 mt-0.5">Formation{{ $statModules > 1 ? 's' : '' }}</div>
              </div>
            </div>

            {{-- Leçons --}}
            <div class="flex items-center gap-3 rounded-[10px] bg-white border border-gray-100 px-3 py-2.5 shadow-sm">
              <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-vertone/10">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-vertone" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
              </div>
              <div>
                <div class="text-lg font-bold text-vertone leading-none">{{ $statLecons }}</div>
                <div class="text-[11px] text-gray-500 mt-0.5">Leçon{{ $statLecons > 1 ? 's' : '' }}</div>
              </div>
            </div>

            {{-- Questions --}}
            <div class="flex items-center gap-3 rounded-[10px] bg-white border border-gray-100 px-3 py-2.5 shadow-sm">
              <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-purple-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <div>
                <div class="text-lg font-bold text-purple-600 leading-none">{{ $statQuestions }}</div>
                <div class="text-[11px] text-gray-500 mt-0.5">Question{{ $statQuestions > 1 ? 's' : '' }}</div>
              </div>
            </div>
          </div>

          {{-- Outils interactifs --}}
          @if($statWc > 0 || $statPoll > 0)
          <div class="flex gap-2 pt-1">
            @if($statWc > 0)
            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
              {{ $statWc }} nuage{{ $statWc > 1 ? 's' : '' }} de mots
            </span>
            @endif
            @if($statPoll > 0)
            <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-100 px-3 py-1 text-xs font-semibold text-teal-700">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
              {{ $statPoll }} sondage{{ $statPoll > 1 ? 's' : '' }}
            </span>
            @endif
          </div>
          @endif
        </div>
      </div>

    </div>
  </header>

  {{-- Preview React Flow (mode lecture seule) --}}
  <div
    data-parcours-builder
    data-available-modules='@json([])'
    data-selected-items='@json($selectedItems)'
    data-csrf-token="{{ csrf_token() }}"
    data-store-url=""
    data-method="GET"
    data-mode="preview"
    class="min-h-[520px]"
  ></div>

</div>

@endsection
