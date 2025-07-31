@extends('stagiaire.master')

@section('content')

<!-- En-tête avec image -->
@if($module->header_image)
    <div class="relative w-full h-[300px] overflow-hidden">
        <img src="{{ asset('storage/' . $module->header_image) }}"
             alt="Header du module"
             class="absolute inset-0 w-full h-full object-cover">

        <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center">
            <div class="w-full max-w-[1248px] mx-auto px-4">
                <h1 class="text-[48px] font-raleway text-white uppercase mb-6">
                    {{ $module->module_name }}
                </h1>

                <nav class="text-sm text-gray-300 mb-6 space-x-2">
                    <a href="{{ url('/') }}" class="hover:text-white">Accueil</a> /
                    <a href="#" class="hover:text-white">Catégories</a> /
                    <span class="text-white">{{ $module->module_title ?? 'Module' }}</span>
                </nav>

                <p class="text-sm text-gray-300">Formateur :
                    <span class="font-medium text-white">{{ $module->formateur->name ?? 'Non défini' }}</span>
                </p>
            </div>
        </div>
    </div>
@endif

<!-- Bloc descriptif avec vidéo alignée à droite -->
<section class="bg-white py-8">
    <div class="max-w-[1248px] mx-auto px-4">
        <div class="flex flex-col lg:flex-row gap-8 items-start">

            <div x-data="{ active: 'presentation' }" class="lg:w-2/3">
    <!-- Tabs navigation -->
    <div class="flex gap-2 border-b border-gray-200 mb-4">
        <button @click="active = 'presentation'"
                class="px-4 py-2 text-sm font-medium border-b-2"
                :class="active === 'presentation' ? 'border-orangeone text-orangeone' : 'border-transparent text-gray-500 hover:text-gray-700'">
            Présentation
        </button>
        <button @click="active = 'objectifs'"
                class="px-4 py-2 text-sm font-medium border-b-2"
                :class="active === 'objectifs' ? 'border-orangeone text-orangeone' : 'border-transparent text-gray-500 hover:text-gray-700'">
            Objectifs
        </button>
        <button @click="active = 'prerequis'"
                class="px-4 py-2 text-sm font-medium border-b-2"
                :class="active === 'prerequis' ? 'border-orangeone text-orangeone' : 'border-transparent text-gray-500 hover:text-gray-700'">
            Prérequis
        </button>
    </div>

    <!-- Présentation -->
    <div x-show="active === 'presentation'" class="space-y-4" x-cloak>
        <h2 class="text-2xl font-bold font-varela text-orangeone">Présentation</h2>
        <p class="text-[18px] leading-relaxed font-lisible text-gray-800">
            {{ $module->description }}
        </p>

        <div class="flex flex-wrap items-center gap-2 mt-2">
            @if($module->bestseller)
                <span class="bg-green-600 text-white text-xs font-semibold px-2 py-1 rounded">Bestseller</span>
            @endif
            @if($module->vedette)
                <span class="bg-blue-600 text-white text-xs font-semibold px-2 py-1 rounded">À la Une</span>
            @endif
            @if($module->surevalue)
                <span class="bg-yellow-500 text-white text-xs font-semibold px-2 py-1 rounded">Valeur sûre</span>
            @endif
        </div>
    </div>

    <!-- Objectifs -->
    <div x-show="active === 'objectifs'" class="space-y-4" x-cloak>
        <h2 class="text-2xl font-bold font-varela text-orangeone">Ce que vous allez apprendre</h2>
        <ul class="list-disc list-inside text-gray-800 text-sm space-y-1">
            <li>Comprendre les principes de base du module</li>
            <li>Appliquer les méthodes dans un contexte réel</li>
            <li>Développer des compétences ciblées</li>
            <li>S'autoévaluer en fin de parcours</li>
        </ul>
    </div>

    <!-- Prérequis -->
    <div x-show="active === 'prerequis'" class="space-y-4" x-cloak>
        <h2 class="text-2xl font-bold font-varela text-orangeone">Prérequis</h2>
        @if(!empty($module->prerequi))
            <ul class="list-disc list-inside text-gray-800 text-sm space-y-1">
                @foreach(explode("\n", $module->prerequi) as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ul>
        @else
            <p class="italic text-gray-500">Aucun prérequis spécifié pour ce module.</p>
        @endif
    </div>
</div>


            {{-- Vidéo ou image à droite --}}
            @php use Illuminate\Support\Str; @endphp

            <div class="lg:w-1/3 w-full">
                <div class="rounded shadow overflow-hidden">
                    @if($module->video && (Str::contains($module->video, 'youtube.com') || Str::contains($module->video, 'youtu.be')))
                        <div class="aspect-w-16 aspect-h-9">
                            <iframe
                                src="{{ Str::contains($module->video, 'watch?v=') ? str_replace('watch?v=', 'embed/', $module->video) : str_replace('youtu.be/', 'youtube.com/embed/', $module->video) }}"
                                title="Vidéo de présentation du module"
                                class="w-full h-full rounded"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen>
                            </iframe>
                        </div>
                    @else
                        <p class="text-sm text-gray-500 italic">Aucune vidéo YouTube fournie pour ce module.</p>
                    @endif
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Contenu principal -->
<section class="py-10 bg-gray-50">
    <div class="max-w-[1248px] mx-auto px-4 flex flex-col lg:flex-row gap-8">

        <!-- Colonne gauche -->
        <div class="w-full lg:w-2/3 space-y-6">
            <div class="bg-white p-6 rounded shadow">
                <section class="py-10 bg-white">
                    <div class="max-w-[1248px] mx-auto px-4">
                        <h3 class="text-lg font-semibold mb-4">Contenu du module</h3>
                        <div class="space-y-4" x-data="{ active: 0 }">
                            @foreach ($module->sections as $index => $section)
                                <div class="border border-gray-200 rounded">
                                    <button
                                        @click="active === {{ $index }} ? active = -1 : active = {{ $index }}"
                                        class="w-full px-4 py-3 text-left font-medium text-gray-800 bg-gray-100 hover:bg-gray-200 flex justify-between items-center"
                                        :aria-expanded="active === {{ $index }}">
                                        <div>
                                            <div class="text-base font-semibold">{{ $section->section_title }}</div>
                                            @if (isset($sectionProgress[$section->id]))
                                                <div class="text-xs text-gray-600 mt-1">
                                                    {{ $sectionProgress[$section->id]['completed'] }} / {{ $sectionProgress[$section->id]['total'] }} réalisées
                                                </div>
                                            @endif
                                        </div>
                                        <svg :class="{'rotate-180': active === {{ $index }}}" class="w-5 h-5 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    @if (isset($sectionProgress[$section->id]) && $sectionProgress[$section->id]['total'] > 0)
                                        @php
                                            $percent = intval(($sectionProgress[$section->id]['completed'] / $sectionProgress[$section->id]['total']) * 100);
                                        @endphp
                                        <div class="w-full bg-gray-200 h-2">
                                            <div class="bg-orangeone h-2" style="width: {{ $percent }}%"></div>
                                        </div>
                                    @endif

                                    <div x-show="active === {{ $index }}" x-collapse class="p-4 bg-white text-sm text-gray-700">
                                        @forelse ($section->lectures as $lecture)
                                            <div class="flex items-center space-x-2 mb-2">
                                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-5.197-3.027A1 1 0 008 9v6a1 1 0 001.555.832l5.197-3.027a1 1 0 000-1.664z"/>
                                                </svg>
                                                <a href="{{ route('stagiaire.module.lecture', ['module' => $module->id, 'section' => $section->id, 'lesson' => $lecture->id]) }}"
                                                    class="flex items-center justify-between px-2 py-1 hover:bg-gray-100 rounded text-sm font-medium text-gray-800">
                                                    <span>{{ $lecture->lecture_title }}</span>
                                                    <span class="ml-2">
                                                        @php $status = $lessonStatuses[$lecture->id] ?? null; @endphp
                                                        @if($status === 'completed')
                                                            <span class="text-green-600">✔️</span>
                                                        @elseif($status === 'incomplete')
                                                            <span class="text-yellow-500">⏳</span>
                                                        @else
                                                            <span class="text-gray-400">–</span>
                                                        @endif
                                                    </span>
                                                </a>
                                            </div>
                                        @empty
                                            <p class="italic text-gray-500">Aucune leçon dans cette section.</p>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <!-- Colonne droite sticky -->
        <div class="w-full lg:w-1/3">
            <div class="sticky top-6 space-y-6">
                <div class="bg-white p-6 rounded shadow">
                    <div class="mb-6">
                        <p class="text-sm text-gray-600 font-medium">Progression du module : {{ $progression }}%</p>
                        <div class="w-full bg-gray-200 rounded h-3 mt-1">
                            <div class="bg-orangeone h-3 rounded" style="width: {{ $progression }}%"></div>
                        </div>
                    </div>
                    <h3 class="text-lg font-semibold mb-4">Informations sur la formation</h3>
                    <div class="space-y-3 text-sm text-gray-700">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Formateur :</span>
                            <span class="font-medium">{{ $module->formateur->name ?? 'Non défini' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Mise à jour :</span>
                            <span class="font-medium">{{ $module->updated_at->format('d M Y') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Durée :</span>
                            <span class="font-medium">{{ $module->duree ?? 'Non précisée' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Ressources :</span>
                            <span class="font-medium">{{ $module->resources ?? 'Non spécifié' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Certificat :</span>
                            <span class="font-medium">{{ $module->certificat ? 'Oui' : 'Non' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Niveau :</span>
                            <span class="font-medium">{{ $module->level ?? 'Tous niveaux' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Langue :</span>
                            <span class="font-medium">Français</span>
                        </div>
                    </div>


                    @if(!empty($module->prerequi))
                        <h4 class="text-md font-semibold mt-6 mb-2 text-gray-800">Prérequis</h4>
                        <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                            @foreach(explode("\n", $module->prerequi) as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    @endif

                        @php
                            $firstSection = $module->sections->first();
                        @endphp

                        @if($firstSection)
                            <a href="{{ route('stagiaire.module.section', ['module' => $module->id, 'section' => $firstSection->id]) }}"
                            class="block text-center bg-indigo-600 hover:bg-indigo-700 text-white font-semibold mt-6 py-2 rounded">
                                🚀 Démarrer la formation
                            </a>
                        @else
                            <p class="text-sm text-gray-500 italic mt-6">Aucune section disponible dans ce module.</p>
                        @endif


                    @if($module->evaluation_id)
                        <button onclick="document.getElementById('evaluationModal').classList.remove('hidden')"
                                class="mt-4 w-full text-center bg-orangeone hover:bg-orange-600 text-white font-semibold py-2 rounded">
                            Lancer l’évaluation
                        </button>
                    @endif

                </div>
            </div>
        </div>
    </div>
</section>

@if($module->evaluation_id)
    <div id="evaluationModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white p-6 rounded shadow-lg w-full max-w-md">
            <h4 class="text-lg font-semibold mb-4 text-orange-600">🎯 Évaluation finale</h4>

            @auth
                <p class="text-sm text-gray-700 mb-4">
                    Cette évaluation ne peut être réalisée <strong>qu’une seule fois</strong>.<br>
                    Veuillez vous assurer d’être prêt avant de commencer.
                </p>
                <div class="flex justify-end space-x-2">
                    <a href="{{ route('evaluation.show', ['id' => $module->evaluation_id]) }}"
                       class="bg-orangeone hover:bg-orange-600 text-white font-semibold px-4 py-2 rounded">
                        Oui, je suis prêt
                    </a>
                    <button onclick="document.getElementById('evaluationModal').classList.add('hidden')"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold px-4 py-2 rounded">
                        Annuler
                    </button>
                </div>
            @else
                <p class="text-sm text-gray-700 mb-4">
                    Vous devez être connecté pour accéder à l’évaluation de ce module.
                </p>
                <div class="flex justify-end">
                    <a href="{{ route('login') }}"
                       class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded">
                        Me connecter
                    </a>
                </div>
            @endauth
        </div>
    </div>
@endif

@endsection
