@extends('stagiaire.master')

@section('content')
{{-- 🧩 EN-TÊTE DE PAGE STAGIAIRE – Mes modules --}}
<div class="bg-white rounded-[20px] shadow-md px-8 pt-4 w-full max-w-[1285px] mx-auto mb-6">
    <div class="grid grid-cols-12 gap-6 items-start">
        <div class="col-span-12">
            <x-typography variant="titre">Mes formations</x-typography>
            <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
                Accédez à vos contenus et suivez votre progression.
            </x-typography>
            <x-typography>
                Chaque module regroupe plusieurs sections. Vous pouvez reprendre une leçon à tout moment.
            </x-typography>

            <nav class="text-sm font-varela text-gray-600 mt-2 mb-6" aria-label="Fil d'Ariane">
                <ol class="list-none p-0 inline-flex items-center space-x-1">
                    <li class="flex items-center">
                        <a href="{{ route('stagiaire.dashboard') }}" class="text-orangeone hover:underline flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 9.75L12 3l9 6.75V19a2 2 0 01-2 2h-4a1 1 0 01-1-1v-5H10v5a1 1 0 01-1 1H5a2 2 0 01-2-2V9.75z"/>
                            </svg>
                        </a>
                        <span class="mx-2 text-gray-400">/</span>
                    </li>
                    <li class="text-gray-400">Mes modules</li>
                </ol>
            </nav>
        </div>
    </div>
</div>


    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($modules as $module)
            <div class="bg-white shadow-lg rounded-xl overflow-hidden flex flex-col">
                {{-- Image du module --}}
                @if($module->module_image)
                    <img src="{{ asset('storage/' . $module->module_image) }}"
                         alt="Image du module"
                         class="w-full h-40 object-cover">
                @endif

                <div class="p-5 flex flex-col flex-1 justify-between">
                    <div>
                        <h2 class="text-lg font-varela text-bleuone mb-1">{{ $module->module_title }}</h2>
                        <p class="text-sm text-gray-600 font-lisible mb-3">{{ Str::limit($module->description, 100) }}</p>

                        {{-- Statut et progression --}}
                        @php
                            $status = $module->progression_status ?? 'not_started';
                            $percentage = $module->progression_percent ?? 0;

                            $badgeText = [
                                'completed' => 'Terminé',
                                'in_progress' => 'En cours',
                                'not_started' => 'Non commencé'
                            ][$status] ?? 'Indéfini';

                            $badgeColor = [
                                'completed' => 'bg-vertone text-white',
                                'in_progress' => 'bg-orangeone text-white',
                                'not_started' => 'bg-gray-200 text-gray-700'
                            ][$status] ?? 'bg-gray-200 text-gray-700';
                        @endphp

                        <div class="flex items-center justify-between mt-2">
                            <span class="text-xs font-varela px-3 py-1 rounded-full {{ $badgeColor }}">
                                {{ $badgeText }}
                            </span>
                            <span class="text-xs text-gray-500 font-lisible">
                                {{ $percentage }}%
                            </span>
                        </div>

                        <div class="w-full bg-gray-200 h-2 rounded mt-2">
                            <div class="h-2 rounded bg-vertone transition-all duration-200"
                                 style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('stagiaire.module.detail', $module->id) }}"
                        class="btn-oneduc w-full text-center">
                            Voir le module
                        </a>
                    </div>

                </div>
            </div>
        @empty
            <div class="col-span-3 text-gray-500 font-lisible text-center">
                Aucun module ne vous a encore été attribué.
            </div>
        @endforelse
    </div>

@endsection
