@extends('formateur.dashboard')

@section('formateur')

@php
  $selectedGroupIds = collect(old('group_ids', $stagiaire->groupesStagiaire->pluck('id')->all()))
    ->map(fn ($groupId) => (int) $groupId)
    ->all();
@endphp

<div class="max-w-[1285px] mx-auto px-8">
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full mb-6">
    <div class="grid grid-cols-12 gap-6 items-start">
      <div class="col-span-12">
        <x-typography variant="titre">Modifier le stagiaire</x-typography>
        <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
          Mettez à jour ses informations et ses groupes.
        </x-typography>
        <x-typography>
          Vous pouvez corriger ses données et ajuster son rattachement aux groupes sans repasser par le wizard de groupe.
        </x-typography>

        <nav class="text-sm font-varela text-gray-600 mt-2 mb-6" aria-label="Fil d'Ariane">
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
              <a href="{{ route('formateur.stagiaires.index') }}" class="hover:underline text-bleuone">Mes stagiaires</a>
              <span class="mx-2 text-gray-400">/</span>
            </li>
            <li class="text-gray-400">Modifier un stagiaire</li>
          </ol>
        </nav>
      </div>
    </div>
  </header>

  <main class="bg-white rounded-[20px] shadow-md px-8 py-10 w-full">
    @if ($errors->any())
      <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <ul class="list-disc list-inside space-y-1">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('formateur.stagiaires.update', $stagiaire->id) }}" class="space-y-8">
      @csrf
      @method('PUT')

      <section class="grid grid-cols-1 lg:grid-cols-[2fr_1fr] gap-6">
        <div class="rounded-[16px] border border-gray-200 p-6">
          <h2 class="text-lg font-bold text-bleuone font-raleway mb-1">Informations du stagiaire</h2>
          <p class="text-sm text-gray-600 font-lisible mb-6">Modifiez ses informations principales.</p>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="prenom" class="block mb-2 text-sm font-medium text-gray-900">Prénom</label>
              <input id="prenom" type="text" name="prenom" value="{{ old('prenom', $stagiaire->prenom) }}"
                     class="w-full rounded-lg border {{ $errors->has('prenom') ? 'border-red-400' : 'border-gray-300' }} bg-gray-50 px-4 py-2.5 text-sm focus:border-orangeone focus:ring-orangeone"
                     required>
            </div>

            <div>
              <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Nom</label>
              <input id="name" type="text" name="name" value="{{ old('name', $stagiaire->name) }}"
                     class="w-full rounded-lg border {{ $errors->has('name') ? 'border-red-400' : 'border-gray-300' }} bg-gray-50 px-4 py-2.5 text-sm focus:border-orangeone focus:ring-orangeone"
                     required>
            </div>

            <div>
              <label for="email" class="block mb-2 text-sm font-medium text-gray-900">Adresse e-mail</label>
              <input id="email" type="email" name="email" value="{{ old('email', $stagiaire->email) }}"
                     class="w-full rounded-lg border {{ $errors->has('email') ? 'border-red-400' : 'border-gray-300' }} bg-gray-50 px-4 py-2.5 text-sm focus:border-orangeone focus:ring-orangeone"
                     required>
            </div>

            <div>
              <label for="password" class="block mb-2 text-sm font-medium text-gray-900">Nouveau mot de passe</label>
              <input id="password" type="password" name="password"
                     class="w-full rounded-lg border {{ $errors->has('password') ? 'border-red-400' : 'border-gray-300' }} bg-gray-50 px-4 py-2.5 text-sm focus:border-orangeone focus:ring-orangeone"
                     placeholder="Laisser vide si inchangé">
            </div>
          </div>
        </div>

        <div class="rounded-[16px] border border-bleuone/20 bg-bleuone/5 p-6">
          <h2 class="text-lg font-bold text-bleuone font-raleway mb-1">Groupes associés</h2>
          <p class="text-sm text-gray-600 font-lisible mb-2">Choisissez ici les groupes du formateur auxquels ce stagiaire doit appartenir.</p>
          <p class="text-xs text-gray-500 font-lisible mb-4">C'est depuis cette fiche que vous pouvez maintenant l'inscrire à un groupe ou le retirer d'un groupe.</p>

          @if($groupes->isNotEmpty())
            <div class="space-y-3">
              @foreach($groupes as $groupe)
                <label class="flex items-start gap-3 rounded-xl border border-white bg-white px-4 py-3 shadow-sm hover:border-bleuone/20">
                  <input type="checkbox"
                         name="group_ids[]"
                         value="{{ $groupe->id }}"
                         @checked(in_array((int) $groupe->id, $selectedGroupIds, true))
                         class="mt-1 rounded border-gray-300 text-bleuone focus:ring-orangeone">
                  <span>
                    <span class="block text-sm font-semibold text-gray-900">{{ $groupe->name }}</span>
                    <span class="block text-xs text-gray-500">Le stagiaire sera visible dans ce groupe.</span>
                  </span>
                </label>
              @endforeach
            </div>
          @else
            <div class="rounded-xl border border-orangeone/20 bg-white px-4 py-3 text-sm text-gray-700">
              Aucun groupe n'est disponible.
              <a href="{{ route('formateur.groupes.create') }}" class="font-semibold text-orangeone hover:underline">
                Créer un groupe
              </a>
            </div>
          @endif
        </div>
      </section>

      <div class="flex flex-col md:flex-row items-center justify-between gap-4">
        <a href="{{ route('formateur.stagiaires.index') }}"
           class="btn-oneduc-outline !px-5 !py-2.5 !text-sm">
          Retour à la liste
        </a>

        <div class="flex w-full md:w-auto flex-col md:flex-row items-center gap-3">
          <button type="submit" class="btn-oneduc w-full md:w-auto px-8 py-3 text-lg">
            Enregistrer les modifications
          </button>
        </div>
      </div>
    </form>
  </main>
</div>

@endsection
