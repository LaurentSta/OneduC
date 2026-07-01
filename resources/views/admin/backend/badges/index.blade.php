@extends('admin.admin_dashboard')

@section('admin')
<div class="w-full px-6 lg:px-8">
    <div class="bg-white rounded-[20px] shadow-soft p-6 my-6 w-full border border-gray-100">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 pb-4 mb-4">
            <div>
                <h1 class="text-[20px] font-varela text-bleuone">Badges</h1>
                <p class="text-sm text-gray-600">Gestion des badges et de leurs associations aux compétences.</p>
            </div>

            <a href="{{ route('admin.badges.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-orangeone text-white text-sm font-varela rounded-lg hover:bg-orangeone-hover transition cursor-pointer">
                <i class="ti ti-plus"></i>
                Créer un badge
            </a>
        </div>

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

        <div class="overflow-x-auto">
            <table class="table-oneduc w-full text-sm text-left text-gray-700">
                <thead class="text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3">Code</th>
                        <th class="px-4 py-3">Image</th>
                        <th class="px-4 py-3">Badge</th>
                        <th class="px-4 py-3">Utilisation</th>
                        <th class="px-4 py-3">Statut</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($badges as $b)
                    @php
                        $isUsed = ($b->competencies_count ?? 0) > 0;
                    @endphp

                    <tr class="border-b border-gray-100 align-top transition">
                        <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $b->code ?? '-' }}</td>

                        <td class="px-4 py-3">
                            @if(!empty($b->image_path))
                                <div class="h-14 w-14 rounded-xl border border-gray-200 bg-white flex items-center justify-center p-2">
                                    <img src="{{ asset('storage/'.$b->image_path) }}"
                                         alt="Badge {{ $b->label }}"
                                         class="max-h-full max-w-full object-contain">
                                </div>
                            @else
                                <span class="text-xs text-gray-400 italic">Aucune</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 font-semibold text-gray-900">{{ $b->label }}</td>

                        <td class="px-4 py-3 text-xs text-gray-700">
                            <div>
                                <span class="font-semibold">{{ $b->competencies_count }}</span>
                                compétence{{ $b->competencies_count > 1 ? 's' : '' }}
                            </div>

                            @if($b->competencies_count > 0)
                                <div class="mt-1 text-gray-600 space-y-0.5">
                                    @foreach($b->competencies as $c)
                                        <div class="truncate">- {{ $c->label }}</div>
                                    @endforeach

                                    @if($b->competencies_count > $b->competencies->count())
                                        <div class="italic text-gray-400">+ autres…</div>
                                    @endif
                                </div>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            @if($b->is_active)
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-green-50 text-green-700 text-xs font-bold">Actif</span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-bold">Inactif</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('admin.badges.edit', $b->id) }}"
                                   class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-bleuone/20 text-bleuone hover:bg-bleuone hover:text-white transition text-xs font-varela cursor-pointer">
                                    <i class="ti ti-pencil"></i>
                                    Éditer
                                </a>

                                <button type="button"
                                        x-data
                                        x-on:click="$dispatch('open-modal', 'delete-badge-{{ $b->id }}')"
                                        class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border text-xs font-varela
                                        {{ $isUsed
                                            ? 'border-red-200 bg-red-50 text-red-300 cursor-not-allowed'
                                            : 'border-red-200 text-red-700 hover:bg-red-600 hover:text-white transition cursor-pointer' }}"
                                        {{ $isUsed ? 'disabled aria-disabled=true' : '' }}
                                        title="{{ $isUsed
                                            ? 'Suppression impossible : badge associé à des compétences'
                                            : 'Supprimer' }}">
                                    <i class="ti ti-trash"></i>
                                    Supprimer
                                </button>
                                <x-confirm-modal
                                    name="delete-badge-{{ $b->id }}"
                                    title="Supprimer définitivement ce badge ?"
                                    :action="route('admin.badges.destroy', $b->id)"
                                    method="DELETE"
                                    confirm-label="Supprimer"
                                />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                            Aucun badge pour le moment.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $badges->links() }}
        </div>

    </div>
</div>
@endsection
