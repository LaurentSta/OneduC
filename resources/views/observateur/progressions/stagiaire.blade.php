@extends('observateur.dashboard')

@section('observateur')
<div class="max-w-[1285px] mx-auto px-8 py-8">
  <div class="bg-white rounded-[20px] shadow-sm border border-gray-100 p-6 mb-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
    <div class="flex items-center gap-4">
      <div class="w-16 h-16 rounded-full bg-bleuone/10 flex items-center justify-center text-2xl font-bold text-bleuone uppercase">
        {{ substr((string) $stagiaire->prenom, 0, 1) }}{{ substr((string) $stagiaire->name, 0, 1) }}
      </div>
      <div>
        <h1 class="text-2xl font-raleway font-bold text-gray-900">{{ $stagiaire->prenom }} {{ $stagiaire->name }}</h1>
        <div class="flex items-center gap-4 text-sm text-gray-500 font-varela mt-1">
          <span>{{ $stagiaire->email }}</span>
          <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
          <span>Inscrit le {{ $stagiaire->created_at->format('d/m/Y') }}</span>
        </div>
      </div>
    </div>

    <div class="flex gap-3">
      <a href="{{ route('observateur.progressions.stagiaires') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg font-bold text-sm hover:bg-gray-200 transition">
        Retour liste
      </a>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
      <span class="text-[10px] font-bold uppercase text-gray-400">Temps d'apprentissage</span>
      <p class="text-2xl font-bold text-gray-900 mt-2">{{ gmdate("H\h i", $engagementTotal) }}</p>
    </div>
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
      <span class="text-[10px] font-bold uppercase text-gray-400">Vidéos visionnées</span>
      <p class="text-2xl font-bold text-gray-900 mt-2">{{ gmdate("H\h i", $videoTime) }}</p>
    </div>
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
      <span class="text-[10px] font-bold uppercase text-gray-400">Réflexion / Question</span>
      <p class="text-2xl font-bold text-gray-900 mt-2">{{ $averageLatencyTime }} s</p>
    </div>
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
      <span class="text-[10px] font-bold uppercase text-gray-400">Questions traitées</span>
      <p class="text-2xl font-bold text-gray-900 mt-2">{{ $uniqueQuestions }}</p>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center justify-center">
      <h3 class="text-sm font-bold text-gray-700 mb-6 w-full text-left">Taux d'acquisition global</h3>
      <div class="relative w-48 h-48 rounded-full mb-4" style="background: conic-gradient(#22c55e {{ $tauxReussiteGlobal }}%, #f3f4f6 0);">
        <div class="absolute inset-0 m-auto w-36 h-36 bg-white rounded-full flex flex-col items-center justify-center shadow-inner">
          <span class="text-3xl font-black text-gray-800">{{ $tauxReussiteGlobal }}%</span>
          <span class="text-[10px] uppercase font-bold text-gray-400">Acquis</span>
        </div>
      </div>
      <div class="text-center text-xs text-gray-500 mt-2">{{ $validatedQuestions }} questions validées sur {{ $uniqueQuestions }}</div>
    </div>

    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
      <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
        <h3 class="text-sm font-bold text-gray-700">Détail des questions & points de blocage</h3>
        <span class="text-[10px] font-semibold text-gray-500 bg-gray-200 px-2 py-1 rounded">Vue observateur</span>
      </div>

      <div class="overflow-y-auto custom-scrollbar flex-1 max-h-[400px]">
        @if($consolidatedQuestions->isNotEmpty())
          <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 sticky top-0 z-10 text-[10px] uppercase text-gray-400 font-bold tracking-wider">
              <tr>
                <th class="px-6 py-3">Question & Module</th>
                <th class="px-4 py-3 text-center">Tentatives</th>
                <th class="px-4 py-3 text-center">1er Essai</th>
                <th class="px-4 py-3 text-center">Statut Final</th>
                <th class="px-4 py-3 text-right">Dernière action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              @foreach($consolidatedQuestions as $q)
                <tr class="hover:bg-gray-50/80 transition-colors">
                  <td class="px-6 py-3">
                    <p class="text-sm font-medium text-gray-800 line-clamp-1" title="{{ $q->question_text }}">{{ $q->question_text }}</p>
                    <p class="text-[10px] text-gray-400 mt-0.5">{{ $q->module_title }}</p>
                  </td>
                  <td class="px-4 py-3 text-center">{{ $q->attempts_count }}</td>
                  <td class="px-4 py-3 text-center">
                    <span class="{{ $q->first_result ? 'text-green-500' : 'text-red-400' }} text-xs font-bold">
                      {{ $q->first_result ? 'Direct' : 'Erreur' }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center">
                    <span class="inline-flex items-center px-2 py-1 rounded text-[10px] font-bold {{ $q->final_status ? 'bg-green-50 text-green-600 border border-green-100' : 'bg-red-50 text-red-600 border border-red-100' }}">
                      {{ $q->final_status ? 'Acquis' : 'Non acquis' }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-right text-xs text-gray-400 tabular-nums">{{ \Carbon\Carbon::parse($q->last_date)->format('d/m H:i') }}</td>
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
            <th class="px-6 py-3">Module</th>
            <th class="px-6 py-3 text-center">Terminé le</th>
          </tr>
        </thead>
        <tbody>
          @forelse($progressions as $p)
            <tr class="border-b border-gray-50 hover:bg-gray-50/50 last:border-0">
              <td class="px-6 py-4 font-medium text-gray-900">{{ $p->lecture->lecture_title ?? 'Leçon supprimée' }}</td>
              <td class="px-6 py-4 text-gray-500">{{ $p->lecture->section->module->module_title ?? 'Module supprimé' }}</td>
              <td class="px-6 py-4 text-center text-gray-500 text-xs">{{ \Carbon\Carbon::parse($p->completed_at)->format('d/m/Y à H:i') }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="3" class="px-6 py-8 text-center text-gray-400 italic">Aucune leçon marquée comme terminée.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="px-6 py-4 border-t border-gray-100">
      {{ $progressions->links() }}
    </div>
  </div>
</div>
@endsection
