@php
    $presentationMode = $presentationMode ?? 'public'; // public|stagiaire|formateur
    $lessonObjectives = collect($lessonObjectives ?? []);
    $lessonStatuses = $lessonStatuses ?? [];
    $sectionProgress = $sectionProgress ?? [];
    $progression = (int) ($progression ?? 0);
    $firstSection = $module->sections->first();

    $isStagiaireView = $presentationMode === 'stagiaire';
    $isFormateurView = $presentationMode === 'formateur';
    $isPublicView = $presentationMode === 'public';

    $authUser = auth()->user();
    $isStagiaireUser = $authUser && $authUser->role === 'stagiaire';
    $isFormateurUser = $authUser && $authUser->role === 'formateur';

    $showProgress = $isStagiaireView || ($isPublicView && $isStagiaireUser);
    $showStatuses = $showProgress;
@endphp

<style>
    [x-cloak] { display: none !important; }
    .font-lisible { font-family: 'Open Sans', system-ui, sans-serif; }
</style>

<div x-data="{
    activeTab: 'presentation',
    openSection: 0,
    showEvalModal: false,
    showAuthModal: false
}" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <nav class="flex mb-4 text-xs font-medium" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2">
            @if($isStagiaireView)
                <li><a href="{{ route('stagiaire.dashboard') }}" class="text-gray-400 hover:text-orangeone transition">Accueil</a></li>
                <li class="text-gray-300">/</li>
                <li><a href="{{ route('stagiaire.modules') }}" class="text-gray-400 hover:text-orangeone transition">Mes formations</a></li>
            @elseif($isFormateurView)
                <li><a href="{{ route('formateur.dashboard') }}" class="text-gray-400 hover:text-orangeone transition">Accueil</a></li>
                <li class="text-gray-300">/</li>
                <li><a href="{{ route('formateur.formations.index') }}" class="text-gray-400 hover:text-orangeone transition">Mes modules</a></li>
            @else
                <li><a href="{{ url('/') }}" class="text-gray-400 hover:text-orangeone transition">Accueil</a></li>
                <li class="text-gray-300">/</li>
                <li><a href="{{ route('categories.all') }}" class="text-gray-400 hover:text-orangeone transition">Formations</a></li>
            @endif
            <li class="text-gray-300">/</li>
            <li class="text-bleuone font-bold truncate max-w-[240px]">
                {{ $module->module_title ?? $module->module_name }}
            </li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <main class="lg:col-span-8">
            <header class="border-b border-gray-100 pb-2 mb-4">
                <div class="flex flex-wrap gap-2 mb-2">
                    @if($module->bestseller)
                        <span class="inline-block bg-green-100 text-green-700 text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded">Bestseller</span>
                    @endif
                    @if($module->vedette)
                        <span class="inline-block bg-blue-100 text-blue-700 text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded">A la une</span>
                    @endif
                    @if($module->surevalue)
                        <span class="inline-block bg-yellow-100 text-yellow-700 text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded">Valeur sure</span>
                    @endif
                </div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-bleuone leading-tight">
                    {{ $module->module_title ?? $module->module_name }}
                </h1>
            </header>

            <div class="border-b border-gray-200 mb-6">
                <nav class="flex space-x-6" aria-label="Tabs">
                    @foreach(['presentation' => 'Presentation', 'objectifs' => 'Objectifs', 'prerequis' => 'Prerequis'] as $id => $label)
                        <button @click="activeTab = '{{ $id }}'"
                            :class="activeTab === '{{ $id }}' ? 'border-orangeone text-orangeone' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-3 px-1 border-b-2 font-bold text-sm transition-all cursor-pointer outline-none">
                            {{ $label }}
                        </button>
                    @endforeach
                </nav>
            </div>

            <div class="min-h-[100px] mb-8">
                <div x-show="activeTab === 'presentation'" x-cloak class="text-gray-700 leading-relaxed font-lisible space-y-4 text-sm md:text-base">
                    {!! nl2br(e($module->description ?? '')) !!}
                </div>

                <div x-show="activeTab === 'objectifs'" x-cloak>
                    <ul class="space-y-3">
                        @forelse($lessonObjectives as $obj)
                            <li class="flex items-start gap-3">
                                <svg class="size-5 text-orangeone mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-gray-700 font-medium text-sm md:text-base">{{ $obj }}</span>
                            </li>
                        @empty
                            <p class="italic text-gray-500">Aucun objectif de lecon n'est encore disponible.</p>
                        @endforelse
                    </ul>
                </div>

                <div x-show="activeTab === 'prerequis'" x-cloak class="bg-gray-50 p-5 rounded-xl border border-gray-100">
                    <h4 class="font-bold text-bleuone mb-2 flex items-center gap-2 text-sm">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Prerequis recommandes
                    </h4>
                    <div class="text-gray-700 pl-6 text-sm">
                        {!! nl2br(e($module->prerequi ?? 'Aucun prerequis specifique pour ce module.')) !!}
                    </div>
                </div>
            </div>

            <section>
                <h2 class="text-xl font-black text-bleuone mb-5 flex items-center gap-3">
                    <span class="size-8 bg-orangeone rounded-lg flex items-center justify-center text-white text-sm shadow-md">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </span>
                    Programme detaille
                </h2>

                <div class="space-y-3">
                    @foreach ($module->sections as $idx => $section)
                        <div class="border border-gray-200 rounded-xl overflow-hidden bg-white shadow-sm hover:shadow-md transition-shadow">
                            <button @click="openSection = (openSection === {{ $idx }} ? -1 : {{ $idx }})"
                                class="w-full flex items-center justify-between p-4 text-left transition-colors hover:bg-gray-50">
                                <div>
                                    <span class="text-[10px] font-black uppercase tracking-widest text-orangeone">Chapitre {{ $idx + 1 }}</span>
                                    <h3 class="font-bold text-gray-800 text-base md:text-lg">{{ $section->section_title }}</h3>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-xs text-gray-400 font-semibold hidden sm:inline-block">{{ $section->lectures->count() }} lecons</span>
                                    <svg :class="openSection === {{ $idx }} ? 'rotate-180' : ''" class="size-5 text-gray-400 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </button>

                            @if($showProgress && isset($sectionProgress[$section->id]) && (($sectionProgress[$section->id]['total'] ?? 0) > 0))
                                @php
                                    $secTotal = (int) ($sectionProgress[$section->id]['total'] ?? 0);
                                    $secDone = (int) ($sectionProgress[$section->id]['completed'] ?? 0);
                                    $secPercent = $secTotal > 0 ? (int) round(($secDone / $secTotal) * 100) : 0;
                                @endphp
                                <div class="w-full bg-gray-100 h-1.5">
                                    <div class="bg-orangeone h-1.5" style="width: {{ $secPercent }}%"></div>
                                </div>
                            @endif

                            <div x-show="openSection === {{ $idx }}" x-cloak class="border-t border-gray-100">
                                @foreach ($section->lectures as $lecture)
                                    @php
                                        $lectureUrl = null;
                                        if ($isStagiaireView) {
                                            $lectureUrl = route('stagiaire.module.lecture', [$module->id, $section->id, $lecture->id]);
                                        } elseif ($isFormateurView) {
                                            $lectureUrl = route('formateur.formations.lecture', ['module' => $module->id, 'section' => $section->id, 'lecture' => $lecture->id]);
                                        } elseif ($isStagiaireUser) {
                                            $lectureUrl = route('stagiaire.module.lecture', [$module->id, $section->id, $lecture->id]);
                                        } elseif ($isFormateurUser) {
                                            $lectureUrl = route('formateur.formations.lecture', ['module' => $module->id, 'section' => $section->id, 'lecture' => $lecture->id]);
                                        }
                                        $status = $lessonStatuses[$lecture->id] ?? null;
                                    @endphp

                                    @if($lectureUrl)
                                        <a href="{{ $lectureUrl }}"
                                           class="flex items-center justify-between p-3 pl-6 hover:bg-orange-50/50 transition-colors border-b border-gray-50 last:border-0 group">
                                            <div class="flex items-center gap-4">
                                                <div class="text-gray-300 group-hover:text-orangeone transition-colors">
                                                    <svg class="size-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                                </div>
                                                <span class="text-sm font-semibold text-gray-700 group-hover:text-bleuone transition-colors">{{ $lecture->lecture_title }}</span>
                                            </div>

                                            @if($showStatuses)
                                                <div>
                                                    @if($status === 'completed')
                                                        <svg class="size-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                        </svg>
                                                    @elseif($status === 'incomplete')
                                                        <svg class="size-5 text-orangeone" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                        </svg>
                                                    @else
                                                        <div class="size-4 border-2 border-gray-200 rounded-full"></div>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-xs text-gray-400">Ouvrir</span>
                                            @endif
                                        </a>
                                    @else
                                        <button type="button" @click="showAuthModal = true"
                                            class="w-full text-left flex items-center justify-between p-3 pl-6 hover:bg-orange-50/50 transition-colors border-b border-gray-50 last:border-0 group">
                                            <div class="flex items-center gap-4">
                                                <div class="text-gray-300 group-hover:text-orangeone transition-colors">
                                                    <svg class="size-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                                </div>
                                                <span class="text-sm font-semibold text-gray-700 group-hover:text-bleuone transition-colors">{{ $lecture->lecture_title }}</span>
                                            </div>
                                            <span class="text-xs text-gray-400">Connexion requise</span>
                                        </button>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </main>

        <aside class="lg:col-span-4 lg:sticky lg:top-8 space-y-6">
            <div class="bg-white rounded-[24px] shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
                <div class="w-full bg-black">
                    @if($module->module_video)
                        @php $moduleVideoSrc = \App\Support\LearningAssetPath::resolveModuleVideoUrl($module->module_video); @endphp
                        <video class="w-full aspect-video object-cover" controls preload="metadata">
                            <source src="{{ $moduleVideoSrc }}" type="video/mp4">
                            Votre navigateur ne supporte pas la lecture de videos.
                        </video>
                    @else
                        <div class="aspect-video bg-bleuone flex items-center justify-center">
                            <img src="{{ asset('images/svg/Modules.svg') }}" class="size-20 opacity-20" alt="Illustration">
                        </div>
                    @endif
                </div>

                <div class="p-6">
                    @if($showProgress)
                        <div class="mb-6">
                            <div class="flex justify-between items-end mb-2">
                                <span class="text-xs font-bold text-gray-500 uppercase">Votre avancee</span>
                                <span class="text-xl font-black text-orangeone">{{ $progression }}%</span>
                            </div>
                            <div class="h-2 w-full bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-orangeone rounded-full transition-all duration-1000 ease-out" style="width: {{ $progression }}%"></div>
                            </div>
                        </div>
                    @endif

                    <div class="space-y-3">
                        @if($firstSection)
                            @if($isStagiaireView || ($isPublicView && $isStagiaireUser))
                                <a href="{{ route('stagiaire.module.section', [$module->id, $firstSection->id]) }}"
                                   class="flex items-center justify-center w-full py-4 px-6 rounded-xl bg-orangeone text-white font-extrabold text-lg hover:bg-orange-600 transition-all shadow-lg shadow-orange-100 hover:-translate-y-0.5">
                                    {{ $progression > 0 ? 'Reprendre' : 'Commencer' }}
                                </a>
                            @elseif($isFormateurView || ($isPublicView && $isFormateurUser))
                                <a href="{{ route('formateur.formations.section', ['module' => $module->id, 'section' => $firstSection->id, 'anonymous' => 1]) }}"
                                   class="flex items-center justify-center w-full py-4 px-6 rounded-xl bg-orangeone text-white font-extrabold text-lg hover:bg-orange-600 transition-all shadow-lg shadow-orange-100 hover:-translate-y-0.5">
                                    Voir le parcours
                                </a>
                            @else
                                <button type="button" @click="showAuthModal = true"
                                    class="w-full py-4 px-6 rounded-xl bg-bleuone text-white font-bold hover:bg-blue-700 transition-all">
                                    Se connecter pour commencer
                                </button>
                            @endif
                        @else
                            <button disabled class="w-full py-4 px-6 rounded-xl bg-gray-100 text-gray-400 font-bold cursor-not-allowed">
                                Bientot disponible
                            </button>
                        @endif

                        @if($module->evaluation_id)
                            <button @click="showEvalModal = true"
                                class="w-full py-4 px-6 rounded-xl border-2 border-bleuone text-bleuone font-bold hover:bg-bleuone hover:text-white transition-colors">
                                Lancer l'evaluation
                            </button>
                        @endif
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-100 space-y-5">
                        <div class="flex items-center gap-4">
                            <div class="size-10 rounded-full bg-orangeone flex items-center justify-center text-white font-bold text-sm shadow-md">
                                {{ substr($module->formateur->name ?? 'E', 0, 1) }}
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-400 font-bold uppercase">Formateur</p>
                                <p class="font-bold text-gray-900">{{ $module->formateur->name ?? 'Equipe Oneduc' }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="size-10 rounded-lg bg-bleuone/5 text-bleuone flex items-center justify-center">
                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-400 font-bold uppercase">Niveau</p>
                                <p class="font-bold text-gray-900">{{ $module->level ?? 'Tous niveaux' }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="size-10 rounded-lg bg-bleuone/5 text-bleuone flex items-center justify-center">
                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-400 font-bold uppercase">Duree estimee</p>
                                <p class="font-bold text-gray-900">{{ $module->formatted_duration ?? $module->duree ?? 'Rythme libre' }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="size-10 rounded-lg bg-bleuone/5 text-bleuone flex items-center justify-center">
                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-400 font-bold uppercase">Contenu</p>
                                <p class="font-bold text-gray-900">{{ $module->sections->flatMap->lectures->count() }} lecons</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    <div x-show="showEvalModal"
         x-cloak
         class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div @click="showEvalModal = false" class="absolute inset-0 bg-bleuone/60 backdrop-blur-sm"></div>

        <div class="relative bg-white rounded-[32px] shadow-2xl max-w-lg w-full p-8 overflow-hidden transform transition-all">
            <button @click="showEvalModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-bleuone">
                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <div class="text-center">
                <div class="size-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-5 text-orangeone">
                    <svg class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-black text-bleuone">Evaluation finale</h3>
                <p class="mt-3 text-gray-600">
                    Vous etes sur le point de lancer l'evaluation de<br><strong>{{ $module->module_name }}</strong>.
                </p>
                <div class="mt-8 flex flex-col gap-3">
                    @if(auth()->check())
                        <a href="{{ route('evaluation.show', $module->evaluation_id ?? 1) }}"
                           class="w-full py-4 bg-orangeone text-white rounded-xl font-bold text-lg hover:bg-orange-600 transition-all shadow-lg shadow-orange-100">
                            Demarrer l'evaluation
                        </a>
                    @else
                        <a href="{{ route('connexion') }}"
                           class="w-full py-4 bg-orangeone text-white rounded-xl font-bold text-lg hover:bg-orange-600 transition-all shadow-lg shadow-orange-100">
                            Se connecter
                        </a>
                    @endif
                    <button @click="showEvalModal = false" class="text-gray-500 font-bold hover:text-bleuone py-2 text-sm">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div x-show="showAuthModal"
         x-cloak
         class="fixed inset-0 z-[110] flex items-center justify-center p-4">
        <div @click="showAuthModal = false" class="absolute inset-0 bg-black/50"></div>

        <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
            <button @click="showAuthModal = false" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <h3 class="text-lg font-bold text-bleuone">Connexion requise</h3>
            <p class="text-sm text-gray-600 mt-2">
                Connectez-vous pour acceder au contenu detaille de la formation.
            </p>
            <div class="mt-5 flex gap-3">
                <a href="{{ route('connexion') }}"
                   class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-orangeone text-white font-semibold hover:bg-orange-600">
                    Se connecter
                </a>
                @if(\Illuminate\Support\Facades\Route::has('login.selection'))
                    <a href="{{ route('login.selection') }}"
                       class="inline-flex items-center justify-center px-4 py-2 rounded-lg border border-gray-300 text-gray-700 font-semibold hover:bg-gray-50">
                        Choisir un acces
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
