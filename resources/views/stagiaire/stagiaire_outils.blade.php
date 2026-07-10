@extends('stagiaire.master')

@section('content')

<div class="max-w-[1285px] mx-auto px-8">

  {{-- EN-TÊTE DE PAGE --}}
  <header class="rounded-[20px] border border-gray-100 bg-white shadow-md mb-6">
    <div class="grid gap-6 px-6 py-6 md:px-8 md:py-7 lg:grid-cols-12 lg:items-center">

      <div class="lg:col-span-8">
        <x-oneduc.breadcrumb :items="[['label' => 'Accueil', 'url' => route('stagiaire.dashboard')], ['label' => 'Mes outils']]" />

        <h1 class="font-raleway text-2xl font-medium leading-tight text-bleuone md:text-3xl">
          Mes outils numériques
        </h1>
        <p class="mt-0.5 font-varela text-base text-orangeone md:text-lg">
          Les outils interactifs utilisés dans votre groupe de formation.
        </p>
        <p class="mt-3 max-w-2xl font-lisible text-sm leading-relaxed text-slate-700">
          Retrouvez ici tous les outils activés par votre formateur et votre niveau de participation à chacun.
        </p>
      </div>

      <div class="lg:col-span-4 flex justify-center lg:justify-end">
        <img src="{{ asset('images/svg/MesFormationsStagiaire.svg') }}"
             alt="Illustration outils numériques"
             class="max-w-[220px] h-auto">
      </div>

    </div>
  </header>

  {{-- CAS : aucun groupe actif --}}
  @if (! $group)
    <div class="bg-white rounded-[20px] shadow-md p-12 text-center">
      <div class="flex justify-center mb-4 text-bleuone/30">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
        </svg>
      </div>
      <p class="font-raleway text-xl font-semibold text-bleuone mb-2">Aucun groupe actif</p>
      <p class="font-lisible text-gray-500">Vous n'êtes pas encore rattaché à un groupe de formation. Les outils apparaîtront ici dès que votre formateur vous aura intégré à un groupe.</p>
    </div>

  {{-- CAS : groupe mais aucun outil utilisé --}}
  @elseif ($tools->isEmpty())
    <div class="bg-white rounded-[20px] shadow-md p-12 text-center">
      <div class="flex justify-center mb-4 text-bleuone/30">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
        </svg>
      </div>
      <p class="font-raleway text-xl font-semibold text-bleuone mb-2">Aucun outil activé pour l'instant</p>
      <p class="font-lisible text-gray-500">
        Votre formateur n'a pas encore utilisé d'outils numériques dans votre groupe
        <span class="font-semibold text-bleuone">{{ $group->name }}</span>.
        Revenez plus tard !
      </p>
    </div>

  @else
  {{-- CAS NORMAL --}}
  <main class="space-y-8">

    {{-- KPI --}}
    <section class="grid grid-cols-1 md:grid-cols-3 gap-6">

      <div class="bg-white rounded-[20px] shadow-md p-5 flex items-center gap-4">
        <div class="p-3 bg-bleuone/10 rounded-xl text-bleuone shrink-0">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
          </svg>
        </div>
        <div>
          <p class="text-[10px] font-bold uppercase text-gray-400">Outils activés</p>
          <p class="text-xl font-bold text-bleuone">{{ $tools->count() }}</p>
        </div>
      </div>

      <div class="bg-white rounded-[20px] shadow-md p-5 flex items-center gap-4">
        <div class="p-3 bg-vertone/10 rounded-xl text-vertone shrink-0">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <div>
          @php $participatedCount = $tools->filter(fn($t) => $t->trackable && $t->participated > 0)->count(); @endphp
          <p class="text-[10px] font-bold uppercase text-gray-400">Outils où j'ai participé</p>
          <p class="text-xl font-bold text-vertone">{{ $participatedCount }}</p>
        </div>
      </div>

      <div class="bg-white rounded-[20px] shadow-md p-5 flex items-center gap-4">
        <div class="p-3 bg-orangeone/10 rounded-xl text-orangeone shrink-0">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
        </div>
        <div>
          <p class="text-[10px] font-bold uppercase text-gray-400">Groupe</p>
          <p class="text-base font-bold text-bleuone truncate max-w-[180px]">{{ $group->name }}</p>
          @if ($formateur)
            <p class="text-xs text-gray-500">Formateur : {{ $formateur->name }}</p>
          @endif
        </div>
      </div>

    </section>

    {{-- Grille d'outils --}}
    <section>
      <h2 class="text-lg font-semibold text-bleuone mb-4">Détail par outil</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

        @foreach ($tools as $tool)
        @php
          $participated    = $tool->trackable && $tool->participated > 0;
          $notParticipated = $tool->trackable && $tool->participated === 0;

          $iconPath = $tool->icon_path ?? match($tool->key) {
            'wordcloud'     => 'M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z',
            'poll'          => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
            'live_quiz'     => 'M13 10V3L4 14h7v7l9-11h-7z',
            'question_wall' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            'whiteboard'    => 'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z',
            'timer'         => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
            'random_wheel'  => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
            'scale'         => 'M3 6h18M3 12h18M3 18h18',
            'true_false'    => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            'buzzer_quiz'   => 'M13 10V3L4 14h7v7l9-11h-7z',
            'component_finder' => 'M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z',
            'emargement'    => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
            default         => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
          };

          $borderColor = $participated ? 'border-vertone' : ($notParticipated ? 'border-orangeone' : 'border-gray-200');
          $iconBg      = $participated ? 'bg-vertone/10 text-vertone' : ($notParticipated ? 'bg-orange-50 text-orangeone' : 'bg-gray-100 text-gray-500');
        @endphp

        <div class="bg-white rounded-[20px] shadow-md p-6 flex flex-col gap-4 border-l-4 {{ $borderColor }}">

          {{-- Icône + nom --}}
          <div class="flex items-center gap-3">
            <div class="p-2 rounded-lg shrink-0 {{ $iconBg }}">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $iconPath }}"/>
              </svg>
            </div>
            <div>
              <p class="font-semibold text-bleuone text-base">{{ $tool->label }}</p>
              @if ($tool->last_used)
                <p class="text-xs text-gray-400">
                  Dernière activité : {{ \Carbon\Carbon::parse($tool->last_used)->diffForHumans() }}
                </p>
              @endif
            </div>
          </div>

          {{-- Compteurs --}}
          <div class="flex items-center justify-between text-sm">
            <div class="text-center">
              <p class="text-lg font-bold text-bleuone">{{ $tool->sessions }}</p>
              <p class="text-[10px] uppercase font-semibold text-gray-400">{{ $tool->sessions > 1 ? 'sessions' : 'session' }}</p>
            </div>

            @if ($tool->trackable)
              <div class="text-center">
                <p class="text-lg font-bold {{ $participated ? 'text-vertone' : 'text-orangeone' }}">
                  {{ $tool->participated }}/{{ $tool->sessions }}
                </p>
                <p class="text-[10px] uppercase font-semibold text-gray-400">participé</p>
              </div>
            @else
              <div class="text-center">
                <p class="text-sm text-gray-400 italic">Non mesurable</p>
              </div>
            @endif
          </div>

          {{-- Badge statut --}}
          @if ($participated)
            <span class="inline-flex items-center gap-1 rounded-full bg-vertone/10 px-3 py-1 text-xs font-semibold text-vertone self-start">
              <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              Participé
            </span>
          @elseif ($notParticipated)
            <span class="inline-flex items-center gap-1 rounded-full bg-orange-50 px-3 py-1 text-xs font-semibold text-orangeone self-start">
              <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              Non participé
            </span>
          @else
            <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-500 self-start">
              Activé dans le groupe
            </span>
          @endif

          @if ($tool->key === 'emargement' && $openSeance)
            <a href="{{ route('stagiaire.emargement.show', $group->id) }}"
               class="inline-flex items-center justify-center rounded-full bg-bleuone px-4 py-2 text-xs font-bold text-white hover:bg-bleuone/90 transition self-start">
              Signer maintenant
            </a>
          @endif

          @if ($tool->key === 'wordcloud' && ! empty($tool->active_url))
            <a href="{{ $tool->active_url }}"
               class="inline-flex items-center justify-center rounded-full bg-orangeone px-4 py-2 text-xs font-bold text-white hover:bg-orangeone-hover transition self-start">
              Participer maintenant
            </a>
          @endif

          @if ($tool->key === 'true_false' && ! empty($tool->active_url))
            <a href="{{ $tool->active_url }}"
               class="inline-flex items-center justify-center rounded-full bg-orangeone px-4 py-2 text-xs font-bold text-white hover:bg-orangeone-hover transition self-start">
              Répondre maintenant
            </a>
          @endif

          @if (in_array($tool->key, ['buzzer_quiz', 'component_finder'], true) && ! empty($tool->active_url))
            <a href="{{ $tool->active_url }}"
               class="inline-flex items-center justify-center rounded-full bg-bleuone px-4 py-2 text-xs font-bold text-white hover:bg-bleuone/90 transition self-start">
              Participer maintenant
            </a>
          @endif

          @if (! empty($tool->active_url) && ! empty($tool->action_label))
            <a href="{{ $tool->active_url }}"
               class="inline-flex items-center justify-center rounded-full bg-bleuone px-4 py-2 text-xs font-bold text-white hover:bg-bleuone/90 transition self-start">
              {{ $tool->action_label }}
            </a>
          @endif

        </div>
        @endforeach

      </div>
    </section>

  </main>
  @endif

</div>
@endsection
