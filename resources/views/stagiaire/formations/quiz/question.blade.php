{{-- /home/laurents/Oneduc_Dev/resources/views/stagiaire/formations/quiz/question.blade.php --}}

@extends('stagiaire.formations.master_lecon_evaluation')

@section('content')
@php
  use Illuminate\Support\Facades\Storage;
  $hasImage = !empty($question->image_path);
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
  $totalQuestions = max(1, (int) ($attempt->total_questions ?? 1));
  $currentPosition = max(1, (int) ($aq->position ?? 1));
  $clozePayload = is_array($question->payload ?? null) ? $question->payload : [];
  $clozeRawText = (string) ($clozePayload['raw_text'] ?? '');
@endphp

<div class="max-w-[1180px] mx-auto px-6 py-10">
  <div class="bg-white rounded-[20px] shadow-md p-8">

    <div class="mb-8">
      <h1 class="mb-4 text-center text-xl font-semibold text-bleuone">
        Quiz d'evaluation des acquis
      </h1>
      <div class="relative flex justify-center">
        <div
          class="group relative inline-flex flex-wrap items-center justify-center gap-2"
          aria-hidden="true"
          tabindex="0"
        >
          @for ($i = 1; $i <= $totalQuestions; $i++)
            <span class="h-2.5 w-2.5 rounded-full {{ $i <= $currentPosition ? 'bg-orangeone' : 'bg-orangeone/20' }}"></span>
          @endfor

          <div class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-3 -translate-x-1/2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-600 shadow-md opacity-0 transition duration-150 group-hover:opacity-100 group-focus-visible:opacity-100">
            Question {{ $aq->position }} / {{ $attempt->total_questions }}
          </div>
        </div>
      </div>
      <span class="sr-only">Question {{ $aq->position }} sur {{ $attempt->total_questions }}</span>
    </div>

    {{-- Contenu : question/reponses a gauche, image a droite --}}
    <div class="grid gap-8 items-start {{ $hasImage ? 'lg:grid-cols-[minmax(0,1fr)_360px]' : 'grid-cols-1' }}">

      <div>

        @if ($errors->any())
          <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            {{ $errors->first() }}
          </div>
        @endif

        <div class="mb-6">
          <div class="flex items-start justify-between gap-4">
            <p class="flex-1 text-gray-900 text-lg leading-relaxed">
              {{ $question->question_text }}
            </p>

            <div class="relative shrink-0 group">
              <button
                type="button"
                class="flex h-7 w-7 items-center justify-center rounded-full border border-gray-200 text-sm font-semibold text-gray-500 transition hover:border-gray-300 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-orangeone/20"
                aria-label="Afficher la consigne de réponse"
              >
                ?
              </button>
              <div class="pointer-events-none absolute right-0 top-full z-10 mt-2 w-72 rounded-xl border border-gray-200 bg-white p-3 text-sm leading-relaxed text-gray-600 shadow-lg opacity-0 transition duration-150 group-hover:opacity-100 group-focus-within:opacity-100">
                {{ $selectionInstruction }}
              </div>
            </div>
          </div>

          @if (!empty($question->audio_path))
            <div class="mt-4">
              <audio controls class="w-full">
                <source src="{{ asset($question->audio_path) }}">
              </audio>

              @if (!empty($question->audio_transcript))
                <details class="mt-2">
                  <summary class="cursor-pointer text-sm text-bleuone">Transcription</summary>
                  <div class="mt-2 text-sm text-gray-700 whitespace-pre-line">{{ $question->audio_transcript }}</div>
                </details>
              @endif
            </div>
          @endif
        </div>

        <form method="POST" action="{{ route('stagiaire.lesson.quiz.answer', ['module'=>$module->id,'section'=>$section->id,'lecture'=>$lecture->id,'attempt'=>$attempt->id]) }}">
          @csrf

          <fieldset class="space-y-3">
            <legend class="sr-only">{{ $selectionInstruction }}</legend>

            @if($questionType === 'cloze')
              <div class="rounded-xl border border-gray-200 bg-white p-4">
                <p class="text-xs uppercase tracking-wider text-gray-500 font-semibold mb-3">Compléter la formule</p>
                <div id="cloze-container-stagiaire" class="text-[17px] leading-relaxed text-gray-900 font-mono break-words"></div>
                <p class="mt-3 text-xs text-gray-500">
                  Remplissez chaque champ puis validez.
                </p>
              </div>

              <script>
                document.addEventListener('DOMContentLoaded', function () {
                  const container = document.getElementById('cloze-container-stagiaire');
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
                @if($questionType === 'multiple')
                  <label class="flex items-start gap-3 rounded-xl border border-gray-200 p-4 hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" name="answers[]" value="{{ $opt->id }}" class="mt-1" @checked(in_array((string) $opt->id, $oldMultipleAnswers, true)) />
                    <span class="text-lg leading-relaxed text-gray-900">{{ $opt->option_text }}</span>
                  </label>
                @else
                  <label class="flex items-start gap-3 rounded-xl border border-gray-200 p-4 hover:bg-gray-50 cursor-pointer">
                    <input type="radio" name="answer" value="{{ $opt->id }}" class="mt-1" required @checked($oldSingleAnswer === (string) $opt->id) />
                    <span class="text-lg leading-relaxed text-gray-900">{{ $opt->option_text }}</span>
                  </label>
                @endif
              @endforeach
            @endif
          </fieldset>
            {{-- DANS LE FORMULAIRE, AVANT LE BOUTON SUBMIT --}}
              <input type="hidden" name="time_spent" id="time_spent_input" value="0">

              {{-- OPTIONNEL : Affichage discret du temps pour le stagiaire --}}
              <div class="mt-4 text-right">
                  <span class="text-xs text-gray-400 font-mono" id="timer_display">00:00</span>
              </div>

              {{-- A LA FIN DU FICHIER (AVANT @endsection) --}}
              <script>
                  document.addEventListener('DOMContentLoaded', function() {
                      let seconds = 0;
                      const input = document.getElementById('time_spent_input');
                      const display = document.getElementById('timer_display');

                      // Incrémente le compteur chaque seconde
                      setInterval(() => {
                          seconds++;
                          input.value = seconds;
                          
                          // Formatage MM:SS pour l'affichage
                          const m = Math.floor(seconds / 60).toString().padStart(2, '0');
                          const s = (seconds % 60).toString().padStart(2, '0');
                          if(display) display.textContent = `${m}:${s}`;
                      }, 1000);
                  });
              </script>
          <div class="flex justify-end pt-6">
            <button type="submit"
              class="inline-flex items-center px-8 py-3 bg-orangeone text-white font-semibold rounded-full hover:opacity-90 transition cursor-pointer">
              Valider
            </button>
          </div>
        </form>
      </div>

      {{-- Colonne droite : image (uniquement si présente) --}}
      @if ($hasImage)
        <aside class="md:sticky md:top-24">
          <img
            src="{{ Storage::url($question->image_path) }}"
            alt="{{ $question->image_alt ?? '' }}"
            class="w-full max-h-[600px] object-contain rounded-[20px] border border-gray-200"
          />
        </aside>
      @endif

    </div>
  </div>
</div>

@endsection
