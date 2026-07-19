{{-- Vue de création partagée par les espaces formateur et administrateur. --}}
@php
  $constructeurAdmin = (bool) ($constructeurAdmin ?? false);
  $layoutConstructeur = $layoutConstructeur ?? ($constructeurAdmin ? 'admin.admin_dashboard' : 'formateur.dashboard');
  $sectionConstructeur = $sectionConstructeur ?? ($constructeurAdmin ? 'admin' : 'formateur');
  $nomRoutesConstructeur = $nomRoutesConstructeur ?? ($constructeurAdmin
      ? 'admin.formations.constructeur'
      : 'formateur.modules.builder');
  $urlAccueilConstructeur = $urlAccueilConstructeur ?? ($constructeurAdmin
      ? (Route::has('admin.dashboard') ? route('admin.dashboard') : url('/admin'))
      : route('formateur.dashboard'));
  $urlRetourConstructeur = $urlRetourConstructeur ?? ($constructeurAdmin
      ? route($nomRoutesConstructeur.'.index')
      : route('formateur.formations.index', ['tab' => 'creations']));
@endphp

@extends($layoutConstructeur)

@section($sectionConstructeur)

<div class="max-w-[1285px] mx-auto px-8">

  <header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full mb-6">
    <div class="flex items-center justify-between">
      <div>
        <x-typography variant="titre">{{ $constructeurAdmin ? 'Créer une formation officielle' : 'Créer une formation' }}</x-typography>
        <nav class="text-sm font-varela text-gray-600 mt-2" aria-label="Fil d'Ariane">
          <ol class="inline-flex items-center space-x-1">
            <li class="flex items-center">
              <a href="{{ $urlAccueilConstructeur }}" class="text-orangeone hover:underline flex items-center" aria-label="Accueil">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9.75L12 3l9 6.75V19a2 2 0 01-2 2h-4a1 1 0 01-1-1v-5H10v5a1 1 0 01-1 1H5a2 2 0 01-2-2V9.75z"/>
                </svg>
              </a>
              <span class="mx-2 text-gray-400">/</span>
            </li>
            <li class="flex items-center">
              <a href="{{ $urlRetourConstructeur }}" class="text-orangeone hover:underline">{{ $constructeurAdmin ? 'Catalogue Oneduc' : 'Créations' }}</a>
              <span class="mx-2 text-gray-400">/</span>
            </li>
            <li class="text-gray-400">Créer</li>
          </ol>
        </nav>
      </div>
      <a href="{{ $urlRetourConstructeur }}"
         class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Retour
      </a>
    </div>
  </header>

  @if(session('success'))
    <div class="mb-6 max-w-xl rounded-[10px] bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
      {{ session('success') }}
    </div>
  @endif

  @if(session('error'))
    <div class="mb-6 max-w-xl rounded-[10px] bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
      {{ session('error') }}
    </div>
  @endif

  @if($constructeurAdmin)
    <div class="mb-6 max-w-xl rounded-[12px] border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
      <p class="font-semibold">Formation officielle du catalogue Oneduc</p>
      <p class="mt-1">Elle est créée en brouillon avec un premier chapitre vide. Vous choisirez explicitement quand la publier.</p>
    </div>
  @endif

  <div class="max-w-xl" x-data="{ mode: @js(old('theme') !== null || $errors->has('document') ? 'ia' : 'manuel') }">
    <div class="mb-4 inline-flex rounded-full border border-gray-200 bg-white p-1 text-sm shadow-sm">
      <button type="button"
              x-on:click="mode = 'manuel'"
              :class="mode === 'manuel' ? 'bg-bleuone text-white' : 'text-gray-500'"
              class="rounded-full px-4 py-1.5 font-semibold transition-colors">
        Créer manuellement
      </button>
      <button type="button"
              x-on:click="mode = 'ia'"
              :class="mode === 'ia' ? 'bg-bleuone text-white' : 'text-gray-500'"
              class="rounded-full px-4 py-1.5 font-semibold transition-colors">
        Générer avec l'IA
      </button>
    </div>

    <div x-show="mode === 'manuel'" class="bg-white rounded-[20px] shadow-md p-6">
      <form method="POST" action="{{ route($nomRoutesConstructeur.'.store') }}" class="space-y-4">
        @csrf

        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Titre de la formation</label>
          <input type="text" name="module_title" value="{{ old('module_title') }}" required maxlength="255"
                 placeholder="Ex : Les bases du numérique"
                 class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-orangeone focus:outline-none focus:ring-2 focus:ring-orange-100">
          @error('module_title')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
          @enderror
        </div>

        @if($constructeurAdmin && isset($categories))
          <div>
            <label for="categorie-formation-manuelle" class="block text-xs font-semibold text-gray-600 mb-1">Catégorie</label>
            <select id="categorie-formation-manuelle" name="category_id"
                    class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-orangeone focus:outline-none focus:ring-2 focus:ring-orange-100">
              <option value="">À classer ultérieurement</option>
              @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id') === (string) $category->id)>
                  {{ $category->category_name }}
                </option>
              @endforeach
            </select>
            @error('category_id')
              <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
          </div>
        @endif

        @include('shared.formations-constructeur.formateur-referent', [
          'idChampReferent' => 'formateur-referent-manuel',
        ])

        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Description</label>
          <textarea name="description" rows="4" maxlength="5000"
                    placeholder="Décrivez brièvement le contenu de la formation"
                    class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-orangeone focus:outline-none focus:ring-2 focus:ring-orange-100">{{ old('description') }}</textarea>
        </div>

        <p class="text-xs text-gray-400">Vous pourrez ajouter les chapitres et les leçons juste après la création.</p>

        <button type="submit" class="btn-oneduc !py-2.5 !text-sm">
          {{ $constructeurAdmin ? 'Créer le brouillon' : 'Créer la formation' }}
        </button>
      </form>
    </div>

    <div x-show="mode === 'ia'" x-cloak
         x-data="{ loading: false }"
         class="bg-white rounded-[20px] shadow-md p-6">
      <form method="POST" action="{{ route($nomRoutesConstructeur.'.generate-structure-ia') }}"
            enctype="multipart/form-data" class="space-y-4" x-on:submit="loading = true">
        @csrf

        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Thème de la formation</label>
          <textarea name="theme" rows="3" maxlength="500"
                    placeholder="Ex : Initiation à la sécurité informatique pour les seniors"
                    class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-orangeone focus:outline-none focus:ring-2 focus:ring-orange-100">{{ old('theme') }}</textarea>
          @error('theme')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
          @enderror
        </div>

        @if($constructeurAdmin && isset($categories))
          <div>
            <label for="categorie-formation-ia" class="block text-xs font-semibold text-gray-600 mb-1">Catégorie</label>
            <select id="categorie-formation-ia" name="category_id"
                    class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-orangeone focus:outline-none focus:ring-2 focus:ring-orange-100">
              <option value="">À classer ultérieurement</option>
              @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id') === (string) $category->id)>
                  {{ $category->category_name }}
                </option>
              @endforeach
            </select>
          </div>
        @endif

        @include('shared.formations-constructeur.formateur-referent', [
          'idChampReferent' => 'formateur-referent-ia',
        ])

        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Niveau des stagiaires (optionnel)</label>
          <select name="niveau_public"
                  class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-orangeone focus:outline-none focus:ring-2 focus:ring-orange-100">
            <option value="">Non précisé</option>
            <option value="debutant" @selected(old('niveau_public') === 'debutant')>Débutant</option>
            <option value="intermediaire" @selected(old('niveau_public') === 'intermediaire')>Intermédiaire</option>
            <option value="avance" @selected(old('niveau_public') === 'avance')>Avancé</option>
            <option value="mixte" @selected(old('niveau_public') === 'mixte')>Mixte (niveaux variés)</option>
          </select>
          @error('niveau_public')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Contexte ou secteur d'activité (optionnel)</label>
          <input type="text" name="contexte_public" value="{{ old('contexte_public') }}" maxlength="300"
                 placeholder="Ex : agents d'accueil en collectivité"
                 class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-orangeone focus:outline-none focus:ring-2 focus:ring-orange-100">
          @error('contexte_public')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Contraintes ou pré-requis particuliers (optionnel)</label>
          <textarea name="contraintes_public" rows="2" maxlength="300"
                    placeholder="Ex : aucun pré-requis technique, session de 2h max"
                    class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-orangeone focus:outline-none focus:ring-2 focus:ring-orange-100">{{ old('contraintes_public') }}</textarea>
          @error('contraintes_public')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Document source (optionnel)</label>
          <input type="file" name="document" accept=".pdf,.docx,.pptx,.txt"
                 class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-full file:border-0 file:bg-gray-100 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-gray-700 hover:file:bg-gray-200">
          <p class="mt-1 text-xs text-gray-400">PDF, Word (.docx), PowerPoint (.pptx) ou texte brut, 20 Mo max.</p>
          @error('document')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <p class="text-xs text-gray-400">
          Renseignez un thème, importez un document, ou les deux : l'IA (Mistral) génère automatiquement les chapitres, les leçons et leur contenu.
          Relisez et ajustez l'ensemble avant de proposer la formation aux stagiaires.
        </p>

        <button type="submit" :disabled="loading" class="btn-oneduc !py-2.5 !text-sm disabled:cursor-not-allowed disabled:opacity-60">
          <span x-show="!loading">{{ $constructeurAdmin ? 'Générer le brouillon' : 'Générer la formation' }}</span>
          <span x-show="loading" x-cloak>Génération en cours… (jusqu'à 5 minutes)</span>
        </button>
      </form>
    </div>
  </div>

</div>

@endsection
