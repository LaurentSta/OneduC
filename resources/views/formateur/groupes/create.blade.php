@extends('formateur.dashboard')

@section('formateur')

@php
  $moduleFlowMeta = static function ($module): array {
    $lessonCount = (int) collect($module->sections ?? [])->flatMap->lectures->count();
    $questionCount = (int) collect($module->sections ?? [])->flatMap->lectures->sum(function ($lecture) {
      $plannedScorm = (int) ($lecture->question_count ?? 0);
      $plannedQuiz = (bool) ($lecture->quiz_enabled ?? false)
        ? (int) ($lecture->quiz_questions_per_attempt ?? 0)
        : 0;

      return max($plannedScorm, $plannedQuiz);
    });

    return [
      'lesson_count' => $lessonCount,
      'question_count' => $questionCount,
      'duration_label' => (string) ($module->formatted_duration ?? 'Rythme libre'),
    ];
  };

  $availableModules = $modules
    ->filter(fn($module) => !empty($module->status) && (int) $module->status === 1)
    ->values()
    ->map(fn($module) => array_merge([
      'id' => (int) $module->id,
      'title' => (string) $module->module_title,
    ], $moduleFlowMeta($module)))
    ->values();

  $modulesById = $availableModules->keyBy('id');
  $oldPositions = old('module_positions', []);

  $oldModuleIds = collect(old('modules', []))
    ->map(fn($id) => (int) $id)
    ->filter(fn($id) => $id > 0)
    ->unique()
    ->values();

  $initialSelectedModules = $oldModuleIds
    ->sortBy(fn($id) => (int) data_get($oldPositions, (string) $id, PHP_INT_MAX))
    ->values()
    ->map(function ($id, $index) use ($modulesById) {
      $moduleMeta = $modulesById->get($id) ?? [];

      return [
        'id' => $id,
        'title' => (string) data_get($moduleMeta, 'title', "Module #{$id}"),
        'position' => $index + 1,
        'persisted' => false,
        'manage_url' => '',
        'lesson_count' => (int) data_get($moduleMeta, 'lesson_count', 0),
        'question_count' => (int) data_get($moduleMeta, 'question_count', 0),
        'duration_label' => (string) data_get($moduleMeta, 'duration_label', 'Rythme libre'),
      ];
    })
    ->values();

  $initialStagiaires = collect(old('stagiaires', []))
    ->map(fn($stagiaire) => [
      'prenom' => trim((string) data_get($stagiaire, 'prenom', '')),
      'nom' => trim((string) data_get($stagiaire, 'nom', '')),
      'email' => trim((string) data_get($stagiaire, 'email', '')),
    ])
    ->filter(fn($stagiaire) => $stagiaire['prenom'] !== '' || $stagiaire['nom'] !== '' || $stagiaire['email'] !== '')
    ->values();

  if ($initialStagiaires->isEmpty()) {
    $initialStagiaires = collect([[
      'prenom' => '',
      'nom' => '',
      'email' => '',
    ]]);
  }

  $steps = [
    ['label' => 'Informations', 'helper' => 'Nom et description'],
    ['label' => 'Stagiaires', 'helper' => 'Accès et invitations'],
    ['label' => 'Modules', 'helper' => 'Parcours pedagogique'],
  ];

  $initialWizardStep = 1;
  if ($errors->has('modules') || $errors->has('modules.*') || $errors->has('module_positions') || $errors->has('module_positions.*')) {
    $initialWizardStep = 3;
  } elseif ($errors->has('stagiaires') || $errors->has('stagiaires.*') || $errors->has('password')) {
    $initialWizardStep = 2;
  }

  $studentErrorMessage = $errors->first('stagiaires')
    ?: $errors->first('stagiaires.*.email')
    ?: $errors->first('stagiaires.*.prenom')
    ?: $errors->first('stagiaires.*.nom');

  $wizardErrorMessages = collect($errors->all())
    ->filter(fn($message) => filled($message))
    ->unique()
    ->values();

  $oldIsActive = old('is_active', 1);
  $isGroupActive = filter_var($oldIsActive, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
  $isGroupActive = $isGroupActive ?? in_array((string) $oldIsActive, ['1', 'on'], true);
  $showOptionsPanel = $errors->has('start_date') || $errors->has('end_date') || $errors->has('is_active') || $errors->has('co_formateurs') || $errors->has('co_formateurs.*');
@endphp

<div class="max-w-[1285px] mx-auto px-8">

  <div class="bg-white rounded-[20px] shadow-md px-8 pt-6 pb-6 w-full mb-6">
    <div class="grid grid-cols-12 gap-6 items-center">
      <div class="col-span-12 md:col-span-9">
        <h1 class="font-raleway text-titre text-bleuone leading-tight mb-2">
          Création d’un nouveau groupe
        </h1>
        <p class="font-varela text-gray-600 mb-4">
          Construisez votre groupe avec le même confort de lecture que dans l’édition : informations, stagiaires, puis organisation du parcours.
        </p>

        <div class="flex flex-wrap items-center gap-3 mb-4 font-varela text-sm">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-bleuone/10 text-bleuone border border-bleuone/20">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <span class="font-bold">3</span> Étapes guidées
          </div>
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orangeone/10 text-orangeone border border-orangeone/20">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <span class="font-bold">{{ $availableModules->count() }}</span> Modules disponibles
          </div>
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-vertone/10 text-vertone border border-vertone/20">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            Invitations stagiaires automatiques
          </div>
        </div>

        <nav class="text-sm font-varela text-gray-600 mt-2" aria-label="Fil d'Ariane">
          <ol class="inline-flex items-center space-x-1">
            <li class="flex items-center">
              <a href="{{ route('formateur.groupes.index') }}" class="hover:underline text-bleuone">Mes groupes</a>
              <span class="mx-2 text-gray-400">/</span>
            </li>
            <li class="text-gray-400">Créer un groupe</li>
          </ol>
        </nav>
      </div>

      <div class="col-span-12 md:col-span-3 flex justify-center md:justify-end">
        <img src="{{ asset('images/svg/Groupes.svg') }}" alt="Illustration" class="max-w-[256px] h-auto opacity-80">
      </div>
    </div>
  </div>

  <main class="bg-white rounded-[20px] shadow-md px-8 py-10 w-full">
    <form id="multi-step-form" method="POST" action="{{ route('formateur.groupes.store') }}" class="space-y-8" novalidate>
      @csrf

      @include('formateur.groupes.partials.wizard-errors', [
        'messages' => $wizardErrorMessages,
        'clientBoxId' => 'wizard-client-errors',
      ])

      <nav aria-label="Sections du groupe">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          @foreach($steps as $index => $step)
            <button
              type="button"
              data-step="{{ $index + 1 }}"
              aria-current="{{ $index + 1 === $initialWizardStep ? 'step' : 'false' }}"
              class="wizard-step w-full px-6 py-4 rounded-full transition font-varela text-lg font-bold focus:outline-none flex items-center justify-center gap-2 border border-bleuone"
            >
              <span>{{ $index + 1 }}.</span>
              <span>{{ $step['label'] }}</span>
            </button>
          @endforeach
        </div>

        <div class="mt-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
          <div class="h-1 w-full overflow-hidden rounded bg-gray-100">
            <div id="progress-bar" class="h-1 bg-orangeone transition-all duration-500" style="width: {{ ($initialWizardStep / count($steps)) * 100 }}%"></div>
          </div>
          <p id="progress-label" class="shrink-0 text-sm font-varela text-gray-500">Étape {{ $initialWizardStep }} sur {{ count($steps) }}</p>
        </div>
        <div id="progress-live" class="sr-only" aria-live="polite">Étape {{ $initialWizardStep }} sur {{ count($steps) }}</div>
      </nav>

      <fieldset id="step-1" class="step">
        <section class="animate-fade-in-down">
          <div class="mb-6">
            <div class="mb-2 flex items-center gap-2">
              <label for="nom" class="block text-base font-medium text-gray-900">Nom du groupe</label>
              <div class="relative group">
                <button type="button" aria-label="Information sur le nom du groupe" class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-gray-300 bg-white text-[11px] font-bold text-gray-600">
                  ?
                </button>
                <div class="pointer-events-none absolute left-1/2 top-full z-20 mt-2 hidden w-72 -translate-x-1/2 rounded-lg border border-gray-200 bg-white p-2 text-xs text-gray-700 shadow-lg group-hover:block group-focus-within:block">
                  Ce champ est obligatoire. Choisissez un nom simple et explicite pour retrouver facilement le groupe par la suite.
                </div>
              </div>
            </div>
            <input
              id="nom"
              name="nom"
              type="text"
              required
              value="{{ old('nom') }}"
              class="bg-gray-50 border {{ $errors->has('nom') ? 'border-red-400' : 'border-gray-300' }} text-base rounded-lg focus:ring-orangeone focus:border-orangeone block w-full p-2.5"
              placeholder="Ex : Groupe Marketing 2025 - Niveau 1"
            >
            @error('nom')
              <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
            @enderror
          </div>

          <div class="mb-6">
            <div class="mb-2 flex items-center gap-2">
              <label for="description" class="block text-base font-medium text-gray-900">Description</label>
              <div class="relative group">
                <button type="button" aria-label="Information sur la description du groupe" class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-gray-300 bg-white text-[11px] font-bold text-gray-600">
                  ?
                </button>
                <div class="pointer-events-none absolute left-1/2 top-full z-20 mt-2 hidden w-72 -translate-x-1/2 rounded-lg border border-gray-200 bg-white p-2 text-xs text-gray-700 shadow-lg group-hover:block group-focus-within:block">
                  Ce champ est facultatif. Vous pouvez ajouter quelques mots pour préciser l’objectif, le public ou le contexte du groupe.
                </div>
              </div>
            </div>
            <textarea
              id="description"
              name="description"
              rows="3"
              class="bg-gray-50 border border-gray-300 text-base rounded-lg focus:ring-orangeone focus:border-orangeone block w-full p-2.5"
              placeholder="Objectifs, public, période…"
            >{{ old('description') }}</textarea>
          </div>

          <details class="mb-6" {{ $showOptionsPanel ? 'open' : '' }}>
            <summary class="inline-flex cursor-pointer items-center gap-3 rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-800 shadow-sm marker:hidden">
              <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-bleuone/10 text-bleuone">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M7 12h10M10 18h4" />
                </svg>
              </span>
              <span>Options</span>
            </summary>

            <div class="mt-3 rounded-[18px] border border-gray-200 bg-white px-4 py-4">
              <div class="grid gap-4 lg:grid-cols-2 lg:items-stretch">
                <div class="space-y-4 rounded-[18px] border {{ $errors->has('is_active') || $errors->has('start_date') || $errors->has('end_date') ? 'border-sky-300 bg-sky-100/80' : 'border-sky-200 bg-sky-50/80' }} px-4 py-4">
                  <div class="rounded-[18px] border {{ $errors->has('is_active') ? 'border-red-300 bg-red-50/70' : 'border-white/70 bg-white/80' }} px-4 py-3">
                    <div class="flex items-center justify-between gap-4">
                      <input type="hidden" name="is_active" value="0">
                      <label for="is_active" class="flex items-center gap-3 text-base font-medium text-gray-900">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-bleuone/10 text-bleuone">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v9m6.364-5.364a9 9 0 11-12.728 0" />
                          </svg>
                        </span>
                        <span>Activer le groupe</span>
                      </label>

                      <input
                        id="is_active"
                        name="is_active"
                        type="checkbox"
                        value="1"
                        class="peer sr-only"
                        {{ $isGroupActive ? 'checked' : '' }}
                      >
                      <label
                        for="is_active"
                        aria-label="Activer ou désactiver le groupe"
                        class="relative inline-flex h-7 w-12 cursor-pointer rounded-full bg-gray-300 transition-colors duration-200 after:absolute after:left-1 after:top-1 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow-sm after:transition-transform after:duration-200 after:content-[''] peer-checked:bg-vertone peer-checked:after:translate-x-5"
                      ></label>
                    </div>
                    @error('is_active')
                      <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                  </div>

                  <div class="space-y-4">
                    <div class="rounded-[18px] border {{ $errors->has('start_date') ? 'border-red-300 bg-red-50/70' : 'border-white/70 bg-white/80' }} px-4 py-4">
                      <div class="mb-2 flex items-center gap-2">
                        <label for="start_date" class="block text-base font-medium text-gray-900">Date de démarrage</label>
                      </div>
                      <input
                        id="start_date"
                        name="start_date"
                        type="date"
                        value="{{ old('start_date') }}"
                        class="bg-white border {{ $errors->has('start_date') ? 'border-red-400' : 'border-gray-300' }} text-base rounded-lg focus:ring-orangeone focus:border-orangeone block w-full p-2.5"
                      >
                      @error('start_date')
                        <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
                      @enderror
                    </div>

                    <div class="rounded-[18px] border {{ $errors->has('end_date') ? 'border-red-300 bg-red-50/70' : 'border-white/70 bg-white/80' }} px-4 py-4">
                      <div class="mb-2 flex items-center gap-2">
                        <label for="end_date" class="block text-base font-medium text-gray-900">Date de fin</label>
                      </div>
                      <input
                        id="end_date"
                        name="end_date"
                        type="date"
                        value="{{ old('end_date') }}"
                        class="bg-white border {{ $errors->has('end_date') ? 'border-red-400' : 'border-gray-300' }} text-base rounded-lg focus:ring-orangeone focus:border-orangeone block w-full p-2.5"
                      >
                      @error('end_date')
                        <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
                      @enderror
                    </div>
                  </div>
                </div>

                @include('formateur.groupes.partials.co-formateurs-field', [
                  'mode' => 'create',
                  'selectedCoFormateurs' => $initialCoFormateurs,
                  'canManageCoFormateurs' => $canManageCoFormateurs,
                  'group' => null,
                  'wrapperClass' => 'min-w-0',
                  'panelClass' => 'border-orange-200 bg-orange-50/80',
                ])
              </div>
            </div>
          </details>
        </section>
      </fieldset>

      <fieldset id="step-2" class="step hidden">
        <section class="animate-fade-in-down">
          <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-4">
              <div class="space-y-3">
                <div class="flex items-center gap-2">
                  <h2 class="text-xl font-bold text-bleuone font-raleway">Ajouter vos stagiaires</h2>
                  <div class="relative group">
                    <button type="button" aria-label="Information sur l'ajout de stagiaires" class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-gray-300 bg-white text-[11px] font-bold text-gray-600">
                      ?
                    </button>
                    <div class="pointer-events-none absolute left-1/2 top-full z-20 mt-2 hidden w-80 -translate-x-1/2 rounded-lg border border-gray-200 bg-white p-3 text-xs text-gray-700 shadow-lg group-hover:block group-focus-within:block">
                      Ajoutez vos apprenants ligne par ligne ou via import CSV. Le code d’accès provisoire défini plus bas sera réutilisé pour leurs comptes.
                    </div>
                  </div>
                </div>
                <p class="text-sm text-gray-600 font-lisible">
                  La présentation suit la même logique que l’édition pour garder les repères visuels au moment de l’ajout.
                </p>
              </div>

              <div class="flex items-center gap-2 sm:pt-1">
                <button
                  type="button"
                  class="w-10 h-10 inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 hover:text-bleuone hover:border-bleuone/30 transition"
                  onclick="openCsvModalCreate()"
                  aria-label="Importer des stagiaires par CSV"
                  title="Importer un lot CSV"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                  </svg>
                </button>
              </div>
            </div>

            @if($studentErrorMessage)
              <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $studentErrorMessage }}
              </div>
            @endif

            <div id="stagiaires-container" class="space-y-3">
              @foreach($initialStagiaires as $index => $stagiaire)
                <div class="bg-white border border-gray-200 p-4 rounded-[12px] shadow-sm relative stagiaire-row group hover:border-orangeone/50 transition">
                  <div class="grid grid-cols-1 md:grid-cols-[1fr_1fr_2fr_auto] gap-4 items-start">
                    <div>
                      <label for="stagiaires_{{ $index }}_prenom" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Prénom</label>
                      <input
                        id="stagiaires_{{ $index }}_prenom"
                        name="stagiaires[{{ $index }}][prenom]"
                        type="text"
                        placeholder="Ex: Thomas"
                        required
                        value="{{ $stagiaire['prenom'] }}"
                        class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-orangeone focus:border-orangeone w-full p-2.5"
                      >
                    </div>

                    <div>
                      <label for="stagiaires_{{ $index }}_nom" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Nom</label>
                      <input
                        id="stagiaires_{{ $index }}_nom"
                        name="stagiaires[{{ $index }}][nom]"
                        type="text"
                        placeholder="Ex: Dupont"
                        required
                        value="{{ $stagiaire['nom'] }}"
                        class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-orangeone focus:border-orangeone w-full p-2.5"
                      >
                    </div>

                    <div>
                      <label for="stagiaires_{{ $index }}_email" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Email professionnel</label>
                      <input
                        id="stagiaires_{{ $index }}_email"
                        name="stagiaires[{{ $index }}][email]"
                        type="email"
                        placeholder="thomas.dupont@entreprise.com"
                        required
                        value="{{ $stagiaire['email'] }}"
                        class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-orangeone focus:border-orangeone w-full p-2.5"
                      >
                    </div>

                    <div class="flex items-end h-full pb-[3px]">
                      <button
                        type="button"
                        class="text-gray-300 hover:text-red-600 transition p-2 rounded-full hover:bg-red-50"
                        onclick="removeStagiaire(this)"
                        title="Supprimer la ligne"
                      >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>

            <div class="mt-3 flex justify-end">
              <button
                type="button"
                class="px-4 py-2 bg-bleuone text-white border border-bleuone font-bold rounded-lg hover:opacity-90 transition inline-flex items-center justify-center gap-2 text-sm"
                onclick="addStagiaire()"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Ajouter un stagiaire
              </button>
            </div>
          </div>

          <div class="mt-6 bg-orangeone/5 border border-orangeone/20 rounded-[12px] p-3">
            <div class="mb-2 flex items-center gap-2">
              <h3 class="text-sm font-bold text-gray-800 font-raleway">Mot de passe provisoire du groupe</h3>
              <div class="relative group">
                <button type="button" aria-label="Information sur le mot de passe provisoire" class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-gray-300 bg-white text-[11px] font-bold text-gray-600">
                  ?
                </button>
                <div class="pointer-events-none absolute left-1/2 top-full z-20 mt-2 hidden w-72 -translate-x-1/2 rounded-lg border border-gray-200 bg-white p-2 text-xs text-gray-700 shadow-lg group-hover:block group-focus-within:block">
                  Les stagiaires recevront un e-mail avec leur identifiant et un lien qu'ils pourront utiliser pour se connecter.
                </div>
              </div>
            </div>

            <div class="w-full max-w-sm">
              <label for="password" class="sr-only">Mot de passe provisoire</label>
              <input
                id="password"
                name="password"
                type="text"
                required
                minlength="8"
                autocomplete="off"
                value="{{ old('password') }}"
                class="bg-white border {{ $errors->has('password') ? 'border-red-400' : 'border-gray-300' }} text-gray-900 text-sm rounded-lg focus:ring-orangeone focus:border-orangeone block w-full px-3 py-2.5 font-mono tracking-wide"
                placeholder="Ex: Formation2026!"
              >
              @error('password')
                <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
              @enderror
            </div>
          </div>
        </section>
      </fieldset>

      <div id="csv-modal-create" class="fixed inset-0 z-[80] hidden items-center justify-center bg-black/40 p-4">
        <div class="w-full max-w-xl rounded-2xl border border-gray-200 bg-white shadow-2xl">
          <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
            <h4 class="font-bold text-bleuone">Import de stagiaires (CSV)</h4>
            <button type="button" class="text-gray-400 hover:text-gray-700" onclick="closeCsvModalCreate()" aria-label="Fermer">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="px-5 py-4 space-y-4">
            <p class="text-sm text-gray-600">
              Format attendu: colonnes <span class="font-bold">prenom</span>, <span class="font-bold">nom</span>, <span class="font-bold">email</span>.
            </p>
            <p class="text-xs text-gray-500 bg-gray-50 border border-gray-100 rounded-lg px-3 py-2">
              Exemple d’en-tête : <code>prenom;nom;email</code> (ou séparateur virgule).
            </p>
            <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
              <p class="text-xs font-semibold text-gray-700 mb-1">Exemple CSV (2 stagiaires)</p>
              <pre class="text-xs text-gray-700 whitespace-pre-wrap">prenom;nom;email
