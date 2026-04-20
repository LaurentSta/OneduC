@extends('formateur.parcours.layout')

@php
    $firstLesson = array_values($currentChapter['lessons'])[0] ?? null;
@endphp

@section('parcours_content')
    <div class="px-4 sm:px-6 lg:px-8 pt-4">
        <x-formateur.hierarchy-breadcrumb
            :module="['label' => 'Module', 'title' => $currentModule['title'], 'url' => $currentModule['url']]"
            :chapter="['label' => $currentChapter['label'] ?? 'Chapitre', 'title' => $currentChapter['title'], 'url' => null]"
        />
    </div>
    <div
        x-data="{ sidebarOpen: window.innerWidth >= 1024 }"
        class="grid items-start gap-6 lg:grid-cols-[24rem_minmax(0,1fr)]"
    >
        @include('formateur.parcours.partials.sidebar')

        <main id="lesson-shell-main" class="min-w-0">
            <div class="px-0 lg:px-0 py-0">
                <main class="w-full px-4 sm:px-6 lg:px-8 py-8">
                    <div class="mb-8 border-b border-gray-100 pb-6">
                        <h1 class="text-3xl md:text-4xl font-raleway font-medium text-bleuone leading-tight">
                            {{ $currentChapter['label'] }} - {{ $currentChapter['title'] }}
                        </h1>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                        <div class="lg:col-span-7 space-y-6" x-data="{ openObjectives: true, openQuestions: false }">
                            <div
                                class="overflow-hidden rounded-[28px] border bg-white transition-all duration-300"
                                :class="openObjectives ? 'border-orangeone shadow-lg shadow-orange-100' : 'border-gray-100 shadow-sm'"
                            >
                                <button
                                    type="button"
                                    @click="openObjectives = !openObjectives"
                                    class="w-full flex items-center justify-between p-6 text-left group"
                                >
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="size-11 rounded-full flex items-center justify-center transition-colors"
                                            :class="openObjectives ? 'bg-orangeone text-white' : 'bg-orange-50 text-orangeone group-hover:bg-orangeone group-hover:text-white'"
                                        >
                                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                                            </svg>
                                        </div>
                                        <span class="text-lg font-bold text-bleuone">Objectifs du chapitre</span>
                                    </div>

                                    <div
                                        class="size-8 rounded-full border border-gray-100 flex items-center justify-center text-gray-400 transition-transform duration-300"
                                        :class="openObjectives ? 'rotate-180 bg-gray-50 text-bleuone' : 'rotate-0'"
                                    >
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </button>

                                <div x-show="openObjectives" x-collapse>
                                    <div class="p-5 pt-0 text-gray-600 leading-relaxed">
                                        <div class="space-y-8">
                                            @foreach ($currentChapter['lessons'] as $lesson)
                                                <div class="pl-4 border-l-2 border-orange-100">
                                                    <h4 class="mb-3 text-sm font-bold uppercase tracking-wide text-bleuone">
                                                        {{ \Illuminate\Support\Str::upper($lesson['title']) }}
                                                    </h4>
                                                    <ul class="space-y-2">
                                                        <li class="flex items-start gap-3 text-sm md:text-base">
                                                            <svg class="size-5 text-green-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                            </svg>
                                                            <span>{{ $lesson['objective'] }}</span>
                                                        </li>
                                                    </ul>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="overflow-hidden rounded-[28px] border bg-white transition-all duration-300"
                                :class="openQuestions ? 'border-orangeone shadow-lg shadow-orange-100' : 'border-gray-100 shadow-sm'"
                            >
                                <button
                                    type="button"
                                    @click="openQuestions = !openQuestions"
                                    class="w-full flex items-center justify-between p-6 text-left group"
                                >
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="size-11 rounded-full flex items-center justify-center transition-colors"
                                            :class="openQuestions ? 'bg-bleuone text-white' : 'bg-blue-50 text-bleuone group-hover:bg-bleuone group-hover:text-white'"
                                        >
                                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <span class="text-lg font-bold text-bleuone">Les questions que l'on se pose souvent</span>
                                    </div>

                                    <div
                                        class="size-8 rounded-full border border-gray-100 flex items-center justify-center text-gray-400 transition-transform duration-300"
                                        :class="openQuestions ? 'rotate-180 bg-gray-50 text-bleuone' : 'rotate-0'"
                                    >
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </button>

                                <div x-show="openQuestions" x-collapse>
                                    <div class="p-5 pt-0 text-gray-600 leading-relaxed">
                                        <div class="prose prose-sm max-w-none prose-p:text-gray-600 prose-li:text-gray-600 prose-strong:text-bleuone">
                                            <p>{{ $currentChapter['description'] }}</p>
                                            <p>{{ $currentChapter['objective'] }}</p>
                                            <ul>
                                                @foreach ($currentChapter['lessons'] as $lesson)
                                                    <li>{{ $lesson['title'] }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="lg:col-span-5 lg:sticky lg:top-8 space-y-6">
                            <div class="bg-white rounded-[24px] shadow-2xl shadow-gray-200/50 border border-gray-100 overflow-hidden p-2">
                                <div class="relative w-full rounded-2xl overflow-hidden bg-black shadow-inner group aspect-video">
                                    <div class="absolute inset-0 bg-gradient-to-br from-slate-100 via-white to-slate-100"></div>
                                    <div class="relative flex h-full items-center justify-center px-6 py-8">
                                        <div class="grid w-full grid-cols-[110px_minmax(0,1fr)] items-center gap-4">
                                            <div class="flex h-[160px] items-center justify-center rounded-[24px] bg-white shadow-[0_18px_40px_-24px_rgba(0,68,97,0.35)]">
                                                <div class="px-4 text-center">
                                                    <p class="text-sm font-bold uppercase tracking-[0.18em] text-orangeone">On educ</p>
                                                    <p class="mt-3 text-2xl font-black leading-8 text-bleuone">{{ $currentChapter['label'] }}</p>
                                                </div>
                                            </div>
                                            <div>
                                                <p class="text-4xl font-light tracking-tight text-bleuone">{{ $currentChapter['title'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if ($firstLesson)
                                    <div class="p-4">
                                        <a
                                            href="{{ $firstLesson['url'] }}"
                                            class="flex items-center justify-center w-full py-4 rounded-xl bg-orangeone text-white font-black text-lg hover:bg-orange-600 transition-all shadow-lg shadow-orange-100 hover:-translate-y-0.5 group"
                                        >
                                            <span>Commencer le chapitre</span>
                                            <svg class="ml-2 size-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                            </svg>
                                        </a>
                                    </div>
                                @endif
                            </div>

                            <div class="bg-blue-50/50 rounded-2xl p-4 border border-blue-100/50 flex items-start gap-3">
                                <svg class="size-6 text-bleuone mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="text-sm text-bleuone/80">
                                    <span class="font-bold block text-bleuone">Le saviez-vous ?</span>
                                    Vous pouvez ouvrir ce chapitre comme un stagiaire, tout en gardant votre logique formateur pour preparer ensuite les contenus et les ressources.
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </main>
    </div>
@endsection
