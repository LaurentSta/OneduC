<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $wordCloud->title }} — Nuage de mots</title>
  @vite(['resources/css/app.css'])
  <style>
    [x-cloak] { display:none !important; }
  </style>
</head>
<body class="min-h-screen bg-slate-100 p-4" x-data="{ activeQ: {{ session('answered_qi', old('question_index', 0)) }} }">

  <div class="mx-auto w-full max-w-2xl space-y-4">

    {{-- En-tête --}}
    <div class="rounded-2xl bg-white shadow-lg p-6">
      <p class="text-xs uppercase tracking-wide text-gray-400">Code · {{ $wordCloud->access_code }}</p>
      <h1 class="text-xl font-bold text-[#004461] mt-1">{{ $wordCloud->title }}</h1>

      @if(!$wordCloud->is_active)
        <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700">
          Cette session est actuellement fermée.
        </div>
      @endif

      {{-- Onglets si plusieurs questions --}}
      @if(count($questions) > 1)
        <div class="mt-4 flex flex-wrap gap-2">
          @foreach($questions as $qi => $q)
            <button type="button"
                    @click="activeQ = {{ $qi }}"
                    :class="activeQ === {{ $qi }}
                      ? 'bg-[#004461] text-white border-[#004461]'
                      : 'bg-white text-gray-600 border-gray-300 hover:border-[#004461]'"
                    class="rounded-full border px-4 py-1.5 text-sm font-semibold transition">
              Question {{ $qi + 1 }}
            </button>
          @endforeach
        </div>
      @endif
    </div>

    {{-- Bloc par question --}}
    @foreach($questions as $qi => $question)
      <div x-show="activeQ === {{ $qi }}" x-cloak class="space-y-4">

        {{-- Formulaire --}}
        <div class="rounded-2xl bg-white shadow-lg p-6">
          <p class="text-gray-700 font-medium mb-4">{{ $question }}</p>

          @if(session('success') && (int) session('answered_qi') === $qi)
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-800">
              Mot envoyé !
            </div>
          @endif

          @if($errors->has('answer') && (int) old('question_index') === $qi)
            <p class="mb-3 text-red-600 text-xs">{{ $errors->first('answer') }}</p>
          @endif

          <form method="POST"
                action="{{ route('wordcloud.submit', $wordCloud->access_code) }}"
                class="flex gap-2">
            @csrf
            <input type="hidden" name="question_index" value="{{ $qi }}">
            <input type="text"
                   name="answer"
                   maxlength="150"
                   required
                   {{ !$wordCloud->is_active ? 'disabled' : '' }}
                   placeholder="1 à 3 mots…"
                   class="flex-1 rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:border-[#004461] focus:outline-none focus:ring-2 focus:ring-[#004461]/20">
            <button type="submit"
                    {{ !$wordCloud->is_active ? 'disabled' : '' }}
                    class="rounded-xl bg-[#004461] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#005577] transition disabled:opacity-50">
              Envoyer
            </button>
          </form>
        </div>

      </div>
    @endforeach

  </div>

  {{-- Alpine.js (depuis CDN si pas déjà chargé via Vite) --}}
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
