{{-- /home/laurents/Oneduc_Dev/resources/views/stagiaire/formations/quiz/result.blade.php --}}

@extends('stagiaire.formations.master_lecon_evaluation')

@section('content')
<div class="max-w-[900px] mx-auto px-6 py-10">
  <div class="bg-white rounded-[20px] shadow-md p-8">

    {{-- En-tête --}}
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
      <div>
        <h1 class="text-xl font-raleway text-bleuone font-semibold">Résultat du quiz</h1>
        <p class="text-sm text-gray-600 mt-1">
          Leçon : <span class="font-medium text-gray-800">{{ $lecture->lecture_title ?? '—' }}</span>
        </p>
      </div>

      {{-- Badge réussite / échec --}}
      @php $passed = (int)($attempt->passed ?? 0) === 1; @endphp
      <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border
                  {{ $passed ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800' }}">
        <span class="text-sm font-semibold">
          {{ $passed ? 'Quiz validé' : 'Quiz non validé' }}
        </span>
      </div>
    </div>

    {{-- Résumé score --}}
    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="p-4 rounded-2xl bg-gray-50 border border-gray-200">
        <p class="text-xs text-gray-500">Score</p>
        <p class="mt-1 text-2xl font-bold text-orangeone">{{ (int)($attempt->score ?? 0) }}%</p>
      </div>

      <div class="p-4 rounded-2xl bg-gray-50 border border-gray-200">
        <p class="text-xs text-gray-500">Bonnes réponses</p>
        <p class="mt-1 text-2xl font-bold text-gray-900">
          {{ (int)($correctCount ?? 0) }} / {{ (int)($attempt->total_questions ?? 0) }}
        </p>
      </div>

      <div class="p-4 rounded-2xl bg-gray-50 border border-gray-200">
        <p class="text-xs text-gray-500">Tentative</p>
        <p class="mt-1 text-base font-semibold text-gray-900">Terminée</p>
        @if(!empty($attempt->finished_at))
          <p class="mt-1 text-xs text-gray-500">
            {{ \Carbon\Carbon::parse($attempt->finished_at)->format('d/m/Y H:i') }}
          </p>
        @endif
      </div>
    </div>

    {{-- Détail question par question --}}
    <div class="mt-8">
      <h2 class="text-base font-semibold text-gray-900">Détail des réponses</h2>
      <p class="text-sm text-gray-600 mt-1">Pour chaque question, seul le statut est affiché.</p>

      <div class="mt-4 space-y-4">
        @foreach($rows as $row)
            <div class="p-5 rounded-2xl border transition-all
                        {{ $row->is_correct ? 'border-green-200 bg-green-50/50' : 'border-red-200 bg-red-50/50' }}">
                
                {{-- En-tête question --}}
                <div class="flex items-start justify-between gap-4 mb-3">
                    <p class="text-[15px] font-bold text-gray-800">
                        Q{{ $row->position }}. {{ $row->question->question_text }}
                    </p>
                    <span class="shrink-0 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wide
                                {{ $row->is_correct ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ $row->is_correct ? 'Correct' : 'Erreur' }}
                    </span>
                </div>

                {{-- Analyse des réponses (Feedback détaillé) --}}
                <div class="space-y-2 pl-4 border-l-2 {{ $row->is_correct ? 'border-green-200' : 'border-red-200' }}">
                    @php
                        // Décodage des réponses données par le stagiaire (JSON stocké en base)
                        $givenIds = json_decode($row->answer_option_ids ?? '[]', true) ?? [];
                        if (!is_array($givenIds)) $givenIds = [(int)$givenIds]; 
                    @endphp

                    @foreach($row->question->options as $opt)
                        @php
                            $isSelected = in_array($opt->id, $givenIds);
                            $isExpected = (bool)$opt->is_correct;
                        @endphp

                        {{-- On affiche uniquement si c'est la réponse choisie ou la réponse attendue --}}
                        @if($isSelected || $isExpected)
                            <div class="flex items-center gap-2 text-sm">
                                {{-- Icône d'état --}}
                                @if($isSelected && $isExpected)
                                    <i class="ti ti-circle-check-filled text-green-600 text-lg"></i> {{-- Bon choix --}}
                                @elseif($isSelected && !$isExpected)
                                    <i class="ti ti-circle-x-filled text-red-500 text-lg"></i> {{-- Mauvais choix --}}
                                @elseif(!$isSelected && $isExpected)
                                    <i class="ti ti-arrow-right text-bleuone text-lg"></i> {{-- Correction --}}
                                @endif

                                <span class="{{ $isSelected ? 'font-semibold' : 'text-gray-500 italic' }}">
                                    {{ $opt->option_text }}
                                </span>

                                {{-- Label explicite --}}
                                @if(!$isSelected && $isExpected)
                                    <span class="text-xs text-bleuone font-bold ml-auto">(Solution)</span>
                                @elseif($isSelected && !$isExpected)
                                    <span class="text-xs text-red-500 font-bold ml-auto">(Votre réponse)</span>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
    </div>

    {{-- Actions --}}
    <div class="mt-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <form method="POST"
            action="{{ route('stagiaire.lesson.quiz.restart', ['module'=>$module->id,'section'=>$section->id,'lecture'=>$lecture->id,'attempt'=>$attempt->id]) }}">
        @csrf
        {{-- AJOUT DE cursor-pointer ICI --}}
        <button type="submit"
                class="inline-flex items-center justify-center px-6 py-3 bg-white border border-gray-200 text-gray-800 font-semibold rounded-xl hover:bg-gray-50 transition cursor-pointer">
          Recommencer le quiz
        </button>
      </form>

      {{-- Leçon suivante (si le contrôleur fournit $nextUrl) --}}
      @if(!empty($nextUrl))
        <a href="{{ $nextUrl }}"
           class="inline-flex items-center justify-center px-6 py-3 bg-bleuone text-white font-semibold rounded-xl hover:opacity-90 transition">
          Leçon suivante
        </a>
      @else
        <a href="{{ route('stagiaire.dashboard') }}"
           class="inline-flex items-center justify-center px-6 py-3 bg-bleuone text-white font-semibold rounded-xl hover:opacity-90 transition">
          Revenir au tableau de bord
        </a>
      @endif
    </div>

  </div>
</div>
@endsection
