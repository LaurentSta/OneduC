@extends('formateur.dashboard')
@section('formateur')

{{-- 🧩 EN-TÊTE DE PAGE FORMATEUR – Progression --}}
<div class="bg-white rounded-[20px] shadow-md px-8 pt-4 w-full max-w-[1285px] mx-auto mb-6">
  <div class="grid grid-cols-12 gap-6 items-start">
    <div class="col-span-12">
      <x-typography variant="titre">Suivi des progressions</x-typography>
      <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
        Visualisez les leçons terminées par vos stagiaires.
      </x-typography>
      <x-typography>
        Cette page présente toutes les leçons complétées, avec leur date et le module associé.
      </x-typography>

      {{-- 📍 Fil d’Ariane --}}
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
          <li class="text-gray-400">Progressions</li>
        </ol>
      </nav>
    </div>
  </div>
</div>

{{-- 📋 CONTENU PRINCIPAL --}}

  <div class="overflow-x-auto bg-white shadow-md rounded-[20px]">
    <table class="min-w-full text-sm text-left text-gray-800 font-lisible">
      <thead class="bg-gray-100 text-xs text-gray-600 uppercase font-varela">
        <tr>
          <th class="py-3 px-4">Stagiaire</th>
          <th class="py-3 px-4">Leçon</th>
          <th class="py-3 px-4">Module</th>
          <th class="py-3 px-4">Terminé le</th>
        </tr>
      </thead>
      <tbody>
        @forelse($progressions as $p)
          <tr class="border-t hover:bg-gray-50">
            <td class="py-3 px-4">{{ $p->user->name ?? 'Inconnu' }}</td>
            <td class="py-3 px-4">{{ $p->lecture->lecture_title ?? 'Leçon supprimée' }}</td>
            <td class="py-3 px-4">{{ $p->lecture->section->module->module_title ?? 'Module supprimé' }}</td>
            <td class="py-3 px-4">{{ \Carbon\Carbon::parse($p->completed_at)->format('d/m/Y à H:i') }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="4" class="text-center py-4 text-gray-500">Aucune progression enregistrée pour le moment.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>


@endsection
