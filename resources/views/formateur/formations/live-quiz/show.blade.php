@extends('formateur.formations.master_lecon')

@section('title', 'Session presentielle')

@section('content')
@php
    use Illuminate\Support\Facades\Storage;

    $backToLessonUrl = route('formateur.formations.lecture', [
        'module' => $module->id,
        'section' => $section->id,
        'lecture' => $lecture->id,
    ]);

    $currentQuestionTypeLabel = match ((string) ($currentQuestion->type ?? '')) {
        'single' => 'Choix unique',
        'multiple' => 'Choix multiple',
        'boolean' => 'Vrai / Faux',
        'cloze' => 'Texte a trous',
        default => 'Question',
    };

    $correctOptions = collect($currentQuestion?->options ?? [])->filter(fn ($option) => (bool) ($option->is_correct ?? false));
    $snapshotJson = json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $snapshotUrl = route('formateur.live-quiz.snapshot', [
        'module' => $module->id,
        'section' => $section->id,
        'lecture' => $lecture->id,
        'session' => $session->id,
    ]);
@endphp

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<div class="max-w-[1400px] mx-auto px-6 py-8">
    <div class="flex flex-col gap-6">
        <section class="rounded-[24px] border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="bg-slate-900 text-white px-6 py-6 md:px-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-orange-300">Session presentielle</p>
                        <h1 class="mt-2 text-3xl font-bold">{{ $lecture->lecture_title ?? 'Lecon' }}</h1>
                        <p class="mt-2 text-sm text-slate-200">
                            Code d'acces <span class="font-bold tracking-[0.3em]">{{ $session->access_code }}</span>
                        </p>
                        <p class="mt-1 text-sm text-slate-300" id="live-status-label">{{ $snapshot['status_label'] }}</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ $backToLessonUrl }}"
                           class="inline-flex items-center justify-center rounded-xl border border-white/20 px-4 py-2 text-sm font-semibold text-white hover:bg-white/10 transition">
                            Retour a la lecon
                        </a>

                        @if (! $session->isClosed())
                            @if ($session->isWaiting())
                                <form method="POST" action="{{ route('formateur.live-quiz.start', ['module' => $module->id, 'section' => $section->id, 'lecture' => $lecture->id, 'session' => $session->id]) }}">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-orangeone px-5 py-2.5 text-sm font-bold text-white hover:opacity-90 transition">
                                        Demarrer la session
                                    </button>
                                </form>
                            @endif

                            @if ($session->isQuestionOpen())
                                <form method="POST" action="{{ route('formateur.live-quiz.reveal', ['module' => $module->id, 'section' => $section->id, 'lecture' => $lecture->id, 'session' => $session->id]) }}">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-500 px-5 py-2.5 text-sm font-bold text-white hover:bg-emerald-600 transition">
                                        Afficher la bonne reponse
                                    </button>
                                </form>
                            @endif

                            @if ($session->isQuestionOpen() || $session->isAnswerRevealed())
                                <form method="POST" action="{{ route('formateur.live-quiz.next', ['module' => $module->id, 'section' => $section->id, 'lecture' => $lecture->id, 'session' => $session->id]) }}">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-bold text-slate-800 hover:bg-slate-200 transition">
                                        {{ (int) $session->current_position >= (int) $session->total_questions ? 'Terminer la session' : 'Question suivante' }}
                                    </button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('formateur.live-quiz.close', ['module' => $module->id, 'section' => $section->id, 'lecture' => $lecture->id, 'session' => $session->id]) }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-red-300 px-5 py-2.5 text-sm font-bold text-red-100 hover:bg-red-500/20 transition">
                                    Clore
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div class="border-b border-emerald-100 bg-emerald-50 px-6 py-3 text-sm text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="border-b border-red-100 bg-red-50 px-6 py-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="grid gap-6 p-6 md:grid-cols-[320px_1fr] md:p-8">
                <aside class="rounded-[20px] border border-slate-200 bg-slate-50 p-5">
                    <h2 class="text-lg font-bold text-slate-900">Connexion stagiaire</h2>
                    <p class="mt-2 text-sm text-slate-600">
                        Les stagiaires scannent le QR code puis se connectent a leur compte pour rejoindre la session.
                    </p>

                    <div class="mt-5 rounded-[18px] bg-white p-4 shadow-sm">
                        <div id="live-quiz-qr" class="flex justify-center"></div>
                    </div>

                    <div class="mt-4 rounded-xl border border-slate-200 bg-white p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Lien direct</p>
                        <p class="mt-2 break-all text-sm text-slate-700">{{ $joinUrl }}</p>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-xl bg-white p-4 border border-slate-200">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Participants</p>
                            <p class="mt-2 text-3xl font-bold text-slate-900" id="metric-participants">{{ $snapshot['participant_count'] }}</p>
                        </div>
                        <div class="rounded-xl bg-white p-4 border border-slate-200">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Repondu</p>
                            <p class="mt-2 text-3xl font-bold text-slate-900" id="metric-answered">{{ $snapshot['answered_count'] }}</p>
                        </div>
                        <div class="rounded-xl bg-white p-4 border border-slate-200">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-500">En attente</p>
                            <p class="mt-2 text-3xl font-bold text-slate-900" id="metric-pending">{{ $snapshot['pending_count'] }}</p>
                        </div>
                        <div class="rounded-xl bg-white p-4 border border-slate-200">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Bonnes reponses</p>
                            <p class="mt-2 text-3xl font-bold text-slate-900" id="metric-correct">{{ $snapshot['correct_count'] }}</p>
                        </div>
                    </div>
                </aside>

                <section class="space-y-6">
                    @if (! $currentQuestion)
                        <div class="rounded-[22px] border border-dashed border-slate-300 bg-slate-50 px-8 py-12 text-center">
                            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Salle en attente</p>
                            <h2 class="mt-3 text-2xl font-bold text-slate-900">La session est prete</h2>
                            <p class="mt-3 text-slate-600">
                                Les stagiaires peuvent rejoindre maintenant. Lancez ensuite la premiere question quand tout le monde est en place.
                            </p>
                        </div>
                    @else
                        <div class="rounded-[22px] border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Question en cours</p>
                                    <h2 class="mt-2 text-2xl font-bold text-slate-900">
                                        {{ (int) $session->current_position }} / {{ (int) $session->total_questions }}
                                    </h2>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <span class="inline-flex items-center rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600">
                                            {{ $currentQuestionTypeLabel }}
                                        </span>
                                        <span class="inline-flex items-center rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600">
                                            {{ $snapshot['status_label'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 grid gap-6 {{ !empty($currentQuestion->image_path) ? 'xl:grid-cols-[1fr_320px]' : 'grid-cols-1' }}">
                                <div>
                                    <p class="text-lg leading-relaxed text-slate-900">{{ $currentQuestion->question_text }}</p>

                                    @if (!empty($currentQuestion->audio_path))
                                        <div class="mt-4">
                                            <audio controls class="w-full">
                                                <source src="{{ asset($currentQuestion->audio_path) }}">
                                            </audio>
                                        </div>
                                    @endif

                                    <div class="mt-6 space-y-3" id="option-distribution">
                                        @if ((string) $currentQuestion->type === 'cloze')
                                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-4">
                                                <p class="text-sm text-slate-700">
                                                    Question a trous : le tableau live affiche surtout le nombre de reponses et de bonnes reponses.
                                                </p>
                                            </div>
                                        @else
                                            @foreach ($snapshot['distribution'] as $option)
                                                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-4" data-option-id="{{ $option['id'] }}">
                                                    <div class="flex items-center justify-between gap-4">
                                                        <p class="text-sm font-medium text-slate-800">{{ $option['text'] }}</p>
                                                        <p class="text-sm font-bold text-slate-900" data-option-count>{{ $option['count'] }}</p>
                                                    </div>
                                                    <div class="mt-3 h-3 rounded-full bg-slate-200 overflow-hidden">
                                                        <div class="h-full rounded-full {{ $option['is_correct'] ? 'bg-emerald-500' : 'bg-orange-400' }}"
                                                             data-option-bar
                                                             style="width: 0%"></div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>

                                @if (!empty($currentQuestion->image_path))
                                    <aside>
                                        <img
                                            src="{{ Storage::url($currentQuestion->image_path) }}"
                                            alt="{{ $currentQuestion->image_alt ?? '' }}"
                                            class="w-full rounded-[20px] border border-slate-200 object-contain"
                                        >
                                    </aside>
                                @endif
                            </div>
                        </div>

                        <div x-data="{ open: false }" class="rounded-[22px] border border-slate-200 bg-slate-50 p-6">
                            <button type="button"
                                    @click="open = !open"
                                    class="flex w-full items-center justify-between gap-4 text-left">
                                <div>
                                    <h3 class="text-lg font-bold text-slate-900">Corrige formateur</h3>
                                    <p class="mt-1 text-sm text-slate-600">
                                        Visible uniquement de votre cote pour preparer l'explication orale.
                                    </p>
                                </div>

                                <span class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition">
                                    <span x-text="open ? 'Masquer' : 'Voir le corrige'"></span>
                                    <svg class="h-4 w-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </span>
                            </button>

                            <div x-show="open" x-collapse class="mt-4 space-y-3">
                                @if ((string) $currentQuestion->type === 'cloze')
                                    @php
                                        $blanks = is_array(data_get($currentQuestion->payload, 'blanks')) ? data_get($currentQuestion->payload, 'blanks') : [];
                                    @endphp
                                    @forelse ($blanks as $blankKey => $blank)
                                        <div class="rounded-xl border border-emerald-200 bg-white px-4 py-3">
                                            <p class="text-sm font-semibold text-slate-900">{{ $blankKey }}</p>
                                            <p class="mt-1 text-sm text-emerald-700">
                                                {{ implode(' / ', is_array($blank['accepted_answers'] ?? null) ? $blank['accepted_answers'] : []) }}
                                            </p>
                                        </div>
                                    @empty
                                        <p class="text-sm text-slate-600">Aucune reponse configuree.</p>
                                    @endforelse
                                @else
                                    @foreach ($correctOptions as $option)
                                        <div class="rounded-xl border border-emerald-200 bg-white px-4 py-3">
                                            <p class="text-sm font-semibold text-slate-900">{{ $option->option_text }}</p>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @endif
                </section>
            </div>
        </section>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const joinUrl = @json($joinUrl);
        const qrTarget = document.getElementById('live-quiz-qr');
        const snapshotUrl = @json($snapshotUrl);
        let currentStateKey = @json($snapshot['state_key']);
        let currentSnapshot = {!! $snapshotJson !!};

        if (qrTarget && typeof QRCode !== 'undefined') {
            new QRCode(qrTarget, {
                text: joinUrl,
                width: 220,
                height: 220,
            });
        }

        function updateMetrics(data) {
            const participantNode = document.getElementById('metric-participants');
            const answeredNode = document.getElementById('metric-answered');
            const pendingNode = document.getElementById('metric-pending');
            const correctNode = document.getElementById('metric-correct');
            const statusNode = document.getElementById('live-status-label');

            if (participantNode) participantNode.textContent = String(data.participant_count ?? 0);
            if (answeredNode) answeredNode.textContent = String(data.answered_count ?? 0);
            if (pendingNode) pendingNode.textContent = String(data.pending_count ?? 0);
            if (correctNode) correctNode.textContent = String(data.correct_count ?? 0);
            if (statusNode) statusNode.textContent = String(data.status_label ?? '');

            const total = Math.max(1, Number(data.participant_count ?? 0));
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
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
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
