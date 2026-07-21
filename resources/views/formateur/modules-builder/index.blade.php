{{-- Vue partagée par les constructeurs formateur et administrateur. --}}
@php
  $constructeurAdmin = (bool) ($constructeurAdmin ?? false);
  $layoutConstructeur = $layoutConstructeur ?? ($constructeurAdmin ? 'admin.admin_dashboard' : 'formateur.dashboard');
  $sectionConstructeur = $sectionConstructeur ?? ($constructeurAdmin ? 'admin' : 'formateur');
  $nomRoutesConstructeur = $nomRoutesConstructeur ?? ($constructeurAdmin
      ? 'admin.formations.constructeur'
      : 'formateur.modules.builder');
  $titreListeConstructeur = $constructeurAdmin ? 'Catalogue Oneduc' : 'Mes créations';
  $descriptionListeConstructeur = $constructeurAdmin
      ? 'Créez, versionnez et affectez les formations officielles du catalogue.'
      : 'Préparez vos formations, leurs leçons et leurs questions, puis assignez-les à vos groupes.';
  $urlAccueilConstructeur = $urlAccueilConstructeur ?? ($constructeurAdmin
      ? (Route::has('admin.dashboard') ? route('admin.dashboard') : url('/admin'))
      : route('formateur.formations.index'));
  $nomRouteDuplicationFormateur = Route::has($nomRoutesConstructeur.'.duplicate-trainer')
      ? $nomRoutesConstructeur.'.duplicate-trainer'
      : (Route::has($nomRoutesConstructeur.'.duplicate') ? $nomRoutesConstructeur.'.duplicate' : null);
@endphp

@extends($layoutConstructeur)

