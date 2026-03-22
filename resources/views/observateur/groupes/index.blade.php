@extends('observateur.dashboard')

@section('observateur')
<div class="max-w-[1285px] mx-auto px-8">
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full mb-6">
    <div class="grid grid-cols-12 gap-6 items-center">
      <div class="col-span-12 md:col-span-9">
        <p class="font-raleway text-titre text-bleuone leading-tight mb-4">Mes groupes observés</p>
        <p class="font-varela text-sous-titre text-orangeone leading-snug mb-3">Visualisez les groupes auxquels vous êtes rattaché.</p>
        <p class="font-lisible text-lg text-gray-800 leading-loose mb-6">
          Chaque groupe reste en lecture seule. Vous pouvez consulter les progressions et le parcours pédagogique sans modifier le contenu.
        </p>
      </div>
      <div class="col-span-12 md:col-span-3 flex justify-center md:justify-end">
        <img src="{{ asset('images/svg/Groupes.svg') }}" alt="Illustration des groupes observés" class="max-w-[256px] h-auto">
      </div>
    </div>
  </header>

  <main class="space-y-8">
    <section aria-labelledby="groupes-title">
      <h2 id="groupes-title" class="sr-only">Liste des groupes observés</h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($groupes as $groupe)
          <article class="bg-white border border-gray-200 rounded-[20px] shadow p-6 flex flex-col">
            <div class="flex items-start justify-between gap-3 mb-3">
              <h3 class="text-xl font-bold text-bleuone font-raleway truncate">{{ $groupe->name }}</h3>
              <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-bold whitespace-nowrap">Observé</span>
            </div>

            <p class="text-sm text-gray-600 mb-4">Formateur : {{ trim(($groupe->instructor->prenom ?? '').' '.($groupe->instructor->name ?? '')) ?: 'Non renseigné' }}</p>

            @if ($groupe->description)
              <p class="text-sm text-gray-700 font-lisible mb-4 line-clamp-3">{{ $groupe->description }}</p>
            @endif

            @if($groupe->observers->isNotEmpty())
              <div class="mb-4">
                <h4 class="text-sm font-medium text-gray-600 font-varela">Observateurs associés :</h4>
                <div class="mt-2 flex flex-wrap gap-2">
                  @foreach($groupe->observers as $observer)
                    <span class="inline-flex items-center bg-blue-50 text-blue-700 text-xs font-varela px-3 py-1 rounded-full">
                      {{ trim(($observer->prenom ?? '').' '.($observer->name ?? '')) }}
                    </span>
                  @endforeach
                </div>
              </div>
            @endif

            <div class="mb-4">
              <h4 class="text-sm font-medium text-gray-600 font-varela">Modules associés :</h4>
              <div class="mt-2 flex flex-wrap gap-2">
                @forelse ($groupe->modules as $module)
                  <a href="{{ route('observateur.groupes.modules.lecons.show', ['group' => $groupe->id, 'module' => $module->id]) }}"
                     class="inline-block bg-vertone/10 text-vertone text-xs font-varela px-3 py-1 rounded-full hover:bg-vertone/20 transition">
                    {{ \Illuminate\Support\Str::limit($module->module_title, 30) }}
                  </a>
                @empty
                  <p class="text-sm text-gray-400 font-lisible italic">Aucun module</p>
                @endforelse
              </div>
            </div>

            <div class="mt-auto flex gap-2 pt-4">
              <a href="{{ route('observateur.progressions.stagiaires', ['group_id' => $groupe->id]) }}" class="btn-oneduc w-1/2 text-center">
                Stagiaires
              </a>
              <a href="{{ route('observateur.progressions.groupes', ['search' => $groupe->name]) }}" class="btn-oneduc bg-bleuone border-bleuone hover:bg-white hover:text-bleuone w-1/2 text-center">
                Progression
              </a>
            </div>
          </article>
        @empty
          <p class="text-gray-500 col-span-full font-lisible">Aucun groupe observé ne vous a encore été attribué.</p>
        @endforelse
      </div>
    </section>
  </main>
</div>
@endsection
