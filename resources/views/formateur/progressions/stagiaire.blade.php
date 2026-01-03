{{-- /home/laurents/Oneduc_Dev/resources/views/formateur/progressions/stagiaire.blade.php --}}
@extends('formateur.dashboard')

@section('formateur')

<div class="max-w-[1285px] mx-auto px-8">

  {{-- EN-TÊTE --}}
  <header class="bg-white rounded-[20px] shadow-md px-8 pt-4 pb-6 w-full mb-6">
    <div class="grid grid-cols-12 gap-6 items-center">

      <div class="col-span-12 md:col-span-9">
        <p class="font-raleway text-titre text-bleuone leading-tight mb-4">
          Détail d’un stagiaire
        </p>

        <p class="font-varela text-sous-titre text-orangeone leading-snug mb-3">
          Historique des leçons terminées
        </p>

        <p class="font-lisible text-lg text-gray-800 leading-loose mb-4">
          Consultez le détail des leçons terminées, avec les modules concernés et les dates de complétion.
        </p>

        {{-- Identité stagiaire --}}
        <div class="flex flex-wrap items-center gap-x-6 gap-y-2 mt-2">
          <p class="text-sm text-gray-600 font-varela">
            Stagiaire :
            <span class="text-gray-900 font-semibold">
              {{ trim(($stagiaire->prenom ?? '').' '.($stagiaire->name ?? '')) ?: 'Inconnu' }}
            </span>
          </p>

          <p class="text-sm text-gray-600 font-varela">
            Email :
            <span class="text-gray-900 font-semibold">{{ $stagiaire->email ?? '—' }}</span>
          </p>
        </div>

        {{-- Fil d’Ariane --}}
        <nav class="text-sm font-varela text-gray-600 mt-3" aria-label="Fil d'Ariane">
          <ol class="inline-flex items-center space-x-1">
            <li class="flex items-center">
              <a href="{{ route('formateur.dashboard') }}" class="text-orangeone hover:underline">
                Accueil
              </a>
              <span class="mx-2 text-gray-400" aria-hidden="true">/</span>
            </li>
            <li class="flex items-center">
              <a href="{{ route('formateur.progressions.groupes') }}" class="text-orangeone hover:underline">
                Progression
              </a>
              <span class="mx-2 text-gray-400" aria-hidden="true">/</span>
            </li>
            <li class="text-gray-400">Détail stagiaire</li>
          </ol>
        </nav>
      </div>

      <div class="col-span-12 md:col-span-3 flex justify-center md:justify-end">
        <img src="{{ asset('images/svg/Progressions.svg') }}"
             alt="Illustration progression"
             class="max-w-[240px] h-auto">
      </div>

    </div>
  </header>

  {{-- CONTENU --}}
  <main class="space-y-6">

    {{-- Actions --}}
    <div class="flex flex-wrap gap-3">
      <a href="{{ route('formateur.progressions.stagiaires') }}" class="btn-oneduc">
        Retour aux stagiaires
      </a>
      <a href="{{ route('formateur.progressions.groupes') }}" class="btn-oneduc">
        Retour aux groupes
      </a>
    </div>

    {{-- Tableau journal --}}
    <div class="overflow-x-auto bg-white shadow-md rounded-[20px]">
      <table class="min-w-full text-sm text-left text-gray-800 font-lisible">

        <thead class="bg-gray-100 text-xs text-gray-600 uppercase font-varela">
          <tr>
            <th class="px-6 py-3">Leçon</th>
            <th class="px-6 py-3">Module</th>
            <th class="px-6 py-3 text-center">Terminé le</th>
          </tr>
        </thead>

        <tbody>
          @forelse($progressions as $p)
            <tr class="border-t hover:bg-gray-50">

              {{-- Leçon --}}
              <td class="px-6 py-4">
                {{ $p->lecture->lecture_title ?? 'Leçon supprimée' }}
              </td>

              {{-- Module --}}
              <td class="px-6 py-4 text-gray-700">
                {{ $p->lecture->section->module->module_title ?? 'Module supprimé' }}
              </td>

              {{-- Date --}}
              <td class="px-6 py-4 text-center text-gray-600">
                @if(!empty($p->completed_at))
                  {{ \Carbon\Carbon::parse($p->completed_at)->format('d/m/Y à H:i') }}
                @else
                  —
                @endif
              </td>

            </tr>
          @empty
            <tr>
              <td colspan="3" class="px-6 py-6 text-center text-gray-500">
                Aucune progression enregistrée pour ce stagiaire.
              </td>
            </tr>
          @endforelse
        </tbody>

      </table>
    </div>

  </main>

</div>
@endsection
