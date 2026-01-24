@extends('formateur.dashboard')

@section('formateur')

{{-- /home/laurents/Oneduc_Dev/resources/views/formateur/groupes/edit.blade.php --}}

{{-- EN-TÊTE --}}
<div class="bg-white rounded-[20px] shadow-md px-8 pt-4 w-full max-w-[1285px] mx-auto mb-6">
  <div class="grid grid-cols-12 gap-6 items-start">
    <div class="col-span-12">
      <x-typography variant="titre">Modifier un groupe</x-typography>
      <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
        Mettez à jour les informations, les modules et les stagiaires.
      </x-typography>
      <x-typography>
        Cette page vous permet de modifier un groupe existant. Vous pouvez aussi ajouter de nouveaux stagiaires si besoin.
      </x-typography>

      {{-- Fil d’Ariane --}}
      <nav class="text-base font-varela text-gray-600 mt-2 mb-6" aria-label="Fil d'Ariane">
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
          <li class="text-gray-400">Modifier un groupe</li>
        </ol>
      </nav>
    </div>
  </div>
</div>

{{-- CONTENU --}}
<div class="bg-white rounded-[20px] shadow-md px-8 py-10 w-full max-w-[1285px] mx-auto font-varela text-base text-gray-800">

  {{-- Erreurs serveur --}}
  @if ($errors->any())
    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
      <p class="font-semibold mb-2">Des erreurs empêchent l’enregistrement :</p>
      <ul class="list-disc list-inside space-y-1">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('formateur.groupes.update', $group->id) }}">
    @csrf
    @method('PUT')

    {{-- Nom --}}
    <div class="mb-8">
      <label for="nom" class="block mb-2 text-base font-medium text-gray-900">
        Nom du groupe <span class="text-red-500">*</span>
      </label>
      <input id="nom" type="text" name="nom" value="{{ old('nom', $group->name) }}" required
             class="w-full bg-gray-50 border border-gray-300 rounded-lg px-4 py-3 text-base
                    focus:ring-orangeone focus:border-orangeone">
    </div>

    {{-- Description --}}
    <div class="mb-8">
      <label for="description" class="block mb-2 text-base font-medium text-gray-900">
        Description
      </label>
      <textarea id="description" name="description" rows="3"
                class="w-full bg-gray-50 border border-gray-300 rounded-lg px-4 py-3 text-base
                       focus:ring-orangeone focus:border-orangeone"
                placeholder="Objectifs, public, période…">{{ old('description', $group->description) }}</textarea>
    </div>

    {{-- Modules associés --}}
    <div class="mb-8">
      <p class="text-base font-medium text-gray-900 mb-3">Modules associés</p>

      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
        @foreach($modules as $module)
          <label class="flex items-start gap-3 bg-gray-50 border border-gray-300 rounded-lg px-4 py-4 hover:bg-gray-100 transition cursor-pointer">
            <input type="checkbox" name="modules[]" value="{{ $module->id }}"
                   class="mt-1 accent-vertone"
                   {{ in_array($module->id, $group->modules->pluck('id')->toArray()) ? 'checked' : '' }}>
            <span class="text-base text-gray-800">{{ $module->module_title }}</span>
          </label>
        @endforeach
      </div>
    </div>
    @if($group->modules->count())
      <div class="mb-8">
        <p class="text-base font-medium text-gray-900 mb-3">Personnaliser les leçons</p>

        <div class="space-y-2">
          @foreach($group->modules as $m)
            <div class="flex items-center justify-between bg-gray-50 border border-gray-300 rounded-lg px-4 py-3">
              <div class="text-base text-gray-900 font-medium">{{ $m->module_title }}</div>

              <a class="btn-oneduc-blue !px-4 !py-2 !border-2"
                href="{{ route('formateur.groupes.modules.lecons.edit', ['group' => $group->id, 'module' => $m->id]) }}">
                Personnaliser
              </a>
            </div>
          @endforeach
        </div>
      </div>
    @endif




    {{-- Stagiaires existants --}}
        <div class="mb-8">
        <p class="text-base font-medium text-gray-900 mb-3">Stagiaires déjà dans ce groupe</p>

        @if ($group->students->count())
            <div id="existing-students-list" class="space-y-2">
            @foreach ($group->students as $stagiaire)
                <div class="student-row flex items-center justify-between gap-4 bg-gray-50 border border-gray-300 rounded-lg px-4 py-3">
                
                <div class="flex-1">
                    <span class="text-base text-gray-900 font-medium">
                    {{ $stagiaire->prenom }} {{ $stagiaire->name }}
                    </span>
                    <span class="text-base text-gray-600 ml-2">
                    — {{ $stagiaire->email }}
                    </span>
                </div>

                <button type="button"
                        class="btn-oneduc-blue !px-4 !py-2 !border-2 text-base"
                        data-student-id="{{ $stagiaire->id }}"
                        data-student-name="{{ $stagiaire->prenom }} {{ $stagiaire->name }}"
                        onclick="markStudentForRemoval(this)">
                    Retirer
                </button>
                </div>
            @endforeach
            </div>
        @else
            <p class="text-base text-gray-600">Aucun stagiaire dans ce groupe.</p>
        @endif

        {{-- Champs cachés pour les retraits --}}
        <div id="remove-students-hidden"></div>
        </div>




    {{-- Ajouter nouveaux stagiaires --}}
    <div class="mb-10">
      <p class="text-base font-medium text-gray-900 mb-3">Ajouter de nouveaux stagiaires</p>

      <div id="nouveaux-stagiaires-container" class="space-y-3">
        <div class="stagiaire-bloc grid grid-cols-1 md:grid-cols-3 gap-3 relative">
          <input type="text" name="stagiaires[0][prenom]" placeholder="Prénom"
                 class="bg-white border border-gray-300 rounded-lg px-4 py-3 text-base focus:ring-orangeone focus:border-orangeone">
          <input type="text" name="stagiaires[0][nom]" placeholder="Nom"
                 class="bg-white border border-gray-300 rounded-lg px-4 py-3 text-base focus:ring-orangeone focus:border-orangeone">
          <input type="email" name="stagiaires[0][email]" placeholder="Email"
                 class="bg-white border border-gray-300 rounded-lg px-4 py-3 text-base focus:ring-orangeone focus:border-orangeone">
        </div>
      </div>

      <button type="button" onclick="ajouterStagiaire()" class="btn-oneduc mt-4">
        Ajouter un stagiaire
      </button>

      <p class="text-base text-gray-600 mt-3">
        Les informations sont utilisées uniquement pour créer l’accès stagiaire au groupe.
      </p>
    </div>

    {{-- Actions --}}
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mt-8">
      <a href="{{ route('formateur.groupes.index') }}"
         class="text-base text-gray-600 hover:text-gray-800 underline">
        Retour à la liste
      </a>

      <button type="submit" class="btn-oneduc">
        Enregistrer les modifications
      </button>
    </div>
  </form>
