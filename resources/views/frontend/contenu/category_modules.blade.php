@extends('frontend.master')

@section('home')
<div class="container mx-auto px-4 pt-8 pb-4">
    <div class="bg-white rounded-[20px] shadow-md p-8 mb-6 w-full">
        <div class="text-center">
            <x-typography variant="titre">"{{ $category->category_name }}"</x-typography>
            <x-typography class="text-gray-600 mt-2">Decouvrez les formations disponibles dans ce parcours et consultez leur presentation publique avant connexion.</x-typography>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($modules as $module)
            @php
                $moduleUrl = !empty($module->category_id)
                    ? route('frontend.modules.show', ['category' => $module->category_id, 'module' => $module->id])
                    : route('frontend.modules.show.legacy', ['module' => $module->id]);

                $visual = $module->header_image ?: $module->module_image;
            @endphp
            <div class="bg-white rounded-2xl shadow-md overflow-hidden flex flex-col transition hover:shadow-lg">
                @if(!empty($visual))
                    <div class="relative group w-full h-48 overflow-hidden">
                        <img src="{{ asset('storage/' . $visual) }}"
                            alt="{{ $module->module_title }}"
                            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                        <div class="absolute inset-0 bg-black bg-opacity-10 group-hover:bg-opacity-30 transition"></div>
                    </div>
                @else
                    <div class="h-48 bg-slate-900 flex items-center justify-center">
                        <img src="{{ asset('images/svg/Modules.svg') }}" alt="" class="w-20 opacity-30">
                    </div>
                @endif

                <div class="p-4 flex flex-col flex-grow justify-between">
                    <div class="space-y-3">
                        <span class="inline-flex items-center rounded-full bg-orange-50 px-3 py-1 text-xs font-semibold text-orangeone">
                            Apercu public du module
                        </span>
                        <h5 class="text-lg font-bold text-gray-800">{{ $module->module_title }}</h5>

                        <div class="flex flex-wrap gap-3 text-xs text-gray-500">
                            @if(!empty($module->formateur?->name))
                                <span>Formateur : {{ $module->formateur->name }}</span>
                            @endif
                            @if(!empty($module->duree))
                                <span>Duree : {{ $module->duree }}</span>
                            @endif
                            @if(!empty($module->certificat))
                                <span>Certificat</span>
                            @endif
                        </div>
                    </div>

                    @if(!empty($module->description))
                        <p class="text-sm text-gray-600 mt-2 line-clamp-3">
                            {{ \Illuminate\Support\Str::limit($module->description, 120) }}
                        </p>
                    @endif

                    <div class="mt-6 text-center">
                        <a href="{{ $moduleUrl }}"
                           class="inline-block px-4 py-2 bg-orangeone text-white text-sm font-medium rounded hover:bg-orange-600 transition">
                            Decouvrir le module
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-12 text-center text-gray-500">
                Aucun module disponible pour cette categorie.
            </div>
        @endforelse
    </div>
</div>
@endsection
