@php
    $chapterPosition = 0;
    $moduleChapterCount = $currentModule['chapter_count'] ?? count($currentModule['chapters'] ?? []);
	    $moduleLessonCount = $currentModule['lesson_count'] ?? 0;
	    $activityStatusMap = $activityStatusMap ?? [];
	    $activeActivityKey = $activeActivityKey ?? null;
	    $initialOpenChapterKey = ($activeChapterKey ?? null) ?: array_key_first($currentModule['chapters'] ?? []);

    $statusIcon = function (bool $completed, bool $inProgress): array {
        if ($completed) {
            return ['icon' => '✗', 'class' => 'text-vertone', 'label' => 'Validee'];
        }

        if ($inProgress) {
            return ['icon' => '⏳', 'class' => 'text-orangeone', 'label' => 'En cours'];
        }

        return ['icon' => '✗', 'class' => 'text-gray-300 opacity-60', 'label' => 'Non commence'];
    };
@endphp

<aside
    x-show="sidebarOpen"
    x-cloak
    x-transition:enter="transition duration-300 ease-out"
    x-transition:enter-start="-translate-x-8 opacity-0"
    x-transition:enter-end="translate-x-0 opacity-100"
    x-transition:leave="transition duration-240 ease-in"
    x-transition:leave-start="translate-x-0 opacity-100"
    x-transition:leave-end="-translate-x-8 opacity-0"
    class="flex w-full flex-shrink-0 flex-col rounded-[24px] border-r border-gray-100 bg-white shadow-sm lg:sticky lg:top-6 lg:w-80"
