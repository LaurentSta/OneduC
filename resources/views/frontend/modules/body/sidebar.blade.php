
{{-- SIDEBAR --}}
<aside class="w-64 bg-white border-r p-6 overflow-y-auto">
    <h2 class="text-xl font-bold text-blue-800 mb-4">{{ $module->module_title }}</h2>

    @foreach ($module->sections as $section)
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-gray-700">
    <a href="{{ route('module.section', ['id' => $module->id, 'section_id' => $section->id]) }}"
       class="hover:underline hover:text-blue-600 transition-colors">
        {{ $section->section_title }}
    </a>

    @if(isset($sectionStatuses[$section->id]))
        @if($sectionStatuses[$section->id] === 'completed')
            <span class="text-green-500 ml-2">✔️</span>
        @elseif($sectionStatuses[$section->id] === 'in_progress')
            <span class="text-yellow-500 ml-2">⏳</span>
        @else
            <span class="text-gray-400 ml-2">–</span>
        @endif
    @endif
</h3>

        <ul class="mt-2 space-y-1">
            @foreach ($section->lectures as $lec)
                <li>
                    <a href="{{ route('module.lecture', ['id' => $module->id, 'lecon' => $lec->id]) }}"
                       class="block px-1 py-1 rounded hover:bg-blue-100 text-sm font-medium
                       {{ isset($selectedLecture) && $selectedLecture->id == $lec->id ? 'bg-blue-50 font-semibold' : '' }}">
                        {{ $lec->lecture_title }}
                        @if(isset($lessonStatuses[$lec->id]))
                            @if($lessonStatuses[$lec->id] === 'completed')
                                <span class="text-green-500 ml-1">✅</span>
                            @elseif($lessonStatuses[$lec->id] === 'incomplete')
                                <span class="text-orange-500 ml-1">⏳</span>
                            @else
                                <span class="text-gray-500 ml-1">❌</span>
                            @endif
                        @else
                            <span class="text-gray-400 ml-1">–</span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    </div>


@endforeach
</aside>
