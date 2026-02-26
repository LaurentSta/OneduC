{{-- /home/laurents/Oneduc_Dev/resources/views/stagiaire/formations/quiz/question.blade.php --}}

@extends('stagiaire.formations.master_lecon_evaluation')

@section('content')
@php
  use Illuminate\Support\Facades\Storage;
  $hasImage = !empty($question->image_path);
  $clozePayload = is_array($question->payload ?? null) ? $question->payload : [];
  $clozeRawText = (string) ($clozePayload['raw_text'] ?? '');
@endphp

<div class="max-w-[900px] mx-auto px-6 py-10">
  <div class="bg-white rounded-[20px] shadow-md p-8">

    {{-- Grille : 2 colonnes uniquement si image, sinon 1 colonne --}}
    <div class="grid gap-8 items-start {{ $hasImage ? 'md:grid-cols-[1fr_360px]' : 'grid-cols-1' }}">

      {{-- Colonne gauche : titre + quiz --}}
      <div>
        <div class="flex items-center justify-between mb-6">
          <div>
            <h1 class="text-xl font-raleway text-bleuone font-semibold">Quiz de validation</h1>
            <p class="text-sm text-gray-600">
              Question {{ $aq->position }} / {{ $attempt->total_questions }}
            </p>
          </div>
        </div>

        @if ($errors->any())
          <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            {{ $errors->first() }}
          </div>
        @endif

        <div class="mb-6">
          <p class="text-gray-900 text-base leading-relaxed">
            {{ $question->question_text }}
          </p>

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
            <legend class="sr-only">Choisir une réponse</legend>

            @if((string) $question->type === 'cloze')
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
                @if($question->type === 'multiple')
                  <label class="flex items-start gap-3 rounded-xl border border-gray-200 p-4 hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" name="answers[]" value="{{ $opt->id }}" class="mt-1" />
                    <span class="text-gray-900">{{ $opt->option_text }}</span>
                  </label>
                @else
                  <label class="flex items-start gap-3 rounded-xl border border-gray-200 p-4 hover:bg-gray-50 cursor-pointer">
                    <input type="radio" name="answer" value="{{ $opt->id }}" class="mt-1" required />
                    <span class="text-gray-900">{{ $opt->option_text }}</span>
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
          @if (!empty($question->image_alt))
            <p class="mt-3 text-xs text-gray-500">{{ $question->image_alt }}</p>
          @endif
        </aside>
      @endif

    </div>
  </div>
</div>

@endsection
