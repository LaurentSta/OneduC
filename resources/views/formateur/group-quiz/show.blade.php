@extends('formateur.dashboard')

@section('formateur')
@php
    $currentSessionQuestion = $session->currentSessionQuestion();
    $currentQuestion = $currentSessionQuestion?->question;
    $snapshotJson = json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
@endphp

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<div class="w-full px-6 lg:px-8">
  <header class="my-6 overflow-hidden rounded-[22px] bg-white shadow-md border border-gray-100">
    <div class="bg-[#004461] px-6 py-5 text-white">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
          <p class="text-xs font-bold uppercase tracking-[0.18em] text-white/60">Quiz en direct</p>
          <h1 class="mt-1 text-2xl font-raleway font-semibold leading-tight">
            {{ $session->group?->name ?? 'Tous les groupes' }}
          </h1>
          <div class="mt-3 flex flex-wrap items-center gap-2 text-sm">
            <span class="rounded-full bg-white/10 px-3 py-1 font-mono">Code : {{ $session->access_code }}</span>
            <span class="rounded-full bg-white/10 px-3 py-1" id="live-status-label">{{ $snapshot['status_label'] }}</span>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <a href="{{ route('formateur.outils.quiz.index') }}"
             class="btn-oneduc-outline !border-white/25 !bg-transparent !px-4 !py-2 !text-sm !text-white hover:!border-white hover:!bg-white hover:!text-[#004461]">
            ← Outils
          </a>

          @if(!$session->isClosed())
            @if($session->isWaiting())
              <form method="POST" action="{{ route('formateur.group-quiz.start', $session) }}">
                @csrf
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[#E94D2A] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#c43d1f] transition">
                  Démarrer la session
                </button>
              </form>
            @endif

            @if($session->isQuestionOpen())
              <form method="POST" action="{{ route('formateur.group-quiz.reveal', $session) }}">
                @csrf
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-500 px-5 py-2.5 text-sm font-bold text-white hover:bg-emerald-600 transition">
                  Afficher la bonne réponse
                </button>
              </form>
            @endif

            @if($session->isQuestionOpen() || $session->isAnswerRevealed())
              <form method="POST" action="{{ route('formateur.group-quiz.next', $session) }}">
                @csrf
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-white/10 px-5 py-2.5 text-sm font-bold text-white hover:bg-white/20 transition">
                  {{ (int) $session->current_position >= (int) $session->total_questions ? 'Terminer le quiz' : 'Question suivante' }}
                </button>
              </form>
            @endif

            <form method="POST" action="{{ route('formateur.group-quiz.close', $session) }}">
              @csrf
              <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-red-300 px-5 py-2.5 text-sm font-bold text-red-100 hover:bg-red-500/20 transition">
                Clore
              </button>
            </form>
          @endif
        </div>
      </div>
    </div>

    @if(session('success'))
      <div class="border-b border-emerald-100 bg-emerald-50 px-6 py-3 text-sm text-emerald-800">
        {{ session('success') }}
      </div>
    @endif

    @if($errors->any())
      <div class="border-b border-red-100 bg-red-50 px-6 py-3 text-sm text-red-700">
        {{ $errors->first() }}
      </div>
    @endif

    <div class="grid gap-6 p-6 md:grid-cols-[320px_1fr] md:p-8">
      <aside class="rounded-[20px] border border-gray-200 bg-gray-50 p-5">
        <h2 class="text-lg font-bold text-gray-900">Connexion stagiaire</h2>
        <p class="mt-2 text-sm text-gray-600">
          @if($session->isForAllGroups())
            Tous vos stagiaires peuvent rejoindre en scannant le QR code.
          @else
            Les stagiaires de {{ $session->group?->name }} peuvent rejoindre en scannant le QR code.
          @endif
        </p>

        <div class="mt-5 rounded-[18px] bg-white p-4 shadow-sm">
          <div id="group-quiz-qr" class="flex justify-center"></div>
        </div>

        <div class="mt-4 rounded-xl border border-gray-200 bg-white p-4">
          <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Lien direct</p>
          <p class="mt-2 break-all text-sm text-gray-700">{{ $joinUrl }}</p>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-3">
          <div class="rounded-xl bg-white p-4 border border-gray-200">
            <p class="text-xs uppercase tracking-[0.18em] text-gray-500">Participants</p>
            <p class="mt-2 text-3xl font-bold text-gray-900" id="metric-participants">{{ $snapshot['participant_count'] }}</p>
          </div>
          <div class="rounded-xl bg-white p-4 border border-gray-200">
            <p class="text-xs uppercase tracking-[0.18em] text-gray-500">Répondu</p>
            <p class="mt-2 text-3xl font-bold text-gray-900" id="metric-answered">{{ $snapshot['answered_count'] }}</p>
          </div>
          <div class="rounded-xl bg-white p-4 border border-gray-200 col-span-2">
            <p class="text-xs uppercase tracking-[0.18em] text-gray-500">Bonnes réponses</p>
            <p class="mt-2 text-3xl font-bold text-gray-900" id="metric-correct">{{ $snapshot['correct_count'] }}</p>
          </div>
        </div>
      </aside>

      <section class="space-y-6">
        @if(!$currentQuestion)
          <div class="rounded-[22px] border border-dashed border-gray-300 bg-gray-50 px-8 py-12 text-center">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-gray-500">Salle en attente</p>
            <h2 class="mt-3 text-2xl font-bold text-gray-900">Le quiz est prêt</h2>
            <p class="mt-3 text-gray-600">
              Les stagiaires peuvent rejoindre maintenant. Démarrez la première question quand tout le monde est en place.
            </p>
          </div>
        @else
          @php
            $typeLabel = match($currentQuestion->type) {
              'boolean' => 'Vrai/Faux',
              'single' => 'Choix unique',
              'multiple' => 'Choix multiples',
              'cloze' => 'Texte à trous',
              default => $currentQuestion->type,
            };
          @endphp
          <div class="rounded-[22px] border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
              <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-gray-400">Question en cours</p>
                <h2 class="mt-2 text-2xl font-bold text-gray-900">
                  {{ (int) $session->current_position }} / {{ (int) $session->total_questions }}
                </h2>
                <span class="mt-2 inline-flex items-center rounded-full border border-gray-200 px-3 py-1 text-xs font-semibold text-gray-600">
                  {{ $typeLabel }}
                </span>
              </div>
            </div>

            <div class="mt-6">
              <p class="text-lg leading-relaxed text-gray-900">{{ $currentQuestion->question_text }}</p>

              <div class="mt-6 space-y-3" id="option-distribution">
                @if($currentQuestion->type === 'cloze')
                  <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-4">
                    <p class="text-sm text-gray-700">
                      Question à trous : le tableau ci-dessus affiche le nombre de réponses reçues et de bonnes réponses.
                    </p>
                  </div>
                @else
                  @foreach($snapshot['distribution'] as $option)
                    <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-4" data-option-id="{{ $option['id'] }}">
                      <div class="flex items-center justify-between gap-4">
                        <p class="text-sm font-medium text-gray-800">{{ $option['text'] }}</p>
                        <p class="text-sm font-bold text-gray-900" data-option-count>{{ $option['count'] }}</p>
                      </div>
                      <div class="mt-3 h-3 rounded-full bg-gray-200 overflow-hidden">
                        <div class="h-full rounded-full {{ $option['is_correct'] ? 'bg-emerald-500' : 'bg-orange-400' }}"
                             data-option-bar
                             style="width: 0%"></div>
                      </div>
                    </div>
                  @endforeach
                @endif
              </div>
            </div>
          </div>
        @endif
      </section>
    </div>
  </header>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const joinUrl = @json($joinUrl);
    const qrTarget = document.getElementById('group-quiz-qr');
    const snapshotUrl = @json(route('formateur.group-quiz.snapshot', $session));
    let currentStateKey = @json($snapshot['state_key']);
    let currentSnapshot = {!! $snapshotJson !!};

    if (qrTarget && typeof QRCode !== 'undefined') {
      new QRCode(qrTarget, { text: joinUrl, width: 220, height: 220 });
    }

    function updateMetrics(data) {
      const participantNode = document.getElementById('metric-participants');
      const answeredNode = document.getElementById('metric-answered');
      const correctNode = document.getElementById('metric-correct');
      const statusNode = document.getElementById('live-status-label');

      if (participantNode) participantNode.textContent = String(data.participant_count ?? 0);
      if (answeredNode) answeredNode.textContent = String(data.answered_count ?? 0);
      if (correctNode) correctNode.textContent = String(data.correct_count ?? 0);
      if (statusNode) statusNode.textContent = String(data.status_label ?? '');

      const total = Math.max(1, Number(data.answered_count ?? 0));
      document.querySelectorAll('[data-option-id]').forEach(function (container) {
        const optionId = Number(container.getAttribute('data-option-id'));
        const optionData = Array.isArray(data.distribution)
          ? data.distribution.find(function (item) { return Number(item.id) === optionId; })
          : null;

        const count = Number(optionData?.count ?? 0);
        const countNode = container.querySelector('[data-option-count]');
        const barNode = container.querySelector('[data-option-bar]');

        if (countNode) countNode.textContent = String(count);
        if (barNode) barNode.style.width = `${Math.min(100, Math.round((count / total) * 100))}%`;
      });
    }

    updateMetrics(currentSnapshot);

    window.setInterval(function () {
      fetch(snapshotUrl, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
      })
        .then(function (response) { return response.json(); })
        .then(function (data) {
          updateMetrics(data);
          if (data.state_key !== currentStateKey) {
            window.location.reload();
          }
        })
        .catch(function () {});
    }, 2000);
  });
</script>
@endsection
