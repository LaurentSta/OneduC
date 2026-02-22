@extends('frontend.master')

@section('home')
<div class="container mx-auto px-4 py-8">

    <!-- Titre -->
    <div class="text-center mb-10">
        <x-typography variant="titre">Sous-catégories de {{ $category->category_name }}</x-typography>
        <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
            Découvrez les formations proposées
        </x-typography>
    </div>

    <!-- Grille -->
    <div class="space-y-12">
        @forelse($subcategories as $subcategory)
            <div class="bg-white rounded-[20px] shadow-md overflow-hidden p-6">
                <div class="flex flex-col md:flex-row gap-6">
                    @if($subcategory->subcategory_image)
                        <img src="{{ asset('storage/'.$subcategory->subcategory_image) }}"
                             alt="{{ $subcategory->subcategory_name }}"
                             class="w-full md:w-64 h-40 object-cover rounded-lg">
                    @endif

                    <div class="flex-1">
                        <h3 class="text-xl font-semibold text-gray-800">{{ $subcategory->subcategory_name }}</h3>
                        <p class="text-gray-600 mt-2">{{ $subcategory->subcategory_description }}</p>
                    </div>
                </div>

                <!-- Modules associés -->
                @if($subcategory->modules->count())
                    <div class="mt-6">
                        <h4 class="text-lg font-bold text-gray-700 mb-3">Formations disponibles :</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($subcategory->modules as $module)
                                @php
                                    $moduleUrl = !empty($module->category_id)
                                        ? route('frontend.modules.show', ['category' => $module->category_id, 'module' => $module->id])
                                        : route('frontend.modules.show.legacy', ['module' => $module->id]);
                                @endphp
                                <div class="bg-gray-50 border rounded-lg p-4 hover:shadow-md transition">
                                    <h5 class="font-semibold text-gray-800 text-base">
                                        <a href="{{ $moduleUrl }}" class="hover:text-orangeone">
                                            {{ $module->module_title }}
                                        </a>
                                    </h5>
                                    <p class="text-sm text-gray-600 mt-1">
                                        {{ Str::limit($module->description, 80) }}
                                    </p>
                                    <div class="mt-2 text-sm text-gray-500">
                                        {{ $module->is_free ? 'Gratuit' : number_format($module->price, 2, ',', ' ') . ' €' }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <p class="mt-4 text-sm italic text-gray-500">Aucune formation pour cette sous-catégorie.</p>
                @endif
            </div>
        @empty
            <p class="text-center text-gray-500">Aucune sous-catégorie disponible pour le moment.</p>
        @endforelse
    </div>

</div>
@endsection
