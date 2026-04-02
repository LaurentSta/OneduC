@extends('observateur.dashboard')

@section('observateur')
<div class="max-w-[1285px] mx-auto px-8 space-y-8">
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-6 pb-6 w-full">
    <div class="grid grid-cols-12 gap-6 items-center">
      <div class="col-span-12 md:col-span-8">
        <h1 class="font-raleway text-titre text-bleuone leading-tight mb-2">Tableau de bord observateur</h1>
        <p class="font-varela text-gray-600 mb-4">
          Consultez les groupes qui vous sont confiés, leur progression et le détail des apprenants associés.
        </p>
        <div class="inline-flex items-center gap-2 rounded-full bg-orangeone/10 text-orangeone px-4 py-2 text-sm font-semibold">
          Espace strictement en lecture seule
        </div>
      </div>
      <div class="col-span-12 md:col-span-4 flex justify-center md:justify-end">
        <img src="{{ asset('images/svg/Groupes.svg') }}" alt="Illustration observateur" class="max-w-[240px] h-auto opacity-80">
      </div>
    </div>
  </header>

  <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <article class="bg-white rounded-[20px] shadow-md p-6 border border-gray-100">
      <p class="text-sm font-varela text-gray-500">Groupes observés</p>
      <p class="mt-2 text-3xl font-bold text-bleuone">{{ $groupCount }}</p>
    </article>
    <article class="bg-white rounded-[20px] shadow-md p-6 border border-gray-100">
      <p class="text-sm font-varela text-gray-500">Formateurs concernés</p>
      <p class="mt-2 text-3xl font-bold text-bleuone">{{ $formateurCount }}</p>
    </article>
    <article class="bg-white rounded-[20px] shadow-md p-6 border border-gray-100">
      <p class="text-sm font-varela text-gray-500">Taux de réussite observé</p>
      <p class="mt-2 text-3xl font-bold text-bleuone">{{ $avgSuccessRate ?? 0 }}%</p>
      <p class="mt-1 text-xs text-gray-500">{{ $learnerCount }} stagiaire(s) suivis</p>
    </article>
  </section>

  <section class="bg-white rounded-[20px] shadow-md p-6 border border-gray-100">
    <div class="flex items-center justify-between gap-4 mb-4">
      <div>
        <h2 class="text-xl font-bold text-bleuone font-raleway">Mes groupes observés</h2>
        <p class="text-sm text-gray-600">Accédez aux progressions et aux parcours en lecture seule.</p>
      </div>
      <a href="{{ route('observateur.groupes.index') }}" class="btn-oneduc">
        <x-icons.eye-iconify class="h-4 w-4" />
        Voir tous les groupes
      </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
      @forelse($observedGroups as $group)
        <article class="rounded-[16px] border border-gray-200 p-5">
          <div class="flex items-start justify-between gap-3">
            <div>
              <h3 class="text-lg font-semibold text-gray-900">{{ $group->name }}</h3>
              <p class="text-sm text-gray-600">Formateur : {{ trim(($group->instructor->prenom ?? '').' '.($group->instructor->name ?? '')) ?: 'Non renseigné' }}</p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-bold">Observé</span>
          </div>

          <div class="mt-4 flex flex-wrap gap-2 text-xs text-gray-600">
            <span>{{ $group->stagiaires_count }} stagiaire(s)</span>
            <span>•</span>
            <span>{{ $group->modules_count }} module(s)</span>
            @if($group->last_completed_at)
              <span>•</span>
              <span>Dernière activité {{ \Carbon\Carbon::parse($group->last_completed_at)->format('d/m/Y') }}</span>
            @endif
          </div>
        </article>
      @empty
        <p class="text-gray-500">Aucun groupe observé pour le moment.</p>
      @endforelse
    </div>
  </section>
</div>
@endsection