>
	    <div
	        x-data="{ openSidebarChapter: @js($initialOpenChapterKey) }"
	        class="flex-1 overflow-y-auto custom-scrollbar bg-gray-50/30"
	    >
        <div class="px-3 py-4">
            <div class="mb-4 rounded-2xl border border-orange-100 bg-white p-4 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-[0.22em] text-orangeone">
                    {{ $currentModule['label'] }}
                </p>

                <a href="{{ $currentModule['url'] }}" class="mt-2 block">
                    <h2 class="text-lg font-black leading-tight text-bleuone">
                        {{ $currentModule['title'] }}
                    </h2>

                    <p class="mt-2 text-[11px] font-semibold text-slate-500">
                        {{ $moduleChapterCount }} chapitre{{ $moduleChapterCount > 1 ? 's' : '' }}
                        ·
                        {{ $moduleLessonCount }} lecon{{ $moduleLessonCount > 1 ? 's' : '' }}
                    </p>
                </a>
            </div>

            <ol class="space-y-3">
                @foreach ($currentModule['chapters'] as $chapterKey => $chapter)
                    @php
                        $chapterPosition++;
                        $isActiveChapter = ($activeChapterKey ?? null) === $chapterKey;
                        $hasActiveLessonInChapter = $isActiveChapter && !empty($activeLessonKey);
                        $completedLabel = '0/' . $chapter['lesson_count'] . ' lecon' . ($chapter['lesson_count'] > 1 ? 's' : '') . ' terminee' . ($chapter['lesson_count'] > 1 ? 's' : '');
                    @endphp

	                    <li class="bg-white rounded-xl border border-gray-100 overflow-hidden shadow-sm">
	                        <button
	                            type="button"
	                            @click="openSidebarChapter = openSidebarChapter === '{{ $chapterKey }}' ? null : '{{ $chapterKey }}'"
	                            class="col-span-2 grid w-full items-center py-4 text-left border-l-4 transition {{ $isActiveChapter ? ($hasActiveLessonInChapter ? 'bg-blue-50/40 border-bleuone' : 'bg-orange-50 border-orangeone') : 'hover:bg-gray-50 border-transparent' }}"
	                            style="grid-template-columns: 44px 1fr 42px;"
	                            :aria-expanded="(openSidebarChapter === '{{ $chapterKey }}').toString()"
	                            aria-controls="sidebar-chapter-{{ $chapterKey }}"
	                        >
	                            <div class="flex justify-center">
	                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-black border {{ $isActiveChapter ? ($hasActiveLessonInChapter ? 'bg-bleuone text-white border-bleuone' : 'bg-orangeone text-white border-orangeone') : 'bg-gray-100 text-gray-500 border-gray-200' }}">
	                                    {{ $chapterPosition }}
	                                </div>
	                            </div>

                            <div class="min-w-0 pr-4 relative">
	                                <p
	                                    class="text-[10px] font-black uppercase tracking-wide {{ $isActiveChapter ? ($hasActiveLessonInChapter ? 'text-bleuone' : 'text-orangeone') : 'text-orangeone' }}"
	                                    data-parcours-tooltip="{{ $chapter['pedagogical_label'] ?? 'Objectif pedagogique' }}"
	                                >
	                                    Chapitre
	                                </p>
	                                <h3
	                                    class="mt-1 text-[14px] font-bold leading-tight truncate max-w-[220px] {{ $isActiveChapter ? ($hasActiveLessonInChapter ? 'text-bleuone' : 'text-orangeone') : 'text-bleuone' }}"
	                                    title="{{ $chapter['title'] }}"
	                                    data-parcours-tooltip="{{ $chapter['pedagogical_label'] ?? 'Objectif pedagogique' }}"
	                                >
                                    {{ $chapter['title'] }}
                                </h3>

	                                <span class="text-[11px] font-bold text-gray-400 mt-1 block italic">
	                                    {{ $completedLabel }}
	                                </span>
	                            </div>
	                            <div class="flex justify-center pr-3">
	                                <span
	                                    class="flex h-8 w-8 items-center justify-center rounded-full border border-orange-100 bg-orange-50 text-orangeone transition"
	                                    :class="openSidebarChapter === '{{ $chapterKey }}' ? 'bg-orangeone text-white border-orangeone' : ''"
	                                >
	                                    <svg
	                                        :class="openSidebarChapter === '{{ $chapterKey }}' ? 'rotate-180' : ''"
	                                        class="h-5 w-5 transition-transform duration-200"
	                                        fill="none"
	                                        viewBox="0 0 24 24"
	                                        stroke="currentColor"
	                                        stroke-width="2.5"
	                                    >
	                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
	                                    </svg>
	                                </span>
	                            </div>
	                        </button>
	
	                        <div
	                            id="sidebar-chapter-{{ $chapterKey }}"
	                            x-show="openSidebarChapter === '{{ $chapterKey }}'"
	                            x-collapse
	                            x-cloak
	                            class="col-span-2 border-t border-gray-50 bg-white"
	                        >
	                            <ul class="py-1">
	                                @foreach ($chapter['lessons'] as $lessonKey => $lesson)
	                                    @php
	                                        $hasActivity = !empty($lesson['activity_page']);
	                                        $hasCompletionActivity = !empty($lesson['completion_activity_key']);
	                                        $hasActivitySlot = $hasActivity || $hasCompletionActivity;
	                                        $isBilan = ($lesson['type'] ?? 'objectif') === 'bilan';
	                                        $lessonTypeLabel = $isBilan ? 'Bilan' : 'Objectif operationnel';
	                                        $activityKey = $lesson['activity_page']['key'] ?? ($lesson['completion_activity_key'] ?? null);
	                                        $activityStatusKey = $hasActivitySlot && $activityKey
	                                            ? implode('.', [$chapterKey, $lessonKey, $activityKey])
	                                            : null;
                                        $isActivityCompleted = $activityStatusKey
                                            ? (($activityStatusMap[$activityStatusKey] ?? false) === true)
                                            : false;
	                                        $isActiveActivity = $hasActivity
	                                            && ($activeLessonKey ?? null) === $lessonKey
	                                            && $activeActivityKey === $activityKey;
                                        $isActiveLesson = ($activeLessonKey ?? null) === $lessonKey && !$isActiveActivity;
                                        $lessonStatusIcon = $statusIcon($isActivityCompleted, $isActiveLesson || $isActiveActivity);
	                                        $lessonStateText = $isActivityCompleted
	                                            ? 'Activite : validee'
	                                            : ($isActiveActivity
	                                                ? 'Activite : en cours'
	                                                : ($isActiveLesson ? 'Contenu de lecon : en cours' : 'Contenu de lecon'));
                                        $lessonStateClass = $isActivityCompleted
                                            ? 'text-vertone'
                                            : (($isActiveLesson || $isActiveActivity) ? 'text-orangeone' : 'text-gray-500');
	                                        $activityStatusIcon = $statusIcon($isActivityCompleted, $isActiveActivity);
	                                        $activitySlotLabel = $isActivityCompleted ? 'Activite validee' : 'Activite';
	                                    @endphp

                                    <li class="space-y-1">
                                        <a
                                            href="{{ $lesson['url'] }}"
                                            class="block py-3 px-3 transition-all border-l-4 {{ $isActiveActivity ? 'bg-blue-50/40 border-bleuone' : ($isActiveLesson ? 'bg-orange-50 border-orangeone' : 'hover:bg-gray-50 border-transparent') }}"
                                            aria-current="{{ ($isActiveLesson || $isActiveActivity) ? 'page' : 'false' }}"
                                        >
                                            <div class="flex items-start gap-2">
                                                <div class="w-6 flex justify-center pt-[2px]">
                                                    <span class="text-[16px] font-black {{ $lessonStatusIcon['class'] }}" aria-label="{{ $lessonStatusIcon['label'] }}">
                                                        {{ $lessonStatusIcon['icon'] }}
                                                    </span>
                                                </div>

                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-start justify-between gap-2">
                                                        <span
                                                            class="block text-[14px] font-bold leading-snug truncate {{ $isActiveLesson ? 'text-orangeone' : 'text-gray-700' }}"
                                                            title="{{ $lesson['title'] }}"
                                                            data-parcours-tooltip="{{ $lessonTypeLabel }}"
                                                        >
	                                                            {{ $lesson['title'] }}
	                                                        </span>
                                                    </div>

                                                    <div class="mt-1 flex items-center justify-between gap-2">
                                                        <span class="text-[11px] font-bold {{ $isBilan ? 'text-bleuone' : $lessonStateClass }}">
                                                            {{ $isBilan ? 'Bilan' : $lessonStateText }}
                                                        </span>

                                                        <span class="text-[11px] font-black text-gray-500 tabular-nums" aria-label="Durée de la leçon">
                                                            {{ $lesson['duration_label'] }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>

	                                        @if ($hasActivitySlot)
	                                            @if ($hasActivity)
	                                            <a
	                                                href="{{ $lesson['activity_page']['url'] }}"
	                                                class="ml-6 block py-2 px-3 transition-all border-l-4 {{ $isActiveActivity ? 'bg-orange-50 border-orangeone' : 'hover:bg-gray-50 border-transparent' }}"
                                                aria-current="{{ $isActiveActivity ? 'page' : 'false' }}"
                                            >
                                                <div class="flex items-center gap-2">
                                                    <div class="w-6 flex justify-center">
                                                        <span class="text-[16px] font-black {{ $activityStatusIcon['class'] }}" aria-label="{{ $activityStatusIcon['label'] }}">
                                                            {{ $activityStatusIcon['icon'] }}
                                                        </span>
                                                    </div>

                                                    <div class="min-w-0 flex-1">
	                                                        <span class="block text-[13px] font-bold leading-snug {{ $isActiveActivity ? 'text-orangeone' : 'text-gray-700' }}">
		                                                            {{ $activitySlotLabel }}
	                                                        </span>
	                                                    </div>
	                                                </div>
	                                            </a>
	                                            @else
	                                            <div class="ml-6 block py-2 px-3 border-l-4 border-transparent">
	                                                <div class="flex items-center gap-2">
	                                                    <div class="w-6 flex justify-center">
	                                                        <span class="text-[16px] font-black {{ $activityStatusIcon['class'] }}" aria-label="{{ $activityStatusIcon['label'] }}">
	                                                            {{ $activityStatusIcon['icon'] }}
	                                                        </span>
	                                                    </div>
	
	                                                    <div class="min-w-0 flex-1">
	                                                        <span class="block text-[13px] font-bold leading-snug {{ $isActivityCompleted ? 'text-vertone' : 'text-gray-400' }}">
		                                                            {{ $activitySlotLabel }}
	                                                        </span>
	                                                    </div>
	                                                </div>
	                                            </div>
	                                            @endif
	                                        @elseif (!$isBilan)
                                            <div class="ml-6 block py-2 px-3 border-l-4 border-transparent">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-6 flex justify-center">
                                                        <span class="text-[16px] font-black text-gray-300" aria-label="Activite a creer">✗</span>
                                                    </div>

                                                    <div class="min-w-0 flex-1">
                                                        <span class="block text-[13px] font-bold leading-snug text-gray-400">
                                                            Activite a creer
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>

    <div class="p-4 bg-white border-t border-gray-100 space-y-3">
        <a
            href="{{ route('formateur.dashboard') }}"
            class="flex items-center gap-3 p-4 rounded-xl bg-bleuone text-white hover:opacity-95 transition-opacity group shadow-md"
        >
            <div class="bg-white/10 p-2 rounded-lg group-hover:bg-orangeone transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
            </div>
            <div>
                <p class="text-[11px] font-black uppercase tracking-widest leading-none">Aide</p>
                <p class="text-[10px] text-white/70 mt-1">Support formateur</p>
                            </div>
        </a>
    </div>
</aside>
