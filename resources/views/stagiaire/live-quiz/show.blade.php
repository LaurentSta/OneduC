@extends('stagiaire.formations.master_lecon_evaluation')

@section('title', 'Session presentielle')

@section('content')
@php
    use Illuminate\Support\Facades\Storage;

    $question = $currentQuestion;
    $attemptQuestion = $currentAttemptQuestion;
    $clozePayload = is_array($question?->payload ?? null) ? $question->payload : [];
    $clozeRawText = (string) ($clozePayload['raw_text'] ?? '');
    $correctOptions = collect(data_get($correction, 'correct_options', []));
@endphp

<div class="max-w-[980px] mx-auto px-6 py-10">
    <div class="rounded-[24px] border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="bg-slate-900 px-8 py-7 text-white">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-orange-300">Session presentielle</p>
                    <h1 class="mt-2 text-3xl font-bold">{{ $lecture->lecture_title ?? 'Lecon' }}</h1>
                    <p class="mt-2 text-sm text-slate-300">
                        Code {{ $session->access_code }} - Question {{ max(0, (int) $snapshot['current_position']) }} / {{ (int) $snapshot['total_questions'] }}
                    </p>
                </div>

                @if ($summary)
                    <div class="flex gap-3 text-center">
                        <div class="rounded-xl bg-white/10 px-4 py-3">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-300">Repondu</p>
                            <p class="mt-2 text-2xl font-bold">{{ $summary['answered'] }}</p>
                        </div>
                        <div class="rounded-xl bg-white/10 px-4 py-3">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-300">Correct</p>
                            <p class="mt-2 text-2xl font-bold">{{ $summary['correct'] }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="p-8">
            @if ($session->isClosed())
                <div class="rounded-[20px] border border-emerald-200 bg-emerald-50 px-6 py-6">
                    <h2 class="text-2xl font-bold text-emerald-900">Session terminee</h2>
                    <p class="mt-3 text-sm leading-6 text-emerald-800">
                        Vos reponses ont ete enregistrees.
                    </p>

                    @if ($summary)
                        <div class="mt-6 grid gap-4 md:grid-cols-3">
                            <div class="rounded-xl bg-white px-4 py-4">
                                <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Questions posees</p>
                                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $summary['total'] }}</p>
                            </div>
                            <div class="rounded-xl bg-white px-4 py-4">
                                <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Bonnes reponses</p>
                                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $summary['correct'] }}</p>
                            </div>
                            <div class="rounded-xl bg-white px-4 py-4">
                                <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Score</p>
                                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $summary['score'] }}%</p>
                            </div>
                        </div>
                    @endif

                    <div class="mt-6 rounded-xl border border-emerald-200 bg-white px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">Et maintenant</p>
                        <p class="mt-2 text-sm text-slate-700">
                            Revenir a la lecon ou passer simplement a la suite.
                        </p>
                    </div>

                    <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                        <a
                            href="{{ $returnToLessonUrl }}"
                            class="inline-flex items-center justify-center rounded-xl border border-emerald-300 bg-white px-5 py-3 text-sm font-bold text-emerald-900 transition hover:bg-emerald-100"
                        >
                            Retour a la lecon
                        </a>

                        @if ($nextUrl)
                            <a
                                href="{{ $nextUrl }}"
                                class="inline-flex items-center justify-center rounded-xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-800"
                            >
                                Lecon suivante
                            </a>
                        @endif
                    </div>
                </div>
            @elseif ($session->isWaiting() || ! $question)
                <div class="rounded-[20px] border border-dashed border-slate-300 bg-slate-50 px-8 py-12 text-center">
                    <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">En attente</p>
                    <h2 class="mt-3 text-2xl font-bold text-slate-900">Le formateur n'a pas encore lance la premiere question</h2>
                    <p class="mt-3 text-slate-600">
                        Restez sur cette page. Elle se mettra a jour automatiquement des que la session demarre.
                    </p>
                </div>
            @elseif ($session->isQuestionOpen() && ! is_null($attemptQuestion?->answered_at))
                <div class="rounded-[20px] border border-sky-200 bg-sky-50 px-8 py-12 text-center">
                    <p class="text-sm font-semibold uppercase tracking-[0.22em] text-sky-600">Reponse enregistree</p>
                    <h2 class="mt-3 text-2xl font-bold text-slate-900">Merci, votre reponse a bien ete envoyee</h2>
                    <p class="mt-3 text-slate-600">
                        Attendez maintenant l'affichage de la correction ou la question suivante.
                    </p>
                </div>
            @elseif ($session->isAnswerRevealed())
                <div class="space-y-6">
                    <div class="rounded-[20px] border border-emerald-200 bg-emerald-50 px-6 py-6">
                        <p class="text-sm font-semibold uppercase tracking-[0.22em] text-emerald-700">Correction affichee</p>
                        <h2 class="mt-3 text-[28px] font-bold text-slate-900">{{ $question->question_text }}</h2>
                    </div>

                    <div class="rounded-[20px] border border-slate-200 bg-white p-6">
                        @if ((string) $question->type === 'cloze')
                            <div class="space-y-3">
                                @foreach (data_get($correction, 'blanks', []) as $blankKey => $blank)
                                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-4">
                                        <p class="text-sm font-semibold text-slate-900">{{ $blankKey }}</p>
                                        <p class="mt-1 text-sm text-slate-700">
                                            Votre reponse : {{ data_get($blank, 'answer') !== '' ? data_get($blank, 'answer') : 'Aucune' }}
                                        </p>
                                        <p class="mt-1 text-sm {{ data_get($blank, 'is_correct') ? 'text-emerald-700' : 'text-red-600' }}">
                                            {{ data_get($blank, 'is_correct') ? 'Correct' : 'Incorrect' }}
                                        </p>
                                        <p class="mt-1 text-sm text-slate-600">
                                            Reponse attendue : {{ implode(' / ', data_get($blank, 'accepted_answers', [])) }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach ($correctOptions as $option)
                                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-emerald-900">
                                        {{ $option->option_text }}
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <p class="mt-5 text-sm text-slate-600">
                            La page se mettra a jour automatiquement quand le formateur passera a la suite.
                        </p>
                    </div>
                </div>
            @else
                <div class="grid gap-6 {{ !empty($question->image_path) ? 'xl:grid-cols-[1fr_300px]' : 'grid-cols-1' }}">
                    <div class="rounded-[20px] border border-slate-200 bg-white p-6">
                        @php
                            $questionType = (string) ($question->type ?? 'single');
                            $selectionInstruction = match ($questionType) {
                                'multiple' => 'Veuillez sélectionner une ou plusieurs réponses. Plusieurs réponses sont possibles.',
                                'cloze' => 'Veuillez compléter tous les champs, puis valider.',
                                default => 'Veuillez répondre en sélectionnant une seule réponse.',
                            };
                            $oldSingleAnswer = (string) old('answer', '');
                            $oldMultipleAnswers = collect(old('answers', []))
                                ->flatten()
                                ->filter(static fn ($value) => $value !== null && $value !== '')
                                ->map(static fn ($value) => (string) $value)
                                ->values()
                                ->all();
                        @endphp

                        @if ($errors->any())
                            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Question live</p>
                        <h2 class="mt-3 text-2xl font-bold text-slate-900">{{ $question->question_text }}</h2>

                        @if (!empty($question->audio_path))
                            <div class="mt-4">
                                <audio controls class="w-full">
                                    <source src="{{ asset($question->audio_path) }}">
                                </audio>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('stagiaire.live-quiz.answer', ['session' => $session->id]) }}" class="mt-6">
                            @csrf

                            <p class="mb-4 text-sm font-medium text-slate-500">
                                {{ $selectionInstruction }}
                            </p>

                            @if ($questionType === 'cloze')
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    <div id="live-cloze-container" class="text-[17px] leading-relaxed text-slate-900 font-mono break-words"></div>
                                </div>
                            @else
                                <fieldset class="space-y-3">
                                    <legend class="sr-only">{{ $selectionInstruction }}</legend>
                                    @foreach ($question->options as $option)
                                        @if ($questionType === 'multiple')
                                            <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-4 hover:bg-slate-50 cursor-pointer">
                                                <input type="checkbox" name="answers[]" value="{{ $option->id }}" class="mt-1" @checked(in_array((string) $option->id, $oldMultipleAnswers, true))>
                                                <span class="text-lg leading-relaxed text-slate-800">{{ $option->option_text }}</span>
                                            </label>
                                        @else
                                            <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-4 hover:bg-slate-50 cursor-pointer">
                                                <input type="radio" name="answer" value="{{ $option->id }}" class="mt-1" required @checked($oldSingleAnswer === (string) $option->id)>
                                                <span class="text-lg leading-relaxed text-slate-800">{{ $option->option_text }}</span>
                                            </label>
                                        @endif
                                    @endforeach
                                </fieldset>
                            @endif

                            <input type="hidden" name="time_spent" id="time_spent_input" value="0">

                            <div class="mt-4 flex items-center justify-between">
                                <p class="text-xs font-mono text-slate-400" id="timer_display">00:00</p>
                                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-orangeone px-6 py-3 text-sm font-bold text-white hover:opacity-90 transition">
                                    Envoyer ma reponse
                                </button>
                            </div>
                        </form>
                    </div>

                    @if (!empty($question->image_path))
                        <aside>
                            <img
                                src="{{ Storage::url($question->image_path) }}"
                                alt="{{ $question->image_alt ?? '' }}"
                                class="w-full rounded-[20px] border border-slate-200 object-contain"
                            >
                        </aside>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        let currentStateKey = @json($snapshot['state_key']);
        const snapshotUrl = @json(route('stagiaire.live-quiz.snapshot', ['session' => $session->id]));
        const timeInput = document.getElementById('time_spent_input');
        const timerDisplay = document.getElementById('timer_display');

        if (timeInput) {
            let seconds = 0;
            window.setInterval(function () {
                seconds += 1;
                timeInput.value = String(seconds);

                const minutes = String(Math.floor(seconds / 60)).padStart(2, '0');
                const remaining = String(seconds % 60).padStart(2, '0');
                if (timerDisplay) {
                    timerDisplay.textContent = `${minutes}:${remaining}`;
                }
            }, 1000);
        }

        const clozeContainer = document.getElementById('live-cloze-container');
        if (clozeContainer) {
            const rawText = @json($clozeRawText);
            const oldAnswers = @json(old('answers', []));
            const regex = new RegExp('\\{\\{\\s*([A-Za-z0-9_]+)\\s*\\}\\}', 'g');
            const fragment = document.createDocumentFragment();
            let lastIndex = 0;
            let match = null;

            while ((match = regex.exec(rawText)) !== null) {
                const key = String(match[1] || '').trim();
                const before = rawText.slice(lastIndex, match.index);
                if (before) {
                    fragment.appendChild(document.createTextNode(before));
                }

                const input = document.createElement('input');
                input.type = 'text';
                input.name = `answers[${key}]`;
                input.required = true;
                input.value = Object.prototype.hasOwnProperty.call(oldAnswers, key) ? String(oldAnswers[key] ?? '') : '';
                input.className = 'inline-block w-32 border-b-2 border-slate-400 focus:border-blue-600 outline-none text-center px-1 bg-transparent mx-1 align-baseline';
                fragment.appendChild(input);

                lastIndex = match.index + match[0].length;
            }

            const tail = rawText.slice(lastIndex);
            if (tail) {
                fragment.appendChild(document.createTextNode(tail));
            }

            clozeContainer.textContent = '';
            clozeContainer.appendChild(fragment);
        }

        window.setInterval(function () {
            fetch(snapshotUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    if (data.state_key !== currentStateKey) {
                        window.location.reload();
                    }
                })
                .catch(function () {});
        }, 2000);
    });
</script>
@endsection
