@extends('formateur.dashboard')

@section('formateur')
@php
  $riskStyles = [
      'good' => [
          'panel' => 'border-green-200 bg-green-50',
          'badge' => 'bg-green-100 text-green-700 border-green-200',
      ],
      'warning' => [
          'panel' => 'border-orange-200 bg-orange-50',
          'badge' => 'bg-orange-100 text-orange-700 border-orange-200',
      ],
      'critical' => [
          'panel' => 'border-red-200 bg-red-50',
          'badge' => 'bg-red-100 text-red-700 border-red-200',
      ],
  ];

  $feedStyles = [
      'lesson' => 'bg-green-50 text-green-700 border-green-200',
      'quiz' => 'bg-orange-50 text-orange-700 border-orange-200',
      'video' => 'bg-blue-50 text-bleuone border-blue-200',
      'scorm' => 'bg-purple-50 text-purple-700 border-purple-200',
  ];

  $riskStyle = $riskStyles[$presenceSummary['risk']['level']] ?? $riskStyles['warning'];
@endphp

<div class="max-w-[1285px] mx-auto px-8 py-8">

  <div class="bg-white rounded-[20px] shadow-sm border border-gray-100 p-6 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-6">
    <div class="flex items-center gap-4">
      <div class="w-16 h-16 rounded-full bg-bleuone/10 flex items-center justify-center text-2xl font-bold text-bleuone uppercase">
        {{ substr($stagiaire->prenom, 0, 1) }}{{ substr($stagiaire->name, 0, 1) }}
      </div>
      <div>
        <h1 class="text-2xl font-raleway font-medium text-bleuone">
          {{ $stagiaire->prenom }} {{ $stagiaire->name }}
        </h1>
        <div class="flex flex-wrap items-center gap-3 text-sm text-gray-500 font-varela mt-1">
          <span class="flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            {{ $stagiaire->email }}
          </span>
          <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
          <span>Compte créé le {{ $stagiaire->created_at->format('d/m/Y') }}</span>
          @if($selectedGroup)
            <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
            <span>Dans le groupe {{ $selectedGroup->name }} depuis le {{ $presenceSummary['started_at']->format('d/m/Y') }}</span>
          @endif
        </div>
      </div>
    </div>

    <div class="flex gap-3">
      <a href="{{ route('formateur.progressions.stagiaires', array_filter(['group_id' => $selectedGroup?->id])) }}" class="btn-oneduc-outline !px-4 !py-2 !text-sm">
        Retour liste
      </a>
      <a href="mailto:{{ $stagiaire->email }}" class="btn-oneduc !px-4 !py-2 !text-sm">
        Contacter
      </a>
    </div>
  </div>

  @if($groupMemberships->isNotEmpty())
    <div class="bg-white rounded-[20px] shadow-sm border border-gray-100 p-5 mb-8">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
          <p class="text-sm font-bold text-gray-700">Contexte du suivi</p>
          <p class="text-xs text-gray-500 mt-1">
            Le suivi temporel et le flux d'activité sont calculés sur le groupe sélectionné et ses formations associées.
          </p>
        </div>
        <div class="flex flex-wrap gap-2">
          @foreach($groupMemberships as $membership)
            <a href="{{ route('formateur.progressions.stagiaire', ['user' => $stagiaire->id, 'group_id' => $membership->id]) }}"
               class="inline-flex items-center px-3 py-2 rounded-full border text-xs font-semibold transition {{ (int) $membership->id === (int) ($selectedGroup->id ?? 0) ? 'border-orangeone bg-orangeone text-white' : 'border-gray-200 bg-white text-gray-600 hover:border-orangeone hover:text-orangeone' }}">
              {{ $membership->name }}
            </a>
          @endforeach
        </div>
      </div>
    </div>
  @endif

  <div class="border rounded-[20px] p-6 mb-8 {{ $riskStyle['panel'] }}">
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
      <div>
        <div class="flex items-center gap-3 mb-2">
          <h2 class="text-lg font-bold text-gray-900">Assiduité et présence</h2>
          <span class="inline-flex items-center px-3 py-1 rounded-full border text-xs font-bold {{ $riskStyle['badge'] }}">
            {{ $presenceSummary['risk']['label'] }}
          </span>
        </div>
        <p class="text-sm text-gray-700">
          {{ $presenceSummary['risk']['reason'] }}
        </p>
      </div>
      <div class="text-sm text-gray-600">
        @if($selectedGroup)
          <span class="font-semibold text-gray-800">{{ $selectedGroup->name }}</span>
          <span class="text-gray-400">|</span>
        @endif
        Suivi ouvert depuis {{ $presenceSummary['started_at']->format('d/m/Y') }}
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
      <p class="text-[11px] font-bold uppercase text-gray-400">Jours actifs</p>
      <p class="text-3xl font-bold text-gray-900 mt-2">{{ $presenceSummary['active_days_count'] }}</p>
      <p class="text-sm text-gray-500 mt-2">
        {{ $presenceSummary['activity_rate'] }}% des jours depuis le début du suivi
      </p>
    </div>

    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
      <p class="text-[11px] font-bold uppercase text-gray-400">Dernière activité</p>
      <p class="text-2xl font-bold text-gray-900 mt-2">
        @if($presenceSummary['last_activity_at'])
          {{ $presenceSummary['last_activity_at']->format('d/m/Y') }}
        @else
          —
        @endif
      </p>
      <p class="text-sm text-gray-500 mt-2">
        @if(!is_null($presenceSummary['inactivity_days']))
          {{ $presenceSummary['inactivity_days'] }} jour{{ $presenceSummary['inactivity_days'] > 1 ? 's' : '' }} sans activité
        @else
          Aucune activité détectée
        @endif
      </p>
    </div>

    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
      <p class="text-[11px] font-bold uppercase text-gray-400">Rythme récent</p>
      <p class="text-3xl font-bold text-gray-900 mt-2">{{ $presenceSummary['last_14_days_active'] }}</p>
      <p class="text-sm text-gray-500 mt-2">
        jours actifs sur les 14 derniers jours, {{ $presenceSummary['last_28_days_active'] }} sur 28 jours
      </p>
    </div>

    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
      <p class="text-[11px] font-bold uppercase text-gray-400">Présence plateforme</p>
      <p class="text-2xl font-bold text-gray-900 mt-2">{{ gmdate("H\h i", (int) $presenceSummary['site_time_total']) }}</p>
      <p class="text-sm text-gray-500 mt-2">
        Série actuelle : {{ $presenceSummary['current_streak_days'] }} jour{{ $presenceSummary['current_streak_days'] > 1 ? 's' : '' }}.
        Record : {{ $presenceSummary['longest_streak_days'] }}.
      </p>
    </div>
  </div>

  <div class="grid grid-cols-1 xl:grid-cols-5 gap-8 mb-10">
    <div class="xl:col-span-3 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-5">
        <div>
          <h3 class="text-sm font-bold text-gray-700">Présence sur 8 semaines</h3>
          <p class="text-xs text-gray-500 mt-1">
            Chaque barre représente une journée. Plus elle est haute, plus l'activité détectée est forte.
          </p>
        </div>
        <div class="flex flex-wrap items-center gap-3 text-[11px] text-gray-500 font-semibold">
          <span class="inline-flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-green-500"></span> Leçons</span>
          <span class="inline-flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-orangeone"></span> Quiz</span>
          <span class="inline-flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-bleuone"></span> Vidéos</span>
          <span class="inline-flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span> SCORM</span>
        </div>
      </div>

      <div class="h-40 flex items-end gap-1">
        @foreach($activityTimeline as $day)
          @php
            $score = (int) ($day['activity_score'] ?? 0);
            $height = $score > 0 ? max(10, (int) round(($score / max(1, $timelineMaxScore)) * 120)) : 8;
            $barClass = 'bg-gray-100';

            if (($day['lesson_completions'] ?? 0) > 0) {
                $barClass = 'bg-green-500';
            } elseif (($day['quiz_attempts'] ?? 0) > 0) {
                $barClass = 'bg-orangeone';
            } elseif (($day['video_sessions'] ?? 0) > 0) {
                $barClass = 'bg-bleuone';
            } elseif (($day['scorm_events'] ?? 0) > 0) {
                $barClass = 'bg-purple-500';
            }
          @endphp
          <div class="group relative flex-1 h-full flex items-end">
            <div class="w-full rounded-t-full transition-all {{ $barClass }}" style="height: {{ $height }}px;"></div>
            <div class="hidden group-hover:block absolute bottom-full left-1/2 -translate-x-1/2 mb-3 w-44 bg-gray-900 text-white text-[11px] rounded-lg px-3 py-2 shadow-lg z-10">
              <p class="font-semibold">{{ \Carbon\Carbon::parse($day['activity_date'])->format('d/m/Y') }}</p>
              <p>{{ $day['lesson_completions'] }} leçon{{ $day['lesson_completions'] > 1 ? 's' : '' }} terminée{{ $day['lesson_completions'] > 1 ? 's' : '' }}</p>
              <p>{{ $day['quiz_attempts'] }} quiz</p>
              <p>{{ $day['video_sessions'] }} activité{{ $day['video_sessions'] > 1 ? 's' : '' }} vidéo</p>
              <p>{{ $day['scorm_events'] }} interaction{{ $day['scorm_events'] > 1 ? 's' : '' }} SCORM</p>
            </div>
          </div>
        @endforeach
      </div>

      <div class="flex items-center justify-between text-[11px] text-gray-400 mt-3">
        <span>{{ \Carbon\Carbon::parse($activityTimeline->first()['activity_date'])->format('d/m') }}</span>
        <span>Aujourd'hui</span>
      </div>
    </div>

    <div class="xl:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
        <h3 class="text-sm font-bold text-gray-700">Journal d'activité unifié</h3>
        <p class="text-xs text-gray-500 mt-1">
          Leçons, quiz, vidéos et SCORM rassemblés dans une même chronologie.
        </p>
      </div>

      <div class="divide-y divide-gray-100 max-h-[440px] overflow-y-auto custom-scrollbar">
        @forelse($activityFeed as $entry)
          <div class="px-6 py-4">
            <div class="flex items-start justify-between gap-4">
              <div class="min-w-0">
                <div class="flex items-center gap-2 mb-2">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full border text-[10px] font-bold {{ $feedStyles[$entry->type] ?? 'bg-gray-50 text-gray-600 border-gray-200' }}">
                    {{ $entry->label }}
                  </span>
                  @if($entry->metric)
                    <span class="text-[11px] font-semibold text-gray-500">{{ $entry->metric }}</span>
                  @endif
                </div>
                <p class="text-sm font-semibold text-gray-900">{{ $entry->title }}</p>
                <p class="text-[11px] text-gray-500 mt-1">{{ $entry->module_title }}</p>
                <p class="text-xs text-gray-600 mt-2">{{ $entry->detail }}</p>
              </div>
              <div class="text-right shrink-0">
                <p class="text-xs font-semibold text-gray-700">{{ $entry->activity_at->format('d/m/Y') }}</p>
                <p class="text-[11px] text-gray-400 mt-1">{{ $entry->activity_at->format('H:i') }}</p>
              </div>
            </div>
          </div>
        @empty
          <div class="px-6 py-12 text-center text-gray-400 text-sm">
            Aucune activité enregistrée dans ce contexte pour le moment.
          </div>
        @endforelse
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
          <div class="flex items-center gap-3 mb-2">
              <div class="p-2 bg-blue-50 text-bleuone rounded-lg">
                  <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
              </div>
              <span class="text-[10px] font-bold uppercase text-gray-400">Temps d'apprentissage</span>
          </div>
          <p class="text-2xl font-bold text-gray-900">{{ gmdate("H\h i", $engagementTotal) }}</p>
      </div>

      <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
          <div class="flex items-center gap-3 mb-2">
              <div class="p-2 bg-orange-50 text-orangeone rounded-lg">
                  <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              </div>
              <span class="text-[10px] font-bold uppercase text-gray-400">Vidéos visionnées</span>
          </div>
          <p class="text-2xl font-bold text-gray-900">{{ gmdate("H\h i", $videoTime) }}</p>
      </div>

      <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
          <div class="flex items-center gap-3 mb-2">
              <div class="p-2 bg-purple-50 text-purple-600 rounded-lg">
                  <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
              </div>
              <span class="text-[10px] font-bold uppercase text-gray-400">Réflexion / Question</span>
          </div>
          <p class="text-2xl font-bold text-gray-900">{{ $averageLatencyTime }} s</p>
      </div>

      <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
          <div class="flex items-center gap-3 mb-2">
              <div class="p-2 bg-green-50 text-green-600 rounded-lg">
                  <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
              </div>
              <span class="text-[10px] font-bold uppercase text-gray-400">Questions traitées</span>
          </div>
          <p class="text-2xl font-bold text-gray-900">{{ $uniqueQuestions }}</p>
      </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center justify-center">
          <h3 class="text-sm font-bold text-gray-700 mb-6 w-full text-left">Taux d'acquisition global</h3>

          <div class="relative w-48 h-48 rounded-full mb-4"
               style="background: conic-gradient(#22c55e {{ $tauxReussiteGlobal }}%, #f3f4f6 0);">
              <div class="absolute inset-0 m-auto w-36 h-36 bg-white rounded-full flex flex-col items-center justify-center shadow-inner">
                  <span class="text-3xl font-black text-gray-800">{{ $tauxReussiteGlobal }}%</span>
                  <span class="text-[10px] uppercase font-bold text-gray-400">Acquis</span>
              </div>
          </div>

          <div class="text-center text-xs text-gray-500 mt-2">
              {{ $validatedQuestions }} questions validées sur {{ $uniqueQuestions }}
          </div>
      </div>

      <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
          <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
              <h3 class="text-sm font-bold text-gray-700">Détail des questions et points de blocage</h3>
              <span class="text-[10px] font-semibold text-gray-500 bg-gray-200 px-2 py-1 rounded">
                  Vue formateur
              </span>
          </div>

          <div class="overflow-y-auto custom-scrollbar flex-1 max-h-[400px]">
              @if($consolidatedQuestions->isNotEmpty())
                  <table class="w-full text-left border-collapse">
                      <thead class="bg-gray-50 sticky top-0 z-10 text-[10px] uppercase text-gray-400 font-bold tracking-wider">
                          <tr>
                              <th class="px-6 py-3">Question et formation</th>
                              <th class="px-4 py-3 text-center">Tentatives</th>
                              <th class="px-4 py-3 text-center">1er essai</th>
                              <th class="px-4 py-3 text-center">Statut final</th>
                              <th class="px-4 py-3 text-right">Dernière action</th>
                          </tr>
                      </thead>
                      <tbody class="divide-y divide-gray-100">
                          @foreach($consolidatedQuestions as $q)
                              <tr class="hover:bg-gray-50/80 transition-colors">
                                  <td class="px-6 py-3">
                                      <p class="text-sm font-medium text-gray-800 line-clamp-1" title="{{ $q->question_text }}">
                                          {{ $q->question_text }}
                                      </p>
                                      <p class="text-[10px] text-gray-400 mt-0.5">
                                          {{ $q->module_title }}
                                      </p>
                                  </td>

                                  <td class="px-4 py-3 text-center">
                                      @if($q->attempts_count > 1)
                                          <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-orange-100 text-orange-600 text-[11px] font-bold" title="A réessayé plusieurs fois">
                                              {{ $q->attempts_count }}
                                          </span>
                                      @else
                                          <span class="text-gray-400 text-xs">1</span>
                                      @endif
                                  </td>

                                  <td class="px-4 py-3 text-center">
                                      @if($q->first_result)
                                          <span class="text-green-500 text-xs font-bold">Direct</span>
                                      @else
                                          <span class="text-red-400 text-xs font-bold">Erreur</span>
                                      @endif
                                  </td>

                                  <td class="px-4 py-3 text-center">
                                      @if($q->final_status)
                                          <span class="inline-flex items-center px-2 py-1 rounded text-[10px] font-bold bg-green-50 text-green-600 border border-green-100">
                                              Acquis
                                          </span>
                                      @else
                                          <span class="inline-flex items-center px-2 py-1 rounded text-[10px] font-bold bg-red-50 text-red-600 border border-red-100 animate-pulse">
                                              Non acquis
                                          </span>
                                      @endif
                                  </td>

                                  <td class="px-4 py-3 text-right text-xs text-gray-400 tabular-nums">
                                      {{ \Carbon\Carbon::parse($q->last_date)->format('d/m H:i') }}
                                  </td>
                              </tr>
                          @endforeach
                      </tbody>
                  </table>
              @else
                  <div class="flex flex-col items-center justify-center h-48 text-gray-400">
                      <p class="text-sm">Aucune activité enregistrée pour ce stagiaire.</p>
                  </div>
              @endif
          </div>
      </div>
  </div>

  <div class="bg-white rounded-[20px] shadow-sm border border-gray-100 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
          <h3 class="text-sm font-bold text-gray-700">Journal des leçons terminées</h3>
      </div>
      <div class="overflow-x-auto">
          <table class="min-w-full text-sm text-left text-gray-800 font-lisible">
              <thead class="bg-white text-xs text-gray-500 uppercase font-varela border-b border-gray-100">
                  <tr>
                      <th class="px-6 py-3">Leçon</th>
                      <th class="px-6 py-3">Formation</th>
                      <th class="px-6 py-3 text-center">Terminé le</th>
                  </tr>
              </thead>
              <tbody>
                  @forelse($progressions as $p)
                      <tr class="border-b border-gray-50 hover:bg-gray-50/50 last:border-0">
                          <td class="px-6 py-4 font-medium text-gray-900">
                              {{ $p->lecture->lecture_title ?? 'Leçon supprimée' }}
                          </td>
                          <td class="px-6 py-4 text-gray-500">
                              {{ $p->lecture->section->module->module_title ?? 'Formation supprimée' }}
                          </td>
                          <td class="px-6 py-4 text-center text-gray-500 text-xs">
                              {{ \Carbon\Carbon::parse($p->completed_at)->format('d/m/Y à H:i') }}
                          </td>
                      </tr>
                  @empty
                      <tr>
                          <td colspan="3" class="px-6 py-8 text-center text-gray-400 italic">
                              Aucune leçon marquée comme terminée.
                          </td>
                      </tr>
                  @endforelse
              </tbody>
          </table>
      </div>
      @if($progressions->hasPages())
          <div class="px-6 py-4 border-t border-gray-100">
              {{ $progressions->links() }}
          </div>
      @endif
  </div>

</div>
@endsection