Camille;Martin;camille.martin@entreprise.fr
Lucas;Bernard;lucas.bernard@entreprise.fr</pre>
            </div>

            <input id="csv-file-create" type="file" accept=".csv,text/csv" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">

            <div id="csv-feedback-create" class="hidden rounded-lg px-3 py-2 text-sm"></div>
          </div>

          <div class="flex items-center justify-end gap-2 border-t border-gray-100 px-5 py-4">
            <button type="button" class="btn-oneduc-outline !px-3 !py-2 !text-sm" onclick="closeCsvModalCreate()">
              Fermer
            </button>
            <button type="button" id="csv-import-confirm-create" class="btn-oneduc-blue !px-3 !py-2 !text-sm">
              Importer
            </button>
          </div>
        </div>
      </div>

      <fieldset id="step-3" class="step hidden">
        <section class="animate-fade-in-down">
          <div class="mb-6">
            <div class="flex items-center gap-2">
              <h2 class="text-xl font-bold text-bleuone font-raleway">Organisation des modules</h2>
              <div class="relative group">
                <button type="button" aria-label="Information sur le parcours pédagogique" class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-gray-300 bg-white text-[11px] font-bold text-gray-600">
                  ?
                </button>
                <div class="pointer-events-none absolute left-1/2 top-full z-20 mt-2 hidden w-80 -translate-x-1/2 rounded-lg border border-gray-200 bg-white p-3 text-xs text-gray-700 shadow-lg group-hover:block group-focus-within:block">
                  Ajoutez les modules utiles pour ce groupe, organisez-les dans l’ordre souhaité, puis validez la création. Vous retrouverez ensuite la même interface dans l’édition pour ajuster le parcours.
                </div>
              </div>
            </div>
            <p class="mt-2 text-sm text-gray-600 font-lisible">
              Le rendu reprend déjà la même logique d’organisation que sur la fiche d’édition.
            </p>
          </div>

          <div
            data-group-module-flow
            data-mode="create"
            data-available-modules='@json($availableModules)'
            data-selected-modules='@json($initialSelectedModules)'
            class="space-y-6"
          ></div>

          @if($errors->has('modules') || $errors->has('modules.*'))
            <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
              {{ $errors->first('modules') ?: $errors->first('modules.*') }}
            </div>
          @endif

          @if($errors->has('module_positions') || $errors->has('module_positions.*'))
            <div class="mt-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
              {{ $errors->first('module_positions') ?: $errors->first('module_positions.*') }}
            </div>
          @endif

          <p class="text-xs text-gray-500 mt-2 font-lisible bg-blue-50 p-2 rounded inline-block border border-blue-100">
            L’ordre défini ici sera celui présenté à l’apprenant dans son espace.
          </p>
        </section>
      </fieldset>

      <hr class="border-gray-100 my-8">

      <div class="flex flex-col md:flex-row items-center justify-between gap-4">
        <a href="{{ route('formateur.groupes.index') }}" class="text-gray-500 font-bold hover:text-bleuone transition">
          Annuler
        </a>

        <div class="flex w-full md:w-auto flex-col sm:flex-row items-center justify-end gap-3">
          <button type="button" id="prevBtn" class="hidden w-full sm:w-auto rounded-lg border border-gray-300 px-5 py-3 font-bold text-gray-600 transition hover:bg-gray-50">
            Précédent
          </button>
          <button type="button" id="nextBtn" class="btn-oneduc w-full sm:w-auto px-8 py-3 text-lg shadow-lg shadow-orangeone/20">
            Suivant
          </button>
          <button type="submit" id="submitBtn" class="btn-oneduc hidden w-full sm:w-auto px-8 py-3 text-lg shadow-lg shadow-orangeone/20">
            Créer le groupe
          </button>
        </div>
      </div>

    </form>
  </main>
