@extends('formateur.formations.master_lecon')

@section('content')
@php
  use Illuminate\Support\Facades\Storage;

  $contextQuery = is_array($contextQuery ?? null) ? $contextQuery : [];
  $hasImage = !empty($question->image_path);
  $questionTypeLabel = match ((string) ($question->type ?? 'single')) {
      'single' => 'Choix unique',
      'multiple' => 'Choix multiple',
      'boolean' => 'Vrai / Faux',
      'cloze' => 'Texte à trous',
      default => ucfirst((string) $question->type),
  };

  $clozePayload = is_array($question->payload ?? null) ? $question->payload : [];
  $clozeRawText = (string) ($clozePayload['raw_text'] ?? '');
  $clozeBlanks = is_array($clozePayload['blanks'] ?? null) ? $clozePayload['blanks'] : [];
  $clozeBlankCount = count($clozeBlanks);

  $correctOptions = collect($question->options ?? [])->filter(fn ($opt) => (bool) ($opt->is_correct ?? false));

  $backToLessonUrl = route('formateur.formations.lecture', [
      'module'  => $module->id,
      'section' => $section->id,
      'lecture' => $lecture->id,
  ] + $contextQuery);
@endphp

<div class="max-w-[1300px] mx-auto px-6 py-8">
  <div class="grid grid-cols-1 xl:grid-cols-[1fr_380px] gap-6">
    <section class="bg-white rounded-[20px] border border-gray-100 shadow-sm p-6 md:p-8">
      <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-6">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-gray-400">Mode Pedagogue</p>
          <h1 class="text-2xl font-bold text-bleuone mt-1">Questionnaire de controle</h1>
          <p class="text-sm text-gray-600 mt-2">
            {{ $lecture->lecture_title ?? 'Lecon' }} - Question {{ (int) $aq->position }} / {{ (int) $attempt->total_questions }}
          </p>
        </div>

        <a href="{{ $backToLessonUrl }}"
           class="inline-flex items-center justify-center px-4 py-2 rounded-lg border border-gray-200 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
          Retour a la lecon
        </a>
      </div>

      @if ($errors->any())
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
          {{ $errors->first() }}
        </div>
      @endif

      <div class="rounded-2xl border border-gray-200 p-5 md:p-6 bg-gray-50/60">
        <div class="flex flex-wrap items-center gap-2 mb-4">
          <span class="inline-flex items-center px-3 py-1 rounded-full bg-white border border-gray-200 text-xs font-bold text-gray-600">
            {{ $questionTypeLabel }}
          </span>
          <span class="inline-flex items-center px-3 py-1 rounded-full bg-white border border-gray-200 text-xs font-bold text-gray-600">
            @if((string) ($question->type ?? '') === 'cloze')
              {{ $clozeBlankCount }} trou{{ $clozeBlankCount > 1 ? 's' : '' }}
            @else
              {{ $question->options->count() }} option{{ $question->options->count() > 1 ? 's' : '' }}
            @endif
          </span>
        </div>

        <p class="text-[18px] leading-relaxed text-gray-900 font-medium">{{ $question->question_text }}</p>

        @if (!empty($question->audio_path))
          <div class="mt-4">
            <audio controls class="w-full">
              <source src="{{ asset($question->audio_path) }}">
            </audio>

            @if (!empty($question->audio_transcript))
              <details class="mt-2 text-sm">
                <summary class="cursor-pointer text-bleuone font-semibold">Afficher la transcription</summary>
                <div class="mt-2 p-3 rounded-lg bg-white border border-gray-200 text-gray-700 whitespace-pre-line">{{ $question->audio_transcript }}</div>
              </details>
            @endif
          </div>
        @endif
      </div>

      <form method="POST"
            action="{{ route('formateur.lesson.quiz.answer', ['module' => $module->id, 'section' => $section->id, 'lecture' => $lecture->id, 'attempt' => $attempt->id] + $contextQuery) }}"
            class="mt-6">
        @csrf

        <fieldset class="space-y-3">
          <legend class="text-sm font-semibold text-gray-700 mb-3">Selection pour test du parcours</legend>

          @if((string) $question->type === 'cloze')
            <div class="rounded-xl border border-gray-200 bg-white p-4">
              <p class="text-xs uppercase tracking-wider text-gray-500 font-semibold mb-3">Compléter la formule</p>
              <div id="cloze-container-formateur" class="text-[17px] leading-relaxed text-gray-900 font-mono break-words"></div>
              <p class="mt-3 text-xs text-gray-500">Remplissez chaque champ pour tester la correction.</p>
            </div>

            <script>
              document.addEventListener('DOMContentLoaded', function () {
                const container = document.getElementById('cloze-container-formateur');
                if (!container) return;

                const rawText = @json($clozeRawText);
                const oldAnswers = @json(old('answers', []));
                const regex = new RegExp('\\{\\{\\s*([A-Za-z0-9_]+)\\s*\\}\\}', 'g');

                const fragment = document.createDocumentFragment();
                let lastIndex = 0;
                let match = null;
                let hasBlank = false;

                while ((match = regex.exec(rawText)) !== null) {
                  hasBlank = true;
                  const key = String(match[1] || '').trim();
                  const textBefore = rawText.slice(lastIndex, match.index);
                  if (textBefore) {
                    fragment.appendChild(document.createTextNode(textBefore));
                  }

                  const input = document.createElement('input');
                  input.type = 'text';
                  input.name = `answers[${key}]`;
                  input.value = Object.prototype.hasOwnProperty.call(oldAnswers, key) ? String(oldAnswers[key] ?? '') : '';
                  input.required = true;
                  input.className = 'inline-block w-32 border-b-2 border-gray-400 focus:border-blue-600 outline-none text-center px-1 bg-transparent mx-1 align-baseline';
                  fragment.appendChild(input);

                  lastIndex = match.index + match[0].length;
                }

                const tail = rawText.slice(lastIndex);
                if (tail) {
                  fragment.appendChild(document.createTextNode(tail));
                }

                container.textContent = '';
                container.appendChild(fragment);

                if (!hasBlank) {
                  const warn = document.createElement('p');
                  warn.className = 'text-sm text-red-600';
                  warn.textContent = 'Question à trous mal configurée: aucun placeholder détecté.';
                  container.appendChild(warn);
                }
              });
            </script>
          @else
            @foreach($question->options as $opt)
              @if((string) $question->type === 'multiple')
                <label class="flex items-start gap-3 rounded-xl border border-gray-200 bg-white p-4 hover:bg-gray-50 cursor-pointer">
                  <input type="checkbox" name="answers[]" value="{{ $opt->id }}" class="mt-1">
                  <span class="text-gray-800">{{ $opt->option_text }}</span>
                </label>
              @else
                <label class="flex items-start gap-3 rounded-xl border border-gray-200 bg-white p-4 hover:bg-gray-50 cursor-pointer">
                  <input type="radio" name="answer" value="{{ $opt->id }}" class="mt-1" required>
                  <span class="text-gray-800">{{ $opt->option_text }}</span>
                </label>
              @endif
            @endforeach
          @endif
        </fieldset>

        <div class="mt-6 flex flex-wrap items-center gap-3">
          <button type="submit"
                  class="btn-oneduc !px-6 !py-3 !text-sm">
            Valider la reponse test
          </button>

          <a href="{{ $backToLessonUrl }}"
             class="btn-oneduc-outline !px-6 !py-3 !text-sm">
            Revenir a la lecon
          </a>
        </div>
      </form>
    </section>

    <aside class="space-y-6">
      @if ($hasImage)
        <div class="bg-white rounded-[20px] border border-gray-100 shadow-sm p-4">
          <img
            src="{{ Storage::url($question->image_path) }}"
            alt="{{ $question->image_alt ?? '' }}"
            class="w-full max-h-[420px] object-contain rounded-xl border border-gray-200"
          >
          @if (!empty($question->image_alt))
            <p class="mt-2 text-xs text-gray-500">{{ $question->image_alt }}</p>
          @endif
        </div>
      @endif

      <div class="bg-white rounded-[20px] border border-gray-100 shadow-sm p-5">
        <h2 class="text-base font-bold text-bleuone">Corrige pedagogique</h2>
        <p class="text-xs text-gray-500 mt-1">Repere immediat pour le formateur.</p>

        <div class="mt-4 space-y-2">
          @if((string) ($question->type ?? '') === 'cloze')
            @forelse($clozeBlanks as $blankKey => $blankCfg)
              @php
                $acceptedAnswers = is_array($blankCfg['accepted_answers'] ?? null) ? $blankCfg['accepted_answers'] : [];
                $points = is_numeric($blankCfg['points'] ?? null) ? (int) $blankCfg['points'] : 1;
              @endphp
              <div class="rounded-lg border border-green-200 bg-green-50 px-3 py-2">
                <p class="text-sm font-semibold text-green-800">{{ $blankKey }} <span class="text-xs font-normal">({{ $points }} pt{{ $points > 1 ? 's' : '' }})</span></p>
                @if(!empty($acceptedAnswers))
                  <p class="text-xs text-green-700 mt-1">{{ implode(' / ', $acceptedAnswers) }}</p>
                @else
                  <p class="text-xs text-red-600 mt-1">Aucune réponse acceptée configurée.</p>
                @endif
              </div>
            @empty
              <p class="text-sm text-gray-500">Aucun trou configuré.</p>
            @endforelse
          @else
            @forelse($correctOptions as $opt)
              <div class="rounded-lg border border-green-200 bg-green-50 px-3 py-2">
                <p class="text-sm font-semibold text-green-800">{{ $opt->option_text }}</p>
                @if(!empty($opt->feedback))
                  <p class="text-xs text-green-700 mt-1">{{ $opt->feedback }}</p>
                @endif
              </div>
            @empty
              <p class="text-sm text-gray-500">Aucune reponse correcte definie.</p>
            @endforelse
          @endif
        </div>
      </div>

      <div class="bg-white rounded-[20px] border border-gray-100 shadow-sm p-5">
        <h2 class="text-base font-bold text-bleuone">Contexte</h2>
        <dl class="mt-3 space-y-2 text-sm">
          <div class="flex items-center justify-between gap-3">
            <dt class="text-gray-500">Formation</dt>
            <dd class="text-right text-gray-800 font-semibold">{{ $module->module_name ?? ('#'.$module->id) }}</dd>
          </div>
          <div class="flex items-center justify-between gap-3">
            <dt class="text-gray-500">Section</dt>
            <dd class="text-right text-gray-800 font-semibold">{{ $section->section_title ?? ('#'.$section->id) }}</dd>
          </div>
          <div class="flex items-center justify-between gap-3">
            <dt class="text-gray-500">Lecon</dt>
            <dd class="text-right text-gray-800 font-semibold">{{ $lecture->lecture_title ?? ('#'.$lecture->id) }}</dd>
          </div>
          <div class="flex items-center justify-between gap-3">
            <dt class="text-gray-500">Tentative</dt>
            <dd class="text-right font-mono text-gray-700">#{{ (int) $attempt->id }}</dd>
          </div>
        </dl>
      </div>
    </aside>
  </div>
</div>
@endsection
