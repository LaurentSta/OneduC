@extends('formateur.dashboard')

@section('formateur')

{{--  CONTENEUR GLOBAL /home/laurents/Oneduc_Dev/resources/views/formateur/groupes/create.blade.php --}}

{{--  EN-TÊTE DE PAGE FORMATEUR – Création d’un groupe --}}
<div class="bg-white rounded-[20px] shadow-md px-8 pt-4 w-full max-w-[1285px] mx-auto mb-6">
  <div class="grid grid-cols-12 gap-8 items-center">

    {{-- Colonne gauche : titre + texte --}}
    <div class="col-span-12 md:col-span-8">
      <x-typography variant="titre">Création d’un groupe</x-typography>
      <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
        Gérez facilement vos groupes, modules et stagiaires.
      </x-typography>
      <x-typography>
        Créez un nouveau groupe en 3 étapes : nom, stagiaires et modules à associer.
      </x-typography>

      {{-- Fil d’Ariane --}}
      <nav class="text-base font-varela text-gray-600 mt-4" aria-label="Fil d'Ariane">
        <ol class="list-none p-0 inline-flex items-center space-x-1">
          <li class="flex items-center">
            <a href="{{ route('formateur.dashboard') }}" class="text-orangeone hover:underline flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 9.75L12 3l9 6.75V19a2 2 0 01-2 2h-4a1 1 0 01-1-1v-5H10v5a1 1 0 01-1 1H5a2 2 0 01-2-2V9.75z"/>
              </svg>
            </a>
            <span class="mx-2 text-gray-400">/</span>
          </li>
          <li class="flex items-center">
            <a href="{{ route('formateur.groupes.index') }}" class="hover:underline text-bleuone">Mes groupes</a>
            <span class="mx-2 text-gray-400">/</span>
          </li>
          <li class="text-gray-400">Création d’un groupe</li>
        </ol>
      </nav>
    </div>

    {{-- Colonne droite : illustration --}}
    <div class="col-span-12 md:col-span-4 flex justify-center md:justify-end">
      <img src="{{ asset('images/svg/Modules_Creation.svg') }}"
           alt="Illustration de la création de groupe"
           class="w-full max-w-sm object-contain">
    </div>

  </div>
</div>

