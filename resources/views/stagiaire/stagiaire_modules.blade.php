@extends('stagiaire.master')

@section('content')
<div class="p-6">
    <h1 class="text-titre font-raleway text-bleuone mb-6">Mes modules de formation</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($modules as $module)
            <div class="bg-white shadow-lg rounded-xl p-5 flex flex-col justify-between">
                <div>
                    <h2 class="text-lg font-varela text-orangeone mb-2">{{ $module->module_title }}</h2>
                    <p class="font-lisible text-gray-600 text-sm">{{ Str::limit($module->description, 100) }}</p>
                </div>

                <div class="mt-4">
                    <a href="{{ route('stagiaire.module.detail', $module->id) }}"
                    class="btn-oneduc w-full text-center">
                    Commencer le module
                    </a>


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
