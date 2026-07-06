@extends('stagiaire.master')

@section('content')
@php
  $stats = $usabilityStats ?? [];

  $secondsToClock = function (?int $seconds): string {
      if ($seconds === null) {
          return '—';
      }
      $seconds = max(0, (int) $seconds);
      $hours = intdiv($seconds, 3600);
      $minutes = intdiv($seconds % 3600, 60);
      $secs = $seconds % 60;

      return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
  };
@endphp

<div class="max-w-[1285px] mx-auto px-8">
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full mb-6">
    <p class="font-raleway text-titre text-bleuone leading-tight mb-2">Félicitations</p>
    <p class="font-varela text-sous-titre text-orangeone">
      Formation terminée : {{ $module->module_name }}
    </p>
  </header>

  <section class="bg-white rounded-[20px] shadow-md p-6 grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
    {{-- Colonne gauche : Résumé chiffres --}}
    <div>
      <h2 class="text-lg font-semibold text-bleuone mb-4">Résumé</h2>
      <ul class="space-y-2 text-gray-800">
        <li>Leçons : <strong>{{ $totalLectures }} sur {{ $totalLectures }}</strong></li>  
        <li>Sections : <strong>{{ $totalSections }} sur {{ $totalSections }}</strong></li>
        <li>Questions : <strong>{{ $questionsAnswered }} sur {{ $totalQuestionsPlanned }}</strong></li>
      </ul>

      <div class="mt-6">
        <a href="{{ route('stagiaire.modules') }}" 
           class="inline-block px-4 py-2 rounded-lg bg-bleuone text-white">
          Retour aux formations
        </a>
      </div>
    </div>

    {{-- Colonne droite : Image --}}
    <div class="flex justify-center">
      <img src="{{ asset('images/svg/Finish.svg') }}" 
           alt="Félicitations" 
           class="max-w-[300px] w-full h-auto">
    </div>
  </section>

  <section class="bg-white rounded-[20px] shadow-md p-6 mt-6">
    <div class="mb-5">
      <h2 class="text-lg font-semibold text-bleuone">Statistiques d’utilisabilité</h2>
      <p class="text-sm text-gray-500 mt-1">
        Indicateurs de fin de parcours pour cette formation.
      </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <article class="rounded-xl border border-gray-200 p-4 bg-gray-50">
        <p class="text-xs uppercase tracking-wide text-gray-500">Progression</p>
        <p class="mt-2 text-2xl font-bold text-bleuone">{{ (int) ($stats['module_completion_percent'] ?? 0) }}%</p>
        <p class="mt-1 text-sm text-gray-600">
          {{ (int) ($stats['completed_lectures'] ?? 0) }} / {{ $totalLectures }} leçons validées
        </p>
      </article>

      <article class="rounded-xl border border-gray-200 p-4 bg-gray-50">
        <p class="text-xs uppercase tracking-wide text-gray-500">Progression sections</p>
        <p class="mt-2 text-2xl font-bold text-bleuone">{{ (int) ($stats['section_completion_percent'] ?? 0) }}%</p>
        <p class="mt-1 text-sm text-gray-600">
          {{ (int) ($stats['completed_sections'] ?? 0) }} / {{ $totalSections }} sections complétées
        </p>
      </article>

      <article class="rounded-xl border border-gray-200 p-4 bg-gray-50">
        <p class="text-xs uppercase tracking-wide text-gray-500">Taux de réussite</p>
        <p class="mt-2 text-2xl font-bold text-bleuone">
          {{ isset($stats['success_rate_percent']) ? ((int) $stats['success_rate_percent']).'%' : '—' }}
        </p>
        <p class="mt-1 text-sm text-gray-600">
          {{ $questionsAnswered }} réponses exploitables
        </p>
      </article>

      <article class="rounded-xl border border-gray-200 p-4 bg-gray-50">
        <p class="text-xs uppercase tracking-wide text-gray-500">Temps d’apprentissage</p>
        <p class="mt-2 text-2xl font-bold text-bleuone">
          {{ $secondsToClock($stats['total_learning_seconds'] ?? 0) }}
        </p>
        <p class="mt-1 text-sm text-gray-600">
          SCORM {{ $secondsToClock($stats['scorm_time_seconds'] ?? 0) }} •
          Quiz {{ $secondsToClock($stats['quiz_time_seconds'] ?? 0) }} •
          Vidéo {{ $secondsToClock($stats['video_time_seconds'] ?? 0) }}
        </p>
      </article>

      <article class="rounded-xl border border-gray-200 p-4 bg-gray-50">
        <p class="text-xs uppercase tracking-wide text-gray-500">Latence moyenne</p>
        <p class="mt-2 text-2xl font-bold text-bleuone">
          {{ $secondsToClock($stats['average_latency_seconds'] ?? null) }}
        </p>
        <p class="mt-1 text-sm text-gray-600">Temps moyen de réponse (SCORM + quiz)</p>
      </article>

      <article class="rounded-xl border border-gray-200 p-4 bg-gray-50">
        <p class="text-xs uppercase tracking-wide text-gray-500">Interactions tracées</p>
        <p class="mt-2 text-2xl font-bold text-bleuone">{{ (int) ($stats['tracked_interactions'] ?? 0) }}</p>
        <p class="mt-1 text-sm text-gray-600">
          SCORM {{ (int) ($stats['scorm_interactions'] ?? 0) }} •
          Quiz {{ (int) ($stats['quiz_answers'] ?? 0) }} •
          Vidéo {{ (int) ($stats['video_segments'] ?? 0) }}
        </p>
      </article>
    </div>

    <div class="mt-4 text-sm text-gray-600">
      Relectures vidéo détectées : <strong>{{ (int) ($stats['video_replays'] ?? 0) }}</strong>
    </div>
  </section>
</div>

@if(!empty($completionToast))
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const toast = document.createElement('div');
      toast.className = 'fixed top-5 right-5 z-[120] max-w-sm bg-white border border-green-200 shadow-lg rounded-xl px-4 py-3';
      toast.innerHTML = '<p class="text-xs font-semibold text-green-700">Formation terminée</p>'
        + '<p class="text-xs text-gray-700 mt-1">{{ addslashes($completionToast) }}</p>';
      document.body.appendChild(toast);
      setTimeout(() => toast.remove(), 4500);
    });
  </script>
@endif
@endsection
