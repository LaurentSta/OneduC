<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $trueFalseSession->title }} — Vrai ou Faux</title>
  @vite(['resources/css/app.css'])
  <style>
    [x-cloak] { display: none !important; }
  </style>
</head>
<body class="min-h-screen bg-slate-100 p-4" x-data="{ activeQ: {{ session('answered_qi', 0) }} }">
  @php
    $questions = collect($trueFalseSession->questions ?? [])->values();
    $answeredCount = $myResponses->count();
  @endphp
  <div class="mx-auto w-full max-w-3xl space-y-4">

    <div class="rounded-2xl bg-white shadow-lg p-6">
      <p class="text-xs uppercase tracking-wide text-gray-400">Code · {{ $trueFalseSession->access_code }}</p>
      <h1 class="text-xl font-bold text-bleuone mt-1 font-raleway">{{ $trueFalseSession->title }}</h1>
      <p class="text-sm text-gray-500 mt-1">{{ $answeredCount }} / {{ $questions->count() }} réponse{{ $questions->count() > 1 ? 's' : '' }}</p>

      @if(!$trueFalseSession->is_active)
        <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700">
          Ce questionnaire est actuellement fermé.
        </div>
      @endif

      @if($questions->count() > 1)
        <div class="mt-4 flex flex-wrap gap-2">
          @foreach($questions as $index => $question)
            @php $isAnswered = $myResponses->has($index); @endphp
            <button type="button"
                    @click="activeQ = {{ $index }}"
                    :class="activeQ === {{ $index }}
                      ? 'bg-bleuone text-white border-bleuone'
                      : 'bg-white text-gray-600 border-gray-300 hover:border-bleuone'"
                    class="relative rounded-full border px-4 py-1.5 text-sm font-semibold transition">
              {{ $index + 1 }}
              @if($isAnswered)
                <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-vertone text-white text-[9px]">✓</span>
              @endif
            </button>
          @endforeach
        </div>
      @endif
    </div>

    @foreach($questions as $index => $question)
      @php
        $hasAnswered = $myResponses->has($index);
        $myAnswer = $hasAnswered ? (bool) $myResponses[$index] : null;
        $correctAnswer = (bool) ($question['answer'] ?? false);
        $isCorrect = $hasAnswered && $myAnswer === $correctAnswer;
      @endphp
      <div x-show="activeQ === {{ $index }}" x-cloak class="space-y-4" data-question-block="{{ $index }}">

        <div class="rounded-2xl bg-white shadow-lg p-6">
          <p class="text-gray-700 font-medium mb-4">{{ $question['statement'] ?? 'Affirmation' }}</p>

          @if(session('success') && (int) session('answered_qi') === $index)
            <div class="mb-4 rounded-lg border {{ $isCorrect ? 'border-green-200 bg-green-50 text-green-800' : 'border-orange-200 bg-orange-50 text-orange-800' }} px-3 py-2 text-sm">
              {{ $isCorrect ? 'Bonne réponse.' : 'Réponse incorrecte.' }}
              La bonne réponse était <strong>{{ $correctAnswer ? 'Vrai' : 'Faux' }}</strong>.
              @if(!empty($question['explanation']))
                <p class="mt-1 text-gray-600">{{ $question['explanation'] }}</p>
              @endif
            </div>
          @elseif($hasAnswered)
            <div class="mb-4 rounded-lg border {{ $isCorrect ? 'border-green-200 bg-green-50 text-green-800' : 'border-orange-200 bg-orange-50 text-orange-800' }} px-3 py-2 text-sm">
              Vous avez répondu <strong>{{ $myAnswer ? 'Vrai' : 'Faux' }}</strong>.
              La bonne réponse était <strong>{{ $correctAnswer ? 'Vrai' : 'Faux' }}</strong>.
              @if(!empty($question['explanation']))
                <p class="mt-1 text-gray-600">{{ $question['explanation'] }}</p>
              @endif
            </div>
          @endif

          @if($errors->has('answer') && (int) old('question_index') === $index)
            <p class="mb-3 text-red-600 text-xs">{{ $errors->first('answer') }}</p>
          @endif

          <form method="POST" action="{{ route('vraifaux.submit', $trueFalseSession->access_code) }}" class="flex gap-3">
            @csrf
            <input type="hidden" name="question_index" value="{{ $index }}">

            <button type="submit" name="answer" value="1"
                    {{ !$trueFalseSession->is_active ? 'disabled' : '' }}
                    class="flex-1 rounded-xl border-2 {{ $myAnswer === true ? 'border-vertone bg-vertone/10' : 'border-gray-200' }} px-5 py-3 text-sm font-bold text-green-700 hover:border-vertone hover:bg-vertone/10 transition disabled:opacity-50">
              Vrai
            </button>
            <button type="submit" name="answer" value="0"
                    {{ !$trueFalseSession->is_active ? 'disabled' : '' }}
                    class="flex-1 rounded-xl border-2 {{ $myAnswer === false ? 'border-orangeone bg-orange-50' : 'border-gray-200' }} px-5 py-3 text-sm font-bold text-orangeone hover:border-orangeone hover:bg-orange-50 transition disabled:opacity-50">
              Faux
            </button>
          </form>
        </div>

        <div class="rounded-2xl bg-white shadow-lg p-6">
          <div class="flex items-center justify-between gap-3 mb-4">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Résultats en direct</p>
            <span class="inline-flex items-center rounded-full bg-orange-100 px-3 py-1 text-xs font-bold text-orange-700">
              <span class="respondents">0</span>&nbsp;répondant(s)
            </span>
          </div>

          <div class="space-y-3">
            @foreach(['true' => 'Vrai', 'false' => 'Faux'] as $key => $label)
              <div class="rounded-[12px] border border-gray-200 bg-gray-50 p-3" data-choice="{{ $key }}">
                <div class="flex items-center justify-between gap-3 mb-2">
                  <p class="text-sm font-semibold text-gray-700">{{ $label }}</p>
                  <p class="text-xs text-gray-500">
                    <span class="votes">0</span> vote(s) · <span class="percent">0</span>%
                  </p>
                </div>
                <div class="h-2 rounded-full bg-gray-200 overflow-hidden">
                  <div class="h-full bg-orangeone transition-all duration-300 bar" style="width: 0%"></div>
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
      const endpoint = @json(route('vraifaux.data', $trueFalseSession->access_code));
      const blocks = Array.from(document.querySelectorAll('[data-question-block]'));

      function render(data) {
        if (!data || !Array.isArray(data.questions)) return;

        blocks.forEach((block) => {
          const questionIndex = Number(block.dataset.questionBlock);
          const questionData = data.questions?.[questionIndex];
          if (!questionData) return;

          const respondentsEl = block.querySelector('.respondents');
          if (respondentsEl) respondentsEl.textContent = questionData.respondents ?? 0;

          ['true', 'false'].forEach((key) => {
            const card = block.querySelector(`[data-choice="${key}"]`);
            if (!card) return;
            const choice = questionData[key];
            if (!choice) return;

            const votesEl = card.querySelector('.votes');
            const percentEl = card.querySelector('.percent');
            const barEl = card.querySelector('.bar');
            if (votesEl) votesEl.textContent = choice.votes ?? 0;
            if (percentEl) percentEl.textContent = choice.percent ?? 0;
            if (barEl) barEl.style.width = `${choice.percent ?? 0}%`;

            if (questionData.correct_answer !== null) {
              const isCorrectChoice = (key === 'true') === questionData.correct_answer;
              card.classList.toggle('border-green-200', isCorrectChoice);
              card.classList.toggle('bg-green-50', isCorrectChoice);
              if (barEl) barEl.classList.toggle('bg-green-500', isCorrectChoice);
            }
          });
        });
      }

      async function refresh() {
        try {
          const res = await fetch(endpoint, { headers: { 'Accept': 'application/json' } });
          if (!res.ok) return;
          render(await res.json());
        } catch {}
      }

      refresh();
      setInterval(refresh, 3000);
    })();
  </script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
