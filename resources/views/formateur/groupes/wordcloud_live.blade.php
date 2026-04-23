@extends('formateur.dashboard')

@section('formateur')
<div class="w-full px-6 lg:px-8" x-data="{ activeQ: 0 }">

  {{-- En-tête --}}
  <div class="bg-white rounded-[20px] shadow-soft p-6 my-6 border border-gray-100">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-2">
      <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
          </svg>
        </div>
        <div>
          <h1 class="text-[18px] font-varela text-bleuone">{{ $item->wc_title }}</h1>
          <p class="text-sm text-gray-500">Groupe : <span class="font-semibold">{{ $group->name }}</span></p>
        </div>
      </div>
      <a href="{{ route('formateur.groupes.edit', $group->id) }}"
         class="btn-oneduc-outline !px-3 !py-2 !text-sm">
        ← Retour au groupe
      </a>
    </div>

    {{-- Tabs questions --}}
    @if(count($questions) > 1)
      <div class="mt-4 flex flex-wrap gap-2">
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
            @if($responseCounts->has($qi))
              <span class="ml-1.5 inline-flex items-center rounded-full bg-white/30 px-1.5 text-[10px]">
                {{ $responseCounts->get($qi) }}/{{ $totalStudents }}
              </span>
            @endif
          </button>
        @endforeach
      </div>
    @endif
  </div>

  {{-- Blocs par question --}}
  @foreach($questions as $qi => $question)
    <div x-show="activeQ === {{ $qi }}" x-cloak class="space-y-4 mb-6">

      {{-- Question + compteur --}}
      <div class="bg-white rounded-[16px] shadow-soft border border-gray-100 px-6 py-4 flex items-center justify-between gap-4">
        <p class="font-varela text-base text-gray-800">{{ $question }}</p>
        <span
          id="counter-{{ $qi }}"
          class="shrink-0 inline-flex items-center gap-1.5 rounded-full bg-amber-50 border border-amber-200 px-3 py-1 text-sm font-semibold text-amber-700"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          <span class="counter-text-{{ $qi }}">{{ $responseCounts->get($qi, 0) }} / {{ $totalStudents }}</span>
        </span>
      </div>

      {{-- Nuage --}}
      <div class="bg-white rounded-[16px] shadow-soft border border-gray-100 px-6 py-5">
        <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-4">Nuage de mots en direct</p>
        <div class="wc-canvas-wrap">
          <canvas id="cloudCanvas-{{ $qi }}" style="display:none;"></canvas>
          <div id="cloudEmpty-{{ $qi }}" class="wc-empty">
            <p class="text-gray-400 text-sm">En attente des premières réponses…</p>
          </div>
        </div>
      </div>

    </div>
  @endforeach

</div>

<style>
  [x-cloak] { display: none !important; }
  .wc-canvas-wrap { position:relative; min-height:420px; border-radius:0.75rem; border:1px solid #e5e7eb; background:#f9fafb; overflow:hidden; }
  .wc-canvas-wrap canvas { display:block; }
  .wc-empty { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; }
</style>

<script src="https://cdn.jsdelivr.net/npm/wordcloud@1/src/wordcloud2.min.js"></script>
<script>
  const _wcPalette = ['#0F766E','#1D4ED8','#B45309','#BE123C','#7C3AED','#0E7490','#15803D','#A16207'];
  function _wcColor(w) {
    let h = 0;
    for (let i = 0; i < w.length; i++) { h = ((h << 5) - h) + w.charCodeAt(i); h |= 0; }
    return _wcPalette[Math.abs(h) % _wcPalette.length];
  }

  const totalStudents = {{ $totalStudents }};

  @foreach($questions as $qi => $question)
  (function() {
    const canvas   = document.getElementById('cloudCanvas-{{ $qi }}');
    const emptyEl  = document.getElementById('cloudEmpty-{{ $qi }}');
    const counter  = document.querySelector('.counter-text-{{ $qi }}');
    const endpoint = @json(route('formateur.groupes.wordcloud.data', [$group, $item])) + '?q={{ $qi }}';
    let lastSig = '';
    let lastW   = 0;

    async function refresh() {
      try {
        const res  = await fetch(endpoint, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        const words = data.words || [];
        const sig   = JSON.stringify(words);

        if (counter) counter.textContent = (data.respondents ?? 0) + ' / ' + totalStudents;

        const W = Math.round(canvas.parentElement.getBoundingClientRect().width);
        if (!W) { lastSig = ''; return; }

        if (!words.length) {
          emptyEl.style.display = 'flex';
          canvas.style.display  = 'none';
          lastSig = '';
          return;
        }

        if (sig === lastSig && W === lastW) return;
        lastSig = sig; lastW = W;

        emptyEl.style.display = 'none';
        canvas.style.display  = 'block';
        canvas.width  = W;
        canvas.height = 420;

        const max = Math.max(...words.map(w => Number(w.score)));
        WordCloud(canvas, {
          list: words.map(item => [item.word, Number(item.score)]),
          gridSize: Math.max(4, Math.round(8 * W / 800)),
          weightFactor: function(s) { return Math.max(13, Math.round(13 + (s / max) * 62)); },
          fontFamily: '"Varela Round", Arial, sans-serif',
          color: function(word) { return _wcColor(word); },
          rotateRatio: 0.3,
          rotationSteps: 2,
          backgroundColor: '#f9fafb',
          clearCanvas: true,
        });
      } catch {}
    }

    refresh();
    setInterval(refresh, 3000);
  })();
  @endforeach
</script>
@endsection
