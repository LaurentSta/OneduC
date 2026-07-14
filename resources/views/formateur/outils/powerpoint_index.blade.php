@extends('formateur.dashboard')

@section('formateur')
@php
  $statusLabels = [
    'pending' => 'En attente',
    'processing' => 'Conversion en cours',
    'ready' => 'Prêt',
    'failed' => 'Échec',
    'none' => 'Non converti',
  ];
  $statusClasses = [
    'pending' => 'bg-amber-100 text-amber-700',
    'processing' => 'bg-blue-100 text-blue-700',
    'ready' => 'bg-green-100 text-green-700',
    'failed' => 'bg-red-100 text-red-700',
    'none' => 'bg-gray-100 text-gray-600',
  ];
@endphp

<div class="w-full px-6 lg:px-8">
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-5 pb-6 my-6">
    <div class="flex flex-wrap items-center justify-between gap-5">
      <div>
        <nav class="text-sm font-varela text-gray-500 mb-2">
          <ol class="inline-flex items-center space-x-1">
            <li><a href="{{ route('formateur.outils.index') }}" class="text-orangeone hover:underline">Outils numériques</a></li>
            <li><span class="mx-2 text-gray-400">/</span></li>
            <li class="text-gray-400">PowerPoint vers module</li>
          </ol>
        </nav>
        <p class="font-raleway text-2xl text-bleuone">Créer un module depuis PowerPoint</p>
        <p class="text-sm text-gray-500 mt-1 max-w-3xl">
          Déposez une présentation. Onéduc crée le module, transforme les diapositives et prépare un lecteur navigable façon SlideShare.
        </p>
      </div>
      <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-violet-100 text-violet-700">
        <svg class="h-9 w-9" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M8 3h8a2 2 0 012 2v14a2 2 0 01-2 2H8a2 2 0 01-2-2V5a2 2 0 012-2zm1 4h6m-6 4h6m-6 4h4"/>
        </svg>
      </div>
    </div>
  </header>

  @if(session('success'))
    <div class="mb-5 rounded-[14px] border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-700">
      {{ session('success') }}
    </div>
  @endif

  @if($errors->any())
    <div class="mb-5 rounded-[14px] border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
      <p class="font-bold">Le module n’a pas pu être créé.</p>
      <p class="mt-1">{{ $errors->first() }}</p>
    </div>
  @endif

  @if(!$conversionEnvironment['pdf_ready'])
    <div class="mb-5 rounded-[14px] border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
      <p class="font-bold">Conversion indisponible sur ce serveur</p>
      <p class="mt-1">Installez <code class="rounded bg-white px-1.5 py-0.5">pdftocairo</code> pour générer les images des diapositives.</p>
    </div>
  @elseif(!$conversionEnvironment['powerpoint_ready'])
    <div class="mb-5 rounded-[14px] border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
      <p class="font-bold">PowerPoint nécessite LibreOffice Impress</p>
      <p class="mt-1">
        Les fichiers PDF peuvent déjà être convertis. Installez <code class="rounded bg-white px-1.5 py-0.5">soffice</code> pour accepter aussi les fichiers
        <strong>.ppt</strong> et <strong>.pptx</strong>.
      </p>
    </div>
  @endif

  <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(20rem,0.85fr)_minmax(0,1.5fr)] mb-8">
    <section
      class="rounded-[20px] bg-white p-6 shadow-md xl:sticky xl:top-6 xl:self-start"
      x-data="{
        categories: @js($categoryPayload),
        categoryId: @js((string) old('category_id', '')),
        subcategoryId: @js((string) old('subcategory_id', '')),
        uploading: false,
        get subcategories() {
          return this.categories.find(category => category.id === this.categoryId)?.subcategories || [];
        },
        categoryChanged() {
          if (!this.subcategories.some(subcategory => subcategory.id === this.subcategoryId)) {
            this.subcategoryId = '';
          }
        }
      }"
    >
      <div class="mb-5 flex items-center gap-3">
        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-100 font-black text-violet-700">1</span>
        <div>
          <h2 class="font-varela text-base font-bold text-bleuone">Nouvelle présentation</h2>
          <p class="text-xs text-gray-500">Le module sera créé en brouillon.</p>
        </div>
      </div>

      <form
        method="POST"
        action="{{ route('formateur.outils.powerpoint.store') }}"
        enctype="multipart/form-data"
        class="space-y-4"
        @submit="uploading = true"
      >
        @csrf

        <div>
          <label for="powerpoint-title" class="block text-xs font-semibold text-gray-600 mb-1">Titre du module</label>
          <input
            id="powerpoint-title"
            type="text"
            name="title"
            value="{{ old('title') }}"
            maxlength="255"
            required
            placeholder="Ex : Les bases de la sécurité alimentaire"
            class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200"
          >
        </div>

        <div>
          <label for="powerpoint-description" class="block text-xs font-semibold text-gray-600 mb-1">Description <span class="font-normal text-gray-400">(optionnel)</span></label>
          <textarea
            id="powerpoint-description"
            name="description"
            rows="3"
            maxlength="3000"
            placeholder="Présentez en quelques mots l’objectif du module."
            class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200"
          >{{ old('description') }}</textarea>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
          <div>
            <label for="powerpoint-category" class="block text-xs font-semibold text-gray-600 mb-1">Catégorie</label>
            <select
              id="powerpoint-category"
              name="category_id"
              x-model="categoryId"
              @change="categoryChanged()"
              required
              class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200"
            >
              <option value="">Choisir...</option>
              <template x-for="category in categories" :key="category.id">
                <option :value="category.id" x-text="category.label"></option>
              </template>
            </select>
          </div>

          <div>
            <label for="powerpoint-subcategory" class="block text-xs font-semibold text-gray-600 mb-1">Sous-catégorie</label>
            <select
              id="powerpoint-subcategory"
              name="subcategory_id"
              x-model="subcategoryId"
              :disabled="!categoryId"
              required
              class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm disabled:bg-gray-100 disabled:text-gray-400 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200"
            >
              <option value="">Choisir...</option>
              <template x-for="subcategory in subcategories" :key="subcategory.id">
                <option :value="subcategory.id" x-text="subcategory.label"></option>
              </template>
            </select>
          </div>
        </div>

        <details class="rounded-[12px] border border-gray-200 bg-gray-50 px-4 py-3">
          <summary class="cursor-pointer text-xs font-bold text-gray-600">Options pédagogiques</summary>
          <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
              <label for="powerpoint-section" class="block text-xs font-semibold text-gray-600 mb-1">Titre du chapitre</label>
              <input
                id="powerpoint-section"
                type="text"
                name="section_title"
                value="{{ old('section_title', 'Présentation') }}"
                maxlength="255"
                class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200"
              >
            </div>
            <div>
              <label for="powerpoint-lesson" class="block text-xs font-semibold text-gray-600 mb-1">Titre de la leçon</label>
              <input
                id="powerpoint-lesson"
                type="text"
                name="lecture_title"
                value="{{ old('lecture_title') }}"
                maxlength="255"
                placeholder="Identique au module par défaut"
                class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200"
              >
            </div>
            <div>
              <label for="powerpoint-duration" class="block text-xs font-semibold text-gray-600 mb-1">Durée estimée <span class="font-normal text-gray-400">(minutes)</span></label>
              <input
                id="powerpoint-duration"
                type="number"
                name="duration"
                value="{{ old('duration') }}"
                min="0"
                max="1440"
                class="w-full rounded-[10px] border border-gray-300 px-3 py-2.5 text-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200"
              >
            </div>
          </div>
        </details>

        <div class="rounded-[14px] border-2 border-dashed border-violet-200 bg-violet-50/60 p-4">
          <label for="powerpoint-file" class="block text-sm font-bold text-violet-800">Fichier PowerPoint ou PDF</label>
          <p class="mt-1 text-xs leading-5 text-violet-700/75">Formats acceptés : .ppt, .pptx et .pdf. Taille maximale : 50 Mo.</p>
          <input
            id="powerpoint-file"
            type="file"
            name="slides_file"
            accept=".ppt,.pptx,.pdf"
            required
            class="mt-3 block w-full text-xs text-gray-500 file:mr-3 file:rounded-lg file:border-0 file:bg-violet-600 file:px-4 file:py-2.5 file:font-bold file:text-white hover:file:bg-violet-700"
          >
        </div>

        <button
          type="submit"
          :disabled="uploading"
          class="inline-flex w-full items-center justify-center gap-2 rounded-[10px] bg-violet-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-violet-700 disabled:cursor-wait disabled:opacity-60"
        >
          <svg x-show="!uploading" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16V4m0 0L7 9m5-5l5 5M5 20h14"/>
          </svg>
          <span x-text="uploading ? 'Création en cours...' : 'Créer le module'"></span>
        </button>
      </form>
    </section>

    <section class="space-y-4">
      <div class="rounded-[20px] bg-white px-6 py-5 shadow-md">
        <div class="flex items-center gap-3">
          <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-100 font-black text-violet-700">2</span>
          <div>
            <h2 class="font-varela text-base font-bold text-bleuone">Mes modules PowerPoint</h2>
            <p class="text-xs text-gray-500">Suivez la conversion, prévisualisez puis publiez vos supports.</p>
          </div>
        </div>
      </div>

      @forelse($presentations as $presentation)
        @php
          $presentationLecture = $presentation->lectures->first();
          $presentationStatus = (string) ($presentationLecture?->slides_status ?? 'none');
        @endphp
        <article class="rounded-[20px] bg-white p-5 shadow-md">
          <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
              <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $statusClasses[$presentationStatus] ?? $statusClasses['none'] }}">
                  {{ $statusLabels[$presentationStatus] ?? 'Inconnu' }}
                </span>
                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-semibold text-gray-600">
                  {{ $presentation->status ? 'Publié' : 'Brouillon' }}
                </span>
              </div>
              <h3 class="mt-3 font-raleway text-lg font-semibold text-bleuone">{{ $presentation->module_title }}</h3>
              <p class="mt-1 text-xs text-gray-500">
                {{ $presentation->category?->category_name }} · {{ $presentation->subCategory?->subcategory_name }}
                @if($presentationLecture?->slide_count)
                  · {{ $presentationLecture->slide_count }} diapositives
                @endif
              </p>
            </div>
            <a
              href="{{ route('formateur.outils.powerpoint.show', $presentation) }}"
              class="inline-flex shrink-0 items-center justify-center gap-2 rounded-[10px] bg-violet-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-violet-700"
            >
              Ouvrir
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </a>
          </div>
        </article>
      @empty
        <div class="rounded-[20px] border-2 border-dashed border-gray-200 bg-white px-6 py-12 text-center shadow-sm">
          <p class="font-varela text-base font-bold text-bleuone">Aucun module PowerPoint</p>
          <p class="mt-2 text-sm text-gray-500">Votre première présentation apparaîtra ici après l’import.</p>
        </div>
      @endforelse

      {{ $presentations->links() }}
    </section>
  </div>
</div>
@endsection
