<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $scaleSession->title }} — Échelle</title>
  @vite(['resources/css/app.css'])
  <style>
    [x-cloak] { display: none !important; }

    input[type=range] {
      -webkit-appearance: none;
      appearance: none;
      width: 100%;
      height: 6px;
      border-radius: 9999px;
      background: #e2e8f0;
      outline: none;
    }
    input[type=range]::-webkit-slider-thumb {
      -webkit-appearance: none;
      appearance: none;
      width: 24px;
      height: 24px;
      border-radius: 50%;
      background: #004461;
      cursor: pointer;
      border: 3px solid white;
      box-shadow: 0 2px 6px rgba(0,68,97,0.35);
      transition: transform 0.1s;
    }
    input[type=range]::-webkit-slider-thumb:active { transform: scale(1.25); }
    input[type=range]::-moz-range-thumb {
      width: 24px;
      height: 24px;
      border-radius: 50%;
      background: #004461;
      cursor: pointer;
      border: 3px solid white;
      box-shadow: 0 2px 6px rgba(0,68,97,0.35);
    }
  </style>
</head>
<body class="min-h-screen bg-slate-100 p-4" x-data="{ activeQ: {{ session('answered_qi', 0) }} }">
  @php
    $questions = collect($scaleSession->questions ?? [])->values();
  @endphp
  <div class="mx-auto w-full max-w-3xl space-y-4">

    <div class="rounded-2xl bg-white shadow-lg p-6">
      <p class="text-xs uppercase tracking-wide text-gray-400">Code · {{ $scaleSession->access_code }}</p>
      <h1 class="text-xl font-bold text-bleuone mt-1 font-raleway">{{ $scaleSession->title }}</h1>

      @if(!$scaleSession->is_active)
        <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700">
          Cette échelle est actuellement fermée.
        </div>
      @endif

      @if($questions->count() > 1)
        <div class="mt-4 flex flex-wrap gap-2">
          @foreach($questions as $qi => $question)
            <button type="button"
                    @click="activeQ = {{ $qi }}"
                    :class="activeQ === {{ $qi }}
                      ? 'bg-bleuone text-white border-bleuone'
                      : 'bg-white text-gray-600 border-gray-300 hover:border-bleuone'"
                    class="rounded-full border px-4 py-1.5 text-sm font-semibold transition">
              Question {{ $qi + 1 }}
            </button>
          @endforeach
        </div>
      @endif
    </div>

    @foreach($questions as $qi => $question)
      @php
        $min          = (int) ($question['min'] ?? 1);
        $max          = (int) ($question['max'] ?? 10);
        $myValue      = $myResponses->has($qi) ? (int) $myResponses[$qi] : (int) ceil(($min + $max) / 2);
        $hasAnswered  = $myResponses->has($qi);
        $labelMin     = $question['label_min'] ?? '';
        $labelMax     = $question['label_max'] ?? '';
      @endphp

      <div x-show="activeQ === {{ $qi }}" x-cloak class="space-y-4" data-question-block="{{ $qi }}">

        {{-- Formulaire slider --}}
        <div class="rounded-2xl bg-white shadow-lg p-6"
             x-data="{ val: {{ $myValue }} }">
          <p class="text-gray-700 font-medium mb-6">{{ $question['question'] ?? 'Question' }}</p>

          @if(session('success') && (int) session('answered_qi') === $qi)
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-800">
              Réponse enregistrée.
            </div>
          @endif

          @if($errors->has('value') && (int) old('question_index') === $qi)
            <p class="mb-3 text-red-600 text-xs">{{ $errors->first('value') }}</p>
          @endif

          <form method="POST" action="{{ route('echelle.submit', $scaleSession->access_code) }}" class="space-y-6">
            @csrf
            <input type="hidden" name="question_index" value="{{ $qi }}">

            {{-- Valeur affichée --}}
            <div class="text-center">
              <span class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-bleuone text-white text-2xl font-bold shadow-md"
                    x-text="val"></span>
              <p class="text-xs text-gray-400 mt-2">sur {{ $max }}</p>
            </div>

            {{-- Slider --}}
            <div class="px-2">
              <input type="range"
                     name="value"
                     min="{{ $min }}" max="{{ $max }}" step="1"
                     x-model.number="val"
                     {{ !$scaleSession->is_active ? 'disabled' : '' }}
                     class="{{ !$scaleSession->is_active ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}">

              <div class="flex justify-between mt-2">
                @if($labelMin || $labelMax)
                  <span class="text-xs text-gray-500 italic">{{ $labelMin ?: $min }}</span>
                  <span class="text-xs text-gray-500 italic">{{ $labelMax ?: $max }}</span>
                @else
                  <span class="text-xs text-gray-400">{{ $min }}</span>
                  <span class="text-xs text-gray-400">{{ $max }}</span>
                @endif
              </div>

              <div class="flex justify-between mt-1 px-0.5">
                @for($v = $min; $v <= $max; $v++)
                  <span class="text-[10px] text-gray-300"
                        :class="val === {{ $v }} ? 'text-bleuone font-bold' : ''">{{ $v }}</span>
                @endfor
              </div>
            </div>

            <button type="submit"
                    {{ !$scaleSession->is_active ? 'disabled' : '' }}
                    class="w-full rounded-xl bg-bleuone px-5 py-2.5 text-sm font-bold text-white hover:bg-bleuone-light transition disabled:opacity-50">
              {{ $hasAnswered ? 'Modifier ma réponse' : 'Valider ma réponse' }}
            </button>
          </form>
        </div>

        {{-- Résultats en direct --}}
        <div class="rounded-2xl bg-white shadow-lg p-6">
          <div class="flex items-center justify-between gap-3 mb-4">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Résultats en direct</p>
            <span class="inline-flex items-center rounded-full bg-teal-100 px-3 py-1 text-xs font-bold text-teal-700">
              <span class="respondents">0</span>&nbsp;répondant(s)
            </span>
          </div>

          {{-- Moyenne --}}
          <div class="flex items-center gap-4 mb-4">
            <div class="text-center">
              <p class="text-2xl font-bold text-bleuone average-val">—</p>
              <p class="text-[10px] uppercase text-gray-400">Moyenne</p>
            </div>
            <div class="flex-1 h-3 rounded-full bg-gray-200 overflow-hidden">
              <div class="h-full rounded-full bg-teal-500 transition-all duration-500 average-bar" style="width: 0%"></div>
            </div>
          </div>

          {{-- Distribution --}}
          <div class="space-y-1.5 distribution-container">
            @for($v = $min; $v <= $max; $v++)
              <div class="flex items-center gap-2" data-bucket="{{ $v }}">
                <span class="w-5 text-right text-xs font-bold text-gray-500 shrink-0">{{ $v }}</span>
                <div class="flex-1 h-4 rounded-full bg-gray-100 overflow-hidden">
                  <div class="h-full rounded-full bg-teal-400 transition-all duration-500 bar" style="width: 0%"></div>
                </div>
                <span class="w-12 text-xs text-gray-400 shrink-0">
                  <span class="count">0</span> (<span class="percent">0</span>%)
                </span>
              </div>
            @endfor
          </div>
        </div>

      </div>
    @endforeach

  </div>

  <script>
    (function() {
      const endpoint = @json(route('echelle.data', $scaleSession->access_code));
      const blocks   = Array.from(document.querySelectorAll('[data-question-block]'));

      function render(data) {
        if (!data || !Array.isArray(data.questions)) return;

        blocks.forEach((block) => {
          const qi           = Number(block.dataset.questionBlock);
          const questionData = data.questions?.[qi];
          if (!questionData) return;

          const respondentsEl = block.querySelector('.respondents');
          if (respondentsEl) respondentsEl.textContent = questionData.respondents ?? 0;

          const avgEl = block.querySelector('.average-val');
          if (avgEl) avgEl.textContent = questionData.average !== null
            ? Number(questionData.average).toFixed(1)
            : '—';

          const avgBar = block.querySelector('.average-bar');
          if (avgBar && questionData.average !== null) {
            const min = questionData.min ?? 1;
            const max = questionData.max ?? 10;
            const pct = (questionData.average - min) / Math.max(1, max - min) * 100;
            avgBar.style.width = `${Math.min(100, Math.max(0, pct))}%`;
          }

          const distContainer = block.querySelector('.distribution-container');
          if (distContainer && Array.isArray(questionData.distribution)) {
            questionData.distribution.forEach((bucket) => {
              const row   = distContainer.querySelector(`[data-bucket="${bucket.value}"]`);
              if (!row) return;
              const barEl   = row.querySelector('.bar');
              const countEl = row.querySelector('.count');
              const pctEl   = row.querySelector('.percent');
              if (barEl)   barEl.style.width   = `${bucket.percent ?? 0}%`;
              if (countEl) countEl.textContent = bucket.count   ?? 0;
              if (pctEl)   pctEl.textContent   = bucket.percent ?? 0;
            });
          }
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
