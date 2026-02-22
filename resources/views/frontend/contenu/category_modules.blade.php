@extends('frontend.master')

@section('home')
<div class="container mx-auto px-4 pt-8 pb-4">
    <div class="bg-white rounded-[20px] shadow-md p-8 mb-6 w-full">
        <div class="text-center">
            <x-typography variant="titre">"{{ $category->category_name }}"</x-typography>
            <x-typography class="text-gray-600 mt-2">Découvrez les formations disponibles dans ce parcours.</x-typography>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($modules as $module)
            <div class="bg-white rounded-2xl shadow-md overflow-hidden flex flex-col transition hover:shadow-lg">
                {{-- Image --}}
                @if(!empty($module->module_image))
                    <div class="relative group w-full h-48 overflow-hidden">
                        <img src="{{ asset('storage/'.$module->module_image) }}"
                            alt="{{ $module->module_title }}"
                            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                        <div class="absolute inset-0 bg-black bg-opacity-10 group-hover:bg-opacity-30 transition"></div>
                    </div>
                @endif


                {{-- Contenu --}}
                <div class="p-4 flex flex-col flex-grow justify-between">
                    <h5 class="text-lg font-bold text-center text-gray-800">{{ $module->module_title }}</h5>


                    @if(!empty($module->description))
                        <p class="text-sm text-gray-600 mt-2 line-clamp-3">
                            {{ \Illuminate\Support\Str::limit($module->description, 120) }}
                        </p>
                    @endif

                    {{-- Bouton --}}
                    <div class="mt-6 text-center">
                        @php
                            $moduleUrl = !empty($module->category_id)
                                ? route('frontend.modules.show', ['category' => $module->category_id, 'module' => $module->id])
                                : route('frontend.modules.show.legacy', ['module' => $module->id]);
                        @endphp
                        <a href="{{ $moduleUrl }}"
                           class="inline-block px-4 py-2 bg-orangeone text-white text-sm font-medium rounded hover:bg-orange-600 transition">
                            Voir le module
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-12 text-center text-gray-500">
                Aucun module disponible pour cette catégorie.
            </div>
        @endforelse
    </div>
</div>
@endsection
