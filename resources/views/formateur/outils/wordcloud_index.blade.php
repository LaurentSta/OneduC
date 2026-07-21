@extends('formateur.dashboard')

@section('formateur')
<div class="w-full px-6 lg:px-8">

  {{-- En-tête --}}
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-5 pb-6 my-6">
    <div class="flex flex-wrap items-center justify-between gap-6">
      <div>
        <nav class="text-sm font-varela text-gray-500 mb-2">
          <ol class="inline-flex items-center space-x-1">
            <li>
              <a href="{{ route('formateur.outils.index') }}" class="text-orangeone hover:underline">Outils numériques</a>
            </li>
            <li><span class="mx-2 text-gray-400">/</span></li>
            <li class="text-gray-400">Nuage de mots</li>
          </ol>
        </nav>
        <div class="flex items-center gap-1.5">
          <p class="font-raleway text-2xl text-bleuone">Nuages de mots</p>
          <span class="relative inline-flex group">
            <svg xmlns="http://www.w3.org/2000/svg" tabindex="0" class="h-4 w-4 text-gray-400 hover:text-bleuone focus:text-bleuone outline-none cursor-help" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="pointer-events-none absolute left-0 top-full z-20 mt-2 hidden w-80 -translate-x-1 rounded-lg border border-slate-200 bg-white px-3 py-2 text-[11px] leading-4 text-slate-700 shadow-lg group-hover:block group-focus-within:block">
              <strong class="text-bleuone">Définition —</strong> un nuage de mots (ou « tag cloud ») est une représentation visuelle d'un ensemble de mots-clés dans laquelle la taille de chaque mot est proportionnelle à sa fréquence : plus un mot est cité par le groupe, plus il apparaît grand.
              <br><br>
              <strong class="text-bleuone">Utilisation —</strong> posez une question ouverte, les stagiaires répondent en direct depuis leur appareil et le nuage se met à jour à l'écran. Idéal en brise-glace, en sondage d'opinion ou en synthèse pour faire ressortir le vocabulaire dominant d'un groupe.
            </span>
          </span>
        </div>
        <p class="text-sm text-gray-600 mt-1 max-w-xl">Un nuage de mots est une représentation visuelle de mots-clés dans laquelle la taille de chaque mot reflète sa fréquence : plus il est cité par le groupe, plus il apparaît grand.</p>
        <p class="text-sm text-gray-500 mt-1">Trois étapes : diffusion, questions, lancement.</p>
      </div>
      <img src="{{ asset('images/svg/NuageDeMots.svg') }}" alt="Nuages de mots" class="hidden sm:block w-48 h-auto shrink-0">
    </div>
  </header>

  @if(session('success'))
    <div class="mb-4 rounded-[14px] bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
      {{ session('success') }}
    </div>
  @endif

  {{-- ── Assistant de création (pas-à-pas) ───────────────────────── --}}
  <div class="bg-white rounded-[20px] shadow-md p-8 max-w-xl mx-auto mb-8"
       x-data="{
         step: 1,
         groups: {{ Js::from($groups->map(fn ($g) => ['id' => (string) $g->id, 'name' => $g->name])->values()) }},
         mode: '',
         groupId: '',
         groupLabel: '',
         title: '{{ old('title', '') }}',
         questions: {{ old('questions') ? Js::from(old('questions')) : Js::from(['']) }},
         selectGroup(id, name) { this.mode = 'lancer'; this.groupId = id; this.groupLabel = name; },
         selectModele() { this.mode = 'modele'; this.groupId = ''; this.groupLabel = ''; },
         addQuestion() { if (this.questions.length < 10) this.questions.push(''); },
         removeQuestion(i) { if (this.questions.length > 1) this.questions.splice(i, 1); },
       }">

    @if($errors->any())
      <div class="mb-6 rounded-[10px] bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
        {{ $errors->first() }}
      </div>
    @endif

    {{-- Puces de progression --}}
    <div class="flex items-center justify-center gap-2 mb-8 text-xs font-semibold">
      <span class="flex items-center gap-1.5" :class="step >= 1 ? 'text-bleuone' : 'text-gray-400'">
        <span class="w-2 h-2 rounded-full" :class="step > 1 ? 'bg-emerald-500' : (step === 1 ? 'bg-bleuone ring-4 ring-bleuone/15' : 'bg-gray-300')"></span>
        Diffusion
      </span>
      <span class="w-6 h-px bg-gray-200"></span>
      <span class="flex items-center gap-1.5" :class="step >= 2 ? 'text-bleuone' : 'text-gray-400'">
        <span class="w-2 h-2 rounded-full" :class="step > 2 ? 'bg-emerald-500' : (step === 2 ? 'bg-bleuone ring-4 ring-bleuone/15' : 'bg-gray-300')"></span>
        Questions
      </span>
      <span class="w-6 h-px bg-gray-200"></span>
      <span class="flex items-center gap-1.5" :class="step >= 3 ? 'text-bleuone' : 'text-gray-400'">
        <span class="w-2 h-2 rounded-full" :class="step === 3 ? 'bg-bleuone ring-4 ring-bleuone/15' : 'bg-gray-300'"></span>
        Lancer
      </span>
    </div>

    <form method="POST" action="{{ route('formateur.nuages.store') }}">
      @csrf
      <input type="hidden" name="group_id" :value="mode === 'lancer' ? groupId : ''">

      {{-- Étape 1 : Diffusion --}}
      <div x-show="step === 1" x-cloak>
        <h2 class="text-lg font-bold text-bleuone text-center mb-1">Pour qui ?</h2>
        <p class="text-xs text-gray-500 text-center mb-5">Lancez pour un groupe maintenant, ou préparez un modèle réutilisable plus tard.</p>

        <div class="space-y-2">
          @forelse($groups as $group)
            <button type="button" @click="selectGroup('{{ $group->id }}', '{{ $group->name }}')"
                    :class="mode === 'lancer' && groupId === '{{ $group->id }}' ? 'border-bleuone bg-bleuone/5' : 'border-gray-200 hover:border-gray-300'"
                    class="w-full flex items-center justify-between rounded-xl border-2 px-4 py-3 text-left transition">
              <span class="text-sm font-semibold text-gray-800">{{ $group->name }}</span>
              <span class="text-xs text-gray-400">Disponible tout de suite</span>
            </button>
          @empty
            <p class="text-xs text-amber-600 mb-2">Vous n'avez pas encore de groupe — vous pouvez tout de même créer un modèle.</p>
          @endforelse

          <button type="button" @click="selectModele()"
                  :class="mode === 'modele' ? 'border-violet-400 bg-violet-50' : 'border-gray-200 hover:border-gray-300'"
                  class="w-full flex items-center justify-between rounded-xl border-2 px-4 py-3 text-left transition">
            <span class="text-sm font-semibold text-gray-800">Créer un modèle (sans groupe)</span>
            <span class="text-xs text-gray-400">À réutiliser dans un parcours</span>
          </button>
        </div>

        <div class="flex justify-end mt-6">
          <button type="button" @click="step = 2" :disabled="mode === ''"
                  :class="mode === '' ? 'bg-gray-300 cursor-not-allowed' : 'bg-bleuone hover:bg-bleuone-light'"
                  class="rounded-[10px] px-5 py-2.5 text-sm font-bold text-white transition">
            Continuer →
          </button>
        </div>
      </div>

      {{-- Étape 2 : Questions --}}
      <div x-show="step === 2" x-cloak>
        <h2 class="text-lg font-bold text-bleuone text-center mb-1">Vos questions</h2>
        <p class="text-xs text-gray-500 text-center mb-5">
          Diffusion : <strong x-text="mode === 'lancer' ? groupLabel : 'Modèle (sans groupe)'"></strong>
        </p>

        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Titre</label>
          <input type="text" name="title" x-model="title" required maxlength="255"
                 placeholder="Ex : Qu'évoque la formation pour vous ?"
                 class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-bleuone focus:outline-none focus:ring-2 focus:ring-bleuone/15">
        </div>

        <div class="space-y-2 mt-4">
          <div class="flex items-center justify-between mb-1">
            <label class="text-xs font-semibold text-gray-600">Questions <span class="text-gray-400">(max 10)</span></label>
            <button type="button" x-show="questions.length < 10" @click="addQuestion()"
                    class="inline-flex items-center gap-1 rounded-[6px] border border-bleuone/20 bg-bleuone/5 px-2 py-1 text-xs font-semibold text-bleuone hover:bg-bleuone hover:text-white transition">
              + Ajouter
            </button>
          </div>

          <template x-for="(q, index) in questions" :key="index">
            <div class="flex gap-2 items-start">
              <div class="flex h-6 w-6 shrink-0 mt-2.5 items-center justify-center rounded-full bg-orangeone/10 text-orangeone text-[10px] font-bold" x-text="index + 1"></div>
              <textarea :name="`questions[${index}]`" x-model="questions[index]" rows="2" required maxlength="500"
                        placeholder="Posez une question ouverte…"
                        class="flex-1 rounded-[10px] border border-gray-300 px-3 py-2 text-sm focus:border-bleuone focus:outline-none focus:ring-2 focus:ring-bleuone/15 resize-none"></textarea>
              <button type="button" x-show="questions.length > 1" @click="removeQuestion(index)"
                      class="mt-2 flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-red-200 bg-white text-red-400 hover:bg-red-50 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
          </template>
        </div>

        <div class="flex items-center justify-between mt-6">
          <button type="button" @click="step = 1" class="text-sm font-semibold text-bleuone hover:underline">← Diffusion</button>
          <button type="button" @click="step = 3" :disabled="!title"
                  :class="!title ? 'bg-gray-300 cursor-not-allowed' : 'bg-bleuone hover:bg-bleuone-light'"
                  class="rounded-[10px] px-5 py-2.5 text-sm font-bold text-white transition">
            Continuer →
          </button>
        </div>
      </div>

      {{-- Étape 3 : Lancer --}}
      <div x-show="step === 3" x-cloak>
        <h2 class="text-lg font-bold text-bleuone text-center mb-1" x-text="mode === 'lancer' ? 'Prêt à lancer' : 'Prêt à enregistrer'"></h2>
        <p class="text-xs text-gray-500 text-center mb-5">Vérifiez avant de continuer.</p>

        <div class="rounded-xl border border-gray-200 divide-y divide-gray-100 mb-6">
          <div class="flex items-center justify-between px-4 py-3">
            <span class="text-xs font-semibold text-gray-500">Diffusion</span>
            <span class="text-sm font-bold text-gray-800" x-text="mode === 'lancer' ? groupLabel : 'Modèle (sans groupe)'"></span>
          </div>
          <div class="flex items-center justify-between px-4 py-3">
            <span class="text-xs font-semibold text-gray-500">Titre</span>
            <span class="text-sm font-bold text-gray-800" x-text="title"></span>
          </div>
          <div class="flex items-center justify-between px-4 py-3">
            <span class="text-xs font-semibold text-gray-500">Questions</span>
            <span class="text-sm font-bold text-gray-800" x-text="questions.length"></span>
          </div>
        </div>

        <div class="flex items-center justify-between">
          <button type="button" @click="step = 2" class="text-sm font-semibold text-bleuone hover:underline">← Questions</button>
          <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-[10px] bg-orangeone px-6 py-2.5 text-sm font-bold text-white hover:bg-orangeone-hover transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span x-text="mode === 'lancer' ? 'Lancer le nuage de mots' : 'Enregistrer le modèle'"></span>
          </button>
        </div>
      </div>
    </form>
  </div>

  {{-- ── Mes nuages de mots ──────────────────────────────────────── --}}
  <div class="space-y-4">
    @forelse($wordClouds as $wc)
      @php $isDraft = $wc->group_id === null; @endphp
      <div class="bg-white rounded-[20px] shadow-md p-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-3 min-w-0">
          <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $wc->is_active ? 'bg-bleuone/10 text-bleuone' : 'bg-gray-100 text-gray-400' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
            </svg>
          </div>
          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-0.5">
              <p class="text-sm font-bold text-gray-900 truncate">{{ $wc->title }}</p>
              <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold
                {{ $isDraft ? 'bg-violet-100 text-violet-700' : ($wc->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500') }}">
                {{ $isDraft ? 'Modèle (parcours)' : ($wc->is_active ? 'Actif' : 'Fermé') }}
              </span>
            </div>
            <p class="text-xs text-gray-500 truncate">{{ $wc->question }}</p>
            <p class="text-[10px] text-gray-400 mt-1">
              @if($isDraft)
                Pas encore lancé pour un groupe
              @else
                Groupe : <span class="font-semibold">{{ $wc->group?->name }}</span>
                · Code : <span class="font-mono font-semibold text-orangeone">{{ $wc->access_code }}</span>
              @endif
            </p>
          </div>
        </div>
        <div class="flex gap-2 shrink-0">
          @unless($isDraft)
            <a href="{{ route('formateur.nuages.live', $wc) }}"
               class="inline-flex items-center gap-1.5 rounded-[8px] bg-bleuone px-3 py-1.5 text-xs font-bold text-white hover:bg-bleuone-light transition">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
              Voir en direct
            </a>
            <a href="{{ route('wordcloud.join.code', $wc->access_code) }}"
               target="_blank"
               class="inline-flex items-center gap-1.5 rounded-[8px] border border-gray-300 bg-white px-3 py-1.5 text-xs font-bold text-gray-600 hover:bg-gray-50 transition">
              Lien stagiaire
            </a>
          @endunless
          <form method="POST"
                action="{{ route('formateur.nuages.destroy', $wc) }}"
                onsubmit="return confirm('Supprimer ce nuage de mots ? Les réponses associées seront supprimées.');">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="inline-flex items-center gap-1.5 rounded-[8px] border border-red-200 bg-white px-3 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50 transition">
              Supprimer
            </button>
          </form>
        </div>
      </div>
    @empty
      <div class="flex flex-col items-center justify-center rounded-[20px] border-2 border-dashed border-gray-200 bg-white py-16 text-center">
        <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-bleuone/10 text-bleuone">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
          </svg>
        </div>
        <p class="text-sm font-semibold text-gray-700">Aucun nuage de mots créé</p>
        <p class="text-xs text-gray-400 mt-1">Utilisez l'assistant ci-dessus pour créer le premier.</p>
      </div>
    @endforelse
  </div>

</div>
@endsection