</div>

<style>
  .animate-fade-in-down {
    animation: fadeInDown 0.3s ease-out;
  }

  @keyframes fadeInDown {
    from {
      opacity: 0;
      transform: translateY(-10px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
</style>

<script>
  let currentStep = {{ $initialWizardStep }};
  const TOTAL_STEPS = {{ count($steps) }};

  const form = document.getElementById('multi-step-form');
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  const submitBtn = document.getElementById('submitBtn');
  const progressBar = document.getElementById('progress-bar');
  const progressLabel = document.getElementById('progress-label');
  const progressLive = document.getElementById('progress-live');
  const stepButtons = document.querySelectorAll('.wizard-step');
  const wizardClientErrorsBox = document.getElementById('wizard-client-errors');
  const wizardClientErrorsList = wizardClientErrorsBox?.querySelector('[data-role="messages"]');
  const completedSteps = new Set();

  for (let step = 1; step < currentStep; step++) {
    completedSteps.add(step);
  }

  stepButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
      const target = parseInt(btn.dataset.step, 10);

      if (target === currentStep) {
        return;
      }

      if (target < currentStep && (target === 1 || completedSteps.has(target))) {
        currentStep = target;
        showStep(currentStep);
      }
    });
  });

  function hideWizardClientErrors() {
    if (!wizardClientErrorsBox || !wizardClientErrorsList) {
      return;
    }

    wizardClientErrorsList.innerHTML = '';
    wizardClientErrorsBox.classList.add('hidden');
  }

  function showWizardClientErrors(messages) {
    if (!wizardClientErrorsBox || !wizardClientErrorsList) {
      return;
    }

    const uniqueMessages = Array.from(new Set(
      (Array.isArray(messages) ? messages : [messages])
        .map((message) => (message || '').trim())
        .filter(Boolean)
    ));

    wizardClientErrorsList.innerHTML = '';

    uniqueMessages.forEach((message) => {
      const item = document.createElement('li');
      item.textContent = message;
      wizardClientErrorsList.appendChild(item);
    });

    wizardClientErrorsBox.classList.remove('hidden');
    wizardClientErrorsBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  function applyStepperStyles(step) {
    stepButtons.forEach((button, index) => {
      const stepNumber = index + 1;
      const isActive = stepNumber === step;
      const isCompleted = stepNumber < step && completedSteps.has(stepNumber);

      button.classList.remove(
        'bg-bleuone',
        'text-white',
        'shadow-md',
        'ring-2',
        'ring-bleuone',
        'ring-offset-2',
        'bg-white',
        'text-bleuone',
        'border',
        'border-bleuone',
        'hover:bg-bleuone/5',
        'opacity-50',
        'cursor-not-allowed'
      );

      if (isActive) {
        button.classList.add('bg-bleuone', 'text-white', 'shadow-md', 'ring-2', 'ring-bleuone', 'ring-offset-2');
        button.disabled = false;
        button.setAttribute('aria-current', 'step');
        return;
      }

      button.classList.add('bg-white', 'text-bleuone', 'border', 'border-bleuone');
      button.setAttribute('aria-current', 'false');

      if (isCompleted) {
        button.classList.add('hover:bg-bleuone/5');
        button.disabled = false;
        return;
      }

      button.classList.add('opacity-50', 'cursor-not-allowed');
      button.disabled = true;
    });
  }

  function showStep(step) {
    document.querySelectorAll('.step').forEach((section) => {
      section.classList.add('hidden');
      section.classList.remove('animate-fade-in-down');
    });

    const activeStep = document.getElementById(`step-${step}`);
    activeStep.classList.remove('hidden');
    activeStep.offsetWidth;
    activeStep.classList.add('animate-fade-in-down');

    applyStepperStyles(step);

    const progressText = `Étape ${step} sur ${TOTAL_STEPS}`;
    progressBar.style.width = `${(step / TOTAL_STEPS) * 100}%`;
    progressLabel.textContent = progressText;
    progressLive.textContent = progressText;

    prevBtn.classList.toggle('hidden', step === 1);
    nextBtn.classList.toggle('hidden', step === TOTAL_STEPS);
    submitBtn.classList.toggle('hidden', step !== TOTAL_STEPS);

    hideWizardClientErrors();

    if (step === 3) {
      window.requestAnimationFrame(() => {
        window.dispatchEvent(new CustomEvent('oneduc:group-flow-refresh'));
      });
    }
  }

  function validateStep(step) {
    let ok = true;
    const current = document.getElementById(`step-${step}`);
    const required = current.querySelectorAll('input[required], textarea[required], select[required]');
    const messages = [];

    const addMessage = (message) => {
      if (message && !messages.includes(message)) {
        messages.push(message);
      }
    };

    required.forEach((el) => {
      const value = (el.value || '').trim();
      const tooShort = Number(el.minLength) > 0 && value.length > 0 && value.length < Number(el.minLength);
      const isStudentField = /^stagiaires\[\d+\]\[(prenom|nom|email)\]$/.test(el.name);

      if (!value || tooShort) {
        el.classList.add('border-red-500');
        ok = false;

        if (el.name === 'nom') {
          addMessage('Veuillez renseigner le nom du groupe.');
        } else if (el.name === 'password') {
          addMessage(!value
            ? 'Veuillez renseigner le code d’accès provisoire.'
            : 'Le code d’accès provisoire doit contenir au moins 8 caractères.');
        } else if (isStudentField) {
          addMessage('Veuillez compléter prénom, nom et e-mail pour chaque stagiaire ajouté.');
        } else {
          addMessage('Veuillez compléter les champs requis avant de continuer.');
        }
      } else {
        el.classList.remove('border-red-500');
      }
    });

    if (!ok) {
      completedSteps.delete(step);
      showWizardClientErrors(messages);
      return false;
    }

    if (step === 3) {
      const selectedModules = current.querySelectorAll('input[name="modules[]"]');
      if (selectedModules.length === 0) {
        completedSteps.delete(step);
        showWizardClientErrors(['Ajoutez au moins un module au parcours.']);
        return false;
      }
    }

    completedSteps.add(step);
    hideWizardClientErrors();
    return true;
  }

  nextBtn.addEventListener('click', (event) => {
    event.preventDefault();

    if (validateStep(currentStep)) {
      currentStep++;
      showStep(currentStep);
    }
  });

  prevBtn.addEventListener('click', (event) => {
    event.preventDefault();

    const target = currentStep - 1;
    if (target >= 1 && (target === 1 || completedSteps.has(target))) {
      currentStep = target;
      showStep(currentStep);
    }
  });

  function fillStagiaireRow(row, data) {
    const prenomInput = row.querySelector('input[name$="[prenom]"]');
    const nomInput = row.querySelector('input[name$="[nom]"]');
    const emailInput = row.querySelector('input[name$="[email]"]');

    if (prenomInput) prenomInput.value = data.prenom || '';
    if (nomInput) nomInput.value = data.nom || '';
    if (emailInput) emailInput.value = data.email || '';
  }

  function findEmptyStagiaireRow() {
    const rows = Array.from(document.querySelectorAll('#stagiaires-container .stagiaire-row'));

    return rows.find((row) => {
      const values = Array.from(row.querySelectorAll('input[name$="[prenom]"], input[name$="[nom]"], input[name$="[email]"]'))
        .map((input) => (input.value || '').trim());
      return values.every((value) => value === '');
    }) || null;
  }

  window.addStagiaire = function (data = null) {
    const container = document.getElementById('stagiaires-container');
    const rows = container.querySelectorAll('.stagiaire-row');
    const index = rows.length;

    const tpl = `
      <div class="bg-white border border-gray-200 p-4 rounded-[12px] shadow-sm relative stagiaire-row group hover:border-orangeone/50 transition mt-3 animate-fade-in-down">
        <div class="grid grid-cols-1 md:grid-cols-[1fr_1fr_2fr_auto] gap-4 items-start">
          <div>
            <label for="stagiaires_${index}_prenom" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Prénom</label>
            <input id="stagiaires_${index}_prenom" name="stagiaires[${index}][prenom]" type="text" placeholder="Prénom" required class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-orangeone focus:border-orangeone w-full p-2.5">
          </div>
          <div>
            <label for="stagiaires_${index}_nom" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Nom</label>
            <input id="stagiaires_${index}_nom" name="stagiaires[${index}][nom]" type="text" placeholder="Nom" required class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-orangeone focus:border-orangeone w-full p-2.5">
          </div>
          <div>
            <label for="stagiaires_${index}_email" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Email professionnel</label>
            <input id="stagiaires_${index}_email" name="stagiaires[${index}][email]" type="email" placeholder="Email" required class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-orangeone focus:border-orangeone w-full p-2.5">
          </div>
          <div class="flex items-end h-full pb-[3px]">
            <button type="button" class="text-gray-300 hover:text-red-600 transition p-2 rounded-full hover:bg-red-50" onclick="removeStagiaire(this)" title="Supprimer la ligne">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>`;

    container.insertAdjacentHTML('beforeend', tpl);

    if (data && (data.prenom || data.nom || data.email)) {
      const inserted = container.lastElementChild;
      if (inserted) fillStagiaireRow(inserted, data);
    }
  };

  window.removeStagiaire = function (btn) {
    const row = btn.closest('.stagiaire-row');
    const container = document.getElementById('stagiaires-container');

    if (container.querySelectorAll('.stagiaire-row').length <= 1) {
      showWizardClientErrors(['Le groupe doit contenir au moins un stagiaire.']);
      return;
    }

    row.remove();
  };

  function showCsvFeedbackCreate(message, type = 'info') {
    const box = document.getElementById('csv-feedback-create');
    if (!box) return;

    box.classList.remove('hidden', 'bg-red-50', 'text-red-700', 'border', 'border-red-100', 'bg-green-50', 'text-green-700', 'border-green-100', 'bg-gray-50', 'text-gray-700', 'border-gray-100');

    if (type === 'error') {
      box.classList.add('bg-red-50', 'text-red-700', 'border', 'border-red-100');
    } else if (type === 'success') {
      box.classList.add('bg-green-50', 'text-green-700', 'border', 'border-green-100');
    } else {
      box.classList.add('bg-gray-50', 'text-gray-700', 'border', 'border-gray-100');
    }

    box.textContent = message;
  }

  window.openCsvModalCreate = function () {
    const modal = document.getElementById('csv-modal-create');
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
  };

  window.closeCsvModalCreate = function () {
    const modal = document.getElementById('csv-modal-create');
    const input = document.getElementById('csv-file-create');
    const box = document.getElementById('csv-feedback-create');

    if (modal) {
      modal.classList.remove('flex');
      modal.classList.add('hidden');
    }

    if (input) input.value = '';

    if (box) {
      box.classList.add('hidden');
      box.textContent = '';
    }
  };

  function normalizeCsvHeaderCreate(header) {
    return (header || '')
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .replace(/\s+/g, '')
      .trim();
  }

  function detectDelimiterCreate(text) {
    const sample = (text || '').split(/\r?\n/).slice(0, 3).join('\n');
    const semicolonCount = (sample.match(/;/g) || []).length;
    const commaCount = (sample.match(/,/g) || []).length;
    return semicolonCount >= commaCount ? ';' : ',';
  }

  function parseCsvLineCreate(line, delimiter) {
    const out = [];
    let current = '';
    let inQuotes = false;

    for (let i = 0; i < line.length; i++) {
      const ch = line[i];

      if (ch === '"') {
        if (inQuotes && line[i + 1] === '"') {
          current += '"';
          i++;
        } else {
          inQuotes = !inQuotes;
        }
        continue;
      }

      if (ch === delimiter && !inQuotes) {
        out.push(current.trim());
        current = '';
        continue;
      }

      current += ch;
    }

    out.push(current.trim());
    return out;
  }

  function extractStudentsFromCsvCreate(text) {
    const delimiter = detectDelimiterCreate(text);
    const lines = (text || '').split(/\r?\n/).filter((line) => line.trim() !== '');
    if (lines.length === 0) {
      return { students: [], skipped: 0 };
    }

    const rows = lines.map((line) => parseCsvLineCreate(line, delimiter));
    const first = rows[0].map(normalizeCsvHeaderCreate);

    const emailAliases = ['email', 'e-mail', 'mail', 'courriel'];
    const prenomAliases = ['prenom', 'firstname', 'first_name', 'givenname', 'given_name'];
    const nomAliases = ['nom', 'name', 'lastname', 'last_name', 'surname', 'familyname'];

    const emailIdx = first.findIndex((header) => emailAliases.includes(header));
    const prenomIdx = first.findIndex((header) => prenomAliases.includes(header));
    const nomIdx = first.findIndex((header) => nomAliases.includes(header));
    const hasHeader = emailIdx !== -1 || prenomIdx !== -1 || nomIdx !== -1;

    const startAt = hasHeader ? 1 : 0;
    const mapIdx = {
      prenom: prenomIdx !== -1 ? prenomIdx : 0,
      nom: nomIdx !== -1 ? nomIdx : 1,
      email: emailIdx !== -1 ? emailIdx : 2,
    };

    const students = [];
    let skipped = 0;

    for (let i = startAt; i < rows.length; i++) {
      const cols = rows[i];
      const prenom = (cols[mapIdx.prenom] || '').trim();
      const nom = (cols[mapIdx.nom] || '').trim();
      const email = (cols[mapIdx.email] || '').trim().toLowerCase();

      if (!prenom && !nom && !email) continue;
      if (!prenom || !nom || !email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        skipped++;
        continue;
      }

      students.push({ prenom, nom, email });
    }

    return { students, skipped };
  }

  function addStudentsFromCsvCreate(students) {
    const existingEmails = new Set(
      Array.from(document.querySelectorAll('#stagiaires-container input[name$="[email]"]'))
        .map((input) => (input.value || '').trim().toLowerCase())
        .filter(Boolean)
    );

    let added = 0;
    let duplicates = 0;

    students.forEach((student) => {
      if (existingEmails.has(student.email)) {
        duplicates++;
        return;
      }

      const emptyRow = findEmptyStagiaireRow();
      if (emptyRow) {
        fillStagiaireRow(emptyRow, student);
      } else {
        addStagiaire(student);
      }

      existingEmails.add(student.email);
      added++;
    });

    return { added, duplicates };
  }

  const csvImportBtnCreate = document.getElementById('csv-import-confirm-create');
  if (csvImportBtnCreate) {
    csvImportBtnCreate.addEventListener('click', () => {
      const input = document.getElementById('csv-file-create');
      const file = input?.files?.[0];

      if (!file) {
        showCsvFeedbackCreate('Sélectionnez un fichier CSV.', 'error');
        return;
      }

      const reader = new FileReader();
      reader.onload = () => {
        const text = String(reader.result || '');
        const { students, skipped } = extractStudentsFromCsvCreate(text);

        if (!students.length) {
          showCsvFeedbackCreate('Aucun stagiaire valide trouvé dans le fichier.', 'error');
          return;
        }

        const { added, duplicates } = addStudentsFromCsvCreate(students);
        showCsvFeedbackCreate(`Import terminé : ${added} ajouté(s), ${duplicates} doublon(s), ${skipped} ligne(s) ignorée(s).`, 'success');
      };
      reader.onerror = () => showCsvFeedbackCreate('Lecture du fichier impossible.', 'error');
      reader.readAsText(file);
    });
  }

  const csvModalCreate = document.getElementById('csv-modal-create');
  if (csvModalCreate) {
    csvModalCreate.addEventListener('click', (event) => {
      if (event.target === csvModalCreate) {
        closeCsvModalCreate();
      }
    });
  }

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      const modal = document.getElementById('csv-modal-create');
      if (modal && modal.classList.contains('flex')) {
        closeCsvModalCreate();
      }
    }
  });

  form.addEventListener('submit', () => {
    document.querySelectorAll('.stagiaire-row').forEach((row) => {
      const values = Array.from(row.querySelectorAll('input')).map((input) => (input.value || '').trim());
      if (values.every((value) => value === '')) row.remove();
    });
  });

  showStep(currentStep);
</script>

@include('formateur.groupes.partials.co-formateurs-script')

@endsection
