{{-- /home/laurents/Oneduc_Dev/resources/views/admin/backend/modules/section/add_module_lecture.blade.php --}}
@extends('admin.admin_dashboard')
@section('admin')

<div class="max-w-6xl mx-auto p-6 font-sans">
    
    {{-- 1. Fil d'Ariane Administratif --}}
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

    {{-- En-tête --}}
    <div class="flex justify-between items-end mb-6 border-b-2 border-bleuone pb-2">
        <div>
            <h1 class="text-xl font-bold text-bleuone uppercase tracking-tight">Configuration de l'arborescence</h1>
            <p class="text-gray-500 text-[10px] italic">Édition technique des chapitres et des unités d'apprentissage</p>
        </div>
        <a href="{{ route('admin.modules') }}" class="text-[10px] font-bold bg-gray-100 px-3 py-1 rounded border border-gray-300 hover:bg-gray-200 transition uppercase">
            <i class="ti ti-arrow-back-up"></i> Retour liste Module
        </a>
    </div>

    {{-- 2. Ajout de Section (Compact) --}}
    <div class="bg-gray-50 p-3 border border-gray-200 rounded mb-6">
        <form action="{{ route('admin.modules.section.store') }}" method="POST" class="flex gap-3 items-center">
            @csrf
            <input type="hidden" name="module_id" value="{{ $module->id }}">
            <span class="text-[11px] font-bold text-bleuone uppercase whitespace-nowrap"><i class="ti ti-square-plus"></i> Nouveau Chapitre :</span>
            <input type="text" name="section_title" 
                   class="flex-1 px-3 py-1.5 text-sm border border-gray-300 rounded focus:border-bleuone outline-none shadow-sm" 
                   placeholder="Saisir le titre du chapitre..." required>
            <button type="submit" class="px-4 py-1.5 bg-bleuone text-white text-[11px] font-bold rounded hover:bg-opacity-90 shadow-sm transition uppercase">
                Enregistrer le chapitre
            </button>
        </form>
    </div>

    {{-- 3. Structure Technique --}}
    <div class="space-y-6">
        @foreach ($section as $key => $item)
        <div class="border border-gray-300 rounded overflow-hidden shadow-sm">
            
            {{-- Entête de Section --}}
            <div class="flex justify-between items-center px-3 py-2 bg-gray-200 border-b border-gray-300">
                <div class="flex items-center gap-2">
                    <span class="font-mono text-[11px] font-bold text-gray-600">CHAP.{{ $loop->iteration }}</span>
                    <h3 class="font-bold text-bleuone text-sm uppercase tracking-wide">{{ $item->section_title }}</h3>
                </div>
                
                <div class="flex items-center gap-1">
                    {{-- Bouton Modifier Section --}}
                    <a href="{{ route('admin.sections.edit', ['id' => $item->id]) }}"
                       class="px-3 py-1 bg-white border border-gray-400 text-[10px] font-bold text-gray-700 rounded hover:bg-bleuone hover:text-white transition uppercase">
                        <i class="ti ti-settings"></i> Modifier la section
                    </a>

                    {{-- Bouton Ajouter Leçon --}}
                    <button onclick="addLectureDiv({{ $module->id }}, {{ $item->id }}, 'lectureContainer{{ $key }}')"
                            class="px-3 py-1 bg-white border border-gray-400 text-[10px] font-bold text-vertone rounded hover:bg-vertone hover:text-white transition uppercase">
                        <i class="ti ti-plus"></i> Ajouter une leçon
                    </button>

                    <form action="{{ route('admin.sections.delete', ['id' => $item->id]) }}" method="POST"
                          onsubmit="return confirm('Supprimer cette section ?')" class="inline ml-1">
                        @csrf
                        <button type="submit" class="p-1 text-gray-400 hover:text-red-700 transition" title="Supprimer">
                            <i class="ti ti-trash"></i>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Table des Lectures --}}
            <div class="bg-white">
                <table class="w-full text-[12px]">
                    <tbody id="lectureContainer{{ $key }}">
                        @forelse ($item->lectures->sortBy('position') as $lecture)
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                            {{-- Ordonnancement --}}
                            <td class="w-10 px-2 py-1 border-r border-gray-100 text-center">
                                <div class="flex flex-col text-gray-400 text-[10px]">
                                    <a href="{{ route('admin.lectures.move.up', $lecture->id) }}" class="hover:text-bleuone" title="Monter"><i class="ti ti-caret-up"></i></a>
                                    <a href="{{ route('admin.lectures.move.down', $lecture->id) }}" class="hover:text-bleuone" title="Descendre"><i class="ti ti-caret-down"></i></a>
                                </div>
                            </td>
                            {{-- Titre --}}
                            <td class="px-4 py-1.5 font-medium text-gray-800">
                                <span class="text-gray-400 mr-2 font-mono text-[10px]">{{ $loop->iteration }}.</span> {{ $lecture->lecture_title }}
                            </td>
                            {{-- Actions (Fixes) --}}
                            <td class="px-4 py-1.5 text-right w-48">
                                <div class="inline-flex gap-3">
                                    <a href="{{ route('admin.lectures.edit',['id' => $lecture->id]) }}"
                                       class="text-bleuone hover:text-bleuone/80 font-bold uppercase text-[10px] flex items-center gap-1">
                                        <i class="ti ti-edit"></i> Éditer
                                    </a>
                                    <a href="{{ route('admin.lectures.delete',['id' => $lecture->id]) }}"
                                       onclick="return confirm('Supprimer cette leçon ?')"
                                       class="text-red-600 hover:text-red-700 font-bold uppercase text-[10px] flex items-center gap-1">
                                        <i class="ti ti-trash"></i> Supprimer
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-center text-gray-400 italic bg-gray-50 text-[11px]">Aucune unité d'apprentissage enregistrée</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>
</div>

<script>
    function addLectureDiv(moduleId, sectionId, containerId) {
        const container = document.getElementById(containerId);
        const row = document.createElement('tr');
        row.className = "bg-orangeone/5 border-b border-orangeone/20";
        row.innerHTML = `
            <td class="w-10 border-r border-orangeone/10"></td>
            <td class="px-4 py-2" colspan="2">
                <div class="flex gap-2 items-center">
                    <input type="text" class="flex-1 px-2 py-1 border border-orangeone/30 rounded lecture-title outline-none text-[12px]" placeholder="Saisir le titre de la nouvelle leçon...">
                    <button onclick="saveLecture(${moduleId}, ${sectionId}, '${containerId}', this.parentNode.parentNode.parentNode)"
                            class="px-3 py-1 bg-orangeone text-white text-[10px] font-bold rounded uppercase">Valider</button>
                    <button onclick="this.closest('tr').remove()"
                            class="px-3 py-1 bg-gray-500 text-white text-[10px] font-bold rounded uppercase">Annuler</button>
                </div>
            </td>
        `;
        container.appendChild(row);
        row.querySelector('input').focus();
    }

    function saveLecture(moduleId, sectionId, containerId, lectureTd) {
        const title = lectureTd.querySelector('.lecture-title').value;
        if(!title) return;

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