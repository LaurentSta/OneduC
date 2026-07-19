{{-- Vue de consommation IA partagée par les espaces formateur et administrateur. --}}
@php
  $constructeurAdmin = (bool) ($constructeurAdmin ?? false);
  $layoutConstructeur = $layoutConstructeur ?? ($constructeurAdmin ? 'admin.admin_dashboard' : 'formateur.dashboard');
  $sectionConstructeur = $sectionConstructeur ?? ($constructeurAdmin ? 'admin' : 'formateur');
  $nomRoutesConstructeur = $nomRoutesConstructeur ?? ($constructeurAdmin
      ? 'admin.formations.constructeur'
      : 'formateur.modules.builder');
  $urlAccueilConstructeur = $urlAccueilConstructeur ?? ($constructeurAdmin
      ? (Route::has('admin.dashboard') ? route('admin.dashboard') : url('/admin'))
      : route('formateur.outils.index'));
@endphp

@extends($layoutConstructeur)

@section($sectionConstructeur)
<div class="w-full px-6 lg:px-8">

  {{-- En-tête --}}
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-5 pb-6 my-6">
    <nav class="text-sm font-varela text-gray-500 mb-2">
      <ol class="inline-flex items-center space-x-1">
        <li>
          <a href="{{ $urlAccueilConstructeur }}" class="text-orangeone hover:underline">{{ $constructeurAdmin ? 'Administration' : 'Outils numériques' }}</a>
        </li>
        <li><span class="mx-2 text-gray-400">/</span></li>
        <li>
          <a href="{{ route($nomRoutesConstructeur.'.index') }}" class="text-orangeone hover:underline">{{ $constructeurAdmin ? 'Catalogue Oneduc' : 'Mes créations' }}</a>
        </li>
        <li><span class="mx-2 text-gray-400">/</span></li>
        <li class="text-gray-400">{{ $constructeurAdmin ? 'Budget IA du catalogue' : 'Ma consommation IA' }}</li>
      </ol>
    </nav>
    <h1 class="font-raleway text-2xl text-bleuone">{{ $constructeurAdmin ? 'Budget IA du catalogue' : 'Ma consommation IA' }}</h1>
    <p class="text-sm text-gray-500 mt-1">
      {{ $constructeurAdmin
          ? 'Suivi du budget plateforme réservé aux générations des formations officielles.'
          : 'Nombre de tokens Mistral consommés lors de vos générations de leçons et de formations par IA.' }}
    </p>
  </header>

  @php
    $budget = $resume['budget'];
    $pourcentage = $budget['limite_mensuelle'] > 0
        ? min(100, (int) round($budget['consomme_ce_mois'] / $budget['limite_mensuelle'] * 100))
        : 0;
    $barreCouleur = $budget['depasse'] ? 'bg-red-500' : ($pourcentage >= 80 ? 'bg-orangeone' : 'bg-vertone');
  @endphp

  <div class="bg-white rounded-[20px] shadow-md p-6 mb-8">
    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
      <p class="text-sm font-semibold text-bleuone">Plafond mensuel de tokens IA</p>
      <p class="text-sm text-gray-600">
        {{ number_format($budget['consomme_ce_mois'], 0, ',', ' ') }} / {{ number_format($budget['limite_mensuelle'], 0, ',', ' ') }} tokens ce mois-ci
      </p>
    </div>
    <div class="w-full h-2.5 rounded-full bg-gray-100 overflow-hidden">
      <div class="h-full rounded-full {{ $barreCouleur }}" style="width: {{ $pourcentage }}%"></div>
    </div>
    @if ($budget['depasse'])
      <p class="mt-2 text-sm text-red-600">Plafond atteint : la génération IA est bloquée jusqu'au mois prochain.</p>
    @endif
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-[20px] shadow-md p-6">
      <p class="text-xs font-semibold text-gray-500 uppercase">Générations IA</p>
      <p class="mt-2 text-3xl font-bold text-bleuone">{{ number_format($resume['totaux']['appels'], 0, ',', ' ') }}</p>
    </div>
    <div class="bg-white rounded-[20px] shadow-md p-6">
      <p class="text-xs font-semibold text-gray-500 uppercase">Tokens consommés (total)</p>
      <p class="mt-2 text-3xl font-bold text-bleuone">{{ number_format($resume['totaux']['total_tokens'], 0, ',', ' ') }}</p>
    </div>
    <div class="bg-white rounded-[20px] shadow-md p-6">
      <p class="text-xs font-semibold text-gray-500 uppercase">Détail prompt / réponse</p>
      <p class="mt-2 text-sm text-gray-700">
        {{ number_format($resume['totaux']['prompt_tokens'], 0, ',', ' ') }} tokens envoyés<br>
        {{ number_format($resume['totaux']['completion_tokens'], 0, ',', ' ') }} tokens générés
      </p>
    </div>
  </div>

  <div class="bg-white rounded-[20px] shadow-md p-6">
    <p class="font-varela text-base font-bold text-bleuone mb-4">Historique des générations</p>

    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
        <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
          <tr>
            <th class="px-3 py-3">Date</th>
            <th class="px-3 py-3">Type</th>
            <th class="px-3 py-3">Modèle</th>
            <th class="px-3 py-3">Tokens</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse ($resume['historique'] as $ligne)
            <tr class="hover:bg-gray-50">
              <td class="px-3 py-3 text-gray-700">{{ $ligne->created_at->format('d/m/Y H:i') }}</td>
              <td class="px-3 py-3 text-gray-700">{{ $ligne->type === 'chat' ? 'Génération' : 'Modération' }}</td>
              <td class="px-3 py-3 text-gray-700">{{ $ligne->model }}</td>
              <td class="px-3 py-3 font-semibold text-bleuone">{{ number_format($ligne->total_tokens ?? 0, 0, ',', ' ') }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="px-3 py-6 text-center text-gray-500">Aucune génération IA pour le moment.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-6">
      {{ $resume['historique']->links() }}
    </div>
  </div>

</div>
@endsection
