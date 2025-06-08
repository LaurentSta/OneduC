@extends('formateur.dashboard')

@section('formateur')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h2 class="text-3xl font-bold mb-6 text-gray-800">Mes groupes</h2>

    @if (session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if ($groupes->isEmpty())
        <p class="text-gray-500">Vous n’avez encore créé aucun groupe.</p>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($groupes as $groupe)
                <div class="bg-white border border-gray-200 rounded-2xl shadow p-5 flex flex-col justify-between h-full">
                    <div>
                        <h3 class="text-xl font-semibold text-blue-700 mb-1">{{ $groupe->name }}</h3>
                        <p class="text-gray-600 text-sm mb-4">{{ Str::limit($groupe->description, 120) }}</p>

                        <div class="mb-3">
                            <h4 class="text-sm font-medium text-gray-500">Modules associés :</h4>
                            <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                                @forelse ($groupe->modules as $module)
                                <li>
                                    <a href="{{ route('frontend.modules.show', $module->id) }}" class="text-blue-500 hover:underline">
                                        {{ $module->module_title }}
                                    </a>
                                </li>
                                @empty
                                    <li class="text-gray-400 italic">Aucun module</li>
                                @endforelse
                            </ul>
                        </div>

                        <div class="mb-2">
                            <h4 class="text-sm font-medium text-gray-500">Stagiaires :</h4>
                            <p class="text-gray-700 text-sm">
                                {{ $groupe->students->count() }} stagiaire{{ $groupe->students->count() > 1 ? 's' : '' }}
                            </p>
                        </div>
                    </div>
                    <form action="{{ route('formateur.groupes.destroy', $groupe->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce groupe ?');" class="ml-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md transition">
                            🗑️ Supprimer
                        </button>
                    </form>


                    <div class="mt-4 flex justify-end">
                        <a href="{{ route('formateur.groupes.edit', $groupe->id) }}"
                           class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition">
                            ✏️ Modifier
                        </a>
                    </div>
                </div>
            @endforeach


        </div>
    @endif
    <!-- Bloc Ajouter un groupe -->
                <a href="{{ route('formateur.groupes.create') }}"
                class="flex flex-col items-center justify-center border-2 border-dashed border-[#E94D2A] rounded-2xl p-10 h-full text-[#E94D2A] hover:bg-[#E94D2A] hover:text-white transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <div class="mt-4 text-lg font-semibold">Ajouter un groupe</div>
                </a>
</div>
@endsection
