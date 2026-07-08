@extends('stagiaire.master')

@section('content')
<div class="max-w-[900px] mx-auto px-6 py-8" x-data="{ activeQ: 0 }">

  {{-- En-tête --}}
  <div class="bg-white rounded-[20px] shadow-md px-8 pt-5 pb-6 mb-6">
    <nav class="text-sm font-varela text-gray-500 mb-4">
      <ol class="inline-flex items-center space-x-1">
        <li>
          <a href="{{ route('stagiaire.dashboard') }}" class="text-orangeone hover:underline flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9.75L12 3l9 6.75V19a2 2 0 01-2 2h-4a1 1 0 01-1-1v-5H10v5a1 1 0 01-1 1H5a2 2 0 01-2-2V9.75z"/>
            </svg>
          </a>
        </li>
        <li><span class="mx-2 text-gray-400">/</span></li>
        <li><a href="{{ route('stagiaire.modules') }}" class="text-orangeone hover:underline">Mes formations</a></li>
        <li><span class="mx-2 text-gray-400">/</span></li>
        <li class="text-gray-400">Nuage de mots</li>
      </ol>
    </nav>

    <div class="flex items-start gap-4">
      <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
        </svg>
      </div>
      <div>
        <p class="font-raleway text-2xl text-bleuone leading-tight">{{ $item->wc_title }}</p>
        <p class="text-sm text-gray-500 mt-1">
          {{ count($questions) }} question{{ count($questions) > 1 ? 's' : '' }}
          @if($item->wc_duration)
            · {{ $item->wc_duration }} min estimée{{ $item->wc_duration > 1 ? 's' : '' }}
          @endif
        </p>
      </div>
    </div>

    {{-- Tabs questions (si plusieurs) --}}
    @if(count($questions) > 1)
      <div class="mt-5 flex flex-wrap gap-2">
        @foreach($questions as $qi => $q)
          <button
            type="button"
            @click="activeQ = {{ $qi }}"
            :class="activeQ === {{ $qi }}
              ? 'bg-amber-500 text-white border-amber-500'
              : 'bg-white text-gray-600 border-gray-300 hover:border-amber-400'"
            class="rounded-full border px-4 py-1.5 text-sm font-semibold transition"
          >
            Question {{ $qi + 1 }}
            @if($myAnswers->has($qi))
              <span class="ml-1.5 inline-flex h-2 w-2 rounded-full bg-green-400"></span>
            @endif
          </button>
        @endforeach
      </div>
    @endif
  </div>

  {{-- Blocs par question --}}
  @foreach($questions as $qi => $question)
    <div x-show="activeQ === {{ $qi }}" x-cloak
      x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0 scale-95"
      x-transition:enter-end="opacity-100 scale-100"
      x-transition:leave="transition ease-in duration-150"
      x-transition:leave-start="opacity-100 scale-100"
      x-transition:leave-end="opacity-0 scale-95"
      class="space-y-5">

      {{-- Carte question + formulaire --}}
      <div class="bg-white rounded-[20px] shadow-md px-8 py-6">
        <p class="font-varela text-lg text-gray-800 mb-4">{{ $question }}</p>

        @if(session('success') && request()->query('qi') == $qi)
          <div class="mb-4 rounded-[10px] bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
          </div>
        @endif

        @if($errors->has('answer') && old('question_index') == $qi)
          <div class="mb-4 rounded-[10px] bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
            {{ $errors->first('answer') }}
          </div>
        @endif

        <form method="POST" action="{{ route('stagiaire.wordcloud.parcours.submit', $item) }}?qi={{ $qi }}" class="flex gap-3">
          @csrf
          <input type="hidden" name="question_index" value="{{ $qi }}">
          <input
            type="text"
            name="answer"
            value="{{ old('answer', $myAnswers->get($qi, '')) }}"
            maxlength="150"
            placeholder="Saisir 1 à 3 mots…"
            class="flex-1 rounded-[10px] border border-gray-300 px-4 py-2.5 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200"
          >
          <button
            type="submit"
            class="inline-flex items-center gap-2 rounded-[10px] bg-amber-500 px-5 py-2.5 text-sm font-bold text-white hover:bg-amber-600 transition"
          >
            @if($myAnswers->has($qi))
              Modifier
            @else
              Envoyer
            @endif
          </button>
        </form>

        @if($myAnswers->has($qi))
          <p class="mt-2 text-xs text-gray-500">
            Ta réponse actuelle : <span class="font-semibold text-amber-700">{{ $myAnswers->get($qi) }}</span>
          </p>
        @endif
      </div>

    </div>
  @endforeach

</div>

<style>
  [x-cloak] { display: none !important; }
</style>
@endsection
