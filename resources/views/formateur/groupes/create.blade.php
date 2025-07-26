@extends('formateur.dashboard')

@section('formateur')

{{-- 🧩 CONTENEUR GLOBAL --}}
<div class="container mx-auto px-4 pt-8 pb-12">

    {{-- ✅ EN-TÊTE DE PAGE --}}
    <div class="bg-white rounded-[20px] shadow-md px-8 py-6 mb-6 w-full max-w-[1285px] mx-auto">
        <div class="grid grid-cols-12 gap-6 items-center">
            {{-- Texte à gauche --}}
            <div class="col-span-12 md:col-span-8">
                <x-typography variant="titre">Création d’un groupe</x-typography>
                <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
                    Gérez facilement vos groupes, modules et stagiaires.
                </x-typography>
                <x-typography>
                    Créez un nouveau groupe en 3 étapes : nom, stagiaires et modules à associer.
                </x-typography>

                {{-- Fil d’Ariane --}}
                <nav class="text-sm font-varela text-gray-600 mt-2 mb-4" aria-label="Fil d'Ariane">
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
                        <li class="text-gray-400">…</li>
                    </ol>
                </nav>
            </div>

            {{-- Illustration à droite --}}
            <div class="col-span-12 md:col-span-4 flex justify-center md:justify-end">
                <div class="w-full max-w-xs">
                    {!! file_get_contents(public_path('frontend/assets/img/illustrations/AssociationOneduc.svg')) !!}
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ FORMULAIRE WIZARD --}}
    <div class="bg-white rounded-[20px] shadow-md px-8 py-10 w-full max-w-[1285px] mx-auto">
        {{-- Barre de progression --}}
        <div class="mb-8">
            <div class="flex justify-between mb-4">
                <span id="step1" class="wizard-step active">Groupe</span>
                <span id="step2" class="wizard-step">Stagiaires</span>
                <span id="step3" class="wizard-step">Modules</span>
            </div>

            <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-orange-200">
                <div id="progress-bar" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-orange-500 w-1/3 transition-all duration-500 ease-in-out"></div>
            </div>
        </div>

        {{-- Formulaire --}}
        <form id="multi-step-form" method="POST" action="{{ route('formateur.groupes.store') }}">
            @csrf

            {{-- Etape 1 --}}
            <div id="step-1" class="step">
                <p class="text-sm text-gray-600 mb-6">Commencez par donner un nom à votre groupe et une description optionnelle.</p>
                <div class="mb-6">
                    <label class="block mb-2 text-sm font-medium text-gray-900">Nom du groupe *</label>
                    <input type="text" name="nom" class="input w-full" required placeholder="Exemple : Groupe Marketing 2025 - Niveau 1">
                </div>
                <div class="mb-6">
                    <label class="block mb-2 text-sm font-medium text-gray-900">Description</label>
                    <textarea name="description" class="input w-full"></textarea>
                </div>
            </div>

            {{-- Etape 2 --}}
            <div id="step-2" class="step hidden">
                <p class="text-sm text-gray-600 text-center mb-6">Définissez un mot de passe commun, puis ajoutez un ou plusieurs stagiaires.</p>
                <div class="mb-6">
                    <label class="block mb-2 text-sm font-medium text-gray-900">Mot de passe commun *</label>
                    <input type="password" name="password" id="password" class="input w-full" required minlength="8">
                </div>

                <div id="stagiaires-container">
                  <div class="mb-4 bg-gray-50 p-4 rounded relative">
                    <div class="flex justify-between items-start mb-2">
                      <span class="text-sm font-medium text-gray-700">Stagiaire 1</span>
                    </div>

                    <div class="grid grid-cols-[1fr_1fr_2fr_auto] gap-3 items-center" x-data="{ showTip: false }">
                      <input type="text" name="stagiaires[0][prenom]" placeholder="Prénom" class="input">
                      <input type="text" name="stagiaires[0][nom]" placeholder="Nom" class="input">
                      <input type="email" name="stagiaires[0][email]" placeholder="Email" class="input">
                      <div class="flex justify-end pr-2 relative" @mouseenter="showTip = true" @mouseleave="showTip = false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#E94D2A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-lock-keyhole-open-icon lucide-lock-keyhole-open">
                          <circle cx="12" cy="16" r="1"/><rect width="18" height="12" x="3" y="10" rx="2"/><path d="M7 10V7a5 5 0 0 1 9.33-2.5"/>
                        </svg>
                        <div x-show="showTip" x-transition x-cloak class="absolute top-6 right-0 w-72 bg-gray-800 text-white text-xs rounded-lg shadow-lg p-3 z-50">
                          Un code d’accès est généré automatiquement à la validation du groupe.<br>
                          Retrouvez dans la barre de menu gauche "Stagiaire".
                        </div>
                      </div>
                    </div>
                  </div>
                </div>


                <button type="button" class="btn-secondary mb-4" onclick="addStagiaire()">+ Ajouter un stagiaire</button>
            </div>

            {{-- Etape 3 --}}
            <div id="step-3" class="step hidden">
                <p class="text-sm text-gray-600 mb-4">Sélectionnez les modules de formation à associer à ce groupe.</p>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach ($modules as $module)
                        <label class="flex items-center space-x-2 bg-gray-50 border rounded px-4 py-2">
                            <input type="checkbox" name="modules[]" value="{{ $module->id }}">
                            <span>{{ $module->module_title }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Navigation --}}
            <div class="flex justify-between mt-8">
                <a href="#" id="prevBtn" class="btn-oneduc hidden">◀ Précédent</a>
                <a href="#" id="nextBtn" class="btn-oneduc ml-auto">Suivant ▶</a>
                <button type="submit" id="submitBtn" class="btn-oneduc hidden ml-auto">Créer le groupe</button>
            </div>
        </form>
    </div>
