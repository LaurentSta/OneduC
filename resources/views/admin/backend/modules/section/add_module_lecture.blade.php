@extends('admin.admin_dashboard')
@section('admin')

<div class="max-w-6xl mx-auto p-6">

    <!-- Formulaire d'ajout de section -->
    <div class="bg-white p-6 shadow rounded-lg mb-6">
        <h2 class="text-lg font-bold text-[#004461] mb-4">Ajouter une section au module : {{ $module->module_title }}</h2>
        <form action="{{ route('admin.modules.section.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="module_id" value="{{ $module->id }}">
            <label class="block text-sm font-medium text-gray-700">Titre de la section</label>
            <input type="text" name="section_title" class="w-full px-4 py-2 border rounded" placeholder="Ex : Introduction" required>
            <div class="text-right">
                <button type="submit" class="px-4 py-2 bg-orangeone text-white rounded hover:bg-orange-600 transition">Enregistrer la section</button>
            </div>
        </form>
    </div>

    <!-- Liste des sections -->
@foreach ($section as $key => $item)
<div class="bg-white shadow rounded-lg mb-6">
    <div class="flex justify-between items-center p-4 border-b">
        <h3 class="font-semibold text-gray-800">{{ $item->section_title }}</h3>
        <div class="flex gap-2">
            <!-- Modifier section -->
            <a href="{{ route('admin.sections.edit', ['id' => $item->id]) }}"
               class="bg-bleuone p-2 rounded-full hover:bg-bleuone/90 transition text-white"
               title="Modifier la section">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15.232 5.232l3.536 3.536M9 11l3 3L20.485 5.515a2.121 2.121 0 00-3-3L9 11zM5 19h14" />
                </svg>
            </a>

            <!-- Supprimer section -->
            <form action="{{ route('admin.sections.delete', ['id' => $item->id]) }}" method="POST"
                  onsubmit="return confirm('Supprimer cette section ?')">
                @csrf
                <button type="submit"
                        class="bg-orangeone p-2 rounded-full hover:bg-orangeone/90 transition text-white"
                        title="Supprimer la section">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </form>

            <!-- Ajouter lecture -->
            <button onclick="addLectureDiv({{ $module->id }}, {{ $item->id }}, 'lectureContainer{{ $key }}')"
                    class="bg-vertone p-2 rounded-full hover:bg-vertone/90 transition text-white"
                    title="Ajouter une lecture">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 4v16m8-8H4" />
                </svg>
            </button>
        </div>
    </div>
    <!-- Lectures -->
    <div class="p-4 space-y-4" id="lectureContainer{{ $key }}">
        @foreach ($item->lectures->sortBy('position') as $lecture)
        <div class="flex justify-between items-center border p-3 rounded bg-gray-50">
            <!-- Flèches à gauche -->
            <div class="flex gap-2 items-center">
                <!-- Monter -->
                <a href="{{ route('admin.lectures.move.up', $lecture->id) }}"
                   class="text-[#004166] hover:text-blue-700 transition"
                   title="Monter cette lecture">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M5 15l7-7 7 7" />
                    </svg>
                </a>
                <!-- Descendre -->
                <a href="{{ route('admin.lectures.move.down', $lecture->id) }}"
                   class="text-[#004166] hover:text-blue-700 transition"
                   title="Descendre cette lecture">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 9l-7 7-7-7" />
                    </svg>
                </a>
            </div>
            <!-- Titre de la leçon -->
            <span class="font-medium flex-1 pl-[20px]">{{ $loop->iteration }}. {{ $lecture->lecture_title }}</span>
            <!-- Actions à droite -->
            <div class="flex gap-2">
                <!-- Modifier lecture -->
                <a href="{{ route('admin.lectures.edit',['id' => $lecture->id]) }}"
                   class="bg-bleuone p-2 rounded-full hover:bg-bleuone/90 transition text-white"
                   title="Modifier la lecture">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15.232 5.232l3.536 3.536M9 11l3 3L20.485 5.515a2.121 2.121 0 00-3-3L9 11zM5 19h14" />
                    </svg>
                </a>
                <!-- Supprimer lecture -->
                <a href="{{ route('admin.lectures.delete',['id' => $lecture->id]) }}"
                   onclick="return confirm('Supprimer cette lecture ?')"
                   class="bg-orangeone p-2 rounded-full hover:bg-orangeone/90 transition text-white"
                   title="Supprimer la lecture">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endforeach
</div>
<!-- JS pour ajout de lecture -->
<script>
    function addLectureDiv(moduleId, sectionId, containerId) {
        const container = document.getElementById(containerId);
        const wrapper = document.createElement('div');
        wrapper.classList.add('bg-white', 'rounded', 'border', 'p-4', 'space-y-2', 'mt-2');

        wrapper.innerHTML = `
            <label class="block text-sm font-medium text-gray-700">Titre de la lecture</label>
            <input type="text" class="w-full px-3 py-2 border rounded lecture-title" placeholder="Ex : Introduction">

            <label class="block text-sm font-medium text-gray-700">Contenu</label>
            <textarea class="w-full px-3 py-2 border rounded lecture-content" rows="3" placeholder="Décris le contenu ici..."></textarea>

            <label class="block text-sm font-medium text-gray-700">URL vidéo</label>
            <input type="url" class="w-full px-3 py-2 border rounded lecture-url" placeholder="https://">

            <div class="flex justify-end gap-2 pt-3">
                <button onclick="saveLecture(${moduleId}, ${sectionId}, '${containerId}', this.parentNode.parentNode)"
                        class="px-4 py-1 bg-green-600 text-white rounded hover:bg-green-700">Enregistrer</button>
                <button onclick="this.closest('div').remove()"
                        class="px-4 py-1 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Annuler</button>
            </div>
        `;
        container.appendChild(wrapper);
    }

    function saveLecture(moduleId, sectionId, containerId, lectureDiv) {
        const title = lectureDiv.querySelector('.lecture-title').value;
        const content = lectureDiv.querySelector('.lecture-content').value;
        const url = lectureDiv.querySelector('.lecture-url').value;

        fetch('{{ route('admin.modules.lecture.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({
                module_id: moduleId,
                section_id: sectionId,
                lecture_title: title,
                content: content,
                lecture_url: url,
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (!data.error) {
                location.reload();
            } else {
                alert(data.error || 'Une erreur est survenue.');
            }
        })
        .catch(error => {
            console.error(error);
        });
    }
</script>

@endsection
