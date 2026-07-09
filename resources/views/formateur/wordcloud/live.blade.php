@extends('formateur.dashboard')

@section('formateur')
@php
  $questions = $wordCloud->questions_array ?: [$wordCloud->question];
  $questionCount = max(1, count($questions));
  $activeQuestionIndex = $wordCloud->active_question_index;
  $activeQuestion = $questions[$activeQuestionIndex] ?? null;
  $isLastQuestion = $activeQuestionIndex >= $questionCount - 1;
  $nextQuestion = $isLastQuestion ? null : ($questions[$activeQuestionIndex + 1] ?? null);
  $remainingQuestions = max(0, $questionCount - ($activeQuestionIndex + 1));
  $progressPercent = $questionCount > 1 ? (($activeQuestionIndex + 1) / $questionCount) * 100 : 100;
@endphp

<div class="w-full px-6 lg:px-8"
     x-data="{
       activeQ: {{ $activeQuestionIndex }},
       copied: false,
       copyJoinLink() {
         const link = @js($joinUrl);
         if (navigator.clipboard && window.isSecureContext) {
           navigator.clipboard.writeText(link);
         } else {
           const textarea = document.createElement('textarea');
           textarea.value = link;
           textarea.setAttribute('readonly', '');
           textarea.style.position = 'fixed';
           textarea.style.opacity = '0';
           document.body.appendChild(textarea);
           textarea.select();
           document.execCommand('copy');
           document.body.removeChild(textarea);
         }
         this.copied = true;
         setTimeout(() => this.copied = false, 1800);
       }
     }">
  <header class="my-6 overflow-hidden rounded-[22px] bg-white shadow-soft border border-gray-100">
    <div class="bg-[#004461] px-6 py-5 text-white">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
          <p class="text-xs font-bold uppercase tracking-[0.18em] text-white/60">Nuage de mots en direct</p>
          <h1 class="mt-1 text-2xl font-raleway font-semibold leading-tight">{{ $wordCloud->title }}</h1>
          <div class="mt-3 flex flex-wrap items-center gap-2 text-sm">
            @if($wordCloud->group)
              <span class="rounded-full bg-white/10 px-3 py-1">{{ $wordCloud->group->name }}</span>
            @endif
            <span class="rounded-full bg-white/10 px-3 py-1 font-mono">Code : {{ $wordCloud->access_code }}</span>
            <a href="{{ $joinUrl }}" target="_blank" rel="noopener"
               class="rounded-full bg-[#E94D2A] px-3 py-1 text-xs font-bold text-white transition hover:bg-[#c43d1f]">
              Lien stagiaire
            </a>
          </div>
        </div>

        <a href="{{ route('formateur.nuages.index') }}"
           class="inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/10 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white hover:text-[#004461]">
          <span aria-hidden="true">&larr;</span>
          Retour
        </a>
      </div>

      <div class="mt-6">
        <div class="flex items-center justify-between text-xs font-semibold text-white/70">
          <span>Question {{ $activeQuestionIndex + 1 }} / {{ $questionCount }}</span>
          <span>{{ $wordCloud->is_active ? 'Session ouverte' : 'Session fermée' }}</span>
        </div>
        <div class="mt-2 h-2 overflow-hidden rounded-full bg-white/15">
          <div class="h-full rounded-full bg-[#E94D2A]" style="width: {{ min(100, max(0, $progressPercent)) }}%"></div>
        </div>
      </div>
    </div>

    <div class="grid gap-4 px-6 py-4 lg:grid-cols-[1fr_auto] lg:items-center">
      @if(session('success'))
        <p class="rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">{{ session('success') }}</p>
      @else
        <div>
          <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">Action du moment</p>
          <p class="mt-1 text-sm font-semibold text-[#004461]">
            @if(!$wordCloud->is_active)
              La session est terminée.
            @elseif($isLastQuestion)
              Dernière question : terminez le nuage quand les réponses sont suffisantes.
            @else
              Quand le groupe a répondu, passez à la question suivante.
            @endif
          </p>
        </div>
      @endif

      <div class="flex flex-wrap items-center gap-2 lg:justify-end">
        @if($activeQuestionIndex > 0)
          <form method="POST" action="{{ route('formateur.nuages.question', $wordCloud) }}">
            @csrf
            <input type="hidden" name="question_index" value="{{ $activeQuestionIndex - 1 }}">
            <button type="submit"
                    class="inline-flex items-center rounded-full border border-[#004461]/20 bg-white px-3 py-2 text-xs font-semibold text-[#004461] transition hover:border-[#004461]">
              Précédente
            </button>
          </form>
        @endif

        @if($wordCloud->is_active && !$isLastQuestion)
          <form method="POST" action="{{ route('formateur.nuages.question', $wordCloud) }}">
            @csrf
            <input type="hidden" name="question_index" value="{{ $activeQuestionIndex + 1 }}">
            <button type="submit"
                    class="inline-flex items-center rounded-full bg-[#E94D2A] px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#c43d1f]">
              Question suivante
            </button>
          </form>
        @elseif($wordCloud->is_active)
          <form method="POST" action="{{ route('formateur.nuages.close', $wordCloud) }}">
            @csrf
            <button type="submit"
                    class="inline-flex items-center rounded-full bg-[#E94D2A] px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#c43d1f]">
              Terminer le nuage
            </button>
          </form>
        @else
          <span class="inline-flex items-center rounded-full bg-gray-100 px-4 py-2 text-sm font-bold text-gray-500">
            Session terminée
          </span>
        @endif
      </div>
    </div>
  </header>

  <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
    <section class="min-w-0">
      @foreach($questions as $qi => $question)
        <article x-show="activeQ === {{ $qi }}" x-cloak
                 class="space-y-4">
          <div class="rounded-[18px] border border-gray-100 bg-white px-6 py-5 shadow-soft">
            <div class="flex flex-wrap items-start justify-between gap-4">
              <div>
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#E94D2A]">Question active</p>
                <h2 class="mt-2 text-xl font-varela leading-snug text-[#004461]">{{ $question }}</h2>
              </div>
              <span class="inline-flex shrink-0 items-center gap-2 rounded-full bg-[#004461]/5 px-3 py-1 text-sm font-semibold text-[#004461]">
                <span class="respondent-count-{{ $qi }}">0</span>
                réponse(s)
              </span>
            </div>
          </div>

          <div class="rounded-[18px] border border-gray-100 bg-white px-6 py-5 shadow-soft">
            <div class="mb-4 flex items-center justify-between gap-3">
              <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">Nuage généré en direct</p>
              <span class="counter-badge-{{ $qi }} rounded-full bg-gray-100 px-2 py-1 text-[11px] font-bold text-gray-500"></span>
            </div>
            <div class="wc-canvas-wrap">
              <canvas id="cloudCanvas-{{ $qi }}" style="display:none;"></canvas>
              <div id="cloudEmpty-{{ $qi }}" class="wc-empty">
                <p class="text-sm font-semibold text-gray-400">En attente des premières réponses...</p>
              </div>
            </div>
          </div>
        </article>
      @endforeach
    </section>

    <aside class="space-y-4">
      <div class="rounded-[18px] border border-gray-100 bg-white p-5 shadow-soft">
        <p class="text-sm font-bold text-[#004461]">Partager</p>
        <div class="mt-4 rounded-[14px] bg-[#004461]/5 p-4">
          <p class="text-xs font-semibold uppercase tracking-[0.14em] text-gray-400">Code</p>
          <p class="mt-1 font-mono text-3xl font-black tracking-widest text-[#004461]">{{ $wordCloud->access_code }}</p>
        </div>
        <button type="button"
                @click="copyJoinLink()"
                class="mt-4 inline-flex w-full items-center justify-center rounded-full bg-[#004461] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#005d85]">
          <span x-text="copied ? 'Lien copié' : 'Copier le lien'"></span>
        </button>
      </div>

      <div class="rounded-[18px] border border-gray-100 bg-white p-5 shadow-soft">
        <p class="text-sm font-bold text-[#004461]">Prochaine étape</p>
        <div class="mt-4 rounded-[14px] border border-gray-100 bg-gray-50 px-4 py-3">
          @if($nextQuestion)
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-gray-400">Question suivante</p>
            <p class="mt-1 text-sm font-semibold leading-snug text-gray-700">{{ $nextQuestion }}</p>
            <p class="mt-2 text-xs text-gray-400">{{ $remainingQuestions }} question{{ $remainingQuestions > 1 ? 's' : '' }} restante{{ $remainingQuestions > 1 ? 's' : '' }}</p>
          @elseif($wordCloud->is_active)
            <p class="text-sm font-semibold leading-snug text-gray-700">Dernière question en cours.</p>
          @else
            <p class="text-sm font-semibold leading-snug text-gray-700">Le nuage est terminé.</p>
          @endif
        </div>

        <details class="mt-4">
          <summary class="cursor-pointer text-xs font-bold uppercase tracking-[0.14em] text-gray-400 hover:text-[#004461]">
            Voir toutes les questions
          </summary>
          <div class="mt-3 space-y-2">
            @foreach($questions as $qi => $question)
              <form method="POST" action="{{ route('formateur.nuages.question', $wordCloud) }}">
                @csrf
                <input type="hidden" name="question_index" value="{{ $qi }}">
                <button type="submit"
                        @click="activeQ = {{ $qi }}"
                        class="w-full rounded-[12px] border px-3 py-2 text-left transition {{ $qi === $activeQuestionIndex ? 'border-[#E94D2A] bg-orange-50 text-[#004461]' : 'border-gray-200 bg-white text-gray-600 hover:border-[#004461]/40' }}">
                  <span class="block text-[11px] font-bold uppercase tracking-[0.12em] {{ $qi === $activeQuestionIndex ? 'text-[#E94D2A]' : 'text-gray-400' }}">
                    Question {{ $qi + 1 }}
                  </span>
                  <span class="mt-1 block text-xs font-semibold leading-snug">{{ \Illuminate\Support\Str::limit($question, 78) }}</span>
                </button>
              </form>
            @endforeach
          </div>
        </details>
      </div>
    </aside>
  </div>
