@extends('stagiaire.master')

@section('content')
@php
    $question = $currentQuestion;
    $clozePayload = is_array($question?->payload ?? null) ? $question->payload : [];
    $clozeRawText = (string) ($clozePayload['raw_text'] ?? '');
    $correctOptions = collect(data_get($correction, 'correct_options', []));
    $typeLabel = match($question?->type) {
        'boolean' => 'Vrai/Faux',
        'single' => 'Choix unique',
        'multiple' => 'Choix multiples',
        'cloze' => 'Texte à trous',
        default => '',
    };
@endphp

<div class="max-w-[980px] mx-auto px-6 py-10">
  <div class="rounded-[24px] border border-slate-200 bg-white shadow-sm overflow-hidden">
    <div class="bg-bleuone px-8 py-7 text-white">
      <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-white/70">Quiz en direct</p>
      <h1 class="mt-2 text-3xl font-bold">
        {{ $session->group?->name ?? 'Tous les groupes' }}
      </h1>
      <p class="mt-2 text-sm text-white/80">
        Code {{ $session->access_code }} · Question {{ max(0, (int) $snapshot['current_position']) }} / {{ (int) $snapshot['total_questions'] }}
      </p>
    </div>

    <div class="p-8">
      @if($session->isClosed())
        <div class="rounded-[20px] border border-emerald-200 bg-emerald-50 px-6 py-6">
          <h2 class="text-2xl font-bold text-emerald-900">Quiz terminé</h2>
          <p class="mt-3 text-sm leading-6 text-emerald-800">Vos réponses ont été enregistrées. Merci pour votre participation.</p>
        </div>
      @elseif($session->isWaiting() || !$question)
        <div class="rounded-[20px] border border-dashed border-slate-300 bg-slate-50 px-8 py-12 text-center">
          <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">En attente</p>
          <h2 class="mt-3 text-2xl font-bold text-slate-900">Le formateur n'a pas encore lancé la première question</h2>
          <p class="mt-3 text-slate-600">Restez sur cette page, elle se mettra à jour automatiquement dès que la session démarre.</p>
        </div>
      @elseif($session->isQuestionOpen() && $myAnswer)
        <div class="rounded-[20px] border border-sky-200 bg-sky-50 px-8 py-12 text-center">
          <p class="text-sm font-semibold uppercase tracking-[0.22em] text-sky-600">Réponse enregistrée</p>
          <h2 class="mt-3 text-2xl font-bold text-slate-900">Merci, votre réponse a bien été envoyée</h2>
          <p class="mt-3 text-slate-600">Attendez maintenant l'affichage de la correction ou la question suivante.</p>
        </div>
      @elseif($session->isAnswerRevealed())
        <div class="space-y-6">
          <div class="rounded-[20px] border border-emerald-200 bg-emerald-50 px-6 py-6">
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-emerald-700">Correction affichée</p>
            <h2 class="mt-3 text-[28px] font-bold text-slate-900">{{ $question->question_text }}</h2>
          </div>

          <div class="rounded-[20px] border border-slate-200 bg-white p-6">
            @if($question->type === 'cloze')
              <div class="space-y-3">
                @foreach(data_get($correction, 'blanks', []) as $blankKey => $blank)
                  <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-sm font-semibold text-slate-900">{{ $blankKey }}</p>
                    <p class="mt-1 text-sm text-slate-700">
                      Réponse attendue : {{ implode(' / ', data_get($blank, 'accepted_answers', [])) }}
                    </p>
                  </div>
                @endforeach
              </div>
            @else
              <div class="space-y-3">
                @foreach($correctOptions as $option)
                  <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-emerald-900">
                    {{ $option->option_text }}
                  </div>
                @endforeach
              </div>
            @endif

            <p class="mt-5 text-sm text-slate-600">
              La page se mettra à jour automatiquement quand le formateur passera à la suite.
            </p>
          </div>
        </div>
      @else
        <div class="rounded-[20px] border border-slate-200 bg-white p-6">
          @if($errors->any())
            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
              {{ $errors->first() }}
            </div>
          @endif

          <span class="inline-flex items-center rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600 mb-3">
            {{ $typeLabel }}
          </span>
          <h2 class="text-2xl font-bold text-slate-900">{{ $question->question_text }}</h2>

          <form method="POST" action="{{ route('stagiaire.group-quiz.answer', $session) }}" class="mt-6">
            @csrf

            @if($question->type === 'cloze')
              <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <div id="group-quiz-cloze-container" class="text-[17px] leading-relaxed text-slate-900 font-mono break-words"></div>
              </div>
            @else
              <p class="mb-4 text-sm font-medium text-slate-500">
                {{ $question->type === 'multiple' ? 'Sélectionnez une ou plusieurs réponses.' : 'Sélectionnez une seule réponse.' }}
              </p>
              <fieldset class="space-y-3">
                @foreach($question->options as $option)
                  @if($question->type === 'multiple')
                    <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-4 hover:bg-slate-50 cursor-pointer">
                      <input type="checkbox" name="answers[]" value="{{ $option->id }}" class="mt-1">
                      <span class="text-lg leading-relaxed text-slate-800">{{ $option->option_text }}</span>
                    </label>
                  @else
                    <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-4 hover:bg-slate-50 cursor-pointer">
                      <input type="radio" name="answer" value="{{ $option->id }}" required class="mt-1">
                      <span class="text-lg leading-relaxed text-slate-800">{{ $option->option_text }}</span>
                    </label>
                  @endif
                @endforeach
              </fieldset>
            @endif

            <div class="mt-4 flex justify-end">
              <button type="submit" class="btn-oneduc !px-6 !py-3 !text-sm">
                Envoyer ma réponse
              </button>
            </div>
          </form>
        </div>
      @endif
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    let currentStateKey = @json($snapshot['state_key']);
    const snapshotUrl = @json(route('stagiaire.group-quiz.snapshot', $session));

    const clozeContainer = document.getElementById('group-quiz-cloze-container');
    if (clozeContainer) {
      const rawText = @json($clozeRawText);
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
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
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
