{{-- resources/views/admin/backend/competencies/index.blade.php --}}
@extends('admin.admin_dashboard')

@section('admin')
<div class="w-full px-6 lg:px-8">
    <div class="bg-white rounded-[20px] shadow-soft p-6 my-6 w-full border border-gray-100">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 pb-4 mb-4">
            <div>
                <h1 class="text-[20px] font-varela text-bleuone">Compétences</h1>
                <p class="text-sm text-gray-600">Gérer la liste des compétences, leur statut et leurs utilisations.</p>
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

        <form method="POST" action="{{ route('admin.competencies.store') }}" class="mb-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end bg-gray-50 p-5 rounded-2xl border border-gray-100">
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
                            class="w-full md:w-auto inline-flex items-center justify-center gap-2 px-4 py-3 bg-orangeone text-white text-sm font-varela rounded-lg hover:bg-orangeone-hover transition cursor-pointer">
                        <i class="ti ti-plus"></i>
                        Ajouter
                    </button>
                </div>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="table-oneduc w-full text-sm text-left text-gray-700">
                <thead class="text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3">Code</th>
                        <th class="px-4 py-3">Libellé</th>
                        <th class="px-4 py-3">Utilisation</th>
                        <th class="px-4 py-3">Statut</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($competencies as $c)
                        @php
                            $isUsed = $c->objectives()->exists() || $c->badges()->exists();
                        @endphp

                        <tr class="border-b border-gray-100 align-top transition">
                            <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $c->code ?? '-' }}</td>

                            <td class="px-4 py-3 font-semibold text-gray-900">
                                {{ $c->label }}
                                @if($isUsed)
                                    <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full bg-amber-50 text-amber-700 text-xs font-bold">
                                        Utilisée
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-xs text-gray-700">
                                <div class="space-y-2">
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
                                                           href="{{ route('admin.lecture-objectives.index', ['competency' => $c->id]) }}">Voir tout</a>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    <div class="pt-2 border-t border-gray-100">
                                        <span class="font-semibold">Badges :</span>
                                        <span class="ml-1">{{ $c->badges_count }}</span>
                                        @if($c->badges_count > 0)
                                            <div class="mt-1 text-gray-600">
                                                @foreach($c->badges as $b)
                                                    <div class="truncate">- {{ $b->label }}</div>
                                                @endforeach
                                                @if($c->badges_count > $c->badges->count())
                                                    <div class="mt-1">
                                                        <a class="text-bleuone font-semibold hover:underline"
                                                           href="{{ route('admin.badges.index', ['competency' => $c->id]) }}">Voir tout</a>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-3">
                                @if($c->is_active)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-green-50 text-green-700 text-xs font-bold">Active</span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-bold">Inactive</span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <form method="POST" action="{{ route('admin.competencies.toggle', $c->id) }}" class="inline">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-bleuone/20 text-bleuone hover:bg-bleuone hover:text-white transition text-xs font-varela cursor-pointer">
                                            <i class="ti ti-refresh"></i>
                                            {{ $c->is_active ? 'Désactiver' : 'Activer' }}
                                        </button>
                                    </form>

                                    <button type="button"
                                            x-data
                                            x-on:click="$dispatch('open-modal', 'delete-competency-{{ $c->id }}')"
                                            class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border text-xs font-varela
                                                   {{ $isUsed
                                                        ? 'border-red-200 bg-red-50 text-red-300 cursor-not-allowed'
                                                        : 'border-red-200 text-red-700 hover:bg-red-600 hover:text-white transition cursor-pointer' }}"
                                            {{ $isUsed ? 'disabled aria-disabled=true' : '' }}
                                            title="{{ $isUsed ? 'Suppression impossible : compétence déjà utilisée' : 'Supprimer' }}">
                                        <i class="ti ti-trash"></i>
                                        Supprimer
                                    </button>
                                    <x-confirm-modal
                                        name="delete-competency-{{ $c->id }}"
                                        title="Supprimer définitivement cette compétence ?"
                                        :action="route('admin.competencies.destroy', $c->id)"
                                        method="DELETE"
                                        confirm-label="Supprimer"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-gray-500">
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
