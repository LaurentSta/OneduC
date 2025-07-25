@extends('formateur.dashboard')

@section('formateur')

{{-- 🧩 EN-TÊTE DE PAGE --}}
<div class="container mx-auto px-4 pt-8 pb-2">
    <div class="bg-white rounded-[20px] shadow-md px-8 py-0 mb-4 w-full max-w-[1285px] mx-auto">
        <div class="grid grid-cols-12 gap-6 items-center">
            <div class="col-span-12 md:col-span-8">
                <x-typography variant="titre">Mes groupes de formation</x-typography>
                <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
                    Gérez facilement vos groupes, modules et stagiaires.
                </x-typography>
                <x-typography>
                    Retrouvez ici tous vos groupes. Vous pouvez les modifier, leur associer des modules ou ajouter des stagiaires.
                </x-typography>
                <nav class="text-sm font-varela text-gray-600 mt-2 mb-6" aria-label="Fil d'Ariane">
                    <ol class="list-none p-0 inline-flex items-center space-x-1">
                        {{-- Accueil formateur --}}
                        <li class="flex items-center">
                            <a href="{{ route('formateur.dashboard') }}" class="text-orangeone hover:underline flex items-center">
                                {{-- Icône maison --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 9.75L12 3l9 6.75V19a2 2 0 01-2 2h-4a1 1 0 01-1-1v-5H10v5a1 1 0 01-1 1H5a2 2 0 01-2-2V9.75z"/>
                                </svg>
                            </a>
                            <span class="mx-2 text-gray-400">/</span>
                        </li>

                        {{-- Étape finale stylisée comme "..." --}}
                <li class="text-gray-400">…</li>
                    </ol>
                </nav>
            </div>
            <div class="col-span-12 md:col-span-4 flex justify-center md:justify-end">
                <div class="w-full max-w-xs">
                    {!! file_get_contents(public_path('frontend/assets/img/illustrations/AssociationOneduc.svg')) !!}
                </div>
            </div>
        </div>
    </div>
    
</div>

{{-- 💼 CONTENU PRINCIPAL --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
    @if (session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-3 rounded mb-6 font-lisible">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        {{-- ➕ Carte "Ajouter un groupe" --}}
        <a href="{{ route('formateur.groupes.create') }}"
           class="flex flex-col items-center justify-center border-4 border-dashed border-orangeone rounded-2xl p-10 h-full text-orangeone hover:bg-orangeone hover:text-white transition font-varela text-lg font-semibold">
            Ajouter un groupe
        </a>

        {{-- 📋 Liste des groupes --}}
        @forelse ($groupes as $groupe)
            <div class="bg-white border border-gray-200 rounded-2xl shadow p-6 flex flex-col justify-between">
                <div class="flex-1">
                    <h3 class="text-xl font-bold text-bleuone font-raleway mb-2 truncate">
                        {{ $groupe->name }}
                    </h3>
                    <p class="text-sm text-gray-700 font-lisible mb-4 line-clamp-3">
                        {{ $groupe->description }}
                    </p>

                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-600 font-varela">Modules associés :</h4>
                        @forelse ($groupe->modules as $module)
                            <a href="{{ route('frontend.modules.show', $module->id) }}"
                            class="inline-block bg-vertone/10 text-vertone text-xs font-varela mr-2 mb-2 px-3 py-1 rounded-full hover:bg-vertone/20 transition">
                                {{ Str::limit($module->module_title, 30) }}
                            </a>
                        @empty
                            <p class="text-sm text-gray-400 font-lisible italic">Aucun module</p>
                        @endforelse
                    </div>

                    <div>
                        <h4 class="text-sm font-medium text-gray-600 font-varela">Stagiaires :</h4>
                        <a href="{{ route('formateur.stagiaires.index') }}"
                        class="text-sm text-orangeone hover:underline font-lisible">
                        {{ $groupe->students->count() }} stagiaire{{ $groupe->students->count() > 1 ? 's' : '' }}
                        </a>

                    </div>
                </div>

                <div class="flex justify-between items-center mt-6 space-x-2">
                    <a href="{{ route('formateur.groupes.edit', $groupe->id) }}" class="btn-oneduc w-1/2 text-center">
                        Modifier
                    </a>
                    <form action="{{ route('formateur.groupes.destroy', $groupe->id) }}" method="POST"
                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce groupe ?');"
                          class="w-1/2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-oneduc bg-bleuone border-bleuone hover:bg-white hover:text-bleuone w-full">
                            Supprimer
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-gray-500 col-span-full font-lisible">Aucun groupe n’a encore été créé.</p>
        @endforelse
    </div>
</div>

@endsection