</div>

{{-- Styles utilitaires --}}
<style>
  .input {
    @apply bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5;
  }
  .btn-secondary {
    @apply px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition;
  }
  .wizard-step {
    @apply text-orangeone bg-orange-100 border border-orangeone text-sm font-semibold px-4 py-2 rounded-full transition;
  }

  .wizard-step.active {
    @apply bg-orangeone text-white;
  }
</style>



<!-- Styles utilitaires -->
<style>
  .input {
    @apply bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5;
  }
  .btn-secondary {
    @apply px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition;
  }
</style>

<!-- JS -->
<script>
  let currentStep = 1;
  const form = document.getElementById('multi-step-form');
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  const submitBtn = document.getElementById('submitBtn');
  const progressBar = document.getElementById('progress-bar');

  function showStep(step) {
    for (let i = 1; i <= 3; i++) {
  const stepEl = document.getElementById(`step${i}`);
  stepEl.classList.toggle('active', i === step);
}

    document.querySelectorAll('.step').forEach(s => s.classList.add('hidden'));
    document.getElementById(`step-${step}`).classList.remove('hidden');
    progressBar.style.width = `${(step / 3) * 100}%`;
    for (let i = 1; i <= 3; i++) {
      const stepIndicator = document.getElementById(`step${i}`);
      i <= step ? stepIndicator.classList.remove('opacity-50') : stepIndicator.classList.add('opacity-50');
    }
    prevBtn.classList.toggle('hidden', step === 1);
    nextBtn.classList.toggle('hidden', step === 3);
    submitBtn.classList.toggle('hidden', step !== 3);
  }

  function validateStep(step) {
    let isValid = true;

    if (step === 2) {
      const password = document.getElementById('password');
      if (!password.value || password.value.length < 8) {
        isValid = false;
        password.classList.add('border-red-500');
      } else {
        password.classList.remove('border-red-500');
      }

      const container = document.getElementById('stagiaires-container');
      const stagiaireBlocks = container.querySelectorAll('div');

      stagiaireBlocks.forEach(block => {
        const inputs = block.querySelectorAll('input');
        const values = Array.from(inputs).map(input => input.value.trim());
        const isEmpty = values.every(v => v === '');
        if (isEmpty) {
          block.remove();
        } else {
          inputs.forEach(input => input.classList.remove('border-red-500'));
        }
      });

      return isValid;
    }

    const currentStepElement = document.getElementById(`step-${step}`);
    const inputs = currentStepElement.querySelectorAll('input[required], select[required]');
    inputs.forEach(input => {
      if (!input.value) {
        isValid = false;
        input.classList.add('border-red-500');
      } else {
        input.classList.remove('border-red-500');
      }
    });

    return isValid;
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
    currentStep--;
    showStep(currentStep);
  });

  function addStagiaire() {
    const container = document.getElementById('stagiaires-container');
    const index = container.children.length;

    const div = document.createElement('div');
    div.classList.add('mb-4', 'bg-gray-50', 'p-4', 'rounded', 'relative');
    div.innerHTML = `
      <div class="flex justify-between items-start mb-2">
        <span class="text-sm font-medium text-gray-700">Stagiaire ${index + 1}</span>
        <button type="button" class="text-xs font-semibold text-red-600 bg-red-100 hover:bg-red-200 px-2 py-1 rounded-full z-50" onclick="removeStagiaire(this)" title="Supprimer ce stagiaire">Fermer</button>
      </div>
      <div class="grid grid-cols-[1fr_1fr_2fr_auto] gap-3 items-center" x-data="{ showTip: false }">
        <input type="text" name="stagiaires[${index}][prenom]" placeholder="Prénom" class="input">
        <input type="text" name="stagiaires[${index}][nom]" placeholder="Nom" class="input">
        <input type="email" name="stagiaires[${index}][email]" placeholder="Email" class="input">
        <div class="flex justify-end pr-2 relative" @mouseenter="showTip = true" @mouseleave="showTip = false">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#E94D2A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-lock-keyhole-open-icon lucide-lock-keyhole-open">
            <circle cx="12" cy="16" r="1"/><rect width="18" height="12" x="3" y="10" rx="2"/><path d="M7 10V7a5 5 0 0 1 9.33-2.5"/>
          </svg>
          <div x-show="showTip" x-transition x-cloak class="absolute top-6 right-0 w-72 bg-gray-800 text-white text-xs rounded-lg shadow-lg p-3 z-50">
            Ce code d’accès sera généré automatiquement à la validation du groupe.<br>
            Il sera affiché dans la fiche des stagiaires.
          </div>
        </div>
      </div>
    `;
    container.appendChild(div);
  }


  function removeStagiaire(button) {
    const container = document.getElementById('stagiaires-container');
    const blocks = container.querySelectorAll('.relative');
    if (blocks.length <= 1) {
      alert("Au moins un stagiaire est requis.");
      return;
    }
    const block = button.closest('div.relative');
    block.remove();
  }

  form.addEventListener('submit', (e) => {
    const container = document.getElementById('stagiaires-container');
    const stagiaireBlocks = container.querySelectorAll('div');

    stagiaireBlocks.forEach(block => {
      const inputs = block.querySelectorAll('input');
      const values = Array.from(inputs).map(input => input.value.trim());
      const isEmpty = values.every(v => v === '');
      if (isEmpty) {
        block.remove();
      }
    });
  });

  showStep(currentStep);
</script>
@endsection
