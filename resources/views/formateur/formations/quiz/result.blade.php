@extends('formateur.formations.master_lecon')

@section('content')
@php
  $contextQuery = is_array($contextQuery ?? null) ? $contextQuery : [];
  $passed = (int) ($attempt->passed ?? 0) === 1;
  $score = (int) ($attempt->score ?? 0);
  $totalQuestions = (int) ($attempt->total_questions ?? 0);
  $correctCount = (int) ($correctCount ?? 0);
  $timeTotal = (int) ($attempt->total_time_seconds ?? 0);
  $timeMinutes = intdiv($timeTotal, 60);
  $timeSeconds = $timeTotal % 60;

  $restartAction = route('formateur.lesson.quiz.restart', [
      'module'  => $module->id,
      'section' => $section->id,
      'lecture' => $lecture->id,
      'attempt' => $attempt->id,
  ] + $contextQuery);

  $backToLessonUrl = route('formateur.formations.lecture', [
      'module'  => $module->id,
      'section' => $section->id,
      'lecture' => $lecture->id,
  ] + $contextQuery);

  $fallbackModuleUrl = route('formateur.formations.detail', ['module' => $module->id] + $contextQuery);
@endphp

<div class="max-w-[1280px] mx-auto px-6 py-8">
  <div class="bg-white rounded-[20px] border border-gray-100 shadow-sm p-6 md:p-8">
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
      <div>
        <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-gray-400">Mode Pedagogue</p>
        <h1 class="text-2xl font-bold text-bleuone mt-1">Resultat du questionnaire</h1>
        <p class="text-sm text-gray-600 mt-2">
          Lecon: <span class="font-semibold text-gray-800">{{ $lecture->lecture_title ?? 'Lecon' }}</span>
        </p>
      </div>

      <span class="inline-flex items-center rounded-full px-4 py-2 text-sm font-bold border
                   {{ $passed ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200' }}">
        {{ $passed ? 'Simulation reussie' : 'Simulation non validee' }}
      </span>
    </div>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
      <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
        <p class="text-xs text-gray-500">Score</p>
        <p class="mt-1 text-2xl font-black text-orangeone">{{ $score }}%</p>
      </div>
      <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
        <p class="text-xs text-gray-500">Reponses correctes</p>
        <p class="mt-1 text-2xl font-black text-gray-900">{{ $correctCount }} / {{ $totalQuestions }} questions posees</p>
      </div>
      <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
        <p class="text-xs text-gray-500">Temps total</p>
        <p class="mt-1 text-2xl font-black text-gray-900">{{ sprintf('%02d:%02d', $timeMinutes, $timeSeconds) }}</p>
      </div>
      <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
        <p class="text-xs text-gray-500">Tentative</p>
        <p class="mt-1 text-lg font-bold text-gray-900">#{{ (int) $attempt->id }}</p>
        @if(!empty($attempt->finished_at))
          <p class="text-xs text-gray-500 mt-1">{{ \Carbon\Carbon::parse($attempt->finished_at)->format('d/m/Y H:i') }}</p>
        @endif
      </div>
    </div>

    <div class="mt-8">
      <h2 class="text-lg font-bold text-bleuone">Analyse question par question</h2>
      <p class="text-sm text-gray-600 mt-1">Detail complet pour corriger rapidement la pedagogie de la formation.</p>

      <div class="mt-4 space-y-4">
        @foreach($rows as $row)
          @php
            $question = $row->question;
            $givenRaw = json_decode($row->answer_option_ids ?? '[]', true);
            $givenIds = is_array($givenRaw) ? collect($givenRaw)->map(fn($v) => (int) $v)->all() : [(int) $givenRaw];

            $allOptions = $question->options ?? collect();
            $givenOptions = $allOptions->whereIn('id', $givenIds);
            $expectedOptions = $allOptions->where('is_correct', true);

            $isCloze = (string) ($question->type ?? '') === 'cloze';
            $givenCloze = is_array($row->given_answer ?? null) ? $row->given_answer : [];
            $clozeBlanks = is_array($givenCloze['blanks'] ?? null) ? $givenCloze['blanks'] : [];
            $clozeScore = (int) ($givenCloze['score'] ?? 0);
            $clozeMax = (int) ($givenCloze['max_score'] ?? 0);
          @endphp

          <article class="rounded-2xl border p-5 {{ $row->is_correct ? 'border-green-200 bg-green-50/40' : 'border-red-200 bg-red-50/40' }}">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
              <h3 class="text-[16px] font-bold text-gray-900">
                Q{{ (int) $row->position }}. {{ $question->question_text }}
              </h3>
              <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wide
                           {{ $row->is_correct ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                {{ $row->is_correct ? 'Correct' : 'Incorrect' }}
              </span>
            </div>

            @if($isCloze)
              <div class="mt-4 rounded-xl border border-gray-200 bg-white p-4">
                <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Correction des trous</p>

                @if(!empty($clozeBlanks))
                  <div class="mt-3 space-y-2">
                    @foreach($clozeBlanks as $blankKey => $blank)
                      @php
                        $blankOk = (bool) ($blank['is_correct'] ?? false);
                        $answer = (string) ($blank['answer'] ?? '');
                        $expected = is_array($blank['accepted_answers'] ?? null) ? $blank['accepted_answers'] : [];
                        $points = (int) ($blank['points'] ?? 0);
                      @endphp
                      <div class="rounded-lg border {{ $blankOk ? 'border-green-200 bg-green-50/60' : 'border-red-200 bg-red-50/60' }} p-2">
                        <div class="flex items-center justify-between gap-2">
                          <span class="text-xs font-semibold text-gray-700 font-mono">{{ $blankKey }}</span>
                          <span class="text-[11px] font-semibold {{ $blankOk ? 'text-green-700' : 'text-red-700' }}">
                            {{ $blankOk ? 'OK' : 'Erreur' }} · {{ $blankOk ? $points : 0 }}/{{ $points }} pt
                          </span>
                        </div>
                        <p class="mt-1 text-sm text-gray-800">
                          <span class="text-gray-500">Saisi:</span> {{ $answer !== '' ? $answer : '—' }}
                        </p>
                        <p class="mt-1 text-xs text-gray-600">
                          Attendu: {{ !empty($expected) ? implode(' / ', $expected) : 'non défini' }}
                        </p>
                      </div>
                    @endforeach
                  </div>
                  <p class="mt-3 text-xs font-semibold text-gray-700">Score partiel: {{ $clozeScore }} / {{ $clozeMax }} points</p>
                @else
                  <p class="mt-2 text-sm text-gray-500">Détail de correction indisponible.</p>
                @endif
              </div>
            @else
              <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="rounded-xl border border-gray-200 bg-white p-4">
                  <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Reponse donnee</p>
                  @if($givenOptions->isNotEmpty())
                    <ul class="mt-2 space-y-2">
                      @foreach($givenOptions as $opt)
                        <li class="text-sm text-gray-800">{{ $opt->option_text }}</li>
                      @endforeach
                    </ul>
                  @else
                    <p class="mt-2 text-sm text-gray-500">Aucune option selectionnee.</p>
                  @endif
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                  <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Reponse attendue</p>
                  @if($expectedOptions->isNotEmpty())
                    <ul class="mt-2 space-y-2">
                      @foreach($expectedOptions as $opt)
                        <li class="text-sm text-gray-800">
                          <span class="font-semibold text-green-700">{{ $opt->option_text }}</span>
                          @if(!empty($opt->feedback))
                            <p class="text-xs text-gray-500 mt-1">{{ $opt->feedback }}</p>
                          @endif
                        </li>
                      @endforeach
                    </ul>
                  @else
                    <p class="mt-2 text-sm text-gray-500">Aucune option correcte definie.</p>
                  @endif
                </div>
              </div>
            @endif
          </article>
        @endforeach
      </div>
    </div>

    <div class="mt-8 flex flex-wrap items-center gap-3">
      <form method="POST" action="{{ $restartAction }}">
        @csrf
        <button type="submit"
                class="btn-oneduc-outline !px-5 !py-3 !text-sm">
          Rejouer le questionnaire
        </button>
      </form>

      <a href="{{ $backToLessonUrl }}"
         class="btn-oneduc-outline !px-5 !py-3 !text-sm">
        Retour a la lecon
      </a>

      <a href="{{ (!empty($nextUrl) && $nextUrl !== '#') ? $nextUrl : $fallbackModuleUrl }}"
         class="btn-oneduc !px-5 !py-3 !text-sm">
        Lecon suivante
      </a>
    </div>
  </div>
</div>
@endsection
