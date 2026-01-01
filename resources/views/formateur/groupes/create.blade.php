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

    {{-- Étape 2 : Stagiaires --}}
    <fieldset id="step-2" class="step hidden">
      <legend class="sr-only">Stagiaires</legend>
      <p class="text-base text-gray-600 text-center mb-6">Définir un mot de passe commun puis ajouter les stagiaires.</p>

      <div class="mb-6">
        <label for="password" class="block mb-2 text-base font-medium text-gray-900">Mot de passe commun *</label>
        <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password"
               class="bg-gray-50 border border-gray-300 text-base rounded-lg focus:ring-orangeone focus:border-orangeone block w-full p-2.5"
               placeholder="Min. 8 caractères">
        <p class="text-xs text-gray-500 mt-1">Servira à la première connexion du groupe.</p>
      </div>

      <div id="stagiaires-container" class="space-y-4">
        {{-- ligne 0 --}}
        <div class="bg-gray-50 p-4 rounded relative stagiaire-row">
          <div class="flex justify-between items-start mb-2">
            <span class="text-base font-medium text-gray-700">Stagiaire 1</span>
            <button type="button" class="text-xs font-semibold text-red-700 bg-red-100 hover:bg-red-200 px-2 py-1 rounded-full"
                    onclick="removeStagiaire(this)">Supprimer</button>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-[1fr_1fr_2fr] gap-3">
            <div>
              <label for="stagiaires_0_prenom" class="sr-only">Prénom</label>
              <input id="stagiaires_0_prenom" name="stagiaires[0][prenom]" type="text" placeholder="Prénom"
                     class="bg-white border border-gray-300 text-base rounded-lg focus:ring-orangeone focus:border-orangeone w-full p-2.5">
            </div>

            <div>
              <label for="stagiaires_0_nom" class="sr-only">Nom</label>
              <input id="stagiaires_0_nom" name="stagiaires[0][nom]" type="text" placeholder="Nom"
                     class="bg-white border border-gray-300 text-base rounded-lg focus:ring-orangeone focus:border-orangeone w-full p-2.5">
            </div>

            <div class="relative">
              <label for="stagiaires_0_email" class="sr-only">Email</label>
              <input id="stagiaires_0_email" name="stagiaires[0][email]" type="email" placeholder="Email"
                     class="bg-white border border-gray-300 text-base rounded-lg focus:ring-orangeone focus:border-orangeone w-full p-2.5 pr-9">
              <span class="absolute top-2 right-2 pointer-events-none" aria-hidden="true" title="Le code d’accès sera généré à la validation.">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                     class="w-5 h-5 text-orangeone" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="11" width="18" height="10" rx="2" ry="2"></rect>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                  <circle cx="12" cy="16" r="1"></circle>
                </svg>
              </span>
            </div>
          </div>
        </div>
      </div>

      <button type="button"
              class="px-4 py-2 bg-gray-200 text-gray-900 rounded-lg hover:bg-gray-300 transition mt-2"
              onclick="addStagiaire()">+ Ajouter un stagiaire</button>
    </fieldset>

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

{{-- JS --}}
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

  // Stepper : retour en arrière uniquement sur étapes validées
  stepButtons.forEach(btn => btn.addEventListener('click', () => {
    const target = parseInt(btn.dataset.step, 10);

    if (target < currentStep) {
      if (completedSteps.has(target)) {
        currentStep = target;
        showStep(currentStep);
      }
      return;
    }

    // Pas d'accès aux étapes futures via stepper
    return;
  }));

  function applyStepperStyles(step) {
    stepButtons.forEach((b, i) => {
      const s = i + 1;
      const active = s === step;

      // Styles : actif bleu plein, inactif blanc + texte bleu
      b.classList.remove('bg-bleuone','text-white','bg-white','text-bleuone','opacity-60','shadow-md');
      if (active) {
        b.classList.add('bg-bleuone','text-white','shadow-md');
        b.setAttribute('aria-current', 'step');
      } else {
        b.classList.add('bg-white','text-bleuone','opacity-60');
        b.setAttribute('aria-current', 'false');
      }

      // Désactivation : seules les étapes validées en arrière sont cliquables
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
    // Retour en arrière autorisé uniquement si l'étape cible est déjà validée
    const target = currentStep - 1;
    if (target >= 1 && completedSteps.has(target)) {
      currentStep--;
      showStep(currentStep);
    }
  });

  window.addStagiaire = function () {
    const container = document.getElementById('stagiaires-container');
    const index = container.querySelectorAll('.stagiaire-row').length;
    const tpl = `
      <div class="bg-gray-50 p-4 rounded relative stagiaire-row">
        <div class="flex justify-between items-start mb-2">
          <span class="text-base font-medium text-gray-700">Stagiaire ${index+1}</span>
          <button type="button" class="text-xs font-semibold text-red-700 bg-red-100 hover:bg-red-200 px-2 py-1 rounded-full"
                  onclick="removeStagiaire(this)">Supprimer</button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-[1fr_1fr_2fr] gap-3">
          <div>
            <label for="stagiaires_${index}_prenom" class="sr-only">Prénom</label>
            <input id="stagiaires_${index}_prenom" name="stagiaires[${index}][prenom]" type="text" placeholder="Prénom"
                   class="bg-white border border-gray-300 text-base rounded-lg focus:ring-orangeone focus:border-orangeone w-full p-2.5">
          </div>
          <div>
            <label for="stagiaires_${index}_nom" class="sr-only">Nom</label>
            <input id="stagiaires_${index}_nom" name="stagiaires[${index}][nom]" type="text" placeholder="Nom"
                   class="bg-white border border-gray-300 text-base rounded-lg focus:ring-orangeone focus:border-orangeone w-full p-2.5">
          </div>
          <div>
            <label for="stagiaires_${index}_email" class="sr-only">Email</label>
            <input id="stagiaires_${index}_email" name="stagiaires[${index}][email]" type="email" placeholder="Email"
                   class="bg-white border border-gray-300 text-base rounded-lg focus:ring-orangeone focus:border-orangeone w-full p-2.5">
          </div>
        </div>
      </div>`;
    container.insertAdjacentHTML('beforeend', tpl);
  }

  window.removeStagiaire = function (btn) {
    const row = btn.closest('.stagiaire-row');
    const container = document.getElementById('stagiaires-container');
    if (container.querySelectorAll('.stagiaire-row').length <= 1) return;
    row.remove();
  }

  form.addEventListener('submit', () => {
    document.querySelectorAll('.stagiaire-row').forEach(row => {
      const vals = Array.from(row.querySelectorAll('input')).map(i => (i.value || '').trim());
      if (vals.every(v => v === '')) row.remove();
    });
  });

  // Init
  showStep(currentStep);
</script>

@endsection
