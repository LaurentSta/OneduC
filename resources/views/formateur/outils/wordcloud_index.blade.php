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
        <p class="text-sm text-gray-500 mt-1">Préparez une ou plusieurs questions. Les stagiaires les découvrent ensuite une par une.</p>
      </div>
      <img src="{{ asset('images/svg/NuageDeMots.svg') }}" alt="Nuages de mots" class="hidden sm:block w-48 h-auto shrink-0">
    </div>
  </header>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

    {{-- Formulaire création --}}
    <div class="lg:col-span-1">
      <div class="bg-white rounded-[20px] shadow-md p-6 sticky top-6">
        <div class="mb-4 flex items-center gap-1.5">
          <p class="font-varela text-base font-bold text-bleuone">Nouveau nuage de mots</p>
          <span class="relative inline-flex group">
            <svg xmlns="http://www.w3.org/2000/svg" tabindex="0" class="h-4 w-4 text-gray-400 hover:text-bleuone focus:text-bleuone outline-none cursor-help" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="pointer-events-none absolute left-1/2 top-full z-20 mt-2 hidden w-60 -translate-x-1/2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-[11px] leading-4 text-slate-700 shadow-lg group-hover:block group-focus-within:block">
              Le premier écran stagiaire s’ouvre sur la question 1. Vous pilotez la suite depuis le live.
            </span>
          </span>
        </div>

        @if(session('success'))
          <div class="mb-4 rounded-[10px] bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
          </div>
        @endif

        @if($errors->any())
          <div class="mb-4 rounded-[10px] bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
          </div>
        @endif

        <form method="POST" action="{{ route('formateur.nuages.store') }}" class="space-y-4"
              x-data="{
                questions: {{ old('questions') ? json_encode(old('questions')) : "['']" }},
                groupId: '{{ old('group_id', '') }}',
                mode: '{{ old('group_id') || $groups->isNotEmpty() ? 'lancer' : 'modele' }}'
              }">
          @csrf

          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-2">Que voulez-vous faire ?</label>
            <div class="grid grid-cols-2 gap-2">
              <button type="button" @click="mode = 'lancer'"
                      {{ $groups->isEmpty() ? 'disabled' : '' }}
                      :class="mode === 'lancer' ? 'border-bleuone bg-bleuone/5' : 'border-gray-200 hover:border-gray-300'"
                      class="rounded-[10px] border-2 px-3 py-2.5 text-left transition disabled:cursor-not-allowed disabled:opacity-40">
                <span class="block text-sm font-bold" :class="mode === 'lancer' ? 'text-bleuone' : 'text-gray-700'">Lancer pour un groupe</span>
                <span class="block text-[11px] text-gray-500 mt-0.5">
                  @if($groups->isEmpty())
                    Créez d'abord un groupe
                  @else
                    Disponible tout de suite pour vos stagiaires
                  @endif
                </span>
              </button>
              <button type="button" @click="mode = 'modele'; groupId = ''"
                      :class="mode === 'modele' ? 'border-bleuone bg-bleuone/5' : 'border-gray-200 hover:border-gray-300'"
                      class="rounded-[10px] border-2 px-3 py-2.5 text-left transition">
                <span class="block text-sm font-bold" :class="mode === 'modele' ? 'text-bleuone' : 'text-gray-700'">Créer un modèle</span>
                <span class="block text-[11px] text-gray-500 mt-0.5">À réutiliser plus tard dans un parcours</span>
              </button>
            </div>
          </div>

          <div x-show="mode === 'lancer'" x-cloak>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Groupe</label>
            <select name="group_id" x-model="groupId" :required="mode === 'lancer'"
                    class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-bleuone focus:outline-none focus:ring-2 focus:ring-bleuone/15">
              <option value="">Choisir un groupe…</option>
              @foreach($groups as $group)
                <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>
                  {{ $group->name }}
                </option>
              @endforeach
            </select>
          </div>

          <div x-show="mode === 'modele'" x-cloak class="rounded-[10px] border border-violet-200 bg-violet-50 px-3 py-2.5 text-[11px] text-violet-700">
            Ce nuage sera enregistré comme modèle, disponible dans le catalogue de vos parcours. Vous pourrez le lancer pour un groupe plus tard.
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Titre</label>
            <input type="text" name="title" value="{{ old('title') }}" required maxlength="255"
                   placeholder="Ex : Qu'évoque la formation pour vous ?"
                   class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-bleuone focus:outline-none focus:ring-2 focus:ring-bleuone/15">
          </div>

          {{-- Questions dynamiques --}}
          <div class="space-y-2">
            <div class="flex items-center justify-between mb-1">
              <label class="flex items-center gap-1 text-xs font-semibold text-gray-600">
                Questions <span class="text-gray-400">(max 10)</span>
                <span class="relative inline-flex group">
                  <svg xmlns="http://www.w3.org/2000/svg" tabindex="0" class="h-3.5 w-3.5 text-gray-400 hover:text-bleuone focus:text-bleuone outline-none cursor-help" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  <span class="pointer-events-none absolute left-0 top-full z-20 mt-2 hidden w-56 rounded-lg border border-slate-200 bg-white px-3 py-2 text-[11px] leading-4 text-slate-700 shadow-lg group-hover:block group-focus-within:block">
                    Les stagiaires découvrent vos questions une par une, dans l'ordre que vous choisissez pendant le live.
                  </span>
                </span>
              </label>
              <button type="button"
                      x-show="questions.length < 10"
                      x-transition:enter="transition ease-out duration-200"
                      x-transition:enter-start="opacity-0 scale-95"
                      x-transition:enter-end="opacity-100 scale-100"
                      x-transition:leave="transition ease-in duration-150"
                      x-transition:leave-start="opacity-100 scale-100"
                      x-transition:leave-end="opacity-0 scale-95"
                      @click="questions.push('')"
                      class="inline-flex items-center gap-1 rounded-[6px] border border-bleuone/20 bg-bleuone/5 px-2 py-1 text-xs font-semibold text-bleuone hover:bg-bleuone hover:text-white transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Ajouter
              </button>
            </div>

            <template x-for="(q, index) in questions" :key="index">
              <div class="flex gap-2 items-start">
                <div class="flex h-6 w-6 shrink-0 mt-2.5 items-center justify-center rounded-full bg-orangeone/10 text-orangeone text-[10px] font-bold"
                     x-text="index + 1"></div>
                <textarea
                  :name="`questions[${index}]`"
                  x-model="questions[index]"
                  rows="2"
                  required
                  maxlength="500"
                  placeholder="Posez une question ouverte…"
                  class="flex-1 rounded-[10px] border border-gray-300 px-3 py-2 text-sm focus:border-bleuone focus:outline-none focus:ring-2 focus:ring-bleuone/15 resize-none"></textarea>
                <button type="button"
                        x-show="questions.length > 1"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        @click="questions.splice(index, 1)"
                        class="mt-2 flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-red-200 bg-white text-red-400 hover:bg-red-50 transition">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>
            </template>
          </div>

          <button type="submit"
                  class="w-full inline-flex items-center justify-center gap-2 rounded-[10px] bg-orangeone px-4 py-2.5 text-sm font-bold text-white hover:bg-orangeone-hover transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span x-text="mode === 'lancer' ? 'Lancer le nuage de mots' : 'Enregistrer le modèle'"></span>
          </button>
        </form>
      </div>
    </div>

    {{-- Liste des nuages --}}
    <div class="lg:col-span-2 space-y-4">
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
          <p class="text-xs text-gray-400 mt-1">Utilisez le formulaire pour lancer votre premier nuage.</p>
        </div>
      @endforelse
    </div>

  </div>
</div>
@endsection
