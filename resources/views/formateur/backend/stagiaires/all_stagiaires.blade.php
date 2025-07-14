@extends('formateur.dashboard')
@section('formateur')

<div class="max-w-7xl mx-auto py-10 px-6">

    <h2 class="text-3xl font-bold text-[#004461] mb-6">Mes stagiaires</h2>

    <!-- 🔎 Barre de recherche -->
    <form method="GET" class="mb-6 flex flex-wrap items-center gap-3">
        <input type="text"
               name="search"
               value="{{ request('search') }}"
               placeholder="Recherche prénom, nom ou email"
               class="w-full md:w-1/2 px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-[#E94D2A] focus:border-[#E94D2A] text-sm"
        >
        <button type="submit"
                class="bg-[#E94D2A] hover:bg-orange-600 text-white px-5 py-2 rounded-md text-sm transition">
            Rechercher
        </button>
    </form>

    <!-- 📋 Tableau des stagiaires -->
    <div class="overflow-x-auto bg-white shadow-md rounded-lg">
        <table class="min-w-full text-sm text-left text-gray-800">
            <thead class="bg-gray-100 uppercase text-xs text-gray-600">
                <tr>
                    <th class="px-6 py-3">#</th>
                    <th class="px-6 py-3">Prénom</th>
                    <th class="px-6 py-3">Nom</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3">Code d'accès</th>
                    <th class="px-6 py-3">Groupe(s)</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($stagiaires as $index => $stagiaire)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium">{{ $stagiaires->firstItem() + $index }}</td>
                        <td class="px-6 py-4">{{ $stagiaire->prenom }}</td>
                        <td class="px-6 py-4">{{ $stagiaire->name }}</td>
                        <td class="px-6 py-4">{{ $stagiaire->email }}</td>
                        <td class="px-6 py-4 font-mono text-sm text-orange-700">
                            {{ $stagiaire->code_acces ?? '—' }}
                        </td>
                        <td class="px-6 py-4">
                            @forelse ($stagiaire->groupesStagiaire as $groupe)
                                <span class="inline-block bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded mr-1">
                                    {{ $groupe->name }}
                                </span>
                            @empty
                                <span class="text-gray-400 text-xs italic">Aucun</span>
                            @endforelse
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex gap-2">
                                <!-- Modifier -->
                                <a href="{{ route('formateur.stagiaires.edit', $stagiaire->id) }}"
                                   class="px-3 py-1 bg-[#E94D2A] text-white text-xs rounded hover:bg-orange-700 transition">
                                    Modifier
                                </a>

                                <!-- Supprimer -->
                                <form action="{{ route('formateur.stagiaires.destroy', $stagiaire->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Supprimer ce stagiaire ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="px-3 py-1 bg-[#004461] text-white text-xs rounded hover:bg-blue-700 transition">
                                        Supprimer
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">Aucun stagiaire trouvé.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- 📄 Pagination -->
    <div class="mt-6">
        {{ $stagiaires->links('pagination::tailwind') }}
    </div>
</div>

@endsection
