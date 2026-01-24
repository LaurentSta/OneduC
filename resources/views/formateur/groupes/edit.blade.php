@extends('formateur.dashboard')

@section('formateur')

@php
    $selectedModuleIds = $group->modules->pluck('id')->toArray();
@endphp

<div class="w-full max-w-[1285px] mx-auto space-y-6 font-varela">
    
    {{-- EN-TÊTE --}}
    <div class="bg-white rounded-[20px] shadow-sm border border-bleuone/10 px-8 py-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <x-typography variant="titre" class="!mb-1 text-bleuone">Modifier le groupe</x-typography>
                <div class="flex items-center gap-2 mt-1">
                    <span class="px-3 py-1 bg-bleuone text-white text-xs font-bold rounded-full">{{ $group->name }}</span>
                    <span class="px-3 py-1 bg-vertone/10 text-vertone text-xs font-bold rounded-full border border-vertone/20">{{ $group->students->count() }} Stagiaires</span>
                </div>
            </div>
            
            <nav class="text-sm font-medium" aria-label="Fil d'Ariane">
                <ol class="flex items-center space-x-2 text-gray-400">
                    <li><a href="{{ route('formateur.dashboard') }}" class="hover:text-orangeone transition-colors text-xs uppercase tracking-widest">Dashboard</a></li>
                    <li><span class="text-gray-300">/</span></li>
                    <li><a href="{{ route('formateur.groupes.index') }}" class="hover:text-orangeone transition-colors text-xs uppercase tracking-widest">Groupes</a></li>
                    <li><span class="text-gray-300">/</span></li>
                    <li class="text-bleuone text-xs uppercase tracking-widest font-bold">Édition</li>
                </ol>
            </nav>
        </div>
    </div>

    <form method="POST" action="{{ route('formateur.groupes.update', $group->id) }}" class="grid grid-cols-12 gap-6">
        @csrf
        @method('PUT')

        {{-- COLONNE PRINCIPALE (Gauche) --}}
        <div class="col-span-12 lg:col-span-8 space-y-6">
            
            {{-- 1. INFORMATIONS --}}
            <div class="bg-white rounded-[20px] shadow-sm border border-gray-100 p-8">
                <div class="flex items-center gap-3 mb-6 border-b border-gray-50 pb-4">
                    <div class="w-10 h-10 rounded-lg bg-orangeone/10 flex items-center justify-center text-orangeone font-bold">1</div>
                    <h2 class="text-xl font-bold text-bleuone">Configuration Générale</h2>
                </div>
                
                <div class="space-y-5">
                    <div>
                        <label for="nom" class="block mb-2 text-sm font-bold text-gray-700 uppercase tracking-wider">Nom du groupe <span class="text-orangeone">*</span></label>
                        <input id="nom" type="text" name="nom" value="{{ old('nom', $group->name) }}" required
                               class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-orangeone focus:border-orangeone outline-none transition-all">
                    </div>
                    <div>
                        <label for="description" class="block mb-2 text-sm font-bold text-gray-700 uppercase tracking-wider">Description / Objectifs</label>
                        <textarea id="description" name="description" rows="3" 
                                  class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-orangeone focus:border-orangeone outline-none transition-all"
                                  placeholder="Ex: Formation Excel débutant - Session Printemps">{{ old('description', $group->description) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- 2. PARCOURS (Avec les encoches vertes dynamiques) --}}
            <div class="bg-white rounded-[20px] shadow-sm border border-gray-100 p-8">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-vertone/10 flex items-center justify-center text-vertone font-bold">2</div>
                        <h2 class="text-xl font-bold text-bleuone">Parcours de Formation</h2>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mb-6 italic">Cliquez sur les modules pour les activer. Une encoche verte confirme la sélection.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($modules as $module)
                        @php $checked = in_array($module->id, $selectedModuleIds); @endphp
                        <label class="relative group cursor-pointer">
                            <input type="checkbox" name="modules[]" value="{{ $module->id }}" class="sr-only peer" {{ $checked ? 'checked' : '' }}>
                            <div class="h-full p-4 rounded-xl border-2 transition-all duration-200
                                peer-checked:border-vertone peer-checked:bg-vertone/[0.03] border-gray-100 bg-white group-hover:border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-colors
                                            peer-checked:bg-vertone border-gray-300 peer-checked:border-vertone">
                                            <svg class="w-4 h-4 text-white transform scale-0 peer-checked:scale-100 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <span class="font-bold text-gray-700 peer-checked:text-vertone">{{ $module->module_title }}</span>
                                    </div>
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>

                @if($group->modules->count() > 0)
                    <div class="mt-8 p-5 bg-bleuone/[0.03] rounded-2xl border border-bleuone/10">
                        <h4 class="font-bold text-bleuone mb-4 flex items-center gap-2 text-sm uppercase tracking-wider">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Personnalisation des leçons
                        </h4>
                        <div class="grid grid-cols-1 gap-2">
                            @foreach($group->modules as $m)
                                <div class="flex items-center justify-between bg-white p-3 rounded-xl shadow-sm border border-gray-100">
                                    <span class="text-sm font-medium text-gray-600">{{ $m->module_title }}</span>
                                    <a href="{{ route('formateur.groupes.modules.lecons.edit', ['group' => $group->id, 'module' => $m->id]) }}" 
                                       class="px-4 py-1.5 bg-white border-2 border-bleuone text-bleuone text-xs font-bold rounded-lg hover:bg-bleuone hover:text-white transition-all">
                                        Gérer les leçons
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- 3. STAGIAIRES (Version Compacte En Ligne) --}}
            <div class="bg-white rounded-[20px] shadow-sm border border-gray-100 p-8">
                <div class="flex items-center gap-3 mb-6 border-b border-gray-50 pb-4">
                    <div class="w-10 h-10 rounded-lg bg-orangeone/10 flex items-center justify-center text-orangeone font-bold">3</div>
                    <h2 class="text-xl font-bold text-bleuone">Liste des Stagiaires</h2>
                </div>

                <div class="hidden md:grid grid-cols-12 gap-4 px-4 mb-2 text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                    <div class="col-span-4">Nom & Prénom</div>
                    <div class="col-span-6">Email</div>
                    <div class="col-span-2 text-right">Action</div>
                </div>

                <div class="space-y-1 mb-8">
                    @forelse ($group->students as $stagiaire)
                        <div class="student-row grid grid-cols-1 md:grid-cols-12 items-center gap-4 p-2 px-4 bg-gray-50 rounded-lg border border-gray-100 transition-all hover:bg-white hover:shadow-sm">
                            <div class="col-span-4 flex items-center gap-3">
                                <div class="w-7 h-7 shrink-0 rounded-full bg-bleuone text-white flex items-center justify-center text-[10px] font-bold uppercase">
                                    {{ substr($stagiaire->prenom, 0, 1) }}{{ substr($stagiaire->name, 0, 1) }}
                                </div>
                                <span class="font-bold text-gray-800 text-sm truncate">{{ $stagiaire->prenom }} {{ $stagiaire->name }}</span>
                            </div>
                            <div class="col-span-6">
                                <span class="text-sm text-gray-500 font-medium truncate italic">{{ $stagiaire->email }}</span>
                            </div>
                            <div class="col-span-2 text-right">
                                <button type="button" onclick="markStudentForRemoval(this)" data-student-id="{{ $stagiaire->id }}" data-student-name="{{ $stagiaire->prenom }} {{ $stagiaire->name }}"
                                        class="p-1.5 text-gray-300 hover:text-orangeone transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <p class="text-center py-6 text-gray-400 italic bg-gray-50 rounded-xl border-2 border-dashed border-gray-200 text-sm">Aucun stagiaire enregistré.</p>
                    @endforelse
                </div>

                <div id="remove-students-hidden"></div>
                
                <div class="p-6 bg-gray-50 border-2 border-dashed border-gray-200 rounded-[20px]">
                    <div id="nouveaux-stagiaires-container" class="space-y-3"></div>
                    <button type="button" onclick="ajouterStagiaire()" class="mt-4 flex items-center gap-2 text-sm font-bold text-orangeone hover:underline">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Inscrire un nouveau stagiaire
                    </button>
                </div>
            </div>
        </div>

        {{-- COLONNE RÉCAP (Droite - Sticky) --}}
        {{-- COLONNE RÉCAP (Droite - Sticky) --}}
        <div class="col-span-12 lg:col-span-4">
            <div class="sticky top-6 space-y-4">
                <div class="bg-bleuone rounded-[20px] p-8 shadow-xl text-white">
                    <h3 class="text-lg font-bold mb-6 flex items-center gap-2 text-vertone">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Résumé
                    </h3>
                    
                    <div class="space-y-4 text-sm mb-8">
                        <div class="flex justify-between border-b border-white/10 pb-2">
                            <span class="opacity-70 text-xs uppercase font-bold tracking-widest">Modules</span>
                            <span id="recap-modules" class="font-bold text-vertone">{{ count($selectedModuleIds) }} actif(s)</span>
                        </div>
                        <div id="recap-removals-box" class="hidden">
                            <span class="text-orangeone text-[10px] uppercase font-bold italic">À retirer à l'enregistrement :</span>
                            <ul id="recap-removals-list" class="mt-1 text-xs text-orangeone/80 list-disc list-inside"></ul>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-orangeone hover:bg-white hover:text-orangeone text-white py-4 rounded-xl font-bold transition-all shadow-lg active:scale-95 uppercase tracking-widest text-xs">
                        Mettre à jour le groupe
                    </button>
                    
                    <a href="{{ route('formateur.groupes.index') }}" class="block text-center mt-4 text-[10px] uppercase font-bold opacity-60 hover:opacity-100 transition-opacity tracking-widest">
                        Annuler les modifications
                    </a>
                </div>

                <div class="bg-white rounded-[20px] p-6 border border-gray-100 shadow-sm">
                    <h4 class="text-xs font-bold text-bleuone mb-2 uppercase tracking-widest">💡 Conseil</h4>
                    <p class="text-xs text-gray-500 leading-relaxed italic">
                        La personnalisation des leçons permet d'ajuster le contenu en fonction de la progression réelle de vos stagiaires.
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function ajouterStagiaire() {
        const container = document.getElementById('nouveaux-stagiaires-container');
        const index = container.querySelectorAll('.stagiaire-bloc').length;
        const html = `
            <div class="stagiaire-bloc grid grid-cols-1 md:grid-cols-3 gap-3 p-4 bg-white rounded-xl border border-gray-200 relative animate-fadeIn shadow-sm">
                <input type="text" name="stagiaires[${index}][prenom]" placeholder="Prénom" class="w-full text-xs bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 outline-none focus:border-orangeone" required>
                <input type="text" name="stagiaires[${index}][nom]" placeholder="Nom" class="w-full text-xs bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 outline-none focus:border-orangeone" required>
                <input type="email" name="stagiaires[${index}][email]" placeholder="Email" class="w-full text-xs bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 outline-none focus:border-orangeone" required>
                <button type="button" onclick="this.closest('.stagiaire-bloc').remove()" class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center text-[10px] shadow-sm">✕</button>
            </div>`;
        container.insertAdjacentHTML('beforeend', html);
    }

    function markStudentForRemoval(btn) {
        const id = btn.getAttribute('data-student-id');
        const name = btn.getAttribute('data-student-name');
        if (!confirm(`Retirer ${name} du groupe ?`)) return;

        const hiddenWrap = document.getElementById('remove-students-hidden');
        if (!hiddenWrap.querySelector(`input[value="${id}"]`)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'remove_students[]';
            input.value = id;
            hiddenWrap.appendChild(input);
        }

        const recapBox = document.getElementById('recap-removals-box');
        const recapList = document.getElementById('recap-removals-list');
        recapBox.classList.remove('hidden');
        const li = document.createElement('li');
        li.textContent = name;
        recapList.appendChild(li);

        const row = btn.closest('.student-row');
        row.style.display = 'none';
    }
</script>

<style>
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fadeIn { animation: fadeIn 0.3s ease-out forwards; }
</style>

@endsection