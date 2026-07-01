@extends('formateur.dashboard')

@section('formateur')
<div class="w-full px-6 lg:px-8">

  {{-- En-tête --}}
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-5 pb-6 my-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
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
        <p class="font-raleway text-2xl text-bleuone">Nuages de mots</p>
        <p class="text-sm text-gray-500 mt-1">Créez un nuage par groupe. Les stagiaires soumettent leurs mots en direct.</p>
      </div>
    </div>
  </header>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

    {{-- Formulaire création --}}
    <div class="lg:col-span-1">
      <div class="bg-white rounded-[20px] shadow-md p-6 sticky top-6">
        <p class="font-varela text-base font-bold text-bleuone mb-4">Nouveau nuage de mots</p>

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
              x-data="{ questions: {{ old('questions') ? json_encode(old('questions')) : "['']" }} }">
          @csrf

          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Groupe</label>
            <select name="group_id" required
                    class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200">
              <option value="">Choisir un groupe…</option>
              @foreach($groups as $group)
                <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>
                  {{ $group->name }}
                </option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Titre</label>
            <input type="text" name="title" value="{{ old('title') }}" required maxlength="255"
                   placeholder="Ex : Qu'évoque la formation pour vous ?"
                   class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200">
          </div>

          {{-- Questions dynamiques --}}
          <div class="space-y-2">
            <div class="flex items-center justify-between mb-1">
              <label class="text-xs font-semibold text-gray-600">Questions <span class="text-gray-400">(max 10)</span></label>
              <button type="button"
                      x-show="questions.length < 10"
                      x-transition:enter="transition ease-out duration-200"
                      x-transition:enter-start="opacity-0 scale-95"
                      x-transition:enter-end="opacity-100 scale-100"
                      x-transition:leave="transition ease-in duration-150"
                      x-transition:leave-start="opacity-100 scale-100"
                      x-transition:leave-end="opacity-0 scale-95"
                      @click="questions.push('')"
                      class="inline-flex items-center gap-1 rounded-[6px] border border-amber-300 bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700 hover:bg-amber-100 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Ajouter
              </button>
            </div>

            <template x-for="(q, index) in questions" :key="index">
              <div class="flex gap-2 items-start">
                <div class="flex h-6 w-6 shrink-0 mt-2.5 items-center justify-center rounded-full bg-amber-100 text-amber-700 text-[10px] font-bold"
                     x-text="index + 1"></div>
                <textarea
                  :name="`questions[${index}]`"
                  x-model="questions[index]"
                  rows="2"
                  required
                  maxlength="500"
                  placeholder="Posez une question ouverte…"
                  class="flex-1 rounded-[10px] border border-gray-300 px-3 py-2 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200 resize-none"></textarea>
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
                  class="w-full inline-flex items-center justify-center gap-2 rounded-[10px] bg-amber-500 px-4 py-2.5 text-sm font-bold text-white hover:bg-amber-600 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Lancer le nuage de mots
          </button>
        </form>
      </div>
    </div>

    {{-- Liste des nuages --}}
    <div class="lg:col-span-2 space-y-4">
      @forelse($wordClouds as $wc)
        <div class="bg-white rounded-[20px] shadow-md p-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div class="flex items-start gap-3 min-w-0">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $wc->is_active ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-400' }}">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
              </svg>
            </div>
            <div class="min-w-0">
              <div class="flex flex-wrap items-center gap-2 mb-0.5">
                <p class="text-sm font-bold text-gray-900 truncate">{{ $wc->title }}</p>
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold
                  {{ $wc->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                  {{ $wc->is_active ? 'Actif' : 'Fermé' }}
                </span>
              </div>
              <p class="text-xs text-gray-500 truncate">{{ $wc->question }}</p>
              <p class="text-[10px] text-gray-400 mt-1">
                Groupe : <span class="font-semibold">{{ $wc->group?->name ?? '—' }}</span>
                · Code : <span class="font-mono font-semibold text-amber-600">{{ $wc->access_code }}</span>
              </p>
            </div>
          </div>
          <div class="flex gap-2 shrink-0">
            <a href="{{ route('formateur.nuages.live', $wc) }}"
               class="inline-flex items-center gap-1.5 rounded-[8px] bg-amber-500 px-3 py-1.5 text-xs font-bold text-white hover:bg-amber-600 transition">
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
          </div>
        </div>
      @empty
        <div class="flex flex-col items-center justify-center rounded-[20px] border-2 border-dashed border-gray-200 bg-white py-16 text-center">
          <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-500">
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
