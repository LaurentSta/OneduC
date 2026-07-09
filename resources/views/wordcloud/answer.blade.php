@php
  $exitUrl = auth()->check() && (auth()->user()->role ?? null) === 'stagiaire' && \Illuminate\Support\Facades\Route::has('stagiaire.outils')
    ? route('stagiaire.outils')
    : route('wordcloud.join');
  $activeQuestionIndex = (int) ($activeQuestionIndex ?? $wordCloud->active_question_index);
  $questionCount = max(1, (int) ($questionCount ?? count($questions ?? [])));
  $progressPercent = $questionCount > 1 ? (($activeQuestionIndex + 1) / $questionCount) * 100 : 100;
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $wordCloud->title }} - Nuage de mots</title>
  @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-[#f3f7f9]">
  <main class="relative flex min-h-screen items-center justify-center px-4 py-10">
    <a href="{{ $exitUrl }}"
       class="absolute left-4 top-4 inline-flex items-center gap-2 rounded-full border border-[#004461]/20 bg-white px-4 py-2 text-sm font-semibold text-[#004461] shadow-sm transition hover:border-[#004461] hover:bg-[#004461] hover:text-white">
      <span aria-hidden="true">&larr;</span>
      Sortir de l'outil
    </a>

    <section class="w-full max-w-xl overflow-hidden rounded-[22px] bg-white shadow-[0_18px_50px_-24px_rgba(0,68,97,0.55)]">
      <div class="bg-[#004461] px-6 py-5 text-white">
        <div class="flex items-center justify-between gap-3">
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-white/60">Nuage de mots</p>
            <h1 class="mt-1 text-xl font-bold leading-tight">{{ $wordCloud->title }}</h1>
          </div>
          <span class="rounded-full bg-white/10 px-3 py-1 font-mono text-sm font-bold">
            {{ $wordCloud->access_code }}
          </span>
        </div>

        <div class="mt-5">
          <div class="flex items-center justify-between text-xs font-semibold text-white/70">
            <span>Question {{ $activeQuestionIndex + 1 }} / {{ $questionCount }}</span>
            @if($wordCloud->is_active)
              <span>En cours</span>
            @else
              <span>Fermé</span>
            @endif
          </div>
          <div class="mt-2 h-2 overflow-hidden rounded-full bg-white/15">
            <div class="h-full rounded-full bg-[#E94D2A]" style="width: {{ min(100, max(0, $progressPercent)) }}%"></div>
          </div>
        </div>
      </div>

      <div class="px-6 py-7">
        @if(!$wordCloud->is_active)
          <div class="mb-5 rounded-[14px] border border-orange-200 bg-orange-50 px-4 py-3 text-sm font-semibold text-orange-800">
            Cette session est actuellement fermée.
          </div>
        @endif

        @if(session('success') && (int) session('answered_qi') === $activeQuestionIndex)
          <div class="mb-5 rounded-[14px] border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800">
            Mot envoyé. Vous pouvez en proposer un autre ou attendre la question suivante.
          </div>
        @endif

        @if($errors->has('answer'))
          <div class="mb-5 rounded-[14px] border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
            {{ $errors->first('answer') }}
          </div>
        @endif

        <p class="text-sm font-bold uppercase tracking-[0.14em] text-[#E94D2A]">Votre réponse</p>
        <p class="mt-2 text-2xl font-bold leading-snug text-[#004461]">
          {{ $activeQuestion ?: 'Question en attente' }}
        </p>
        <p class="mt-3 text-sm leading-6 text-gray-500">
          Répondez avec 1 à 3 mots. Le formateur fera avancer les questions une par une.
        </p>

        <form method="POST"
              action="{{ route('wordcloud.submit', $wordCloud->access_code) }}"
              class="mt-6 space-y-3">
          @csrf
          <input type="hidden" name="question_index" value="{{ $activeQuestionIndex }}">
          <input type="text"
                 name="answer"
                 maxlength="150"
                 required
                 {{ !$wordCloud->is_active ? 'disabled' : '' }}
                 placeholder="Ex : confiance, entraide..."
                 class="w-full rounded-[14px] border border-gray-300 px-4 py-3 text-base focus:border-[#004461] focus:outline-none focus:ring-4 focus:ring-[#004461]/10 disabled:bg-gray-100">
          <button type="submit"
                  {{ !$wordCloud->is_active ? 'disabled' : '' }}
                  class="inline-flex w-full items-center justify-center rounded-full bg-[#E94D2A] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#c43d1f] disabled:cursor-not-allowed disabled:opacity-50">
            Envoyer mon mot
          </button>
        </form>
      </div>
    </section>
  </main>

  <script>
    (function () {
      const stateUrl = @json(route('wordcloud.state', $wordCloud->access_code));
      const currentQuestion = {{ $activeQuestionIndex }};
      const currentActive = {{ $wordCloud->is_active ? 'true' : 'false' }};

      async function refreshIfQuestionChanged() {
        try {
          const response = await fetch(stateUrl, { headers: { 'Accept': 'application/json' } });
          if (!response.ok) return;
          const state = await response.json();
          if (Number(state.current_question_index) !== currentQuestion || Boolean(state.active) !== currentActive) {
            window.location.reload();
          }
        } catch (error) {}
      }

      setInterval(refreshIfQuestionChanged, 4000);
    })();
  </script>
</body>
</html>
