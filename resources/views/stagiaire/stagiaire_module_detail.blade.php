@extends('stagiaire.master')

@section('content')

{{-- Initialisation Alpine.js avec gestion des onglets, de l'accordéon et de la modal --}}
<div x-data="{ 
    activeTab: 'presentation', 
    openSection: 0, 
    showEvalModal: false 
}" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- FIL D'ARIANE (Breadcrumb) --}}
    <nav class="flex mb-8 text-sm font-medium" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2">
            <li><a href="{{ route('stagiaire.dashboard') }}" class="text-gray-400 hover:text-orangeone transition">Accueil</a></li>
            <li class="text-gray-300">/</li>
            <li><a href="#" class="text-gray-400 hover:text-orangeone transition">Mes modules</a></li>
            <li class="text-gray-300">/</li>
            <li class="text-bleuone font-bold">{{ $module->module_name }}</li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        {{-- COLONNE GAUCHE (8/12) --}}
        <main class="lg:col-span-8 space-y-8">
            
            {{-- HEADER DU MODULE --}}
            <header>
                <div class="flex flex-wrap gap-2 mb-4">
                    @if($module->bestseller) <span class="bg-green-100 text-green-700 text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-md">Bestseller</span> @endif
                    @if($module->vedette) <span class="bg-blue-100 text-blue-700 text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-md">À la Une</span> @endif
                </div>
                <h1 class="text-4xl font-extrabold text-gray-900 leading-tight">
                    {{ $module->module_title ?? $module->module_name }}
                </h1>
                <div class="mt-6 flex items-center gap-4 border-b border-gray-100 pb-8">
                    <div class="size-12 rounded-full bg-orangeone flex items-center justify-center text-white font-bold shadow-lg shadow-orange-100">
                        {{ substr($module->formateur->name ?? 'E', 0, 1) }}
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Formateur Expert</p>
                        <p class="text-lg font-bold text-bleuone">{{ $module->formateur->name ?? 'Équipe Oneduc' }}</p>
                    </div>
                </div>
            </header>

            {{-- NAVIGATION PAR ONGLETS --}}
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8" aria-label="Tabs">
                    @foreach(['presentation' => 'Présentation', 'objectifs' => 'Objectifs', 'prerequis' => 'Prérequis'] as $id => $label)
                        <button @click="activeTab = '{{ $id }}'"
                            :class="activeTab === '{{ $id }}' ? 'border-orangeone text-orangeone' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition-all cursor-pointer outline-none">
                            {{ $label }}
                        </button>
                    @endforeach
                </nav>
            </div>

            {{-- CONTENU DES ONGLETS --}}
            <div class="min-h-[150px]">
                <div x-show="activeTab === 'presentation'" x-cloak class="text-gray-700 leading-relaxed text-lg font-lisible">
                    {{ $module->description }}
                </div>

                <div x-show="activeTab === 'objectifs'" x-cloak>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @forelse($module->objectifs ?? [] as $obj)
                            <div class="flex items-start gap-3 p-4 rounded-xl bg-gray-50 border border-gray-100">
                                <svg class="size-5 text-green-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="text-gray-700 font-medium">{{ $obj }}</span>
                            </div>
                        @empty
                            <p class="italic text-gray-500">Consultez le programme pour voir les détails.</p>
                        @endforelse
                    </div>
                </div>

                <div x-show="activeTab === 'prerequis'" x-cloak class="bg-bleuone/5 p-6 rounded-2xl border border-bleuone/10">
                    <h4 class="font-bold text-bleuone mb-2">Prérequis recommandés :</h4>
                    <div class="text-gray-700">
                        {!! nl2br(e($module->prerequi ?? 'Aucun prérequis spécifique.')) !!}
                    </div>
                </div>
            </div>

            {{-- PROGRAMME (ACCORDION) --}}
            <section class="pt-8">
                <h2 class="text-2xl font-black text-gray-900 mb-6 flex items-center gap-3">
                    <span class="size-8 bg-orangeone rounded-lg flex items-center justify-center text-white text-sm">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                    </span>
                    Programme détaillé
                </h2>

                <div class="space-y-4">
                    @foreach ($module->sections as $idx => $section)
                        <div class="border border-gray-200 rounded-2xl overflow-hidden bg-white shadow-sm">
                            <button @click="openSection = (openSection === {{ $idx }} ? -1 : {{ $idx }})"
                                class="w-full flex items-center justify-between p-5 text-left transition-colors hover:bg-gray-50">
                                <div>
                                    <span class="text-[10px] font-black uppercase tracking-widest text-orangeone">Section {{ $idx + 1 }}</span>
                                    <h3 class="font-bold text-gray-800 text-lg">{{ $section->section_title }}</h3>
                                </div>
                                <svg :class="openSection === {{ $idx }} ? 'rotate-180' : ''" class="size-5 text-gray-400 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>

                            <div x-show="openSection === {{ $idx }}" x-cloak class="p-2 bg-gray-50/50 space-y-1">
                                @foreach ($section->lectures as $lecture)
                                    <a href="{{ route('stagiaire.module.lecture', [$module->id, $section->id, $lecture->id]) }}" 
                                       class="flex items-center justify-between p-3 rounded-xl hover:bg-white hover:shadow-sm transition-all group">
                                        <div class="flex items-center gap-3">
                                            <div class="size-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 group-hover:text-orangeone transition-colors">
                                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            </div>
                                            <span class="text-sm font-semibold text-gray-700">{{ $lecture->lecture_title }}</span>
                                        </div>
                                        <div>
                                            @php $status = $lessonStatuses[$lecture->id] ?? null; @endphp
                                            @if($status === 'completed')
                                                <span class="text-green-500" title="Terminé">
                                                    <svg class="size-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                                </span>
                                            @elseif($status === 'incomplete')
                                                <span class="text-orangeone animate-pulse" title="En cours">
                                                    <svg class="size-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" /></svg>
                                                </span>
                                            @endif
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </main>

        {{-- COLONNE DROITE (Aside Sticky 4/12) --}}
        <aside class="lg:col-span-4 lg:sticky lg:top-8">
            <div class="bg-white rounded-[32px] shadow-2xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
                
                {{-- APERÇU VIDÉO OU IMAGE --}}
                <div class="aspect-video bg-gray-900 relative">
                    @if($module->module_video)
                        <video class="w-full h-full object-cover" controls preload="metadata">
                            <source src="{{ url('modules/scorm/02_videos/' . trim($module->module_video, '/')) }}" type="video/mp4">
                        </video>
                    @else
                        <div class="absolute inset-0 flex items-center justify-center">
                            <img src="{{ asset('images/svg/Modules.svg') }}" class="size-24 opacity-20" alt="Illustration">
                        </div>
                    @endif
                </div>

                <div class="p-8">
                    {{-- PROGRESSION --}}
                    <div class="mb-8">
                        <div class="flex justify-between items-end mb-3">
                            <span class="text-sm font-black text-gray-900 uppercase tracking-tighter">Progression</span>
                            <span class="text-3xl font-black text-orangeone">{{ $progression }}%</span>
                        </div>
                        <div class="h-4 w-full bg-gray-100 rounded-full p-1">
                            <div class="h-full bg-orangeone rounded-full transition-all duration-1000 ease-out" style="width: {{ $progression }}%"></div>
                        </div>
                    </div>

                    {{-- ACTIONS PRINCIPALES --}}
                    <div class="space-y-4">
                        @php $firstSection = $module->sections->first(); @endphp
                        @if($firstSection)
                            <a href="{{ route('stagiaire.module.section', [$module->id, $firstSection->id]) }}" 
                               class="flex items-center justify-center w-full py-4 px-6 rounded-2xl bg-orangeone text-white font-black text-xl hover:bg-orange-600 transition-all hover:scale-[1.02] active:scale-95 shadow-xl shadow-orange-200">
                                {{ $progression > 0 ? 'Continuer' : 'Démarrer' }}
                            </a>
                        @endif

                        @if($module->evaluation_id)
                            <button @click="showEvalModal = true"
                                class="w-full py-4 px-6 rounded-2xl border-2 border-orangeone text-orangeone font-bold hover:bg-orange-50 transition-colors cursor-pointer">
                                Évaluation finale
                            </button>
                        @endif
                    </div>

                    {{-- INFOS TECHNIQUES --}}
                    <div class="mt-8 pt-8 border-t border-gray-100 space-y-4 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 font-medium">Durée totale</span>
                            <span class="font-bold text-gray-900">{{ $module->duree ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 font-medium">Niveau</span>
                            <span class="font-bold text-gray-900">{{ $module->level ?? 'Tous niveaux' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 font-medium">Certificat</span>
                            <span class="font-bold text-green-600">{{ $module->certificat ? 'Disponible' : 'Non' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    {{-- MODAL ÉVALUATION (Alpine.js) --}}
    <div x-show="showEvalModal" 
         x-cloak 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        {{-- Overlay flou --}}
        <div @click="showEvalModal = false" 
             class="absolute inset-0 bg-bleuone/40 backdrop-blur-sm transition-opacity"></div>

        {{-- Contenu Modal --}}
        <div class="relative bg-white rounded-[32px] shadow-2xl max-w-lg w-full p-10 overflow-hidden transform transition-all">
            <div class="absolute top-0 right-0 p-4">
                <button @click="showEvalModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            
            <div class="text-center">
                <div class="size-20 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-6 text-orangeone">
                    <svg class="size-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <h3 class="text-2xl font-black text-bleuone">Prêt pour l'évaluation ?</h3>
                <p class="mt-4 text-gray-600 font-lisible">
                    Vous allez passer l'examen final de <strong>{{ $module->module_name }}</strong>. 
                    Il est conseillé d'avoir complété 100% des leçons avant de tenter d'obtenir votre certificat.
                </p>
                <div class="mt-8 flex flex-col gap-3">
                    <a href="{{ route('evaluation.show', $module->evaluation_id ?? 1) }}" 
                       class="w-full py-4 bg-orangeone text-white rounded-2xl font-black text-lg hover:bg-orange-600 transition-all shadow-lg shadow-orange-100">
                       Démarrer l'examen
                    </a>
                    <button @click="showEvalModal = false" class="text-gray-400 font-bold hover:text-gray-600 py-2">
                        Revenir plus tard
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
    .font-lisible { font-family: 'Open Sans', system-ui, sans-serif; letter-spacing: 0.01em; }
</style>

@endsection