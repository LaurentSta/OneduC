@extends('frontend.master')

@section('title', 'Catalogue des formations - Onéduc')
@section('description', 'Parcourez le catalogue des formations Onéduc : modules d\'inclusion numérique, quiz et contenus pédagogiques accessibles à tous.')

@section('home')
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Barre de filtre -->
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 space-y-4 md:space-y-0">
            <p class="text-sm text-gray-700">
                Nous avons trouvé
                <span class="font-semibold text-gray-900">{{ $modules->count() }}</span>
                modules disponibles pour vous
            </p>
            <form action="{{ route('frontend.modules.index') }}" method="GET" class="w-full md:w-auto">
                <select name="category_id" class="w-full md:w-60 p-2 border border-gray-300 rounded-md shadow-sm" onchange="this.form.submit()">
                    <option value="">Toutes les catégories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        <!-- Grille des modules -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($modules as $module)
                @php
                    $moduleUrl = !empty($module->category_id)
                        ? route('frontend.modules.show', ['category' => $module->category_id, 'module' => $module->id])
                        : route('frontend.modules.show.legacy', ['module' => $module->id]);
                @endphp
                <div class="bg-white shadow rounded-lg overflow-hidden transition hover:shadow-md">
                    <a href="{{ $moduleUrl }}">
                        @if($module->module_image)
                            <img src="{{ asset('storage/' . $module->module_image) }}" alt="{{ $module->module_title }}" class="w-full h-48 object-cover">
                        @else
                            <img src="{{ asset('images/default-module.png') }}" alt="Image par défaut" class="w-full h-48 object-cover">
                        @endif
                    </a>

                    @if($module->bestseller || $module->vedette || $module->surevalue)
                        <div class="absolute top-2 left-2 space-y-1">
                            @if($module->bestseller)
                                <span class="bg-yellow-500 text-white text-xs font-semibold px-2 py-1 rounded">Bestseller</span>
                            @endif
                            @if($module->vedette)
                                <span class="bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded">À la une</span>
                            @endif
                            @if($module->surevalue)
                                <span class="bg-green-500 text-white text-xs font-semibold px-2 py-1 rounded">Valeur sûre</span>
                            @endif
                        </div>
                    @endif

                    <div class="p-4">
                        <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded mb-2">{{ $module->level ?? 'Tous niveaux' }}</span>
                        <h3 class="text-lg font-bold text-gray-900 mb-1">
                            <a href="{{ $moduleUrl }}">{{ $module->module_title }}</a>
                        </h3>
                        <div class="mb-2 space-y-1 text-sm text-gray-700">
                            <p>Auteur : <strong>Catalogue Oneduc</strong></p>
                            @if(!empty($module->formateur_id))
                                <p>Référent : <strong>{{ trim(($module->formateur?->prenom ?? '').' '.($module->formateur?->name ?? '')) }}</strong></p>
                            @endif
                        </div>
                        <div class="flex items-center justify-between">
                            <p class="text-md font-semibold text-gray-800">
                                {{ $module->is_free ? 'Gratuit' : number_format($module->price, 2, ',', ' ') . ' €' }}
                            </p>
                            <a href="{{ $moduleUrl }}" class="text-white bg-indigo-600 hover:bg-indigo-700 text-sm font-medium px-4 py-2 rounded">
                                Voir
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 col-span-3">Aucun module trouvé dans cette catégorie.</p>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-10">
            {{ $modules->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    </div>
</section>
@endsection