</div>

<script>
  function ajouterStagiaire() {
    const container = document.getElementById('nouveaux-stagiaires-container');
    const index = container.querySelectorAll('.stagiaire-bloc').length;

    const div = document.createElement('div');
    div.className = 'stagiaire-bloc grid grid-cols-1 md:grid-cols-3 gap-3 relative';

    div.innerHTML = `
      <div class="md:col-span-3 relative">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
          <input type="text" name="stagiaires[${index}][prenom]" placeholder="Prénom"
                 class="bg-white border border-gray-300 rounded-lg px-4 py-3 text-base focus:ring-orangeone focus:border-orangeone">
          <input type="text" name="stagiaires[${index}][nom]" placeholder="Nom"
                 class="bg-white border border-gray-300 rounded-lg px-4 py-3 text-base focus:ring-orangeone focus:border-orangeone">
          <input type="email" name="stagiaires[${index}][email]" placeholder="Email"
                 class="bg-white border border-gray-300 rounded-lg px-4 py-3 text-base focus:ring-orangeone focus:border-orangeone">
        </div>

        <button type="button"
                class="absolute -top-3 -right-3 bg-orangeone text-white rounded-full w-9 h-9 flex items-center justify-center transition hover:scale-105"
                onclick="this.closest('.stagiaire-bloc').remove()"
                aria-label="Supprimer ce stagiaire"
                title="Supprimer">
          <svg xmlns="http://www.w3.org/2000/svg"
               width="18" height="18" viewBox="0 0 24 24"
               fill="none" stroke="white" stroke-width="2"
               stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 6 6 18"/>
            <path d="m6 6 12 12"/>
          </svg>
        </button>
      </div>
    `;

    container.appendChild(div);
  }
</script>
<script>
  function markStudentForRemoval(btn) {
    const id = btn.getAttribute('data-student-id');
    const name = btn.getAttribute('data-student-name');

    const ok = confirm(`Retirer ${name} du groupe ?`);
    if (!ok) return;

    // ajoute un input hidden remove_students[]
    const hiddenWrap = document.getElementById('remove-students-hidden');

    // évite doublon
    if (hiddenWrap.querySelector(`input[value="${id}"]`)) return;

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'remove_students[]';
    input.value = id;
    hiddenWrap.appendChild(input);

    // retire visuellement la ligne
    const row = btn.closest('.student-row');
    if (row) row.remove();
  }
</script>

@endsection
