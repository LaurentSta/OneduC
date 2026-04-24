<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $pollSession->title }} — Sondage</title>
  @vite(['resources/css/app.css'])
  <style>
    [x-cloak] { display: none !important; }
  </style>
</head>
<body class="min-h-screen bg-slate-100 p-4" x-data="{ activeQ: {{ session('answered_qi', 0) }} }">
  @php
    $questions = collect($pollSession->questions ?? [])->values();
  @endphp
  <div class="mx-auto w-full max-w-3xl space-y-4">

    <div class="rounded-2xl bg-white shadow-lg p-6">
      <p class="text-xs uppercase tracking-wide text-gray-400">Code · {{ $pollSession->access_code }}</p>
      <h1 class="text-xl font-bold text-[#004461] mt-1">{{ $pollSession->title }}</h1>

      @if(!$pollSession->is_active)
        <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700">
          Ce sondage est actuellement fermé.
        </div>
      @endif

      @if($questions->count() > 1)
        <div class="mt-4 flex flex-wrap gap-2">
          @foreach($questions as $qi => $question)
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

    @foreach($questions as $qi => $question)
      @php
        $selectedChoice = $myResponses->has($qi) ? (int) $myResponses[$qi] : null;
      @endphp
      <div x-show="activeQ === {{ $qi }}" x-cloak class="space-y-4" data-question-block="{{ $qi }}">

        <div class="rounded-2xl bg-white shadow-lg p-6">
          <p class="text-gray-700 font-medium mb-4">{{ $question['question'] ?? 'Question' }}</p>

          @if(session('success') && (int) session('answered_qi') === $qi)
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-800">
              Réponse enregistrée.
            </div>
          @endif

          @if($errors->has('choice_index') && (int) old('question_index') === $qi)
            <p class="mb-3 text-red-600 text-xs">{{ $errors->first('choice_index') }}</p>
          @endif

          <form method="POST" action="{{ route('sondages.submit', $pollSession->access_code) }}" class="space-y-3">
            @csrf
            <input type="hidden" name="question_index" value="{{ $qi }}">

            @foreach(($question['choices'] ?? []) as $ci => $choice)
              <label class="flex items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 hover:border-[#004461]/40 cursor-pointer">
                <input type="radio"
                       name="choice_index"
                       value="{{ $ci }}"
                       {{ $selectedChoice === $ci ? 'checked' : '' }}
                       {{ !$pollSession->is_active ? 'disabled' : '' }}
                       class="text-[#004461] focus:ring-[#004461]">
                <span class="text-sm text-gray-700">{{ $choice }}</span>
              </label>
            @endforeach

            <button type="submit"
                    {{ !$pollSession->is_active ? 'disabled' : '' }}
                    class="w-full rounded-xl bg-[#004461] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#005577] transition disabled:opacity-50">
              Valider ma réponse
            </button>
          </form>
        </div>

        <div class="rounded-2xl bg-white shadow-lg p-6">
          <div class="flex items-center justify-between gap-3 mb-4">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Résultats en direct</p>
            <span class="inline-flex items-center rounded-full bg-teal-100 px-3 py-1 text-xs font-bold text-teal-700">
              <span class="respondents">0</span> répondant(s)
            </span>
          </div>

          <div class="space-y-3">
            @foreach(($question['choices'] ?? []) as $ci => $choice)
              <div class="rounded-[12px] border border-gray-200 bg-gray-50 p-3" data-choice="{{ $ci }}">
                <div class="flex items-center justify-between gap-3 mb-2">
                  <p class="text-sm font-semibold text-gray-700">{{ $choice }}</p>
                  <p class="text-xs text-gray-500">
                    <span class="votes">0</span> vote(s) · <span class="percent">0</span>%
                  </p>
                </div>
                <div class="h-2 rounded-full bg-gray-200 overflow-hidden">
                  <div class="h-full bg-teal-500 transition-all duration-300 bar" style="width: 0%"></div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    @endforeach

  </div>

  <script>
    (function() {
      const endpoint = @json(route('sondages.data', $pollSession->access_code));
      const blocks = Array.from(document.querySelectorAll('[data-question-block]'));

      function render(data) {
        if (!data || !Array.isArray(data.questions)) return;

        blocks.forEach((block) => {
          const qi = Number(block.dataset.questionBlock);
          const questionData = data.questions?.[qi];
          if (!questionData) return;

          const respondentsEl = block.querySelector('.respondents');
          if (respondentsEl) respondentsEl.textContent = questionData.respondents ?? 0;

          const choiceCards = Array.from(block.querySelectorAll('[data-choice]'));
          choiceCards.forEach((card, ci) => {
            const choice = questionData.choices?.[ci];
            if (!choice) return;
            const votesEl = card.querySelector('.votes');
            const percentEl = card.querySelector('.percent');
            const barEl = card.querySelector('.bar');
            if (votesEl) votesEl.textContent = choice.votes ?? 0;
            if (percentEl) percentEl.textContent = choice.percent ?? 0;
            if (barEl) barEl.style.width = `${choice.percent ?? 0}%`;
          });
        });
      }

      async function refresh() {
        try {
          const res = await fetch(endpoint, { headers: { 'Accept': 'application/json' } });
          if (!res.ok) return;
          const data = await res.json();
          render(data);
        } catch {}
      }

      refresh();
      setInterval(refresh, 3000);
    })();
  </script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
