@extends('formateur.parcours.layout')

@php
    $chapterCount = $currentModule['chapter_count'] ?? count($currentModule['chapters'] ?? []);
    $lessonCount = $currentModule['lesson_count'] ?? 0;
    $ctaUrl = $currentModule['entry_url'] ?? $currentModule['first_chapter_url'] ?? $currentModule['url'];
    $presentationVideoEmbedUrl = $currentModule['presentation_video_embed_url'] ?? null;
    $presentationVideoTitle = $currentModule['presentation_video_title'] ?? ('Video de presentation - ' . $currentModule['title']);
    $presentationVideoNote = $currentModule['presentation_video_note'] ?? 'Video de presentation a ajouter.';
    $specificObjective = $currentModule['specific_objective'] ?? $currentModule['description'];
    $activityStatusMap = $activityStatusMap ?? [];
    $activityCount = 0;
    $completedActivityCount = 0;
    $bilanCount = 0;

    $activityStatusKeyFor = static function (string $chapterKey, string $lessonKey, array $lesson): ?string {
        if (($lesson['type'] ?? 'objectif') === 'bilan') {
            return null;
        }

        $activityKey = $lesson['activity_page']['key'] ?? ($lesson['completion_activity_key'] ?? null);

        return $activityKey ? implode('.', [$chapterKey, $lessonKey, $activityKey]) : null;
    };

    foreach (($currentModule['chapters'] ?? []) as $moduleChapterKey => $chapter) {
        foreach (($chapter['lessons'] ?? []) as $moduleLessonKey => $lesson) {
            $activityStatusKey = $activityStatusKeyFor((string) $moduleChapterKey, (string) $moduleLessonKey, $lesson);

            if ($activityStatusKey) {
                $activityCount++;

                if (($activityStatusMap[$activityStatusKey] ?? false) === true) {
                    $completedActivityCount++;
                }
            }

            if (($lesson['type'] ?? 'objectif') === 'bilan') {
                $bilanCount++;
            }
        }
    }

    $progressPercentage = $activityCount > 0 ? (int) round(($completedActivityCount / $activityCount) * 100) : 0;
@endphp

