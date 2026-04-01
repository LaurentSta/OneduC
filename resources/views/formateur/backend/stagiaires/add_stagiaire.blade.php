@extends('formateur.dashboard')

@section('formateur')

<div class="max-w-[1285px] mx-auto px-8">

  <header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full mb-6">
    <div class="grid grid-cols-12 gap-6 items-center">
      <div class="col-span-12 md:col-span-8">
        <x-typography variant="titre">Ajouter un stagiaire</x-typography>
        <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
          Créez un stagiaire en quelques champs.
        </x-typography>
        <x-typography>
          Renseignez l'identité, l'e-mail et, si besoin, le groupe.
        </x-typography>

        <nav class="text-sm font-varela text-gray-600 mt-2" aria-label="Fil d'Ariane">
          <ol class="inline-flex items-center space-x-1">
            <li class="flex items-center">
              <a href="{{ route('formateur.dashboard') }}" class="text-orangeone hover:underline flex items-center">
                <span class="sr-only">Accueil</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 9.75L12 3l9 6.75V19a2 2 0 01-2 2h-4a1 1 0 01-1-1v-5H10v5a1 1 0 01-1 1H5a2 2 0 01-2-2V9.75z"/>
                </svg>
              </a>
              <span class="mx-2 text-gray-400" aria-hidden="true">/</span>
            </li>
            <li class="flex items-center">
              <a href="{{ route('formateur.stagiaires.index') }}" class="hover:underline text-bleuone">Mes stagiaires</a>
              <span class="mx-2 text-gray-400" aria-hidden="true">/</span>
            </li>
            <li class="text-gray-400">Ajouter un stagiaire</li>
          </ol>
        </nav>
      </div>

      <div class="col-span-12 md:col-span-4 flex justify-center md:justify-end">
        <img src="{{ asset('images/svg/ListesStagiaires.svg') }}"
             alt="Illustration des stagiaires"
             class="max-w-[340px] h-auto">
      </div>
    </div>
  </header>

  <main class="bg-white rounded-[20px] shadow-md px-8 py-10 w-full">
    @if ($errors->any())
      <div class="mx-auto mb-6 max-w-4xl rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <ul class="list-disc list-inside space-y-1">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('formateur.stagiaires.store') }}" method="POST" class="mx-auto max-w-4xl space-y-6">
      @csrf

      <section class="rounded-[16px] border border-gray-200 p-6 md:p-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div>
            <label for="prenom" class="block mb-2 text-sm font-medium text-gray-900">Prénom</label>
            <input id="prenom" name="prenom" type="text" required
                   value="{{ old('prenom') }}"
                   class="w-full rounded-lg border {{ $errors->has('prenom') ? 'border-red-400' : 'border-gray-300' }} bg-gray-50 px-4 py-2.5 text-sm focus:border-orangeone focus:ring-orangeone"
                   placeholder="Camille">
          </div>

          <div>
            <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Nom</label>
            <input id="name" name="name" type="text" required
                   value="{{ old('name') }}"
                   class="w-full rounded-lg border {{ $errors->has('name') ? 'border-red-400' : 'border-gray-300' }} bg-gray-50 px-4 py-2.5 text-sm focus:border-orangeone focus:ring-orangeone"
                   placeholder="Martin">
          </div>

          <div class="md:col-span-2">
            <div class="mb-2 flex items-center gap-2">
              <label for="email" class="block text-sm font-medium text-gray-900">Adresse e-mail</label>
              <div class="relative group">
                <button type="button" aria-label="Information sur l'adresse e-mail" class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-gray-300 bg-white text-[11px] font-bold text-gray-600">
                  ?
                </button>
                <div class="pointer-events-none absolute left-1/2 top-full z-20 mt-2 hidden w-72 -translate-x-1/2 rounded-lg border border-gray-200 bg-white p-2 text-xs text-gray-700 shadow-lg group-hover:block group-focus-within:block">
                  Si l'e-mail existe deja, le compte stagiaire sera reutilise.
                </div>
              </div>
            </div>
            <input id="email" name="email" type="email" required
                   value="{{ old('email') }}"
                   class="w-full rounded-lg border {{ $errors->has('email') ? 'border-red-400' : 'border-gray-300' }} bg-gray-50 px-4 py-2.5 text-sm focus:border-orangeone focus:ring-orangeone"
                   placeholder="camille.martin@entreprise.fr">
          </div>

          <div class="md:col-span-2">
            <label for="group_id" class="block mb-2 text-sm font-medium text-gray-900">Groupe</label>
            @if($groupes->isNotEmpty())
              <select id="group_id" name="group_id"
                      class="w-full rounded-lg border {{ $errors->has('group_id') ? 'border-red-400' : 'border-gray-300' }} bg-gray-50 px-4 py-2.5 text-sm focus:border-orangeone focus:ring-orangeone">
                <option value="">Aucun groupe pour le moment</option>
                @foreach($groupes as $groupe)
                  <option value="{{ $groupe->id }}" @selected((string) old('group_id', $selectedGroupId) === (string) $groupe->id)>
                    {{ $groupe->name }}
                  </option>
                @endforeach
              </select>
            @else
              <div class="rounded-lg border border-orangeone/20 bg-orangeone/5 px-4 py-3 text-sm text-gray-700">
                Aucun groupe disponible.
                <a href="{{ route('formateur.groupes.create') }}" class="font-semibold text-orangeone hover:underline">
                  Créer un groupe
                </a>
              </div>
            @endif
          </div>
        </div>

        <div class="mt-8 border-t border-gray-100 pt-6">
          <div class="flex items-center gap-2">
            <h3 class="text-sm font-bold uppercase tracking-wide text-gray-500">Mot de passe facultatif</h3>
            <div class="relative group">
              <button type="button" aria-label="Information sur le mot de passe" class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-gray-300 bg-white text-[11px] font-bold text-gray-600">
                ?
              </button>
              <div class="pointer-events-none absolute left-1/2 top-full z-20 mt-2 hidden w-72 -translate-x-1/2 rounded-lg border border-gray-200 bg-white p-2 text-xs text-gray-700 shadow-lg group-hover:block group-focus-within:block">
                Si un groupe est selectionne, le stagiaire utilisera le mot de passe du groupe.
              </div>
            </div>
          </div>
          <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label for="password" class="block mb-2 text-sm font-medium text-gray-900">Mot de passe</label>
              <input id="password" name="password" type="password"
                     class="w-full rounded-lg border {{ $errors->has('password') ? 'border-red-400' : 'border-gray-300' }} bg-gray-50 px-4 py-2.5 text-sm focus:border-orangeone focus:ring-orangeone"
                     placeholder="Minimum 8 caracteres">
            </div>

            <div>
              <label for="password_confirmation" class="block mb-2 text-sm font-medium text-gray-900">Confirmation</label>
              <input id="password_confirmation" name="password_confirmation" type="password"
                     class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm focus:border-orangeone focus:ring-orangeone"
                     placeholder="Retapez le mot de passe">
            </div>
          </div>
        </div>
      </section>

      <div class="flex flex-col md:flex-row items-center justify-between gap-4">
        <a href="{{ route('formateur.stagiaires.index') }}" class="btn-oneduc-outline !px-5 !py-2.5 !text-sm">
          Annuler
        </a>

        <button type="submit" class="btn-oneduc w-full md:w-auto px-8 py-3 text-lg">
          Créer le stagiaire
        </button>
      </div>
    </form>
  </main>
</div>

@endsection
