{{-- /home/laurents/Oneduc_Dev/resources/views/admin/backend/modules/section/add_module_lecture.blade.php --}}
@extends('admin.admin_dashboard')
@section('admin')

<div class="w-full px-6 lg:px-8">
    <div class="bg-white rounded-[20px] shadow-soft p-6 my-6 w-full border border-gray-100">
        {{-- Fil d'Ariane --}}
        <nav class="flex mb-4 text-xs font-semibold uppercase tracking-wider text-gray-400" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-2">
                <li>
                    <a href="{{ route('admin.modules') }}" class="hover:text-orangeone flex items-center">
                        <i class="ti ti-folders mr-1 text-sm"></i> Modules
                    </a>
                </li>
                <li class="flex items-center">
                    <i class="ti ti-chevron-right mx-1"></i>
                    <span class="text-bleuone">Structure : {{ $module->module_title }}</span>
                </li>
            </ol>
        </nav>

        {{-- En-tête principal --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 pb-4 mb-4">
            <div>
                <h1 class="text-[20px] font-varela text-bleuone">Configuration de l'arborescence</h1>
                <p class="text-sm text-gray-600">
                    Gérer les chapitres, les unités d'apprentissage et l'ordre des contenus pédagogiques.
                </p>
            </div>

            <a href="{{ route('admin.modules') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-100 transition text-xs font-varela cursor-pointer">
                <i class="ti ti-arrow-back-up"></i>
                Retour liste module
            </a>
        </div>

        {{-- Ajout chapitre --}}
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
            <form action="{{ route('admin.modules.section.store') }}" method="POST" class="flex flex-col gap-3 md:flex-row md:items-center">
                @csrf
                <input type="hidden" name="module_id" value="{{ $module->id }}">
                <label for="section_title" class="text-xs font-semibold uppercase tracking-wide text-bleuone md:w-56">
                    <i class="ti ti-square-plus mr-1"></i>
                    Nouveau chapitre
                </label>
                <input
                    id="section_title"
                    type="text"
                    name="section_title"
                    class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:border-orangeone focus:ring-2 focus:ring-orange-100 outline-none"
                    placeholder="Saisir le titre du chapitre..."
                    required
                >
                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-orangeone text-white text-sm font-varela rounded-lg hover:bg-orangeone-hover transition"
                >
                    <i class="ti ti-device-floppy"></i>
                    Enregistrer le chapitre
                </button>
            </form>
        </div>

        <div class="flex flex-wrap items-center gap-2 mt-4">
            <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-50 text-bleuone text-xs font-semibold">
                {{ $section->count() }} chapitre(s)
            </span>
            <span class="inline-flex items-center px-3 py-1 rounded-full bg-orange-50 text-orange-700 text-xs font-semibold">
                {{ $section->sum(fn($s) => $s->lectures->count()) }} leçon(s)
            </span>
        </div>

        {{-- Structure chapitre/leçons --}}
        <div class="space-y-5 mt-6">
            @forelse ($section as $key => $item)
                <section class="rounded-xl border border-gray-200 overflow-hidden">
                    <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between px-4 py-3 bg-gray-50 border-b border-gray-200">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="inline-flex items-center px-2 py-1 rounded-full bg-blue-50 text-bleuone text-xs font-semibold">
                                CHAP.{{ $loop->iteration }}
                            </span>
                            <h2 class="text-sm font-varela text-gray-800 truncate">
                                {{ $item->section_title }}
                            </h2>
                            <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-semibold">
                                {{ $item->lectures->count() }} leçon(s)
                            </span>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('admin.sections.edit', ['id' => $item->id]) }}"
                               class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-bleuone/20 text-bleuone hover:bg-bleuone hover:text-white transition text-xs font-varela cursor-pointer">
                                <i class="ti ti-settings"></i>
                                Modifier
                            </a>

                            <button
                                type="button"
                                onclick="addLectureDiv({{ $module->id }}, {{ $item->id }}, 'lectureContainer{{ $key }}')"
                                class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-orange-200 text-orange-700 hover:bg-orangeone hover:text-white hover:border-orangeone transition text-xs font-varela cursor-pointer"
                            >
                                <i class="ti ti-plus"></i>
                                Ajouter une leçon
                            </button>

                            <form action="{{ route('admin.sections.delete', ['id' => $item->id]) }}" method="POST"
                                  onsubmit="return confirm('Supprimer cette section ?')" class="inline-block">
                                @csrf
                                <button
                                    type="submit"
                                    class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-red-200 text-red-700 hover:bg-red-600 hover:text-white transition text-xs font-varela cursor-pointer"
                                    title="Supprimer cette section"
                                >
                                    <i class="ti ti-trash" aria-hidden="true"></i>
                                    Supprimer la section
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="table-oneduc w-full text-sm text-left text-gray-700">
                            <thead class="text-xs uppercase">
                                <tr>
                                    <th class="px-4 py-3 w-24">Ordre</th>
                                    <th class="px-4 py-3">Leçon</th>
                                    <th class="px-4 py-3 text-right w-64">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="lectureContainer{{ $key }}">
                                @forelse ($item->lectures->sortBy('position') as $lecture)
                                    <tr class="border-b border-gray-100 transition">
                                        <td class="px-4 py-3">
                                            <div class="inline-flex items-center gap-2">
                                                <span class="inline-flex items-center justify-center h-6 min-w-6 px-2 rounded-full bg-gray-100 text-gray-700 text-xs font-semibold">
                                                    {{ $loop->iteration }}
                                                </span>
                                                <span class="inline-flex items-center gap-1 text-gray-500">
                                                    <a href="{{ route('admin.lectures.move.up', $lecture->id) }}" class="hover:text-bleuone" title="Monter">
                                                        <i class="ti ti-caret-up"></i>
                                                    </a>
                                                    <a href="{{ route('admin.lectures.move.down', $lecture->id) }}" class="hover:text-bleuone" title="Descendre">
                                                        <i class="ti ti-caret-down"></i>
                                                    </a>
                                                </span>
                                            </div>
                                        </td>

                                        <td class="px-4 py-3">
                                            <p class="font-medium text-gray-900">{{ $lecture->lecture_title }}</p>
                                            <p class="text-xs text-gray-500">Leçon ID : {{ $lecture->id }}</p>
                                        </td>

                                        <td class="px-4 py-3 text-right">
                                            <div class="inline-flex items-center gap-2">
                                                <a href="{{ route('admin.lectures.edit', ['id' => $lecture->id]) }}"
                                                   class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-bleuone/20 text-bleuone hover:bg-bleuone hover:text-white transition text-xs font-varela cursor-pointer">
                                                    <i class="ti ti-edit"></i>
                                                    Editer
                                                </a>
                                                <a href="{{ route('admin.lectures.delete', ['id' => $lecture->id]) }}"
                                                   onclick="return confirm('Supprimer cette leçon ?')"
                                                   class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-red-200 text-red-700 hover:bg-red-600 hover:text-white transition text-xs font-varela cursor-pointer">
                                                    <i class="ti ti-trash"></i>
                                                    Supprimer
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-4 text-center text-gray-500 bg-gray-50">
                                            Aucune unité d'apprentissage enregistrée pour ce chapitre.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            @empty
                <div class="rounded-xl border border-dashed border-gray-300 p-8 text-center bg-gray-50">
                    <p class="text-gray-600 font-medium">Aucun chapitre n'est encore défini pour ce module.</p>
                    <p class="text-sm text-gray-500 mt-1">Utilise le formulaire ci-dessus pour créer le premier chapitre.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
    function addLectureDiv(moduleId, sectionId, containerId) {
        const container = document.getElementById(containerId);
        const row = document.createElement('tr');
        row.className = "border-b border-orange-100 bg-orange-50/70";
        row.innerHTML = `
            <td class="px-4 py-3">
                <span class="inline-flex items-center justify-center px-2 py-1 rounded-full bg-orange-100 text-orange-700 text-xs font-semibold">Nouveau</span>
            </td>
            <td class="px-4 py-3" colspan="2">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <input
                        type="text"
                        class="flex-1 px-3 py-2 border border-orange-200 rounded-lg lecture-title outline-none text-sm focus:border-orangeone focus:ring-2 focus:ring-orange-100"
                        placeholder="Saisir le titre de la nouvelle leçon..."
                    >
                    <div class="inline-flex items-center gap-2">
                        <button
                            type="button"
                            onclick="saveLecture(${moduleId}, ${sectionId}, '${containerId}', this.closest('tr'))"
                            class="inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-orangeone text-white text-xs font-varela hover:bg-orangeone-hover transition"
                        >
                            <i class="ti ti-check"></i> Valider
                        </button>
                        <button
                            type="button"
                            onclick="this.closest('tr').remove()"
                            class="inline-flex items-center gap-1 px-3 py-2 rounded-lg border border-gray-300 text-gray-700 text-xs font-varela hover:bg-gray-100 transition"
                        >
                            <i class="ti ti-x"></i> Annuler
                        </button>
                    </div>
                </div>
            </td>
        `;
        container.appendChild(row);
        row.querySelector('input').focus();
    }

    function saveLecture(moduleId, sectionId, containerId, lectureTd) {
        const input = lectureTd.querySelector('.lecture-title');
        const title = input.value.trim();
        if (!title) {
            input.focus();
            return;
        }

        fetch('{{ route('admin.modules.lecture.store') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ module_id: moduleId, section_id: sectionId, lecture_title: title }),
        })
        .then(response => response.json())
        .then(data => { if (!data.error) location.reload(); else alert(data.error); })
        .catch(error => console.error(error));
    }
</script>

@endsection
