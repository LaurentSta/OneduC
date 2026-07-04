@extends('formateur.dashboard')

@section('formateur')
<div class="w-full px-6 lg:px-8" x-data="{ filtre: 'all' }">

  {{-- En-tête --}}
  <div class="rounded-[20px] border border-gray-100 bg-white shadow-md my-6">
    <div class="grid gap-6 px-6 py-6 md:px-8 md:py-7 lg:grid-cols-12 lg:items-center">
      <div class="lg:col-span-8">
        <x-oneduc.breadcrumb :items="[['label' => 'Accueil', 'url' => route('formateur.dashboard')], ['label' => 'Outils numériques']]" />

        <h1 class="font-raleway text-2xl font-medium leading-tight text-bleuone md:text-3xl">
          Outils numériques
        </h1>
        <p class="mt-0.5 font-varela text-base text-orangeone md:text-lg">
          Animez vos sessions en présentiel ou à distance.
        </p>
        <p class="mt-3 max-w-2xl font-lisible text-sm leading-relaxed text-slate-700">
          Nuages de mots, quiz en direct, tableau blanc collaboratif — tous vos outils d'animation interactifs réunis.
        </p>

        {{-- 📊 Statistiques --}}
        <div class="mt-4 flex flex-wrap gap-2 text-xs font-varela">
          <span class="inline-flex items-center gap-1.5 rounded-full border border-bleuone/15 bg-bleuone/5 px-3 py-1 text-bleuone">
            {{ $groups->count() }} groupes
          </span>
          <span class="inline-flex items-center gap-1.5 rounded-full border border-orangeone/20 bg-orangeone/10 px-3 py-1 text-orangeone">
            {{ $groups->sum('students_count') }} stagiaires
          </span>
        </div>
      </div>
      <div class="lg:col-span-4 flex justify-center lg:justify-end">
        <img src="{{ asset('images/svg/Modules.svg') }}" alt="Outils numériques" class="max-w-[220px] h-auto opacity-90">
      </div>
    </div>
  </div>

  {{-- Barre de filtres --}}
  <div class="bg-white rounded-[16px] shadow-sm px-5 py-3 mb-6 flex flex-wrap items-center gap-2">
    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider shrink-0 mr-1">Famille</span>

    <button @click="filtre = 'all'"
      :class="filtre === 'all' ? 'bg-bleuone text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
      class="rounded-full px-4 py-1.5 text-sm font-semibold transition">
      Tous les outils
    </button>

    <button @click="filtre = 'interaction'"
      :class="filtre === 'interaction' ? 'bg-bleuone text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
      class="rounded-full px-4 py-1.5 text-sm font-semibold transition">
      Interaction &amp; Feedback
    </button>

    <button @click="filtre = 'collaboration'"
      :class="filtre === 'collaboration' ? 'bg-bleuone text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
      class="rounded-full px-4 py-1.5 text-sm font-semibold transition">
      Collaboration
    </button>

    <button @click="filtre = 'animation'"
      :class="filtre === 'animation' ? 'bg-bleuone text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
      class="rounded-full px-4 py-1.5 text-sm font-semibold transition">
      Animation de session
    </button>
  </div>

  {{-- Grille des outils --}}
  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-6 mb-8">

    {{-- ── NUAGE DE MOTS ──────────────────────────────────────────────── --}}
    <div x-show="filtre === 'all' || filtre === 'interaction'" x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0 scale-95"
      x-transition:enter-end="opacity-100 scale-100"
      x-transition:leave="transition ease-in duration-150"
      x-transition:leave-start="opacity-100 scale-100"
      x-transition:leave-end="opacity-0 scale-95"
      class="flex flex-col bg-white rounded-[20px] shadow-md overflow-hidden">
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
    <div x-show="filtre === 'all' || filtre === 'interaction'" x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0 scale-95"
      x-transition:enter-end="opacity-100 scale-100"
      x-transition:leave="transition ease-in duration-150"
      x-transition:leave-start="opacity-100 scale-100"
      x-transition:leave-end="opacity-0 scale-95"
      class="flex flex-col bg-white rounded-[20px] shadow-md overflow-hidden">
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
    <div x-show="filtre === 'all' || filtre === 'collaboration'" x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0 scale-95"
      x-transition:enter-end="opacity-100 scale-100"
      x-transition:leave="transition ease-in duration-150"
      x-transition:leave-start="opacity-100 scale-100"
      x-transition:leave-end="opacity-0 scale-95"
      class="flex flex-col bg-white rounded-[20px] shadow-md overflow-hidden">
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
    <div x-show="filtre === 'all' || filtre === 'interaction'" x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0 scale-95"
      x-transition:enter-end="opacity-100 scale-100"
      x-transition:leave="transition ease-in duration-150"
      x-transition:leave-start="opacity-100 scale-100"
      x-transition:leave-end="opacity-0 scale-95"
      class="flex flex-col bg-white rounded-[20px] shadow-md overflow-hidden">
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

    {{-- ── SONDAGE ─────────────────────────────────────────────────────── --}}
    <div x-show="filtre === 'all' || filtre === 'interaction'" x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0 scale-95"
      x-transition:enter-end="opacity-100 scale-100"
      x-transition:leave="transition ease-in duration-150"
      x-transition:leave-start="opacity-100 scale-100"
      x-transition:leave-end="opacity-0 scale-95"
      class="flex flex-col bg-white rounded-[20px] shadow-md overflow-hidden">
      <div class="bg-teal-600 px-6 py-5 flex items-center gap-3">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/20">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
          </svg>
        </div>
        <h2 class="text-lg font-bold text-white">Sondage</h2>
      </div>
      <div class="flex-1 px-6 py-5 space-y-3">
        <p class="text-sm text-gray-600 leading-relaxed">
          Créez des questions à choix multiples dans vos parcours formateur. Les stagiaires répondent pendant l'activité et vous animez la session autour de leurs réponses.
        </p>
        <div class="flex flex-wrap gap-2 text-[11px]">
          <span class="rounded-full bg-green-100 px-2.5 py-0.5 font-semibold text-green-700">Présentiel</span>
          <span class="rounded-full bg-blue-100 px-2.5 py-0.5 font-semibold text-blue-700">Distanciel</span>
          <span class="rounded-full bg-teal-100 px-2.5 py-0.5 font-semibold text-teal-700">Interaction</span>
        </div>
      </div>

      @if($recentPolls->isNotEmpty())
        <div class="border-t border-gray-100 px-6 py-4">
          <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Sondages récents</p>
          <div class="space-y-2">
            @foreach($recentPolls as $poll)
              @php
                $firstQuestion = collect($poll->questions ?? [])->first();
              @endphp
              <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                  <p class="text-xs font-semibold text-gray-800 truncate">
                    {{ $firstQuestion['question'] ?? 'Sondage sans question' }}
                  </p>
                  <p class="text-[10px] text-gray-400 truncate">
                    {{ $poll->group?->name }} · {{ $poll->responses_count }} réponse{{ $poll->responses_count > 1 ? 's' : '' }}
                  </p>
                </div>
                <a href="{{ route('formateur.sondages.show', $poll) }}"
                   class="shrink-0 rounded-[6px] bg-teal-100 px-2 py-1 text-[10px] font-bold text-teal-700 hover:bg-teal-200 transition">
                  Ouvrir
                </a>
              </div>
            @endforeach
          </div>
        </div>
      @endif

      <div class="border-t border-gray-100 px-6 py-4 mt-auto">
        <a href="{{ route('formateur.sondages.index') }}"
           class="w-full inline-flex items-center justify-center gap-2 rounded-[10px] bg-teal-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-teal-700 transition">
          Gérer les sondages
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </a>
      </div>
    </div>

    {{-- ── ÉCHELLE DE POSITIONNEMENT ──────────────────────────────────── --}}
    <div x-show="filtre === 'all' || filtre === 'interaction'" x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0 scale-95"
      x-transition:enter-end="opacity-100 scale-100"
      x-transition:leave="transition ease-in duration-150"
      x-transition:leave-start="opacity-100 scale-100"
      x-transition:leave-end="opacity-0 scale-95"
      class="flex flex-col bg-white rounded-[20px] shadow-md overflow-hidden">
      <div class="bg-indigo-600 px-6 py-5 flex items-center gap-3">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/20">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 6h18M3 12h18M3 18h18"/>
          </svg>
        </div>
        <h2 class="text-lg font-bold text-white">Échelle de positionnement</h2>
      </div>
      <div class="flex-1 px-6 py-5 space-y-3">
        <p class="text-sm text-gray-600 leading-relaxed">
          Un curseur de 1 à 10 pour mesurer instantanément la perception ou le ressenti de chaque stagiaire. Visualisez la moyenne et la distribution en temps réel.
        </p>
        <div class="flex flex-wrap gap-2 text-[11px]">
          <span class="rounded-full bg-green-100 px-2.5 py-0.5 font-semibold text-green-700">Présentiel</span>
          <span class="rounded-full bg-blue-100 px-2.5 py-0.5 font-semibold text-blue-700">Distanciel</span>
          <span class="rounded-full bg-gray-100 px-2.5 py-0.5 font-semibold text-gray-500">Synchrone</span>
        </div>
        @if(isset($recentScales) && $recentScales->isNotEmpty())
          <div class="mt-3 space-y-2 border-t border-gray-100 pt-3">
            @foreach($recentScales as $scale)
              @php $firstQ = collect($scale->questions ?? [])->first(); @endphp
              <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                  <p class="text-xs font-semibold text-gray-800 truncate">{{ $firstQ['question'] ?? 'Échelle sans question' }}</p>
                  <p class="text-[10px] text-gray-400 truncate">
                    {{ $scale->group?->name }} · {{ $scale->responses_count }} réponse{{ $scale->responses_count > 1 ? 's' : '' }}
                  </p>
                </div>
                <a href="{{ route('formateur.echelle.show', $scale) }}"
                   class="shrink-0 rounded-[6px] bg-indigo-100 px-2 py-1 text-[10px] font-bold text-indigo-700 hover:bg-indigo-200 transition">
                  Ouvrir
                </a>
              </div>
            @endforeach
          </div>
        @endif
      </div>
      <div class="border-t border-gray-100 px-6 py-4 mt-auto">
        <a href="{{ route('formateur.echelle.index') }}"
           class="w-full inline-flex items-center justify-center gap-2 rounded-[10px] bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-indigo-700 transition">
          Gérer les échelles
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </a>
      </div>
    </div>

    {{-- ── ROUE ALÉATOIRE ─────────────────────────────────────────────── --}}
    <div x-show="filtre === 'all' || filtre === 'animation'" x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0 scale-95"
      x-transition:enter-end="opacity-100 scale-100"
      x-transition:leave="transition ease-in duration-150"
      x-transition:leave-start="opacity-100 scale-100"
      x-transition:leave-end="opacity-0 scale-95"
      class="flex flex-col bg-white rounded-[20px] shadow-md overflow-hidden">
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

    {{-- ── MINUTEUR COLLABORATIF ──────────────────────────────────────── --}}
    @if(config('outils.minuteur.enabled'))
    <div x-show="filtre === 'all' || filtre === 'animation'" x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0 scale-95"
      x-transition:enter-end="opacity-100 scale-100"
      x-transition:leave="transition ease-in duration-150"
      x-transition:leave-start="opacity-100 scale-100"
      x-transition:leave-end="opacity-0 scale-95"
      class="flex flex-col bg-white rounded-[20px] shadow-md overflow-hidden">
      <div class="bg-rose-600 px-6 py-5 flex items-center gap-3">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/20">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <h2 class="text-lg font-bold text-white">Minuteur</h2>
      </div>
      <div class="flex-1 px-6 py-5 space-y-3">
        <p class="text-sm text-gray-600 leading-relaxed">
          Lancez un compte à rebours visible en temps réel par tous les stagiaires du groupe. Présets Pomodoro, pause, exercice — avec contrôle total pour le formateur.
        </p>
        <div class="flex flex-wrap gap-2 text-[11px]">
          <span class="rounded-full bg-green-100 px-2.5 py-0.5 font-semibold text-green-700">Présentiel</span>
          <span class="rounded-full bg-blue-100 px-2.5 py-0.5 font-semibold text-blue-700">Distanciel</span>
          <span class="rounded-full bg-rose-100 px-2.5 py-0.5 font-semibold text-rose-700">Synchrone</span>
        </div>
      </div>

      @if($groups->isNotEmpty())
        <div class="border-t border-gray-100 px-6 py-4">
          <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Mes groupes</p>
          <div class="space-y-2">
            @foreach($groups->take(4) as $group)
              <div class="flex items-center justify-between gap-2">
                <p class="text-xs font-semibold text-gray-800 truncate">{{ $group->name }}</p>
                <a href="{{ route('formateur.groupes.timer.show', $group) }}"
                   class="shrink-0 rounded-[6px] bg-rose-100 px-2 py-1 text-[10px] font-bold text-rose-700 hover:bg-rose-200 transition">
                  Ouvrir
                </a>
              </div>
            @endforeach
          </div>
        </div>
      @else
        <div class="border-t border-gray-100 px-6 py-4">
          <p class="text-xs text-gray-500">
            Créez un <a href="{{ route('formateur.groupes.create') }}" class="text-rose-600 hover:underline font-semibold">groupe</a> pour accéder au minuteur.
          </p>
        </div>
      @endif
    </div>
    @endif

    {{-- ── MES MODULES (MODULE BUILDER) ───────────────────────────────── --}}
    <div x-show="filtre === 'all'" x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0 scale-95"
      x-transition:enter-end="opacity-100 scale-100"
      x-transition:leave="transition ease-in duration-150"
      x-transition:leave-start="opacity-100 scale-100"
      x-transition:leave-end="opacity-0 scale-95"
      class="flex flex-col bg-white rounded-[20px] shadow-md overflow-hidden">
      <div class="bg-emerald-600 px-6 py-5 flex items-center gap-3">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/20">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s4.832.477 6 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
          </svg>
        </div>
        <h2 class="text-lg font-bold text-white">Mes modules</h2>
      </div>
      <div class="flex-1 px-6 py-5 space-y-3">
        <p class="text-sm text-gray-600 leading-relaxed">
          Créez vos propres modules de formation (chapitres et leçons en texte riche) et assignez-les à vos groupes, sans passer par le catalogue admin.
        </p>
        <div class="flex flex-wrap gap-2 text-[11px]">
          <span class="rounded-full bg-green-100 px-2.5 py-0.5 font-semibold text-green-700">Présentiel</span>
          <span class="rounded-full bg-blue-100 px-2.5 py-0.5 font-semibold text-blue-700">Distanciel</span>
          <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 font-semibold text-emerald-700">Asynchrone</span>
        </div>
      </div>

      @if($recentModules->isNotEmpty())
        <div class="border-t border-gray-100 px-6 py-4">
          <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Modules récents</p>
          <div class="space-y-2">
            @foreach($recentModules as $module)
              <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                  <p class="text-xs font-semibold text-gray-800 truncate">{{ $module->module_title }}</p>
                  <p class="text-[10px] text-gray-400">
                    {{ $module->sections_count }} chapitre(s) · {{ $module->groups_count }} groupe(s)
                  </p>
                </div>
                <a href="{{ route('formateur.modules.builder.edit', $module) }}"
                   class="shrink-0 rounded-[6px] bg-emerald-100 px-2 py-1 text-[10px] font-bold text-emerald-700 hover:bg-emerald-200 transition">
                  Ouvrir
                </a>
              </div>
            @endforeach
          </div>
        </div>
      @endif

      <div class="border-t border-gray-100 px-6 py-4 mt-auto">
        <a href="{{ route('formateur.modules.builder.index') }}"
           class="w-full inline-flex items-center justify-center gap-2 rounded-[10px] bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-700 transition">
          Gérer mes modules
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </a>
      </div>
    </div>

    {{-- ── PAGE COLLABORATIVE (HEDGEDOC) ─────────────────────────────── --}}
    <div x-show="filtre === 'all' || filtre === 'collaboration'" x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0 scale-95"
      x-transition:enter-end="opacity-100 scale-100"
      x-transition:leave="transition ease-in duration-150"
      x-transition:leave-start="opacity-100 scale-100"
      x-transition:leave-end="opacity-0 scale-95"
      class="flex flex-col bg-white rounded-[20px] shadow-md overflow-hidden">
      <div class="bg-cyan-600 px-6 py-5 flex items-center gap-3">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/20">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <h2 class="text-lg font-bold text-white">Page collaborative</h2>
      </div>
      <div class="flex-1 px-6 py-5 space-y-3">
        <p class="text-sm text-gray-600 leading-relaxed">
          Éditez un document Markdown à plusieurs en temps réel (texte, tableaux, images, listes, etc.), avec partage d'un lien d'édition et d'un lien lecture seule.
        </p>
        <div class="flex flex-wrap gap-2 text-[11px]">
          <span class="rounded-full bg-green-100 px-2.5 py-0.5 font-semibold text-green-700">Présentiel</span>
          <span class="rounded-full bg-blue-100 px-2.5 py-0.5 font-semibold text-blue-700">Distanciel</span>
          <span class="rounded-full bg-cyan-100 px-2.5 py-0.5 font-semibold text-cyan-700">Markdown</span>
        </div>
      </div>
      <div class="border-t border-gray-100 px-6 py-4 mt-auto">
        <a href="{{ route('formateur.pages-collaboratives.index') }}"
           class="w-full inline-flex items-center justify-center gap-2 rounded-[10px] bg-cyan-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-cyan-700 transition">
          Ouvrir l'outil
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </a>
      </div>
    </div>

  </div>

</div>
@endsection
