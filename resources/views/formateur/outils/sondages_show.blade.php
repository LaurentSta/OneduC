@extends('formateur.dashboard')

@section('formateur')
@php
  $questions = collect($pollSession->questions ?? [])->values();
@endphp
<div class="w-full px-6 lg:px-8" x-data="{ activeQ: 0 }">

  <header class="bg-white rounded-[20px] shadow-md px-8 pt-5 pb-6 my-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div>
        <nav class="text-sm font-varela text-gray-500 mb-2">
          <ol class="inline-flex items-center space-x-1">
            <li><a href="{{ route('formateur.outils.index') }}" class="text-orangeone hover:underline">Outils numériques</a></li>
            <li><span class="mx-2 text-gray-400">/</span></li>
            <li><a href="{{ route('formateur.sondages.index') }}" class="text-orangeone hover:underline">Sondages</a></li>
            <li><span class="mx-2 text-gray-400">/</span></li>
            <li class="text-gray-400">{{ $pollSession->title }}</li>
          </ol>
        </nav>
        <p class="font-raleway text-2xl text-bleuone">{{ $pollSession->title }}</p>
        <p class="text-sm text-gray-500 mt-1">
          Groupe : <span class="font-semibold">{{ $pollSession->group?->name ?? '—' }}</span>
          · Code : <span class="font-mono font-semibold text-teal-700">{{ $pollSession->access_code }}</span>
        </p>
      </div>

      <div class="flex flex-wrap gap-2">
        <a href="{{ $joinUrl }}" target="_blank"
           class="inline-flex items-center rounded-[10px] border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
          Ouvrir la vue stagiaire
        </a>

        <form method="POST" action="{{ route('formateur.sondages.toggle', $pollSession) }}">
          @csrf
          <button type="submit"
                  class="inline-flex items-center rounded-[10px] px-4 py-2 text-sm font-bold text-white transition {{ $pollSession->is_active ? 'bg-red-500 hover:bg-red-600' : 'bg-green-600 hover:bg-green-700' }}">
            {{ $pollSession->is_active ? 'Fermer le sondage' : 'Rouvrir le sondage' }}
          </button>
        </form>
      </div>
    </div>

    @if($questions->count() > 1)
      <div class="mt-4 flex flex-wrap gap-2">
        @foreach($questions as $qi => $question)
          <button type="button"
                  @click="activeQ = {{ $qi }}"
                  :class="activeQ === {{ $qi }}
                    ? 'bg-teal-600 text-white border-teal-600'
                    : 'bg-white text-gray-600 border-gray-300 hover:border-teal-500'"
                  class="rounded-full border px-4 py-1.5 text-sm font-semibold transition">
            Question {{ $qi + 1 }}
          </button>
        @endforeach
      </div>
    @endif
  </header>

  <div class="mb-4 rounded-[12px] border border-teal-100 bg-teal-50 px-4 py-3 text-sm text-teal-800">
    Répondants uniques: <span id="respondents-total" class="font-bold">{{ $stats['respondents_total'] ?? 0 }}</span>
  </div>

  @foreach($questions as $qi => $question)
    <section x-show="activeQ === {{ $qi }}" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-[20px] shadow-md p-6 mb-6"
             data-question-block="{{ $qi }}">
      <div class="flex items-center justify-between gap-3 mb-4">
        <h2 class="text-base font-bold text-bleuone">{{ $question['question'] ?? 'Question' }}</h2>
        <span class="inline-flex items-center rounded-full bg-teal-100 px-3 py-1 text-xs font-bold text-teal-700">
          <span class="respondents-q">{{ data_get($stats, "questions.$qi.respondents", 0) }}</span> répondant(s)
        </span>
      </div>

      <div class="space-y-3">
        @foreach(($question['choices'] ?? []) as $ci => $choice)
          @php
            $choiceStats = collect(data_get($stats, "questions.$qi.choices", []))->get($ci, ['votes' => 0, 'percent' => 0]);
          @endphp
          <div class="rounded-[12px] border border-gray-200 bg-gray-50 p-3" data-choice="{{ $ci }}">
            <div class="flex items-center justify-between gap-3 mb-2">
              <p class="text-sm font-semibold text-gray-700">{{ $choice }}</p>
              <p class="text-xs text-gray-500">
                <span class="votes">{{ $choiceStats['votes'] }}</span> vote(s) · <span class="percent">{{ $choiceStats['percent'] }}</span>%
              </p>
            </div>
            <div class="h-2 rounded-full bg-gray-200 overflow-hidden">
              <div class="h-full bg-teal-500 transition-all duration-300 bar" style="width: {{ $choiceStats['percent'] }}%"></div>
            </div>
          </div>
        @endforeach
      </div>
    </section>
  @endforeach

</div>

<script>
  (function() {
    const endpoint = @json(route('formateur.sondages.state', $pollSession));
    const blocks = Array.from(document.querySelectorAll('[data-question-block]'));
    const totalEl = document.getElementById('respondents-total');

    function render(data) {
      if (!data || !Array.isArray(data.questions)) return;
      if (totalEl) totalEl.textContent = data.respondents_total ?? 0;

      blocks.forEach((block) => {
        const qi = Number(block.dataset.questionBlock);
        const questionData = data.questions?.[qi];
        if (!questionData) return;

        const respondentsEl = block.querySelector('.respondents-q');
        if (respondentsEl) respondentsEl.textContent = questionData.respondents ?? 0;

        const cards = Array.from(block.querySelectorAll('[data-choice]'));
        cards.forEach((card) => {
          const ci = Number(card.dataset.choice);
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

    setInterval(refresh, 3000);
  })();
</script>
@endsection
