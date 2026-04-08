@php
    $chapterPosition = 0;
@endphp

<aside
    x-cloak
    x-show="sidebarOpen"
    class="w-80 flex-shrink-0 bg-white border-r border-gray-100 flex flex-col rounded-[24px] shadow-sm lg:sticky lg:top-6"
    style="display: none;"
>
    <div class="flex-1 overflow-y-auto custom-scrollbar bg-gray-50/30">
        <div class="px-3 py-4">
        <ol class="space-y-3">
            @foreach ($currentModule['chapters'] as $chapterKey => $chapter)
                @php
                    $chapterPosition++;
                    $isActiveChapter = ($activeChapterKey ?? null) === $chapterKey;
                    $hasActiveLessonInChapter = $isActiveChapter && !empty($activeLessonKey);
                    $completedLabel = '0/' . $chapter['lesson_count'] . ' leçon' . ($chapter['lesson_count'] > 1 ? 's' : '') . ' terminées';
                @endphp

                <li class="bg-white rounded-xl border border-gray-100 overflow-hidden shadow-sm">
                    <a
                        href="{{ $chapter['url'] }}"
                        class="col-span-2 grid items-center py-4 border-l-4 transition {{ $isActiveChapter ? ($hasActiveLessonInChapter ? 'bg-blue-50/40 border-bleuone' : 'bg-orange-50 border-orangeone') : 'hover:bg-gray-50 border-transparent' }}"
                        style="grid-template-columns: 44px 1fr;"
                        aria-current="{{ $isActiveChapter ? 'page' : 'false' }}"
                    >
                        <div class="flex justify-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-black border {{ $isActiveChapter ? ($hasActiveLessonInChapter ? 'bg-bleuone text-white border-bleuone' : 'bg-orangeone text-white border-orangeone') : 'bg-gray-100 text-gray-500 border-gray-200' }}">
                                {{ $chapterPosition }}
                            </div>
                        </div>

                        <div class="min-w-0 pr-4 relative">
                            <h3
                                class="text-[15px] font-bold leading-tight truncate max-w-[220px] {{ $isActiveChapter ? ($hasActiveLessonInChapter ? 'text-bleuone' : 'text-orangeone') : 'text-bleuone' }}"
                                title="{{ $chapter['title'] }}"
                            >
                                Ch. {{ $chapterPosition }} - {{ $chapter['title'] }}
                            </h3>

                            <span class="text-[11px] font-bold text-gray-400 mt-1 block italic">
                                {{ $completedLabel }}
                            </span>
                        </div>
                    </a>

                    <div class="col-span-2 border-t border-gray-50 bg-white">
                        <ul class="py-1">
                            @foreach ($chapter['lessons'] as $lessonKey => $lesson)
                                @php
                                    $isActiveLesson = ($activeLessonKey ?? null) === $lessonKey;
                                    $lessonStatusIcon = $isActiveLesson
                                        ? ['icon' => '⏳', 'class' => 'text-orangeone', 'label' => 'En cours']
                                        : ['icon' => '✗', 'class' => 'text-gray-400', 'label' => 'Non commencé'];
                                    $lessonStateText = $isActiveLesson ? 'Contenu en cours' : 'Contenu : non commencé';
                                @endphp

                                <li>
                                    <a
                                        href="{{ $lesson['url'] }}"
                                        class="block py-3 px-3 transition-all border-l-4 {{ $isActiveLesson ? 'bg-orange-50 border-orangeone' : 'hover:bg-gray-50 border-transparent' }}"
                                        aria-current="{{ $isActiveLesson ? 'page' : 'false' }}"
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
                                                    >
                                                        Leç. {{ $loop->iteration }} - {{ $lesson['title'] }}
                                                    </span>

                                                    @if ($isActiveLesson)
                                                        <span class="text-[10px] font-black uppercase tracking-tighter text-orangeone/70 whitespace-nowrap">
                                                            Lecture en cours
                                                        </span>
                                                    @endif
                                                </div>

                                                <div class="mt-1 flex items-center justify-between gap-2">
                                                    <span class="text-[11px] font-bold {{ $isActiveLesson ? 'text-orangeone' : 'text-gray-500' }}">
                                                        {{ $lessonStateText }}
                                                    </span>

                                                    <span class="text-[11px] font-black text-gray-500 tabular-nums" aria-label="Durée de la leçon">
                                                        {{ $lesson['duration_label'] }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
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
