@extends('formateur.parcours.layout')

@php
    $chapterCount = $currentModule['chapter_count'] ?? count($currentModule['chapters'] ?? []);
    $lessonCount = $currentModule['lesson_count'] ?? 0;
    $ctaUrl = $currentModule['entry_url'] ?? $currentModule['first_chapter_url'] ?? $currentModule['url'];
    $presentationVideoEmbedUrl = $currentModule['presentation_video_embed_url'] ?? null;
    $presentationVideoTitle = $currentModule['presentation_video_title'] ?? ('Video de presentation - ' . $currentModule['title']);
    $specificObjective = $currentModule['specific_objective'] ?? $currentModule['description'];
    $fullTitle = $currentModule['full_title'] ?? null;
    $activityStatusMap = $activityStatusMap ?? [];
    $activityCount = 0;
    $completedActivityCount = 0;
    $bilanCount = 0;

    $activityStatusKeyFor = static function (string $chapterKey, string $lessonKey, array $lesson): ?string {
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
        <div x-data="{ openSection: 0 }">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                {{-- CONTENU PRINCIPAL --}}
                <main class="lg:col-span-8 space-y-8">

                    {{-- En-tête --}}
                    <header>
                        @if (!empty($currentModule['is_under_construction']))
                            <div class="mb-4 rounded-[18px] border border-amber-200 bg-amber-50 px-5 py-4">
                                <p class="text-xs font-black uppercase tracking-[0.22em] text-amber-700">
                                    {{ $currentModule['construction_label'] ?? 'En cours de construction' }}
                                </p>
                                <p class="mt-2 text-sm leading-6 text-amber-900">
                                    {{ $currentModule['construction_note'] ?? 'Ce module est en cours de construction.' }}
                                </p>
                            </div>
                        @endif

                        <p class="mb-2 text-sm font-varela uppercase tracking-[0.24em] text-orangeone">
                            {{ $currentModule['label'] }}
                            @if (!empty($currentModule['status_label']))
                                <span class="text-slate-400">·</span>
                                <span class="font-varela text-slate-400">{{ $currentModule['status_label'] }}</span>
                            @endif
                        </p>
                        <h1 class="font-raleway text-2xl font-semibold leading-tight text-bleuone md:text-3xl">
                            {{ $currentModule['title'] }}
                        </h1>
                        @if ($fullTitle && $fullTitle !== $currentModule['title'])
                            <p class="mt-2 font-lisible text-base leading-7 text-slate-500">
                                {{ $fullTitle }}
                            </p>
                        @endif
                    </header>

                    {{-- Onglets --}}
                    <div x-data="{ activeTab: 'presentation' }">
                        <div class="border-b border-gray-200">
                            <nav class="flex space-x-6" aria-label="Tabs">
                                @foreach (['presentation' => 'Présentation', 'objectifs' => 'But du module', 'prerequis' => 'Prérequis'] as $tabId => $tabLabel)
                                    <button
                                        @click="activeTab = '{{ $tabId }}'"
                                        :class="activeTab === '{{ $tabId }}' ? 'border-orangeone text-orangeone' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                        class="cursor-pointer whitespace-nowrap border-b-2 px-1 py-3 font-varela text-base outline-none transition-all"
                                    >
                                        {{ $tabLabel }}
                                    </button>
                                @endforeach
                            </nav>
                        </div>

                        <div class="mb-8 mt-6 min-h-[100px]">
                            <div x-show="activeTab === 'presentation'" x-cloak class="space-y-4 font-lisible text-base leading-8 text-slate-700">
                                @foreach ($currentModule['presentation'] as $paragraph)
                                    <p>{{ $paragraph }}</p>
                                @endforeach
                            </div>

                            <div x-show="activeTab === 'objectifs'" x-cloak>
                                <div class="mb-5 rounded-2xl border border-orange-100 bg-orange-50/60 p-5">
                                    <p class="font-varela text-base uppercase tracking-[0.18em] text-orangeone">But du module</p>
                                    <p class="mt-2 font-raleway text-lg font-semibold leading-8 text-bleuone">{{ $specificObjective }}</p>
                                </div>
                                <ul class="space-y-3">
                                    @foreach ($currentModule['goals'] as $goal)
                                        <li class="flex items-start gap-3">
                                            <svg class="mt-0.5 size-5 flex-shrink-0 text-orangeone" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span class="font-lisible text-base leading-8 text-slate-700">{{ $goal }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            <div x-show="activeTab === 'prerequis'" x-cloak class="rounded-[24px] border border-[#E7EEF3] bg-[#f8f7fa] p-5">
                                <h4 class="mb-3 flex items-center gap-2 font-varela text-base text-bleuone">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Prérequis recommandés
                                </h4>
                                <div class="space-y-2 pl-6 font-lisible text-base leading-8 text-slate-600">
                                    @foreach ($currentModule['prerequisites'] as $prerequisite)
                                        <p>{{ $prerequisite }}</p>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Programme --}}
                    <section>
                        <h2 class="mb-5 flex items-center gap-3 font-varela text-lg font-normal text-bleuone">
                            <span class="flex size-8 items-center justify-center rounded-lg bg-orangeone text-white shadow-md">
                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </span>
                            Programme du module
                        </h2>

                        <div class="space-y-3">

                            @if ($chapterCount === 0)
                                <div class="overflow-hidden rounded-[28px] border border-gray-200 bg-white shadow-sm">
                                    <div class="p-6">
                                        <p class="font-varela text-sm uppercase tracking-[0.24em] text-orangeone">Préparation</p>
                                        <h3 class="mt-3 font-raleway text-base font-semibold text-bleuone md:text-lg">Le détail du module est en préparation</h3>
                                        <p class="mt-4 font-lisible text-sm leading-7 text-slate-600">
                                            Le contenu sera ajouté dès que les chapitres, les leçons et les contenus interactifs seront disponibles.
                                        </p>
                                    </div>
                                </div>
                            @else
                                @foreach ($currentModule['chapters'] as $chapterKey => $chapter)
                                    @php
                                        $chapterLessons = $chapter['lessons'] ?? [];
                                        $chapterActivityCount = 0;
                                        $chapterCompletedActivityCount = 0;
                                        $chapterTip = $chapter['tip'] ?? null;

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
                                    <div class="overflow-hidden rounded-[28px] border border-gray-200 bg-white shadow-sm transition-shadow hover:shadow-md">
                                        <button
                                            @click="openSection = (openSection === {{ $loop->index }} ? -1 : {{ $loop->index }})"
                                            class="group w-full flex items-start justify-between gap-4 p-4 text-left transition-colors hover:bg-gray-50"
                                        >
                                            <div class="min-w-0">
                                                <h3
                                                    class="font-raleway text-base font-semibold text-bleuone"
                                                    data-parcours-tooltip="{{ $chapter['pedagogical_label'] ?? 'Objectif pédagogique' }}"
                                                >
                                                    {{ $chapter['title'] }}
                                                </h3>
                                                <p class="mt-2 font-lisible text-sm leading-6 text-slate-600">{{ $chapter['objective'] }}</p>
                                            </div>
                                            <div class="flex shrink-0 items-center gap-3">
                                                <span class="hidden text-right font-varela text-xs text-gray-400 sm:inline-block">
                                                    {{ $chapter['lesson_count'] }} leçon{{ $chapter['lesson_count'] > 1 ? 's' : '' }}
                                                    @if ($chapterActivityCount > 0)
                                                        <br>{{ $chapterActivityCount }} étape{{ $chapterActivityCount > 1 ? 's' : '' }}
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

                                        <div class="h-1.5 w-full bg-gray-100">
                                            <div class="h-1.5 bg-orangeone transition-all duration-700" style="width: {{ $chapterProgress }}%"></div>
                                        </div>

                                        <div x-show="openSection === {{ $loop->index }}" x-cloak class="border-t border-gray-100">

                                            @if ($chapterTip)
                                                <div class="mx-4 mt-4 flex items-start gap-3 rounded-[14px] border border-orange-100 bg-orange-50/60 px-4 py-3">
                                                    <svg class="mt-0.5 size-4 shrink-0 text-orangeone" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.355a7.5 7.5 0 0 1-3 0M12 3v1.5m0-1.5a9 9 0 0 1 9 9c0 2.568-1.072 4.886-2.802 6.576M12 3a9 9 0 0 0-9 9c0 2.568 1.072 4.886 2.802 6.576" />
                                                    </svg>
                                                    <p class="text-xs leading-5 text-orange-800">{{ $chapterTip }}</p>
                                                </div>
                                            @endif

                                            @foreach ($chapterLessons as $lessonKey => $lesson)
                                                @php
                                                    $isBilan = ($lesson['type'] ?? 'objectif') === 'bilan';
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
                                                    $openLabel = $isActivityCompleted ? 'Reprendre' : 'Commencer';
                                                @endphp
                                                <a
                                                    href="{{ $lesson['url'] }}"
                                                    class="group flex items-start justify-between gap-4 border-b border-gray-50 p-4 pl-6 transition-colors last:border-0 hover:bg-orange-50/50"
                                                >
                                                    <div class="flex min-w-0 items-start gap-4">
                                                        <div class="pt-1 transition-colors group-hover:text-orangeone
                                                            {{ $isBilan ? 'text-bleuone' : ($isActivityCompleted ? 'text-vertone' : 'text-gray-300') }}"
                                                        >
                                                            @if ($isActivityCompleted)
                                                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                            @else
                                                                <svg class="size-4" fill="currentColor" viewBox="0 0 24 24">
                                                                    <path d="M8 5v14l11-7z" />
                                                                </svg>
                                                            @endif
                                                        </div>
                                                        <div class="min-w-0">
                                                            @if ($isBilan)
                                                                <div class="flex flex-wrap items-center gap-2">
                                                                    <span class="rounded-full border border-bleuone/20 bg-bleuone/5 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-bleuone">
                                                                        Bilan
                                                                    </span>
                                                                </div>
                                                            @endif
                                                            <span class="{{ $isBilan ? 'mt-2' : '' }} block text-sm font-semibold text-gray-700 transition-colors group-hover:text-bleuone">
                                                                {{ $lesson['title'] }}
                                                            </span>
                                                            <p class="mt-1 text-sm leading-6 text-slate-500">{{ $lesson['objective'] }}</p>
                                                            <div class="mt-3 flex flex-wrap gap-2">
                                                                <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-[11px] font-bold text-slate-500">
                                                                    {{ $lesson['scorm_slot_label'] ?? 'Contenu de leçon' }}
                                                                </span>
                                                                @unless ($isBilan)
                                                                    <span class="rounded-full border {{ $activityPillClass }} px-3 py-1 text-[11px] font-bold">
                                                                        {{ $activityPillLabel }}
                                                                    </span>
                                                                @endunless
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="shrink-0 whitespace-nowrap text-sm font-medium transition-colors group-hover:text-orangeone
                                                        {{ $isActivityCompleted ? 'text-vertone' : 'text-slate-400' }}"
                                                    >
                                                        {{ $openLabel }}
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

                {{-- ASIDE --}}
                <aside class="lg:col-span-4 lg:sticky lg:top-8 space-y-6">
                    <div class="overflow-hidden rounded-[24px] border border-gray-100 bg-white shadow-xl shadow-gray-200/50">

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
                        @elseif (!empty($currentModule['illustration_path']))
                            <div class="border-b border-gray-100 bg-gradient-to-br from-bleuone/5 via-white to-orange-50/30 px-6 pt-6 pb-0">
                                <p class="font-varela text-sm uppercase tracking-[0.24em] text-orangeone">
                                    {{ $currentModule['label'] }}
                                </p>
                                <p class="mt-1 font-raleway text-lg font-semibold leading-snug text-bleuone">
                                    {{ $currentModule['title'] }}
                                </p>
                                <img
                                    src="{{ asset($currentModule['illustration_path']) }}"
                                    alt="Illustration {{ $currentModule['title'] }}"
                                    class="mt-4 w-full"
                                >
                            </div>
                        @else
                            <div class="border-b border-gray-100 bg-gradient-to-br from-bleuone/5 via-white to-orange-50/40 px-6 py-5">
                                <p class="font-varela text-sm uppercase tracking-[0.24em] text-orangeone">
                                    {{ $currentModule['label'] }}
                                </p>
                                @if (!empty($currentModule['is_under_construction']))
                                    <span class="mt-1 inline-flex rounded-full border border-amber-200 bg-amber-50 px-3 py-0.5 font-varela text-xs text-amber-700">
                                        {{ $currentModule['construction_label'] ?? 'En cours de construction' }}
                                    </span>
                                @endif
                                <p class="mt-1 font-raleway text-lg font-semibold leading-snug text-bleuone">
                                    {{ $currentModule['title'] }}
                                </p>
                                @if ($fullTitle && $fullTitle !== $currentModule['title'])
                                    <p class="mt-1 font-lisible text-xs leading-5 text-slate-500">
                                        {{ $fullTitle }}
                                    </p>
                                @endif
                            </div>
                        @endif

                        <div class="p-6">
                            <div class="mb-6">
                                <div class="mb-2 flex items-end justify-between">
                                    <span class="font-varela text-xs uppercase tracking-[0.2em] text-gray-500">Votre avancée</span>
                                    <span class="font-varela text-lg font-bold text-orangeone">{{ $progressPercentage }}%</span>
                                </div>
                                <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
                                    <div class="h-full rounded-full bg-orangeone transition-all duration-1000 ease-out" style="width: {{ $progressPercentage }}%"></div>
                                </div>
                                @if ($activityCount > 0)
                                    <p class="mt-1.5 text-xs text-slate-400">
                                        {{ $completedActivityCount }} / {{ $activityCount }} étape{{ $activityCount > 1 ? 's' : '' }} complétée{{ $completedActivityCount > 1 ? 's' : '' }}
                                    </p>
                                @endif
                            </div>

                            <a href="{{ $ctaUrl }}" class="btn-oneduc w-full !rounded-full !px-6 !py-3.5 !text-base !font-varela !font-bold">
                                {{ $currentModule['cta_label'] }}
                            </a>

                            <div class="mt-8 space-y-5 border-t border-gray-100 pt-6">
                                <div class="flex items-center gap-4">
                                    <div class="flex size-10 items-center justify-center rounded-full bg-orangeone text-sm font-bold text-white shadow-md">
                                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($currentModule['trainer_name'], 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-varela text-xs uppercase tracking-[0.2em] text-gray-400">Formateur</p>
                                        <p class="font-varela font-bold text-slate-800">{{ $currentModule['trainer_name'] }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-4">
                                    <div class="flex size-10 items-center justify-center rounded-lg bg-bleuone/5 text-bleuone">
                                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-varela text-xs uppercase tracking-[0.2em] text-gray-400">Durée estimée</p>
                                        <p class="font-varela font-bold text-slate-800">{{ $currentModule['duration_label'] }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-4">
                                    <div class="flex size-10 items-center justify-center rounded-lg bg-bleuone/5 text-bleuone">
                                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-varela text-xs uppercase tracking-[0.2em] text-gray-400">Niveau</p>
                                        <p class="font-varela font-bold text-slate-800">{{ $currentModule['level_label'] }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-4">
                                    <div class="flex size-10 items-center justify-center rounded-lg bg-bleuone/5 text-bleuone">
                                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-varela text-xs uppercase tracking-[0.2em] text-gray-400">Contenu</p>
                                        <p class="font-varela font-bold text-slate-800">{{ $chapterCount }} chapitre{{ $chapterCount > 1 ? 's' : '' }}</p>
                                        <p class="mt-1 font-varela text-xs text-slate-500">
                                            {{ $lessonCount }} leçon{{ $lessonCount > 1 ? 's' : '' }}
                                            @if ($activityCount > 0)
                                                · {{ $activityCount }} activité{{ $activityCount > 1 ? 's' : '' }}
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

    @if (!empty($currentModule['is_under_construction']))
        <div
            x-data="{ open: true }"
            x-show="open"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            @keydown.escape.window="open = false"
            role="dialog"
            aria-modal="true"
            aria-labelledby="construction-modal-title"
        >
            <div
                class="absolute inset-0 bg-bleuone/60 backdrop-blur-sm"
                @click="open = false"
                aria-hidden="true"
            ></div>

            <div class="relative z-10 w-full max-w-md rounded-[24px] bg-white p-8 shadow-[0_32px_64px_-24px_rgba(0,68,97,0.35)]">
                <div class="mx-auto mb-6 flex size-14 items-center justify-center rounded-full border border-amber-200 bg-amber-50">
                    <svg class="size-7 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </div>

                <p id="construction-modal-title" class="text-center text-xs font-black uppercase tracking-[0.22em] text-amber-700">
                    {{ $currentModule['construction_label'] ?? 'En cours de construction' }}
                </p>
                <h2 class="mt-3 text-center font-raleway text-2xl font-semibold text-bleuone">
                    {{ $currentModule['label'] }} — {{ $currentModule['title'] }}
                </h2>
                <p class="mt-4 text-center text-sm leading-7 text-slate-600">
                    {{ $currentModule['construction_note'] ?? 'Ce module est en cours de construction. Certains contenus peuvent encore être ajustés.' }}
                </p>
                <p class="mt-3 text-center text-sm leading-7 text-slate-600">
                    Pour l'instant, seul le <strong class="font-semibold text-bleuone">Module 2 — Mettre en place un environnement de formation</strong> est entièrement disponible.
                </p>

                <div class="mt-8 flex flex-col gap-3">
                    <a
                        href="{{ route('formateur.parcours.modules.show', ['module' => 'organiser-ses-parcours']) }}"
                        class="btn-oneduc w-full !rounded-full !px-6 !py-3 text-center"
                    >
                        Accéder au Module 2
                    </a>
                    <button
                        type="button"
                        @click="open = false"
                        class="btn-oneduc-outline w-full !rounded-full !px-6 !py-3"
                    >
                        Rester sur cette page
                    </button>
                </div>
            </div>
        </div>
    @endif

@endsection
