@extends('formateur.dashboard')

@section('formateur')

{{-- 🧩 EN-TÊTE DE PAGE --}}
<div class="container mx-auto px-4 pt-8 pb-2">
    <div class="bg-white rounded-[20px] shadow-md px-8 py-0 mb-4 w-full max-w-[1285px] mx-auto">
        <div class="grid grid-cols-12 gap-6 items-center">
            <div class="col-span-12 md:col-span-8">
                <x-typography variant="titre">Mes stagiaires</x-typography>
                <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
                    Gérer l’ensemble de vos apprenants en un coup d’œil.
                </x-typography>
                <x-typography>
                    Depuis cette page, vous pouvez modifier, supprimer ou filtrer les stagiaires rattachés à vos groupes.
                </x-typography>
            </div>
            <div class="col-span-12 md:col-span-4 flex justify-center md:justify-end">
                <div class="w-full max-w-xs">
                    {!! file_get_contents(public_path('frontend/assets/img/illustrations/AssociationOneduc.svg')) !!}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 📋 CONTENU PRINCIPAL --}}
<div class="max-w-7xl mx-auto py-10 px-6">

    {{-- 🔎 Barre de recherche --}}
    <form method="GET" class="mb-8 flex flex-wrap items-center gap-3">
        <input type="text"
               name="search"
               value="{{ request('search') }}"
               placeholder="Recherche prénom, nom ou email"
               class="w-full md:w-1/2 px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orangeone focus:border-orangeone text-sm font-lisible"
        >
        <button type="submit" class="btn-oneduc">
            Rechercher
        </button>
    </form>

    {{-- 📊 Tableau des stagiaires --}}
    <div class="overflow-x-auto bg-white shadow-md rounded-[20px]">
        <table class="min-w-full text-sm text-left text-gray-800 font-lisible">
            <thead class="bg-gray-100 uppercase text-xs text-gray-600 font-varela">
                <tr>
                    <th class="px-6 py-3">#</th>
                    <th class="px-6 py-3">Prénom</th>
                    <th class="px-6 py-3">Nom</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3">Code d'accès</th>
                    <th class="px-6 py-3">Groupe(s)</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($stagiaires as $index => $stagiaire)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium">{{ $stagiaires->firstItem() + $index }}</td>
                        <td class="px-6 py-4">{{ $stagiaire->prenom }}</td>
                        <td class="px-6 py-4">{{ $stagiaire->name }}</td>
                        <td class="px-6 py-4">{{ $stagiaire->email }}</td>
                        <td class="px-6 py-4 font-mono text-sm text-orangeone">
                            {{ $stagiaire->code_acces ?? '—' }}
                        </td>
                        <td class="px-6 py-4">
                            @forelse ($stagiaire->groupesStagiaire as $groupe)
                                <span class="inline-block bg-vertone/10 text-vertone text-xs font-varela px-2 py-1 rounded-full mr-1">
                                    {{ $groupe->name }}
                                </span>
                            @empty
                                <span class="text-gray-400 text-xs italic">Aucun</span>
                            @endforelse
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex gap-2">
                                {{-- Modifier --}}
                                <a href="{{ route('formateur.stagiaires.edit', $stagiaire->id) }}"
                                   class="btn-oneduc px-3 py-1 text-xs text-white bg-orangeone border-orangeone hover:bg-white hover:text-orangeone">
                                    Modifier
                                </a>

                                {{-- Supprimer --}}
                                <form action="{{ route('formateur.stagiaires.destroy', $stagiaire->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Supprimer ce stagiaire ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="btn-oneduc px-3 py-1 text-xs text-white bg-bleuone border-bleuone hover:bg-white hover:text-bleuone">
                                        Supprimer
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">Aucun stagiaire trouvé.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- 📄 Pagination --}}
    <div class="mt-6">
        {{ $stagiaires->links('pagination::tailwind') }}
    </div>
</div>

@endsection
