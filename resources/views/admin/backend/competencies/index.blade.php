{{-- resources/views/admin/backend/competencies/index.blade.php --}}
@extends('admin.admin_dashboard')

@section('admin')
<div class="max-w-6xl mx-auto mt-8 px-4">
    <div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100">

        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-2xl font-extrabold text-bleuone">Compétences</h2>
                <p class="text-sm text-gray-500 font-medium">Gérer la liste des compétences (activation, ajout).</p>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-100 text-green-700 text-sm font-semibold">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm font-semibold">
                <div class="mb-2">Certaines informations sont incorrectes :</div>
                <ul class="list-disc ml-5 space-y-1 font-normal">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Création --}}
        <form method="POST" action="{{ route('admin.competencies.store') }}" class="mb-8">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end bg-gray-50 p-5 rounded-2xl">
                <div class="md:col-span-3">
                    <label class="block text-xs font-extrabold text-bleuone uppercase ml-1">Code</label>
                    <input type="text" name="code" value="{{ old('code') }}"
                           class="mt-2 w-full px-4 py-3 bg-white border border-gray-200 rounded-xl font-semibold"
                           maxlength="50" placeholder="EXCEL_001">
                </div>

                <div class="md:col-span-7">
                    <label class="block text-xs font-extrabold text-bleuone uppercase ml-1">Libellé</label>
                    <input type="text" name="label" value="{{ old('label') }}"
                           class="mt-2 w-full px-4 py-3 bg-white border border-gray-200 rounded-xl font-semibold"
                           maxlength="255" required placeholder="Saisir et sélectionner des cellules">
                </div>

                <div class="md:col-span-2 flex md:justify-end">
                    <button type="submit"
                            class="w-full md:w-auto px-6 py-3 rounded-xl bg-orangeone text-white font-semibold hover:opacity-90">
                        Ajouter
                    </button>
                </div>
            </div>
        </form>

        {{-- Tableau --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="py-3 pr-4">Code</th>
                        <th class="py-3 pr-4">Libellé</th>
                        <th class="py-3 pr-4">Utilisation</th>
                        <th class="py-3 pr-4">Statut</th>
                        <th class="py-3 pr-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
@forelse($competencies as $c)
    @php
        $isUsed = $c->objectives()->exists() || $c->badges()->exists();
    @endphp


    <tr class="border-b">
        <td class="py-4 pr-4 font-mono text-xs text-gray-700">
            {{ $c->code ?? '-' }}
        </td>

        <td class="py-4 pr-4 font-semibold text-gray-900">
            {{ $c->label }}
            @if($isUsed)
                <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full bg-amber-50 text-amber-700 text-xs font-bold">
                    Utilisée
                </span>
            @endif
        </td>
        <td class="py-4 pr-4 text-xs text-gray-700">
    <div class="space-y-1">
        <div>
            <span class="font-semibold">Objectifs :</span>
            <span class="ml-1">{{ $c->objectives_count }}</span>

            @if($c->objectives_count > 0)
                <div class="mt-1 text-gray-600">
                    @foreach($c->objectives as $o)
                        <div class="truncate">- {{ $o->title }}</div>
                    @endforeach

                    @if($c->objectives_count > $c->objectives->count())
                        <div class="mt-1">
                            <a class="text-bleuone font-semibold hover:underline"
                               href="{{ route('admin.lecture-objectives.index', ['competency' => $c->id]) }}">
                                Voir tout
                            </a>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <div class="pt-1 border-t border-gray-100">
            <span class="font-semibold">Badges :</span>
            <span class="ml-1">{{ $c->badges_count }}</span>

            @if($c->badges_count > 0)
                <div class="mt-1 text-gray-600">
                    @foreach($c->badges as $b)
                        <div class="truncate">- {{ $b->label }}
</div>
                    @endforeach

                    @if($c->badges_count > $c->badges->count())
                        <div class="mt-1">
                            <a class="text-bleuone font-semibold hover:underline"
                               href="{{ route('admin.badges.index', ['competency' => $c->id]) }}">
                                Voir tout
                            </a>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</td>

        <td class="py-4 pr-4">
            @if($c->is_active)
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-green-50 text-green-700 text-xs font-bold">
                    Active
                </span>
            @else
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-bold">
                    Inactive
                </span>
            @endif
        </td>

        <td class="py-4 pr-4 text-right space-x-2">
            {{-- Activer / Désactiver --}}
            <form method="POST" action="{{ route('admin.competencies.toggle', $c->id) }}" class="inline">
                @csrf
                <button type="submit"
                        class="px-4 py-2 rounded-xl bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200">
                    {{ $c->is_active ? 'Désactiver' : 'Activer' }}
                </button>
            </form>

            {{-- Supprimer (bloqué si utilisé) --}}
            <form method="POST"
                  action="{{ route('admin.competencies.destroy', $c->id) }}"
                  class="inline"
                  onsubmit="return confirm('Supprimer définitivement cette compétence ?');">
                @csrf
                @method('DELETE')

                <button type="submit"
                        class="px-4 py-2 rounded-xl font-semibold
                               {{ $isUsed
                                    ? 'bg-red-50 text-red-300 cursor-not-allowed'
                                    : 'bg-red-100 text-red-700 hover:bg-red-200' }}"
                        {{ $isUsed ? 'disabled aria-disabled=true' : '' }}
                        title="{{ $isUsed ? 'Suppression impossible : compétence déjà utilisée' : 'Supprimer' }}">
                    Supprimer
                </button>
            </form>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="4" class="py-10 text-center text-gray-500">
            Aucune compétence pour le moment.
        </td>
    </tr>
@endforelse
</tbody>

            </table>
        </div>

        <div class="mt-6">
            {{ $competencies->links() }}
        </div>

    </div>
</div>
@endsection
