@extends('formateur.dashboard')

@section('formateur')
@php
  $questions = collect($trueFalseSession->questions ?? [])->values();
@endphp
<div class="w-full px-6 lg:px-8" x-data="{ activeQ: 0 }">

  <header class="bg-white rounded-[20px] shadow-md px-8 pt-5 pb-6 my-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div>
        <nav class="text-sm font-varela text-gray-500 mb-2">
          <ol class="inline-flex items-center space-x-1">
            <li><a href="{{ route('formateur.outils.index') }}" class="text-orangeone hover:underline">Outils numériques</a></li>
            <li><span class="mx-2 text-gray-400">/</span></li>
            <li><a href="{{ route('formateur.vraifaux.index') }}" class="text-orangeone hover:underline">Vrai ou Faux</a></li>
            <li><span class="mx-2 text-gray-400">/</span></li>
            <li class="text-gray-400">{{ $trueFalseSession->title }}</li>
          </ol>
        </nav>
        <p class="font-raleway text-2xl text-bleuone">{{ $trueFalseSession->title }}</p>
        <p class="text-sm text-gray-500 mt-1">
          Groupe : <span class="font-semibold">{{ $trueFalseSession->group?->name ?? '—' }}</span>
          · Code : <span class="font-mono font-semibold text-orangeone">{{ $trueFalseSession->access_code }}</span>
        </p>
      </div>

      <div class="flex flex-wrap gap-2">
        <a href="{{ $joinUrl }}" target="_blank"
           class="inline-flex items-center rounded-[10px] border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
          Ouvrir la vue stagiaire
        </a>

        <form method="POST" action="{{ route('formateur.vraifaux.toggle', $trueFalseSession) }}">
          @csrf
          <button type="submit"
                  class="inline-flex items-center rounded-[10px] px-4 py-2 text-sm font-bold text-white transition {{ $trueFalseSession->is_active ? 'bg-red-500 hover:bg-red-600' : 'bg-green-600 hover:bg-green-700' }}">
            {{ $trueFalseSession->is_active ? 'Fermer le questionnaire' : 'Rouvrir le questionnaire' }}
          </button>
        </form>
      </div>
    </div>

    @if($questions->count() > 1)
      <div class="mt-4 flex flex-wrap gap-2">
        @foreach($questions as $index => $question)
          <button type="button"
                  @click="activeQ = {{ $index }}"
                  :class="activeQ === {{ $index }}
                    ? 'bg-orangeone text-white border-orangeone'
                    : 'bg-white text-gray-600 border-gray-300 hover:border-orangeone'"
                  class="rounded-full border px-4 py-1.5 text-sm font-semibold transition">
            Affirmation {{ $index + 1 }}
          </button>
        @endforeach
      </div>
    @endif
  </header>

  <div class="mb-4 rounded-[12px] border border-orange-100 bg-orange-50 px-4 py-3 text-sm text-orange-800">
    Répondants uniques : <span id="respondents-total" class="font-bold">{{ $stats['respondents_total'] ?? 0 }}</span>
  </div>

  @foreach($questions as $index => $question)
    <section x-show="activeQ === {{ $index }}" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-[20px] shadow-md p-6 mb-6"
             data-question-block="{{ $index }}">
      <div class="flex items-center justify-between gap-3 mb-4">
        <h2 class="text-base font-bold text-bleuone">{{ $question['statement'] ?? 'Affirmation' }}</h2>
        <span class="inline-flex items-center rounded-full bg-orange-100 px-3 py-1 text-xs font-bold text-orange-700">
          <span class="respondents-q">{{ data_get($stats, "questions.$index.respondents", 0) }}</span> répondant(s)
        </span>
      </div>

      <p class="text-xs text-gray-500 mb-4">
        Réponse correcte :
        <span class="font-bold {{ ($question['answer'] ?? false) ? 'text-green-700' : 'text-orangeone' }}">
          {{ ($question['answer'] ?? false) ? 'Vrai' : 'Faux' }}
        </span>
        · <span class="correct-percent">{{ data_get($stats, "questions.$index.correct_percent", 0) }}</span>% de bonnes réponses
      </p>

      @if(!empty($question['explanation']))
        <div class="mb-4 rounded-[10px] border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-600">
          <span class="font-bold text-gray-700">Explication :</span> {{ $question['explanation'] }}
        </div>
      @endif

      <div class="space-y-3">
        @foreach(['true' => 'Vrai', 'false' => 'Faux'] as $key => $label)
          @php
            $choiceStats = data_get($stats, "questions.$index.$key", ['votes' => 0, 'percent' => 0]);
            $isCorrectChoice = ($key === 'true') === (bool) ($question['answer'] ?? false);
          @endphp
          <div class="rounded-[12px] border {{ $isCorrectChoice ? 'border-green-200 bg-green-50' : 'border-gray-200 bg-gray-50' }} p-3" data-choice="{{ $key }}">
            <div class="flex items-center justify-between gap-3 mb-2">
              <p class="text-sm font-semibold {{ $isCorrectChoice ? 'text-green-700' : 'text-gray-700' }}">
                {{ $label }}
                @if($isCorrectChoice)
                  <span class="text-[10px] font-bold uppercase text-green-600">(correct)</span>
                @endif
              </p>
              <p class="text-xs text-gray-500">
                <span class="votes">{{ $choiceStats['votes'] }}</span> vote(s) · <span class="percent">{{ $choiceStats['percent'] }}</span>%
              </p>
            </div>
            <div class="h-2 rounded-full bg-gray-200 overflow-hidden">
              <div class="h-full {{ $isCorrectChoice ? 'bg-green-500' : 'bg-gray-400' }} transition-all duration-300 bar" style="width: {{ $choiceStats['percent'] }}%"></div>
            </div>
          </div>
        @endforeach
      </div>
    </section>
  @endforeach

</div>

<script>
  (function() {
    const endpoint = @json(route('formateur.vraifaux.state', $trueFalseSession));
    const blocks = Array.from(document.querySelectorAll('[data-question-block]'));
    const totalEl = document.getElementById('respondents-total');

    function render(data) {
      if (!data || !Array.isArray(data.questions)) return;
      if (totalEl) totalEl.textContent = data.respondents_total ?? 0;

      blocks.forEach((block) => {
        const questionIndex = Number(block.dataset.questionBlock);
        const questionData = data.questions?.[questionIndex];
        if (!questionData) return;

        const respondentsEl = block.querySelector('.respondents-q');
        if (respondentsEl) respondentsEl.textContent = questionData.respondents ?? 0;

        const correctPercentEl = block.querySelector('.correct-percent');
        if (correctPercentEl) correctPercentEl.textContent = questionData.correct_percent ?? 0;

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

    setInterval(refresh, 3000);
  })();
</script>
@endsection