{{-- ✅ FORMULAIRE WIZARD --}}
<div class="bg-white rounded-[20px] shadow-md px-8 py-10 w-full max-w-[1285px] mx-auto
            font-varela text-base text-gray-800">


  {{-- Stepper --}}
  @php $steps = ['Groupe','Stagiaires','Modules']; @endphp
  <nav class="mb-8" aria-label="Progression">
    <ol class="grid grid-cols-1 sm:grid-cols-3 gap-3">
      @foreach($steps as $i => $label)
        <li>
          <button type="button"
                  class="wizard-step w-full px-6 py-4 rounded-full border transition font-varela text-lg
                         border-bleuone focus:outline-none focus:ring-2 focus:ring-bleuone focus:ring-offset-2"
                  data-step="{{ $i+1 }}"
                  aria-current="{{ $i===0 ? 'step' : 'false' }}">
            Étape {{ $i+1 }} : {{ $label }}
          </button>
        </li>
      @endforeach
    </ol>

    {{-- Barre de progression --}}
    <div class="overflow-hidden h-2 mt-4 rounded bg-orangeone/15" aria-hidden="true">
      <div id="progress-bar" class="h-2 bg-orangeone w-1/3 transition-all duration-500"></div>
    </div>
    <div id="progress-live" class="sr-only" aria-live="polite">Étape 1 sur 3</div>
  </nav>

  <form id="multi-step-form" method="POST" action="{{ route('formateur.groupes.store') }}" novalidate>
    @csrf

    {{-- Étape 1 : Groupe --}}
    <fieldset id="step-1" class="step">
      <legend class="sr-only">Informations du groupe</legend>
      <p class="text-base text-gray-600 mb-6">Nommer le groupe et ajouter une description.</p>

      <div class="mb-6">
        <label for="nom" class="block mb-2 text-base font-medium text-gray-900">Nom du groupe *</label>
        <input id="nom" name="nom" type="text" required
               class="bg-gray-50 border border-gray-300 text-base rounded-lg focus:ring-orangeone focus:border-orangeone block w-full p-2.5"
               placeholder="Ex : Groupe Marketing 2025 - Niveau 1">
        <p class="text-xs text-gray-500 mt-1">Un nom clair facilite la recherche et le suivi.</p>
      </div>

      <div class="mb-6">
        <label for="description" class="block mb-2 text-base font-medium text-gray-900">Description</label>
        <textarea id="description" name="description" rows="3"
                  class="bg-gray-50 border border-gray-300 text-base rounded-lg focus:ring-orangeone focus:border-orangeone block w-full p-2.5"
                  placeholder="Objectifs, public, période…"></textarea>
      </div>
    </fieldset>


    {{-- Étape 2 : Stagiaires & Accès --}}
    <fieldset id="step-2" class="step hidden">
      <legend class="sr-only">Stagiaires</legend>
      
      {{-- A. LISTE DES STAGIAIRES --}}
      <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
            <div>
                <h3 class="text-xl font-bold text-bleuone font-raleway">Ajouter vos stagiaires</h3>
                <p class="text-sm text-gray-600 font-lisible mt-1">
                    Renseignez les informations de vos apprenants.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button"
                    class="w-10 h-10 inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 hover:text-bleuone hover:border-bleuone/30 transition"
                    onclick="openCsvModalCreate()"
                    aria-label="Importer des stagiaires par CSV"
                    title="Importer un lot CSV">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                    </svg>
                </button>

                <button type="button"
                    class="px-4 py-2 bg-bleuone/10 text-bleuone border border-bleuone/20 font-bold rounded-lg hover:bg-bleuone hover:text-white transition flex items-center justify-center gap-2 text-sm"
                    onclick="addStagiaire()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Ajouter un stagiaire
                </button>
            </div>
        </div>

        <div id="stagiaires-container" class="space-y-3">
            {{-- Ligne 0 (Initiale) --}}
            <div class="bg-white border border-gray-200 p-4 rounded-[12px] shadow-sm relative stagiaire-row group hover:border-orangeone/50 transition">
                <div class="grid grid-cols-1 md:grid-cols-[1fr_1fr_2fr_auto] gap-4 items-start">
                    
                    {{-- Prénom --}}
                    <div>
                        <label for="stagiaires_0_prenom" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Prénom</label>
                        <input id="stagiaires_0_prenom" name="stagiaires[0][prenom]" type="text" placeholder="Ex: Thomas" required
                            class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-orangeone focus:border-orangeone w-full p-2.5">
                    </div>

                    {{-- Nom --}}
                    <div>
                        <label for="stagiaires_0_nom" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Nom</label>
                        <input id="stagiaires_0_nom" name="stagiaires[0][nom]" type="text" placeholder="Ex: Dupont" required
                            class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-orangeone focus:border-orangeone w-full p-2.5">
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="stagiaires_0_email" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Email professionnel</label>
                        <input id="stagiaires_0_email" name="stagiaires[0][email]" type="email" placeholder="thomas.dupont@entrepise.com" required
                            class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-orangeone focus:border-orangeone w-full p-2.5">
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-end h-full pb-[3px]">
                         <button type="button" class="text-gray-300 hover:text-red-600 transition p-2 rounded-full hover:bg-red-50"
                                onclick="removeStagiaire(this)" title="Supprimer la ligne">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
      </div>

      <hr class="border-gray-100 my-8">

      {{-- B. MOT DE PASSE PROVISOIRE --}}
      <div class="bg-orangeone/5 border border-orangeone/20 rounded-[16px] p-6 flex flex-col md:flex-row gap-6 items-start">
        {{-- Icône clé --}}
        <div class="hidden md:flex flex-shrink-0 pt-1">
            <div class="w-12 h-12 rounded-full bg-white text-orangeone flex items-center justify-center shadow-sm border border-orangeone/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
            </div>
        </div>
        
        <div class="flex-grow w-full">
            <h4 class="text-lg font-bold text-gray-800 font-raleway mb-2">Code d'accès provisoire du groupe</h4>
            <div class="text-sm text-gray-600 mb-4 font-lisible space-y-1">
                <p>Ce code servira <strong>uniquement à la première connexion</strong>.</p>
                <p class="text-xs text-gray-500">Note : Vos stagiaires devront obligatoirement choisir leur propre mot de passe ensuite (RGPD).</p>
            </div>

            <div class="w-full max-w-md">
                <label for="password" class="sr-only">Mot de passe commun</label>
                <div class="relative flex items-center">
                    <input id="password" name="password" type="text" required minlength="8" autocomplete="off"
                        class="bg-white border border-gray-300 text-gray-900 text-base rounded-l-lg focus:ring-orangeone focus:border-orangeone block w-full pl-4 py-3 font-mono tracking-wide"
                        placeholder="Ex: Formation2026!">
                    
                    <button type="button" onclick="generatePassword()" 
                            class="bg-gray-100 border border-l-0 border-gray-300 text-gray-600 hover:bg-gray-200 hover:text-gray-800 font-bold py-3 px-4 rounded-r-lg transition text-sm whitespace-nowrap">
                        🎲 Générer
                    </button>
                </div>
                <p class="text-[10px] text-gray-400 mt-2 italic">Minimum 8 caractères.</p>
            </div>
        </div>
      </div>
    </fieldset>

    {{-- Modale import CSV (discrète, dans le wizard) --}}
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
            Exemple d’en-tête: <code>prenom;nom;email</code> (ou séparateur virgule).
          </p>

          <input id="csv-file-create" type="file" accept=".csv,text/csv"
                 class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">

          <div id="csv-feedback-create" class="hidden rounded-lg px-3 py-2 text-sm"></div>
        </div>

        <div class="flex items-center justify-end gap-2 border-t border-gray-100 px-5 py-4">
          <button type="button"
                  class="px-3 py-2 text-sm rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50"
                  onclick="closeCsvModalCreate()">
            Fermer
          </button>
          <button type="button"
                  id="csv-import-confirm-create"
                  class="px-3 py-2 text-sm rounded-lg bg-bleuone text-white hover:opacity-90">
            Importer
          </button>
        </div>
      </div>
    </div>

    {{-- Étape 3 : Modules --}}
    <fieldset id="step-3" class="step hidden">
      <legend class="sr-only">Modules</legend>
      <p class="text-base text-gray-600 mb-4">Sélectionner les modules à associer.</p>

      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
        @foreach ($modules as $module)
          @if (!empty($module->status) && (int)$module->status === 1)
            <label class="flex items-center space-x-2 bg-gray-50 border rounded px-4 py-2">
              <input type="checkbox" name="modules[]" value="{{ $module->id }}">
              <span class="text-base">{{ $module->module_title }}</span>
            </label>
          @endif
        @endforeach
      </div>
    </fieldset>

    {{-- Navigation --}}
    <div class="flex justify-between mt-8">
      <button type="button" id="prevBtn" class="btn-oneduc hidden">◀ Précédent</button>
      <button type="button" id="nextBtn" class="btn-oneduc ml-auto">Suivant ▶</button>
      <button type="submit" id="submitBtn" class="btn-oneduc hidden ml-auto">Créer le groupe</button>
    </div>

    {{-- Zone erreurs client --}}
    <div id="client-errors" class="mt-4 text-base text-red-700" aria-live="polite"></div>
  </form>
