@extends('admin.admin_dashboard')

@section('admin')
<div class="max-w-6xl mx-auto mt-8 px-4">
    <div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100">

        {{-- En-tête --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-2xl font-extrabold text-bleuone">Badges</h2>
                <p class="text-sm text-gray-500 font-medium">
                    Gestion des badges et de leurs associations aux compétences.
                </p>
            </div>

            <a href="{{ route('admin.badges.create') }}"
               class="inline-flex items-center gap-2 px-5 py-3 rounded-xl
                      bg-orangeone text-white font-semibold hover:opacity-90">
                Créer un badge
            </a>
        </div>

        {{-- Messages --}}
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-100 text-green-700 text-sm font-semibold">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm font-semibold">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Tableau --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="py-3 pr-4">Code</th>
                        <th class="py-3 pr-4">Badge</th>
                        <th class="py-3 pr-4">Utilisation</th>
                        <th class="py-3 pr-4">Statut</th>
                        <th class="py-3 pr-4 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($badges as $b)
                    @php
                        $isUsed = ($b->competencies_count ?? 0) > 0;
                    @endphp

                    <tr class="border-b align-top">
                        {{-- Code --}}
                        <td class="py-4 pr-4 font-mono text-xs text-gray-600">
                            {{ $b->code ?? '-' }}
                        </td>

                        {{-- Label --}}
                        <td class="py-4 pr-4 font-semibold text-gray-900">
                            {{ $b->label }}
                        </td>

                        {{-- Utilisation --}}
                        <td class="py-4 pr-4 text-xs text-gray-700">
                            <div>
                                <span class="font-semibold">
                                    {{ $b->competencies_count }}
                                </span>
                                compétence{{ $b->competencies_count > 1 ? 's' : '' }}
                            </div>

                            @if($b->competencies_count > 0)
                                <div class="mt-1 text-gray-600 space-y-0.5">
                                    @foreach($b->competencies as $c)
                                        <div class="truncate">– {{ $c->label }}</div>
                                    @endforeach

                                    @if($b->competencies_count > $b->competencies->count())
                                        <div class="italic text-gray-400">
                                            + autres…
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </td>

                        {{-- Statut --}}
                        <td class="py-4 pr-4">
                            @if($b->is_active)
                                <span class="inline-flex items-center px-3 py-1 rounded-full
                                             bg-green-50 text-green-700 text-xs font-bold">
                                    Actif
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full
                                             bg-gray-100 text-gray-600 text-xs font-bold">
                                    Inactif
                                </span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="py-4 pr-4 text-right space-x-2">
                            <a href="{{ route('admin.badges.edit', $b->id) }}"
                               class="inline-flex items-center px-4 py-2 rounded-xl
                                      bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200">
                                Éditer
                            </a>

                            <form method="POST"
                                  action="{{ route('admin.badges.destroy', $b->id) }}"
                                  class="inline"
                                  onsubmit="return confirm('Supprimer définitivement ce badge ?');">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="px-4 py-2 rounded-xl font-semibold
                                        {{ $isUsed
                                            ? 'bg-red-50 text-red-300 cursor-not-allowed'
                                            : 'bg-red-100 text-red-700 hover:bg-red-200' }}"
                                        {{ $isUsed ? 'disabled aria-disabled=true' : '' }}
                                        title="{{ $isUsed
                                            ? 'Suppression impossible : badge associé à des compétences'
                                            : 'Supprimer' }}">
                                    Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center text-gray-500">
                            Aucun badge pour le moment.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $badges->links() }}
        </div>

    </div>
</div>
@endsection