</div>

<style>
  [x-cloak] { display:none !important; }
  .wc-canvas-wrap { position:relative; min-height:430px; border-radius:1rem; border:1px solid #e5e7eb; background:#f7fafc; overflow:hidden; }
  .wc-canvas-wrap canvas { display:block; }
  .wc-empty { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; }
</style>

<script src="https://cdn.jsdelivr.net/npm/wordcloud@1/src/wordcloud2.min.js"></script>
<script>
  const _wcPalette = ['#004461', '#E94D2A', '#01c69c', '#005d85', '#c43d1f', '#0F766E', '#7C3AED', '#A16207'];
  function _wcColor(w) {
    let h = 0;
    for (let i = 0; i < w.length; i++) { h = ((h << 5) - h) + w.charCodeAt(i); h |= 0; }
    return _wcPalette[Math.abs(h) % _wcPalette.length];
  }

  @foreach($questions as $qi => $question)
  (function() {
    const canvas   = document.getElementById('cloudCanvas-{{ $qi }}');
    const emptyEl  = document.getElementById('cloudEmpty-{{ $qi }}');
    const counter  = document.querySelector('.respondent-count-{{ $qi }}');
    const badge    = document.querySelector('.counter-badge-{{ $qi }}');
    const endpoint = @json(route('formateur.nuages.live.data', $wordCloud)) + '?q={{ $qi }}';
    let lastSig = '';
    let lastW   = 0;

    async function refresh() {
      try {
        const res  = await fetch(endpoint, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        const words = data.words || [];
        const responses = data.respondents ?? data.total_entries ?? 0;
        const sig = JSON.stringify(words);

        if (counter) counter.textContent = responses;
        if (badge) badge.textContent = responses > 0 ? `${responses} réponse${responses > 1 ? 's' : ''}` : '';

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
        canvas.height = 430;

        const max = Math.max(...words.map(w => Number(w.score)));
        WordCloud(canvas, {
          list: words.map(item => [item.word, Number(item.score)]),
          gridSize: Math.max(4, Math.round(8 * W / 800)),
          weightFactor: function(s) { return Math.max(14, Math.round(14 + (s / max) * 66)); },
          fontFamily: '"Varela Round", Arial, sans-serif',
          color: function(word) { return _wcColor(word); },
          rotateRatio: 0.25,
          rotationSteps: 2,
          backgroundColor: '#f7fafc',
          clearCanvas: true,
        });
      } catch (error) {}
    }

    refresh();
    setInterval(refresh, 3000);
  })();
  @endforeach
</script>
@endsection
