@extends('formateur.dashboard')

@section('formateur')
<div class="w-full px-6 lg:px-8">

  {{-- En-tête --}}
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-5 pb-6 my-6">
    <div class="grid grid-cols-12 gap-6 items-center">
      <div class="col-span-12 md:col-span-8">
        <p class="font-raleway text-titre text-bleuone leading-tight mb-3">Outils numériques</p>
        <p class="font-varela text-sous-titre text-orangeone leading-snug mb-3">
          Animez vos sessions en présentiel ou à distance.
        </p>
        <p class="font-lisible text-base text-gray-700 leading-loose">
          Nuages de mots, quiz en direct, tableau blanc collaboratif — tous vos outils d'animation interactifs réunis.
        </p>
        <nav class="text-sm font-varela text-gray-600 mt-3" aria-label="Fil d'Ariane">
          <ol class="inline-flex items-center space-x-1">
            <li>
              <a href="{{ route('formateur.dashboard') }}" class="text-orangeone hover:underline flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9.75L12 3l9 6.75V19a2 2 0 01-2 2h-4a1 1 0 01-1-1v-5H10v5a1 1 0 01-1 1H5a2 2 0 01-2-2V9.75z"/>
                </svg>
              </a>
            </li>
            <li><span class="mx-2 text-gray-400">/</span></li>
            <li class="text-gray-400">Outils numériques</li>
          </ol>
        </nav>
      </div>
      <div class="col-span-12 md:col-span-4 flex justify-center md:justify-end">
        <img src="{{ asset('images/svg/Modules.svg') }}" alt="Outils numériques" class="max-w-[260px] h-auto opacity-90">
      </div>
    </div>
  </header>

  {{-- Grille des outils --}}
  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-5 gap-6 mb-8">

    {{-- ── NUAGE DE MOTS ──────────────────────────────────────────────── --}}
    <div class="flex flex-col bg-white rounded-[20px] shadow-md overflow-hidden">
      <div class="bg-amber-500 px-6 py-5 flex items-center gap-3">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/20">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
          </svg>
        </div>
        <h2 class="text-lg font-bold text-white">Nuage de mots</h2>
      </div>
      <div class="flex-1 px-6 py-5 space-y-3">
        <p class="text-sm text-gray-600 leading-relaxed">
          Posez une question ouverte. Les stagiaires soumettent leurs mots et vous voyez le nuage se construire en direct. Fonctionne en autonomie (parcours) ou en session live avec un code.
        </p>
        <div class="flex flex-wrap gap-2 text-[11px]">
          <span class="rounded-full bg-green-100 px-2.5 py-0.5 font-semibold text-green-700">Présentiel</span>
          <span class="rounded-full bg-blue-100 px-2.5 py-0.5 font-semibold text-blue-700">Distanciel</span>
          <span class="rounded-full bg-amber-100 px-2.5 py-0.5 font-semibold text-amber-700">Asynchrone</span>
        </div>
      </div>

      {{-- Nuages récents --}}
      @if($recentWordclouds->isNotEmpty())
        <div class="border-t border-gray-100 px-6 py-4">
          <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Sessions récentes</p>
          <div class="space-y-2">
            @foreach($recentWordclouds as $wc)
              <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                  <p class="text-xs font-semibold text-gray-800 truncate">{{ $wc->title }}</p>
                  <p class="text-[10px] text-gray-400">{{ $wc->group?->name }}</p>
                </div>
                <a href="{{ route('formateur.nuages.live', $wc) }}"
                   class="shrink-0 rounded-[6px] bg-amber-100 px-2 py-1 text-[10px] font-bold text-amber-700 hover:bg-amber-200 transition">
                  Voir
                </a>
              </div>
            @endforeach
          </div>
        </div>
      @endif

      <div class="border-t border-gray-100 px-6 py-4 mt-auto">
        <a href="{{ route('formateur.nuages.index') }}"
           class="w-full inline-flex items-center justify-center gap-2 rounded-[10px] bg-amber-500 px-4 py-2.5 text-sm font-bold text-white hover:bg-amber-600 transition">
          Gérer les nuages de mots
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </a>
      </div>
    </div>

    {{-- ── QUIZ EN DIRECT ─────────────────────────────────────────────── --}}
    <div class="flex flex-col bg-white rounded-[20px] shadow-md overflow-hidden">
      <div class="bg-[#004461] px-6 py-5 flex items-center gap-3">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/20">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
          </svg>
        </div>
        <h2 class="text-lg font-bold text-white">Quiz en direct</h2>
      </div>
      <div class="flex-1 px-6 py-5 space-y-3">
        <p class="text-sm text-gray-600 leading-relaxed">
          Lancez une session de quiz synchronisée avec votre groupe. Les stagiaires répondent en temps réel depuis leur espace et vous visualisez les résultats question par question.
        </p>
        <div class="flex flex-wrap gap-2 text-[11px]">
          <span class="rounded-full bg-green-100 px-2.5 py-0.5 font-semibold text-green-700">Présentiel</span>
          <span class="rounded-full bg-blue-100 px-2.5 py-0.5 font-semibold text-blue-700">Distanciel</span>
          <span class="rounded-full bg-gray-100 px-2.5 py-0.5 font-semibold text-gray-500">Synchrone</span>
        </div>
      </div>
      <div class="border-t border-gray-100 px-6 py-4 mt-auto">
        <a href="{{ route('formateur.outils.quiz.index') }}"
           class="w-full inline-flex items-center justify-center gap-2 rounded-[10px] bg-[#004461] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#005577] transition">
          Gérer les quiz en direct
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </a>
      </div>
    </div>

    {{-- ── TABLEAU BLANC ──────────────────────────────────────────────── --}}
    <div class="flex flex-col bg-white rounded-[20px] shadow-md overflow-hidden">
      <div class="bg-[#E94D2A] px-6 py-5 flex items-center gap-3">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/20">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <h2 class="text-lg font-bold text-white">Tableau blanc</h2>
      </div>
      <div class="flex-1 px-6 py-5 space-y-3">
        <p class="text-sm text-gray-600 leading-relaxed">
          Un espace collaboratif partagé avec votre groupe. Dessinez, annotez, organisez des idées ensemble en temps réel. Idéal pour le brainstorming et la co-construction.
        </p>
        <div class="flex flex-wrap gap-2 text-[11px]">
          <span class="rounded-full bg-green-100 px-2.5 py-0.5 font-semibold text-green-700">Présentiel</span>
          <span class="rounded-full bg-blue-100 px-2.5 py-0.5 font-semibold text-blue-700">Distanciel</span>
          <span class="rounded-full bg-gray-100 px-2.5 py-0.5 font-semibold text-gray-500">Synchrone</span>
        </div>
      </div>

      @if($groups->isNotEmpty())
        <div class="border-t border-gray-100 px-6 py-4">
          <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Mes groupes</p>
          <div class="space-y-2">
            @foreach($groups->take(5) as $group)
              <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                  <p class="text-xs font-semibold text-gray-800 truncate">{{ $group->name }}</p>
                  <p class="text-[10px] text-gray-400">{{ $group->students_count }} stagiaire{{ $group->students_count > 1 ? 's' : '' }}</p>
                </div>
                <a href="{{ route('formateur.groupes.whiteboard.show', $group) }}"
                   class="shrink-0 rounded-[6px] bg-red-100 px-2 py-1 text-[10px] font-bold text-[#E94D2A] hover:bg-red-200 transition">
                  Ouvrir
                </a>
              </div>
            @endforeach
          </div>
        </div>
      @else
        <div class="border-t border-gray-100 px-6 py-4">
          <p class="text-xs text-gray-500">
            Créez un <a href="{{ route('formateur.groupes.create') }}" class="text-[#E94D2A] hover:underline font-semibold">groupe</a> pour accéder à son tableau blanc.
          </p>
        </div>
      @endif
    </div>

    {{-- ── MUR DE QUESTIONS ANONYME ─────────────────────────────────── --}}
    <div class="flex flex-col bg-white rounded-[20px] shadow-md overflow-hidden">
      <div class="bg-indigo-600 px-6 py-5 flex items-center gap-3">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/20">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h8m-8 4h5m-9 5h16a2 2 0 002-2V7a2 2 0 00-2-2h-3.586a1 1 0 01-.707-.293l-1.414-1.414A1 1 0 0013.586 3h-3.172a1 1 0 00-.707.293L8.293 4.707A1 1 0 017.586 5H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
        </div>
        <h2 class="text-lg font-bold text-white">Mur de questions</h2>
      </div>
      <div class="flex-1 px-6 py-5 space-y-3">
        <p class="text-sm text-gray-600 leading-relaxed">
          Les stagiaires posent leurs questions avec ou sans anonymat. Le groupe vote pour les blocages prioritaires et vous qualifiez chaque question en direct.
        </p>
        <div class="flex flex-wrap gap-2 text-[11px]">
          <span class="rounded-full bg-green-100 px-2.5 py-0.5 font-semibold text-green-700">Presentiel</span>
          <span class="rounded-full bg-blue-100 px-2.5 py-0.5 font-semibold text-blue-700">Distanciel</span>
          <span class="rounded-full bg-indigo-100 px-2.5 py-0.5 font-semibold text-indigo-700">Priorisation</span>
        </div>
      </div>

      @if($recentQuestionWalls->isNotEmpty())
        <div class="border-t border-gray-100 px-6 py-4">
          <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Murs recents</p>
          <div class="space-y-2">
            @foreach($recentQuestionWalls as $wall)
              <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                  <p class="text-xs font-semibold text-gray-800 truncate">{{ $wall->title }}</p>
                  <p class="text-[10px] text-gray-400">
                    {{ $wall->questions_count }} question{{ $wall->questions_count > 1 ? 's' : '' }} · {{ $wall->group?->name }}
                  </p>
                </div>
                <a href="{{ route('formateur.questions.show', $wall) }}"
                   class="shrink-0 rounded-[6px] bg-indigo-100 px-2 py-1 text-[10px] font-bold text-indigo-700 hover:bg-indigo-200 transition">
                  Ouvrir
                </a>
              </div>
            @endforeach
          </div>
        </div>
      @endif

      <div class="border-t border-gray-100 px-6 py-4 mt-auto">
        <a href="{{ route('formateur.questions.index') }}"
           class="w-full inline-flex items-center justify-center gap-2 rounded-[10px] bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-700 transition">
          Gerer les murs de questions
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </a>
      </div>
    </div>

    {{-- ── ROUE ALÉATOIRE ─────────────────────────────────────────────── --}}
    <div class="flex flex-col bg-white rounded-[20px] shadow-md overflow-hidden">
      <div class="bg-violet-600 px-6 py-5 flex items-center gap-3">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/20">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
        </div>
        <h2 class="text-lg font-bold text-white">Roue aléatoire</h2>
      </div>
      <div class="flex-1 px-6 py-5 space-y-3">
        <p class="text-sm text-gray-600 leading-relaxed">
          Désignez un stagiaire au hasard d'un simple clic. La roue tourne en direct sur l'écran de chaque participant. Idéal pour les interrogations orales, les rapporteurs ou les brise-glace.
        </p>
        <div class="flex flex-wrap gap-2 text-[11px]">
          <span class="rounded-full bg-green-100 px-2.5 py-0.5 font-semibold text-green-700">Présentiel</span>
          <span class="rounded-full bg-blue-100 px-2.5 py-0.5 font-semibold text-blue-700">Distanciel</span>
          <span class="rounded-full bg-gray-100 px-2.5 py-0.5 font-semibold text-gray-500">Synchrone</span>
        </div>
      </div>
      <div class="border-t border-gray-100 px-6 py-4 mt-auto">
        <a href="{{ route('formateur.roue.index') }}"
           class="w-full inline-flex items-center justify-center gap-2 rounded-[10px] bg-violet-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-violet-700 transition">
          Gérer les roues
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </a>
      </div>
    </div>

  </div>

</div>
@endsection
