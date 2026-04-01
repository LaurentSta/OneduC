@extends('frontend.master')
@section('home')

{{-- BLOC INTRO — Les parcours --}}
<div class="container mx-auto px-4 pt-8 pb-2"> {{-- py réduite ici --}}
    <div class="bg-white rounded-[20px] shadow-md p-8 mb-4 w-full"> {{-- my-10 ➜ mb-4 pour coller à la suite --}}
        <div class="grid grid-cols-12 gap-6 items-center">

            {{-- Texte --}}
            <div class="col-span-12 md:col-span-8">
                <x-typography variant="titre">Les parcours Onéduc</x-typography>
                <x-typography variant="sous-titre" class="font-varela text-sous-titre text-orangeone">
                    …explorer, progresser, se transformer.
                </x-typography>
                <x-typography>
                    Onéduc vous propose une diversité de parcours adaptés à vos besoins et à votre rythme.
                    Chaque parcours vous ouvre la porte vers une nouvelle compétence, une découverte ou un renforcement de vos savoirs numériques.
                </x-typography>
            </div>

            {{-- Image --}}
            <div class="col-span-12 md:col-span-4 flex justify-center md:justify-end">
                <div class="w-full max-w-xs">
                    {!! file_get_contents(public_path('images/svg/ParcoursOneduc.svg')) !!}
                </div>
            </div>

        </div>
    </div>
</div>


{{-- BLOC CARTES CATÉGORIES --}}
<div class="container mx-auto px-4 pt-2 pb-8"> {{-- py réduite ici aussi --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($categories as $category)
            <div class="bg-white rounded-2xl shadow-md overflow-hidden flex flex-col transition hover:shadow-lg">

                {{-- Image --}}
                <div class="relative group w-full h-48 overflow-hidden">
                    <img src="{{ $category->category_image_url }}"
                         alt="{{ $category->category_name }}"
                         class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                    <div class="absolute inset-0 bg-black bg-opacity-10 group-hover:bg-opacity-30 transition"></div>
                </div>

                {{-- Contenu --}}
                <div class="p-4 flex flex-col flex-grow justify-between">
                    <h5 class="text-lg font-bold text-center text-gray-800">{{ $category->category_name }}</h5>

                    @if(!empty($category->category_description))
                        <p class="text-sm text-gray-600 mt-2 line-clamp-2">
                            {{ \Illuminate\Support\Str::limit($category->category_description, 100) }}
                        </p>
                    @endif

                    {{-- Tags --}}
                    @if($category->subcategories && count($category->subcategories) > 0)
                        <div class="mt-4 flex flex-wrap justify-center gap-2">
                            @foreach($category->subcategories as $subcategory)
                                <a href="{{ route('frontend.subcategory.modules', $subcategory->id) }}"
                                   class="bg-orange-100 text-orange-700 text-xs font-medium px-3 py-1 rounded-full hover:bg-orange-200 transition">
                                    {{ $subcategory->subcategory_name }}
                                </a>
                            @endforeach
                        </div>
                    @endif

                    {{-- Bouton formations --}}
                    <div class="mt-6 text-center">
                        <a href="{{ route('frontend.category.modules', $category->id) }}"
                           class="btn-oneduc !px-4 !py-2 !text-sm">
                            Voir les formations
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@endsection
