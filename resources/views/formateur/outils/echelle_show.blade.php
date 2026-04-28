@extends('formateur.dashboard')

@section('formateur')
@php
  $questions = collect($scaleSession->questions ?? [])->values();
@endphp
<div class="w-full px-6 lg:px-8" x-data="{ activeQ: 0 }">

  <header class="bg-white rounded-[20px] shadow-md px-8 pt-5 pb-6 my-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div>
        <nav class="text-sm font-varela text-gray-500 mb-2">
          <ol class="inline-flex items-center space-x-1">
            <li><a href="{{ route('formateur.outils.index') }}" class="text-orangeone hover:underline">Outils numériques</a></li>
            <li><span class="mx-2 text-gray-400">/</span></li>
            <li><a href="{{ route('formateur.echelle.index') }}" class="text-orangeone hover:underline">Échelle</a></li>
            <li><span class="mx-2 text-gray-400">/</span></li>
            <li class="text-gray-400">{{ $scaleSession->title }}</li>
          </ol>
        </nav>
        <p class="font-raleway text-2xl text-bleuone">{{ $scaleSession->title }}</p>
        <p class="text-sm text-gray-500 mt-1">
          Groupe : <span class="font-semibold">{{ $scaleSession->group?->name ?? '—' }}</span>
          · Code : <span class="font-mono font-semibold text-teal-700">{{ $scaleSession->access_code }}</span>
        </p>
      </div>

      <div class="flex flex-wrap gap-2">
        <a href="{{ $joinUrl }}" target="_blank"
           class="inline-flex items-center rounded-[10px] border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
          Ouvrir la vue stagiaire
        </a>

        <form method="POST" action="{{ route('formateur.echelle.toggle', $scaleSession) }}">
          @csrf
          <button type="submit"
                  class="inline-flex items-center rounded-[10px] px-4 py-2 text-sm font-bold text-white transition {{ $scaleSession->is_active ? 'bg-red-500 hover:bg-red-600' : 'bg-green-600 hover:bg-green-700' }}">
            {{ $scaleSession->is_active ? 'Fermer l\'échelle' : 'Rouvrir l\'échelle' }}
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
    Répondants uniques : <span id="respondents-total" class="font-bold">{{ $stats['respondents_total'] ?? 0 }}</span>
  </div>

  @foreach($questions as $qi => $question)
    @php
      $qStats = $stats['questions'][$qi] ?? [];
      $min = $qStats['min'] ?? 1;
      $max = $qStats['max'] ?? 10;
    @endphp
    <section x-show="activeQ === {{ $qi }}" x-cloak
             class="bg-white rounded-[20px] shadow-md p-6 mb-6"
             data-question-block="{{ $qi }}">

      <div class="flex items-center justify-between gap-3 mb-6">
        <h2 class="text-base font-bold text-bleuone">{{ $question['question'] ?? 'Question' }}</h2>
        <span class="inline-flex items-center rounded-full bg-teal-100 px-3 py-1 text-xs font-bold text-teal-700 shrink-0">
          <span class="respondents-q">{{ $qStats['respondents'] ?? 0 }}</span>&nbsp;répondant(s)
        </span>
      </div>

      {{-- KPI : moyenne et médiane --}}
      <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="rounded-[12px] bg-bleuone/5 p-4 text-center">
          <p class="text-xs font-bold uppercase text-gray-400 mb-1">Moyenne</p>
          <p class="text-3xl font-bold text-bleuone average-val">
            {{ $qStats['average'] !== null ? number_format($qStats['average'], 1) : '—' }}
          </p>
          <p class="text-xs text-gray-400 mt-1">sur {{ $max }}</p>
        </div>
        <div class="rounded-[12px] bg-bleuone/5 p-4 text-center">
          <p class="text-xs font-bold uppercase text-gray-400 mb-1">Médiane</p>
          <p class="text-3xl font-bold text-bleuone median-val">
            {{ $qStats['median'] !== null ? number_format($qStats['median'], 1) : '—' }}
          </p>
          <p class="text-xs text-gray-400 mt-1">sur {{ $max }}</p>
        </div>
      </div>

      {{-- Curseur indicatif (lecture seule) --}}
      @if($qStats['average'] !== null)
        <div class="mb-6 px-2">
          <div class="flex justify-between text-xs text-gray-400 mb-1">
            <span>{{ $question['label_min'] ?: $min }}</span>
            <span>{{ $question['label_max'] ?: $max }}</span>
          </div>
          <div class="relative h-3 rounded-full bg-gray-200">
            @php
              $pct = ($qStats['average'] - $min) / max(1, $max - $min) * 100;
            @endphp
            <div class="absolute left-0 top-0 h-full rounded-full bg-teal-500 transition-all duration-500 average-bar"
                 style="width: {{ $pct }}%"></div>
          </div>
          <div class="flex justify-between text-xs text-gray-400 mt-1">
            @for($v = $min; $v <= $max; $v++)
              <span>{{ $v }}</span>
            @endfor
          </div>
        </div>
      @endif

      {{-- Distribution --}}
      <div>
        <p class="text-xs font-bold uppercase text-gray-400 mb-3">Distribution des réponses</p>
        <div class="space-y-2 distribution-container">
          @foreach($qStats['distribution'] ?? [] as $bucket)
            <div class="flex items-center gap-3" data-bucket="{{ $bucket['value'] }}">
              <span class="w-6 text-right text-xs font-bold text-gray-500 shrink-0">{{ $bucket['value'] }}</span>
              <div class="flex-1 h-5 rounded-full bg-gray-100 overflow-hidden">
                <div class="h-full rounded-full bg-teal-400 transition-all duration-500 bar"
                     style="width: {{ $bucket['percent'] }}%"></div>
              </div>
              <span class="w-16 text-xs text-gray-500 shrink-0">
                <span class="count">{{ $bucket['count'] }}</span> (<span class="percent">{{ $bucket['percent'] }}</span>%)
              </span>
            </div>
          @endforeach
        </div>
      </div>

    </section>
  @endforeach

</div>

<script>
  (function() {
    const endpoint = @json(route('formateur.echelle.state', $scaleSession));
    const blocks   = Array.from(document.querySelectorAll('[data-question-block]'));
    const totalEl  = document.getElementById('respondents-total');

    function render(data) {
      if (!data || !Array.isArray(data.questions)) return;
      if (totalEl) totalEl.textContent = data.respondents_total ?? 0;

      blocks.forEach((block) => {
        const qi           = Number(block.dataset.questionBlock);
        const questionData = data.questions?.[qi];
        if (!questionData) return;

        const respondentsEl = block.querySelector('.respondents-q');
        if (respondentsEl) respondentsEl.textContent = questionData.respondents ?? 0;

        const avgEl = block.querySelector('.average-val');
        if (avgEl) avgEl.textContent = questionData.average !== null ? Number(questionData.average).toFixed(1) : '—';

        const medEl = block.querySelector('.median-val');
        if (medEl) medEl.textContent = questionData.median !== null ? Number(questionData.median).toFixed(1) : '—';

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
            const row     = distContainer.querySelector(`[data-bucket="${bucket.value}"]`);
            if (!row) return;
            const barEl   = row.querySelector('.bar');
            const countEl = row.querySelector('.count');
            const pctEl   = row.querySelector('.percent');
            if (barEl)   barEl.style.width   = `${bucket.percent ?? 0}%`;
            if (countEl) countEl.textContent = bucket.count ?? 0;
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

    setInterval(refresh, 3000);
  })();
</script>
@endsection
