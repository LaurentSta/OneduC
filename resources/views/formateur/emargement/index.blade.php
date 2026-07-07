@extends('formateur.dashboard')

@section('formateur')
<div class="w-full px-6 lg:px-8">

  <header class="bg-white rounded-[20px] shadow-md px-8 pt-5 pb-6 my-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div>
        <nav class="text-sm font-varela text-gray-500 mb-2">
          <ol class="inline-flex items-center space-x-1">
            <li>
              <a href="{{ route('formateur.outils.index') }}" class="text-orangeone hover:underline">Outils numériques</a>
            </li>
            <li><span class="mx-2 text-gray-400">/</span></li>
            @if ($selectedGroup)
              <li>
                <a href="{{ route('formateur.emargement.index') }}" class="text-orangeone hover:underline">Émargement</a>
              </li>
              <li><span class="mx-2 text-gray-400">/</span></li>
              <li class="text-gray-400">{{ $selectedGroup->name }}</li>
            @else
              <li class="text-gray-400">Émargement</li>
            @endif
          </ol>
        </nav>
        <p class="font-raleway text-2xl text-bleuone">Émargement</p>
        <p class="text-sm text-gray-500 mt-1">Feuilles de présence par séance datée, signées individuellement par chaque stagiaire. À activer groupe par groupe, selon vos besoins.</p>
      </div>
    </div>
  </header>

  @if (session('success'))
    <div class="mb-6 rounded-lg bg-vertone/10 text-vertone px-4 py-2 text-sm font-semibold">
      {{ session('success') }}
    </div>
  @endif

  @if (! $selectedGroup)
    @if ($groupes->isEmpty())
      <div class="bg-white rounded-[20px] shadow-md px-8 py-10 text-center">
        <p class="text-gray-500">
          Créez un <a href="{{ route('formateur.groupes.create') }}" class="text-orangeone hover:underline font-semibold">groupe</a> pour pouvoir émarger ses stagiaires.
        </p>
      </div>
    @else
      <div class="bg-white rounded-[20px] shadow-md px-8 py-8">
        <p class="text-sm font-bold text-bleuone uppercase mb-4">Groupes</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          @foreach ($groupes as $groupe)
            @if ($groupe->emargement_enabled)
              <a href="{{ route('formateur.emargement.index', ['group_id' => $groupe->id]) }}"
                 class="rounded-[12px] border border-gray-200 p-5 hover:border-slate-500 hover:shadow-md transition">
                <div class="flex items-center justify-between gap-2">
                  <p class="font-bold text-bleuone">{{ $groupe->name }}</p>
                  <span class="inline-flex items-center rounded-full bg-vertone/10 px-2 py-0.5 text-[10px] font-bold text-vertone">Activé</span>
                </div>
                <p class="text-xs text-gray-500 mt-1">{{ $groupe->students_count }} stagiaire{{ $groupe->students_count > 1 ? 's' : '' }}</p>
              </a>
            @else
              <div class="rounded-[12px] border border-dashed border-gray-300 p-5">
                <p class="font-bold text-gray-700">{{ $groupe->name }}</p>
                <p class="text-xs text-gray-500 mt-1 mb-3">{{ $groupe->students_count }} stagiaire{{ $groupe->students_count > 1 ? 's' : '' }} — émargement non activé</p>
                <form method="POST" action="{{ route('formateur.emargement.activer', $groupe->id) }}">
                  @csrf
                  <button type="submit" class="btn-oneduc-outline w-full !py-2 !text-sm">Activer pour ce groupe</button>
                </form>
              </div>
            @endif
          @endforeach
        </div>
      </div>
    @endif
  @elseif (! $selectedGroup->emargement_enabled)
    <div class="bg-white rounded-[20px] shadow-md px-8 py-10 text-center">
      <p class="text-gray-700 font-semibold mb-1">Émargement non activé pour {{ $selectedGroup->name }}</p>
      <p class="text-sm text-gray-500 mb-4">Activez l'outil pour ce groupe afin de créer des séances et suivre les présences.</p>
      <form method="POST" action="{{ route('formateur.emargement.activer', $selectedGroup->id) }}" class="inline">
        @csrf
        <button type="submit" class="btn-oneduc !py-2 !text-sm">Activer pour ce groupe</button>
      </form>
    </div>
  @else
    <div class="bg-white rounded-[20px] shadow-md px-8 py-8">
      <div class="flex justify-end mb-4">
        <form method="POST" action="{{ route('formateur.emargement.desactiver', $selectedGroup->id) }}">
          @csrf
          <button type="submit" class="text-xs font-semibold text-gray-400 hover:text-red-600 transition">
            Désactiver l'émargement pour ce groupe
          </button>
        </form>
      </div>
      @include('formateur.emargement.seances-panel', ['group' => $selectedGroup, 'seances' => $seances])
    </div>
  @endif
</div>
@endsection