</div>

<script>
  let currentStep = 1;
  const TOTAL_STEPS = 3;

  const form = document.getElementById('multi-step-form');
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  const submitBtn = document.getElementById('submitBtn');
  const progressBar = document.getElementById('progress-bar');
  const progressLive = document.getElementById('progress-live');
  const stepButtons = document.querySelectorAll('.wizard-step');
  const errorsBox = document.getElementById('client-errors');

  // Étapes validées (autorise retour en arrière uniquement si validée)
  const completedSteps = new Set();

  // --- LOGIQUE STEPPER (Inchangée) ---

  stepButtons.forEach(btn => btn.addEventListener('click', () => {
    const target = parseInt(btn.dataset.step, 10);

    if (target < currentStep) {
      if (completedSteps.has(target)) {
        currentStep = target;
        showStep(currentStep);
      }
      return;
    }
    return;
  }));

  function applyStepperStyles(step) {
    stepButtons.forEach((b, i) => {
      const s = i + 1;
      const active = s === step;

      b.classList.remove('bg-bleuone','text-white','bg-white','text-bleuone','opacity-60','shadow-md');
      if (active) {
        b.classList.add('bg-bleuone','text-white','shadow-md');
        b.setAttribute('aria-current', 'step');
      } else {
        b.classList.add('bg-white','text-bleuone','opacity-60');
        b.setAttribute('aria-current', 'false');
      }

      if (s < step && completedSteps.has(s)) {
        b.disabled = false;
        b.classList.remove('cursor-not-allowed');
      } else if (s === step) {
        b.disabled = false;
        b.classList.remove('cursor-not-allowed');
      } else {
        b.disabled = true;
        b.classList.add('cursor-not-allowed');
      }
    });
  }

  function showStep(step) {
    document.querySelectorAll('.step').forEach(s => s.classList.add('hidden'));
    document.getElementById(`step-${step}`).classList.remove('hidden');

    applyStepperStyles(step);

    progressBar.style.width = `${(step / TOTAL_STEPS) * 100}%`;
    progressLive.textContent = `Étape ${step} sur ${TOTAL_STEPS}`;

    prevBtn.classList.toggle('hidden', step === 1);
    nextBtn.classList.toggle('hidden', step === TOTAL_STEPS);
    submitBtn.classList.toggle('hidden', step !== TOTAL_STEPS);

    errorsBox.textContent = '';
  }

  function validateStep(step) {
    let ok = true;
    const current = document.getElementById(`step-${step}`);
    const required = current.querySelectorAll('input[required], textarea[required], select[required]');

    required.forEach(el => {
      const value = (el.value || '').trim();
      const tooShort = el.minLength && value.length < el.minLength;

      if (!value || tooShort) {
        el.classList.add('border-red-500');
        ok = false;
      } else {
        el.classList.remove('border-red-500');
      }
    });

    if (!ok) {
      completedSteps.delete(step);
      errorsBox.textContent = 'Veuillez compléter les champs requis avant de continuer.';
      return false;
    }

    completedSteps.add(step);
    return true;
  }

  nextBtn.addEventListener('click', (e) => {
    e.preventDefault();
    if (validateStep(currentStep)) {
      currentStep++;
      showStep(currentStep);
    }
  });

  prevBtn.addEventListener('click', (e) => {
    e.preventDefault();
    const target = currentStep - 1;
    if (target >= 1 && completedSteps.has(target)) {
      currentStep--;
      showStep(currentStep);
    }
  });


  // --- GESTION DES STAGIAIRES & PASSWORD (MODIFIÉ) ---

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
        .map((i) => (i.value || '').trim());
      return values.every(v => v === '');
    }) || null;
  }

  // Ajout dynamique avec le nouveau design HTML
  window.addStagiaire = function (data = null) {
    const container = document.getElementById('stagiaires-container');
    const rows = container.querySelectorAll('.stagiaire-row');
    const index = rows.length; // Calcule le bon index pour le tableau PHP

    const tpl = `
      <div class="bg-white border border-gray-200 p-4 rounded-[12px] shadow-sm relative stagiaire-row group hover:border-orangeone/50 transition mt-3 animate-fade-in-down">
        <div class="grid grid-cols-1 md:grid-cols-[1fr_1fr_2fr_auto] gap-4 items-start">
            
            <div>
                <label for="stagiaires_${index}_prenom" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Prénom</label>
                <input id="stagiaires_${index}_prenom" name="stagiaires[${index}][prenom]" type="text" placeholder="Prénom" required
                    class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-orangeone focus:border-orangeone w-full p-2.5">
            </div>

            <div>
                <label for="stagiaires_${index}_nom" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Nom</label>
                <input id="stagiaires_${index}_nom" name="stagiaires[${index}][nom]" type="text" placeholder="Nom" required
                    class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-orangeone focus:border-orangeone w-full p-2.5">
            </div>

            <div>
                <label for="stagiaires_${index}_email" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Email professionnel</label>
                <input id="stagiaires_${index}_email" name="stagiaires[${index}][email]" type="email" placeholder="Email" required
                    class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-orangeone focus:border-orangeone w-full p-2.5">
            </div>

            <div class="flex items-end h-full pb-[3px]">
                <button type="button" class="text-gray-300 hover:text-red-600 transition p-2 rounded-full hover:bg-red-50"
                        onclick="removeStagiaire(this)" title="Supprimer la ligne">
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
  }

  // Suppression d'une ligne (avec sécurité min 1 ligne)
  window.removeStagiaire = function (btn) {
    const row = btn.closest('.stagiaire-row');
    const container = document.getElementById('stagiaires-container');
    
    if (container.querySelectorAll('.stagiaire-row').length <= 1) {
        alert("Le groupe doit contenir au moins un stagiaire.");
        return;
    }
    row.remove();
  }

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
    const lines = (text || '').split(/\r?\n/).filter(l => l.trim() !== '');
    if (lines.length === 0) {
      return { students: [], skipped: 0 };
    }

    const rows = lines.map(line => parseCsvLineCreate(line, delimiter));
    const first = rows[0].map(normalizeCsvHeaderCreate);

    const emailAliases = ['email', 'e-mail', 'mail', 'courriel'];
    const prenomAliases = ['prenom', 'firstname', 'first_name', 'givenname', 'given_name'];
    const nomAliases = ['nom', 'name', 'lastname', 'last_name', 'surname', 'familyname'];

    const emailIdx = first.findIndex(h => emailAliases.includes(h));
    const prenomIdx = first.findIndex(h => prenomAliases.includes(h));
    const nomIdx = first.findIndex(h => nomAliases.includes(h));
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
        .map((i) => (i.value || '').trim().toLowerCase())
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
        showCsvFeedbackCreate(`Import terminé: ${added} ajouté(s), ${duplicates} doublon(s), ${skipped} ligne(s) ignorée(s).`, 'success');
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

  // Générateur de mot de passe (Nouveau)
  window.generatePassword = function() {
      const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*";
      const length = 8;
      let password = "";
      for (let i = 0, n = chars.length; i < length; ++i) {
          password += chars.charAt(Math.floor(Math.random() * n));
      }
      const pwdInput = document.getElementById('password');
      if (pwdInput) {
        pwdInput.value = password;
        pwdInput.classList.remove('border-red-500'); // Retire l'erreur rouge si elle était là
      }
  }

  // Nettoyage avant soumission (supprime les lignes vides)
  form.addEventListener('submit', () => {
    document.querySelectorAll('.stagiaire-row').forEach(row => {
      const vals = Array.from(row.querySelectorAll('input')).map(i => (i.value || '').trim());
      // Si tous les champs de la ligne sont vides, on la vire pour pas polluer le POST
      if (vals.every(v => v === '')) row.remove();
    });
  });

  // Init
  showStep(currentStep);
</script>

@endsection