@section('parcours_content')
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        <div
            x-data="{
                activeTab: 'presentation',
                openSection: 0
            }"
        >
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <main class="lg:col-span-8">
                <header class="border-b border-gray-100 pb-2 mb-4">
                    @if (!empty($currentModule['is_under_construction']))
                        <div class="mb-4 rounded-[18px] border border-amber-200 bg-amber-50 px-5 py-4 text-amber-900">
                            <p class="text-xs font-black uppercase tracking-[0.22em] text-amber-700">
                                {{ $currentModule['construction_label'] ?? 'En cours de construction' }}
                            </p>
                            <p class="mt-2 text-sm leading-6">
                                {{ $currentModule['construction_note'] ?? 'Ce module est en cours de construction.' }}
                            </p>
                        </div>
                    @endif

                    <h1 class="font-raleway text-2xl md:text-3xl font-medium text-bleuone leading-tight">
                        {{ $currentModule['title'] }}
                    </h1>
                </header>

                <div class="border-b border-gray-200 mb-6">
                    <nav class="flex space-x-6" aria-label="Tabs">
                        @foreach (['presentation' => 'Presentation', 'objectifs' => 'But du module', 'prerequis' => 'Prerequis'] as $tabId => $tabLabel)
                            <button
                                @click="activeTab = '{{ $tabId }}'"
                                :class="activeTab === '{{ $tabId }}' ? 'border-orangeone text-orangeone' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap py-3 px-1 border-b-2 font-bold text-sm transition-all cursor-pointer outline-none"
                            >
                                {{ $tabLabel }}
                            </button>
                        @endforeach
                    </nav>
                </div>

                <div class="min-h-[100px] mb-8">
                    <div x-show="activeTab === 'presentation'" x-cloak class="text-gray-700 leading-relaxed font-lisible space-y-4 text-sm md:text-base">
                        @foreach ($currentModule['presentation'] as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    </div>

                    <div x-show="activeTab === 'objectifs'" x-cloak>
                        <div class="mb-5 rounded-2xl border border-orange-100 bg-orange-50/60 p-5">
                            <p class="text-xs font-black uppercase tracking-[0.22em] text-orangeone">But du module</p>
                            <p class="mt-2 text-base font-semibold leading-7 text-bleuone">{{ $specificObjective }}</p>
                        </div>
                        <ul class="space-y-3">
                            @foreach ($currentModule['goals'] as $goal)
                                <li class="flex items-start gap-3">
                                    <svg class="size-5 text-orangeone mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-gray-700 font-medium text-sm md:text-base">{{ $goal }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div x-show="activeTab === 'prerequis'" x-cloak class="bg-gray-50 p-5 rounded-xl border border-gray-100">
                        <h4 class="font-bold text-bleuone mb-2 flex items-center gap-2 text-sm">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Prerequis recommandes
                        </h4>
                        <div class="space-y-2 pl-6 text-sm text-gray-700">
                            @foreach ($currentModule['prerequisites'] as $prerequisite)
                                <p>{{ $prerequisite }}</p>
                            @endforeach
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
                        Programme du module
                    </h2>

                    <div class="space-y-3">
                        @if ($chapterCount === 0)
                            <div class="border border-gray-200 rounded-xl overflow-hidden bg-white shadow-sm">
                                <div class="p-6">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-orangeone">Preparation</p>
                                    <h3 class="mt-3 font-bold text-gray-800 text-base md:text-lg">Le module detail est pret</h3>
                                    <p class="mt-4 text-sm leading-7 text-slate-600">
	                                        Le contenu de detail de ce module sera ajoute des que les chapitres, les lecons et les contenus interactifs seront disponibles.
                                    </p>
                                </div>
                            </div>
                        @else
                            @foreach ($currentModule['chapters'] as $chapterKey => $chapter)
                                @php
                                    $chapterLessons = $chapter['lessons'] ?? [];
                                    $chapterActivityCount = 0;
                                    $chapterCompletedActivityCount = 0;

                                    foreach ($chapterLessons as $chapterLessonKey => $chapterLesson) {
                                        $chapterStatusKey = $activityStatusKeyFor((string) $chapterKey, (string) $chapterLessonKey, $chapterLesson);

                                        if (! $chapterStatusKey) {
                                            continue;
                                        }

                                        $chapterActivityCount++;

                                        if (($activityStatusMap[$chapterStatusKey] ?? false) === true) {
                                            $chapterCompletedActivityCount++;
                                        }
                                    }

                                    $chapterProgress = $chapterActivityCount > 0
                                        ? (int) round(($chapterCompletedActivityCount / $chapterActivityCount) * 100)
                                        : 0;
                                @endphp
                                <div class="border border-gray-200 rounded-xl overflow-hidden bg-white shadow-sm hover:shadow-md transition-shadow">
                                    <button
                                        @click="openSection = (openSection === {{ $loop->index }} ? -1 : {{ $loop->index }})"
	                                        class="group w-full flex items-start justify-between gap-4 p-4 text-left transition-colors hover:bg-gray-50"
                                    >
	                                        <div class="min-w-0">
	                                            <h3
	                                                class="font-bold text-gray-800 text-base md:text-lg"
	                                                data-parcours-tooltip="{{ $chapter['pedagogical_label'] ?? 'Objectif pedagogique' }}"
	                                            >
                                                {{ $chapter['title'] }}
                                            </h3>
                                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ $chapter['objective'] }}</p>
                                        </div>
                                        <div class="flex shrink-0 items-center gap-3">
                                            <span class="hidden text-right text-xs font-semibold text-gray-400 sm:inline-block">
	                                                {{ $chapter['lesson_count'] }} lecon{{ $chapter['lesson_count'] > 1 ? 's' : '' }}
                                                @if ($chapterActivityCount > 0)
                                                    <br>{{ $chapterActivityCount }} activite{{ $chapterActivityCount > 1 ? 's' : '' }}
                                                @endif
                                            </span>
	                                            <span
	                                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border-2 border-orange-200 bg-orange-50 text-orangeone shadow-sm transition group-hover:border-orangeone group-hover:bg-orangeone group-hover:text-white"
	                                                :class="openSection === {{ $loop->index }} ? 'border-orangeone bg-orangeone text-white' : ''"
	                                                aria-hidden="true"
	                                            >
	                                                <svg :class="openSection === {{ $loop->index }} ? 'rotate-180' : ''" class="h-7 w-7 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
	                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.7" d="M19 9l-7 7-7-7" />
	                                                </svg>
	                                            </span>
	                                        </div>
                                    </button>

                                    <div class="w-full bg-gray-100 h-1.5">
                                        <div class="bg-orangeone h-1.5" style="width: {{ $chapterProgress }}%"></div>
                                    </div>

                                    <div x-show="openSection === {{ $loop->index }}" x-cloak class="border-t border-gray-100">
                                        @foreach ($chapterLessons as $lessonKey => $lesson)
                                            @php
                                                $isBilan = ($lesson['type'] ?? 'objectif') === 'bilan';
                                                $hasActivity = !empty($lesson['activity_page']);
                                                $lessonActivityStatusKey = $activityStatusKeyFor((string) $chapterKey, (string) $lessonKey, $lesson);
                                                $isActivityCompleted = $lessonActivityStatusKey
                                                    ? (($activityStatusMap[$lessonActivityStatusKey] ?? false) === true)
                                                    : false;
                                                $hasActivitySlot = !empty($lessonActivityStatusKey);
                                                $activityPillClass = $isActivityCompleted
                                                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                                    : ($hasActivitySlot ? 'border-orange-200 bg-orange-50 text-orange-700' : 'border-slate-200 bg-white text-slate-500');
                                                $activityPillLabel = $isActivityCompleted
                                                    ? 'Activité validée'
                                                    : ($hasActivitySlot ? 'Activité disponible' : ($lesson['activity_slot_label'] ?? 'Activité à créer'));
                                            @endphp
                                            <a
                                                href="{{ $lesson['url'] }}"
                                                class="flex items-start justify-between gap-4 p-4 pl-6 hover:bg-orange-50/50 transition-colors border-b border-gray-50 last:border-0 group"
                                            >
                                                <div class="flex min-w-0 items-start gap-4">
                                                    <div class="{{ $isBilan ? 'text-bleuone' : 'text-gray-300' }} group-hover:text-orangeone transition-colors pt-1">
                                                        <svg class="size-4" fill="currentColor" viewBox="0 0 24 24">
                                                            <path d="M8 5v14l11-7z" />
                                                        </svg>
                                                    </div>
	                                                    <div class="min-w-0">
	                                                        @if ($isBilan)
	                                                            <div class="flex flex-wrap items-center gap-2">
	                                                                <span
	                                                                    class="rounded-full border border-bleuone/20 bg-bleuone/5 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-bleuone"
	                                                                    data-parcours-tooltip="Bilan"
	                                                                >
	                                                                    Bilan
	                                                                </span>
	                                                            </div>
	                                                        @endif
		                                                        <span
		                                                            class="{{ $isBilan ? 'mt-2' : '' }} block text-sm font-semibold text-gray-700 group-hover:text-bleuone transition-colors"
		                                                            data-parcours-tooltip="{{ $isBilan ? 'Bilan' : 'Objectif operationnel' }}"
		                                                        >
                                                            {{ $lesson['title'] }}
                                                        </span>
                                                        <p class="mt-1 text-sm leading-6 text-slate-500">{{ $lesson['objective'] }}</p>
                                                        <div class="mt-3 flex flex-wrap gap-2">
                                                            <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-[11px] font-bold text-slate-500">
	                                                                {{ $lesson['scorm_slot_label'] ?? 'Contenu de lecon' }}
                                                            </span>
                                                            @unless ($isBilan)
                                                                <span class="rounded-full border {{ $activityPillClass }} px-3 py-1 text-[11px] font-bold">
                                                                    {{ $activityPillLabel }}
                                                                </span>
                                                            @endunless
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="shrink-0 text-sm font-medium text-slate-400 group-hover:text-orangeone transition-colors">
                                                    Ouvrir
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </section>
                </main>

                <aside class="lg:col-span-4 lg:sticky lg:top-8 space-y-6">
                    <div class="bg-white rounded-[24px] shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
                        <div class="w-full bg-black">
                            @if ($presentationVideoEmbedUrl)
                                <div class="aspect-video bg-slate-950">
                                    <iframe
                                        src="{{ $presentationVideoEmbedUrl }}"
                                        title="{{ $presentationVideoTitle }}"
                                        class="h-full w-full"
                                        frameborder="0"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                        referrerpolicy="strict-origin-when-cross-origin"
                                        allowfullscreen
                                    ></iframe>
                                </div>
                            @else
                                <div class="flex aspect-video items-center justify-center bg-gradient-to-br from-slate-100 via-white to-slate-100 px-6 py-8">
                                    <div class="grid w-full grid-cols-[120px_minmax(0,1fr)] items-center gap-4">
                                        <div class="flex h-[170px] items-center justify-center rounded-[24px] bg-white shadow-[0_18px_40px_-24px_rgba(0,68,97,0.35)]">
                                            <div class="px-4 text-center">
                                                <p class="text-sm font-bold uppercase tracking-[0.18em] text-orangeone">On educ</p>
                                                <p class="mt-3 text-2xl font-black leading-8 text-bleuone">{{ $currentModule['label'] }}</p>
                                            </div>
                                        </div>
                                        <div>
                                            <p class="text-4xl font-light tracking-tight text-bleuone">{{ $currentModule['title'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="p-6">
                            <div class="mb-6 border-b border-gray-100 pb-6">
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-orangeone">{{ $currentModule['label'] }}</p>
                                @if (!empty($currentModule['is_under_construction']))
                                    <span class="mt-2 inline-flex rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-[10px] font-black uppercase tracking-wide text-amber-700">
                                        {{ $currentModule['construction_label'] ?? 'En cours de construction' }}
                                    </span>
                                @endif
                                <p class="mt-2 font-raleway text-2xl font-medium leading-tight text-bleuone">{{ $currentModule['title'] }}</p>
                                <p class="mt-2 text-sm text-slate-500 font-lisible">
                                    {{ $presentationVideoEmbedUrl ? 'Video de presentation' : $presentationVideoNote }}
                                </p>
                            </div>

                            <div class="mb-6">
                                <div class="flex justify-between items-end mb-2">
                                    <span class="text-xs font-bold text-gray-500 uppercase">Votre avancee</span>
                                    <span class="text-xl font-black text-orangeone">{{ $progressPercentage }}%</span>
                                </div>
                                <div class="h-2 w-full bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-orangeone rounded-full transition-all duration-1000 ease-out" style="width: {{ $progressPercentage }}%"></div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <a
                                    href="{{ $ctaUrl }}"
                                    class="btn-oneduc w-full !rounded-full !px-6 !py-4 !text-lg !font-varela !font-bold"
                                >
                                    {{ $currentModule['cta_label'] }}
                                </a>
                            </div>

                            <div class="mt-8 pt-6 border-t border-gray-100 space-y-5">
                                <div class="flex items-center gap-4">
                                    <div class="size-10 rounded-full bg-orangeone flex items-center justify-center text-white font-bold text-sm shadow-md">
                                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($currentModule['trainer_name'], 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase">Formateur</p>
                                        <p class="font-bold text-gray-900">{{ $currentModule['trainer_name'] }}</p>
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
                                        <p class="font-bold text-gray-900">{{ $currentModule['level_label'] }}</p>
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
                                        <p class="font-bold text-gray-900">{{ $currentModule['duration_label'] }}</p>
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
	                                        <p class="font-bold text-gray-900">{{ $chapterCount }} chapitre{{ $chapterCount > 1 ? 's' : '' }}</p>
	                                        <p class="mt-1 text-xs font-semibold text-slate-500">
	                                            {{ $lessonCount }} lecon{{ $lessonCount > 1 ? 's' : '' }}
                                            @if ($activityCount > 0)
                                                · {{ $activityCount }} activite{{ $activityCount > 1 ? 's' : '' }}
                                            @endif
                                            @if ($bilanCount > 0)
                                                · {{ $bilanCount }} bilan{{ $bilanCount > 1 ? 's' : '' }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>
@endsection
