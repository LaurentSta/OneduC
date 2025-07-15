@extends('stagiaire.master')

@section('content')
<div class="p-6">
    <div class="bg-white rounded-[20px] shadow-md p-8 mb-6">
        <x-typography variant="titre">Mes modules de formation</x-typography>
        <x-typography variant="sous-titre" class="text-orangeone">
            Accédez à vos contenus et suivez votre progression.
        </x-typography>
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
                            <div class="h-2 rounded bg-vertone transition-all duration-500"
                                 style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>

                    <div class="mt-4">
                        @php
                            $firstSection = $module->sections->first();
                        @endphp

                        @if ($firstSection)
                            <a href="{{ route('stagiaire.module.section', ['module' => $module->id, 'section' => $firstSection->id]) }}"
                            class="btn-oneduc w-full text-center">
                                Commencer la formation
                            </a>
                        @else
                            <a href="{{ route('stagiaire.module.detail', $module->id) }}"
                            class="btn-oneduc w-full text-center">
                                Voir le module
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-3 text-gray-500 font-lisible text-center">
                Aucun module ne vous a encore été attribué.
            </div>
        @endforelse
    </div>
</div>
@endsection
