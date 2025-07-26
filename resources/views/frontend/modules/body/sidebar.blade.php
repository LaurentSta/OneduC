{{-- SIDEBAR --}}
<aside class="w-full md:w-64 bg-white border-r p-6 overflow-y-auto h-full">
    <h2 class="text-2xl font-raleway font-bold text-bleuone mb-6">{{ $module->module_title }}</h2>

    @foreach ($module->sections as $section)
        <div class="mb-6">
            {{-- Titre de la section --}}
            <h3 class="text-base text-gray-700 font-semibold mb-1 flex items-center justify-between">
                <a href="{{ route('stagiaire.module.section', ['module' => $module->id, 'section' => $section->id]) }}"
                   class="hover:underline hover:text-orangeone transition-all">
                    {{ $section->section_title }}
                </a>

                @if(isset($sectionStatuses[$section->id]))
                    <span class="text-sm">
                        @switch($sectionStatuses[$section->id])
                            @case('completed') <span class="text-green-500 font-semibold">✔ Terminé</span> @break
                            @case('in_progress') <span class="text-yellow-500 font-semibold">⏳ En cours</span> @break
                            @default <span class="text-gray-400 font-medium">– Non commencé</span>
                        @endswitch
                    </span>
                @endif
            </h3>

            {{-- Leçons --}}
            <ul class="mt-2 space-y-1">
                @foreach ($section->lectures as $lec)
                    @php
                        $stat = $lectureStats[$lec->id]['status'] ?? 'not_started';
                    @endphp
                    <li>
                        <a href="{{ route('stagiaire.module.lecture', ['module' => $module->id, 'section' => $section->id, 'lesson' => $lec->id]) }}"
                        class="block px-3 py-2 rounded-lg text-sm transition {{ isset($selectedLecture) && $selectedLecture->id == $lec->id ? 'bg-orangeone text-white' : 'hover:bg-gray-100 text-gray-800' }}">
                            <div class="flex justify-between font-varela">
                                <span>{{ $lec->lecture_title }}</span>
                                <span>
                                    @switch($stat)
                                        @case('acquired') <span class="text-green-500">✔</span> @break
                                        @case('not_acquired') <span class="text-red-500">❌</span> @break
                                        @case('incomplete') <span class="text-orange-500">⏳</span> @break
                                        @default <span class="text-gray-400">–</span>
                                    @endswitch
                                </span>
                            </div>
                        </a>
                    </li>
                @endforeach

            </ul>
        </div>
    @endforeach
</aside>