@section($sectionConstructeur)
<div class="w-full px-6 lg:px-8">

  {{-- En-tête --}}
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-5 pb-6 my-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div class="flex items-start gap-3">
        <div>
        <nav class="text-sm font-varela text-gray-500 mb-2">
          <ol class="inline-flex items-center space-x-1">
            <li>
              <a href="{{ $urlAccueilConstructeur }}" class="text-orangeone hover:underline">{{ $constructeurAdmin ? 'Administration' : 'Formations' }}</a>
            </li>
            <li><span class="mx-2 text-gray-400">/</span></li>
            <li class="text-gray-400">{{ $titreListeConstructeur }}</li>
          </ol>
        </nav>
        <h1 class="font-raleway text-2xl text-bleuone">{{ $titreListeConstructeur }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $descriptionListeConstructeur }}</p>
        </div>
      </div>
      <div class="flex flex-wrap items-center gap-3">
        @if(!$constructeurAdmin && Route::has('formateur.outils.quiz-questions.index'))
          <a href="{{ route('formateur.outils.quiz-questions.index') }}"
             class="btn-oneduc-outline !px-4 !py-2 !text-sm">
            <i class="ti ti-list-check" aria-hidden="true"></i>
            Questions de mes formations
          </a>
        @endif
        @if(Route::has($nomRoutesConstructeur.'.consommation-ia'))
          <a href="{{ route($nomRoutesConstructeur.'.consommation-ia') }}"
             class="text-sm font-medium text-orangeone hover:underline whitespace-nowrap">
            {{ $constructeurAdmin ? 'Budget IA du catalogue' : 'Ma consommation IA' }}
          </a>
        @endif
        @if($constructeurAdmin)
          <a href="{{ route($nomRoutesConstructeur.'.create') }}" class="btn-oneduc !px-4 !py-2 !text-sm">
            <i class="ti ti-plus" aria-hidden="true"></i>
            Créer une formation
          </a>
        @endif
      </div>
    </div>
  </header>

  @if(session('success'))
    <div class="mb-6 rounded-[10px] bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
      {{ session('success') }}
    </div>
  @endif

  @if($errors->any())
    <div class="mb-6 rounded-[10px] bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
      {{ $errors->first() }}
    </div>
  @endif

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

    {{-- Formulaire création --}}
    <div class="lg:col-span-1">
      <div class="bg-white rounded-[20px] shadow-md p-6 sticky top-6">
        <p class="font-varela text-base font-bold text-bleuone mb-4">{{ $constructeurAdmin ? 'Nouvelle formation officielle' : 'Nouvelle formation' }}</p>

        @if($constructeurAdmin)
          <p class="text-sm leading-relaxed text-gray-600">
            Commencez avec un chapitre vide ou laissez l'IA préparer une structure à relire avant publication.
          </p>
          <a href="{{ route($nomRoutesConstructeur.'.create') }}" class="btn-oneduc mt-5 w-full !py-2.5 !text-sm">
            Ouvrir le constructeur
          </a>
        @else
        <form method="POST" action="{{ route($nomRoutesConstructeur.'.store') }}" class="space-y-4">
          @csrf

          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Titre de la formation</label>
            <input type="text" name="module_title" value="{{ old('module_title') }}" required maxlength="255"
                   placeholder="Ex : Les bases du numérique"
                   class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-orangeone focus:outline-none focus:ring-2 focus:ring-orange-100">
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Description</label>
            <textarea name="description" rows="3" maxlength="5000"
                      placeholder="Décrivez brièvement le contenu de la formation"
                      class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-orangeone focus:outline-none focus:ring-2 focus:ring-orange-100">{{ old('description') }}</textarea>
          </div>

          <button type="submit" class="btn-oneduc w-full !py-2.5 !text-sm">
            Créer la formation
          </button>
        </form>
        @endif
      </div>
    </div>

    {{-- Liste des formations --}}
    <div class="lg:col-span-2 space-y-4">
      @forelse($modules as $module)
        <div class="bg-white rounded-[20px] shadow-md p-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div class="flex items-start gap-3 min-w-0">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-orange-100 text-orangeone">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s4.832.477 6 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
              </svg>
            </div>
            <div class="min-w-0">
              <p class="text-sm font-bold text-gray-900 truncate">{{ $module->module_title ?? $module->module_name }}</p>
              <p class="text-xs text-gray-500 truncate">{{ $module->description ?: 'Aucune description' }}</p>
              <p class="text-[10px] text-gray-400 mt-1">
                {{ $module->sections_count ?? 0 }} chapitre(s) · {{ $module->groups_count ?? 0 }} groupe(s) assigné(s)
              </p>
              @if($constructeurAdmin)
                @php
                  $etatPublication = $module->publication_state ?? ($module->status ? 'published' : 'draft');
                  $libelleEtat = ['draft' => 'Brouillon', 'published' => 'Publié', 'archived' => 'Archivé'][$etatPublication] ?? ucfirst((string) $etatPublication);
                  $classesEtat = match ($etatPublication) {
                      'published' => 'bg-emerald-50 text-emerald-700',
                      'archived' => 'bg-gray-100 text-gray-600',
                      default => 'bg-amber-50 text-amber-700',
                  };
                @endphp
                <div class="mt-2 flex flex-wrap items-center gap-2">
                  <span class="inline-flex rounded-full px-2 py-1 text-[11px] font-semibold {{ $classesEtat }}">{{ $libelleEtat }}</span>
                  <span class="text-[11px] font-semibold text-gray-500">Version {{ $module->version_number ?? 1 }}</span>
                  <span class="text-[11px] text-gray-400">{{ $module->formateur?->name ?? 'Catalogue Oneduc' }}</span>
                </div>
              @endif
            </div>
          </div>
          <div class="flex gap-2 shrink-0">
            <a href="{{ route($nomRoutesConstructeur.'.edit', $module) }}"
               class="inline-flex items-center gap-1.5 rounded-[8px] bg-orangeone px-3 py-1.5 text-xs font-bold text-white hover:opacity-90 transition">
              {{ $constructeurAdmin && in_array($module->publication_state ?? null, ['published', 'archived'], true) ? 'Consulter' : 'Modifier' }}
            </a>
            @if(!$constructeurAdmin || !in_array($module->publication_state ?? null, ['published', 'archived'], true))
            <button type="button" x-data x-on:click="$dispatch('open-modal', 'delete-module-{{ $module->id }}')"
                    class="inline-flex items-center gap-1.5 rounded-[8px] border border-gray-300 bg-white px-3 py-1.5 text-xs font-bold text-gray-600 hover:bg-gray-50 transition">
              Supprimer
            </button>
            <x-confirm-modal
              name="delete-module-{{ $module->id }}"
              title="Supprimer cette formation ?"
              message="Cette action est irréversible."
              :action="route($nomRoutesConstructeur.'.destroy', $module)"
              method="DELETE"
              confirm-label="Supprimer"
            />
            @endif
          </div>
        </div>
      @empty
        <div class="flex flex-col items-center justify-center rounded-[20px] border-2 border-dashed border-gray-200 bg-white py-16 text-center">
          <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-orange-100 text-orangeone">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s4.832.477 6 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
          </div>
          <p class="text-sm font-semibold text-gray-700">{{ $constructeurAdmin ? 'Le catalogue ne contient encore aucune formation' : 'Aucune formation créée' }}</p>
          <p class="text-xs text-gray-400 mt-1">{{ $constructeurAdmin ? 'Créez la première formation officielle Oneduc.' : 'Utilisez le formulaire pour créer votre première formation.' }}</p>
        </div>
      @endforelse
    </div>

  </div>

  @if($constructeurAdmin && isset($formationsFormateurs))
    <section class="mb-10 rounded-[20px] border border-gray-100 bg-white p-6 shadow-md" aria-labelledby="creations-formateurs-title">
      <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
          <h2 id="creations-formateurs-title" class="font-varela text-lg font-bold text-bleuone">Créations personnelles des formateurs</h2>
          <p class="mt-1 text-sm text-gray-600">Consultez une création ou copiez-la dans le catalogue sans modifier l'original.</p>
        </div>
        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">{{ $formationsFormateurs->count() }} création(s)</span>
      </div>

      <div class="mt-5 grid grid-cols-1 gap-3 xl:grid-cols-2">
        @forelse($formationsFormateurs as $formationFormateur)
          <article class="flex flex-col justify-between gap-4 rounded-[14px] border border-gray-200 p-4 sm:flex-row sm:items-center">
            <div class="min-w-0">
              <h3 class="truncate text-sm font-bold text-gray-900">{{ $formationFormateur->module_title ?? $formationFormateur->module_name }}</h3>
              <p class="mt-1 truncate text-xs text-gray-500">{{ $formationFormateur->description ?: 'Aucune description' }}</p>
              <p class="mt-2 text-[11px] text-gray-500">
                Par <span class="font-semibold text-bleuone">{{ $formationFormateur->formateur?->name ?? 'Formateur non renseigné' }}</span>
                · {{ $formationFormateur->sections_count ?? 0 }} chapitre(s)
              </p>
            </div>
            <div class="flex shrink-0 items-center gap-2">
              <a href="{{ route($nomRoutesConstructeur.'.preview', $formationFormateur) }}" target="_blank" rel="noopener"
                 class="btn-oneduc-outline !px-3 !py-1.5 !text-xs">Consulter</a>
              @if($nomRouteDuplicationFormateur)
                <form method="POST" action="{{ route($nomRouteDuplicationFormateur, $formationFormateur) }}">
                  @csrf
                  <button type="submit" class="btn-oneduc !px-3 !py-1.5 !text-xs">Copier dans le catalogue</button>
                </form>
              @endif
            </div>
          </article>
        @empty
          <p class="xl:col-span-2 rounded-[12px] bg-gray-50 px-4 py-8 text-center text-sm text-gray-500">Aucune création formateur n'est disponible.</p>
        @endforelse
      </div>
    </section>
  @endif
</div>
@endsection
